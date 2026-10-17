<?php
/**
 * ProjectController for Student Workflows
 * Refactored (Phase 9): Eliminated N+1 query patterns via eager loading and batch operations.
 */
class ProjectController extends Controller
{
    /**
     * รายการโครงงานของฉัน (Dashboard นักเรียน)
     * Phase 9: Already uses correlated subquery + GROUP_CONCAT — no N+1 present.
     */
    public function index()
    {
        $userId = Auth::id();

        $projects = Database::fetchAll(
            "SELECT p.*, s.name as subject_name,
                    COALESCE(u_adv.full_name, u_adv.username) as advisor_name,
                    (SELECT GROUP_CONCAT(COALESCE(u2.full_name, u2.username) SEPARATOR ', ')
                     FROM project_members pm2
                     JOIN users u2 ON pm2.student_id = u2.id
                     WHERE pm2.project_id = p.id) as team_members
             FROM projects p
             JOIN project_members pm ON p.id = pm.project_id
             LEFT JOIN project_subjects s ON p.subject_id = s.id
             LEFT JOIN users u_adv ON p.advisor_id = u_adv.id
             WHERE pm.student_id = ?
             GROUP BY p.id
             ORDER BY p.updated_at DESC",
            [$userId]
        );

        $this->view('projects/index', [
            'title' => 'ระบบติดตามโครงงาน | โครงงานของฉัน',
            'projects' => $projects
        ]);
    }

    /**
     * หน้าสร้างโครงงานใหม่ (Proposal)
     */
    public function create()
    {
        $subjects = Database::fetchAll("SELECT * FROM project_subjects WHERE is_active = 1");
        $teachers = Database::fetchAll("SELECT id, COALESCE(full_name, username) as display_name FROM users WHERE role = 'teacher' OR role = 'admin' ORDER BY display_name ASC");

        $this->view('projects/create', [
            'title' => 'เสนอหัวข้อโครงงานใหม่',
            'subjects' => $subjects,
            'teachers' => $teachers
        ]);
    }

    /**
     * บันทึกโครงงานใหม่ลงฐานข้อมูล
     * Phase 9: Batch-inserts teammates instead of per-row INSERT queries.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect('projects');
        }

        CSRF::check();

        $userId = Auth::id();
        $title_th = $_POST['title_th'] ?? '';
        $title_en = $_POST['title_en'] ?? '';
        $project_type = $_POST['project_type'] ?? 'experimental';
        $subject_id = !empty($_POST['subject_id']) ? (int) $_POST['subject_id'] : null;
        $advisor_id = !empty($_POST['advisor_id']) ? (int) $_POST['advisor_id'] : null;
        $abstract_th = $_POST['abstract_th'] ?? '';

        if (empty($title_th) || empty($advisor_id)) {
            Session::flash('error', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
            App::redirect('projects/create');
        }

        try {
            Database::beginTransaction();

            $year = date('Y');
            $code = 'PRJ' . $year . '-' . strtoupper(substr(uniqid(), -4));

            // Insert Project (Resilient Pattern: Try modern schema first, fallback to legacy)
            try {
                $projectId = Database::insert(
                    "INSERT INTO projects (project_code, title_th, title_en, project_type, subject_id, advisor_id, abstract_th, status, academic_year, semester)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'proposal_submitted', ?, 1)",
                    [$code, $title_th, $title_en, $project_type, $subject_id, $advisor_id, $abstract_th, $year]
                );
            } catch (\Exception $e) {
                $projectId = Database::insert(
                    "INSERT INTO projects (project_code, title_th, title_en, project_type, subject_id, advisor_id, abstract_th, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'proposal_submitted')",
                    [$code, $title_th, $title_en, $project_type, $subject_id, $advisor_id, $abstract_th]
                );
                App::log("Project created with legacy fall-back", 'WARNING');
            }

            // Add student as member (leader)
            Database::execute(
                "INSERT INTO project_members (project_id, student_id, role_in_project) VALUES (?, ?, 'leader')",
                [$projectId, $userId]
            );

            // Add teammates — Phase 9: Batch INSERT replaces per-row queries
            $teammates = $_POST['teammates'] ?? [];
            if (!empty($teammates)) {
                $memberRows = [];
                $memberValues = [];
                foreach ($teammates as $tId) {
                    $memberRows[] = "(?, ?, 'member')";
                    $memberValues[] = $projectId;
                    $memberValues[] = (int) $tId;
                }

                Database::execute(
                    "INSERT INTO project_members (project_id, student_id, role_in_project)
                     VALUES " . implode(', ', $memberRows),
                    $memberValues
                );

                // Notify teammates (no DB queries per row — Notifier handles its own batching)
                foreach ($teammates as $tId) {
                    Notifier::send(
                        (int) $tId,
                        'project',
                        '🙌 คุณได้เข้าร่วมทีมโครงงานใหม่',
                        "คุณถูกเพิ่มเข้าเป็นสมาชิกในโครงงาน: {$title_th}",
                        "projects/{$projectId}"
                    );
                }
            }

            // Initial logbook entry
            Database::execute(
                "INSERT INTO logbooks (project_id, student_id, activity_detail, hours_spent) VALUES (?, ?, 'สร้างข้อเสนอโครงงานและเพิ่มสมาชิกทีม', 1.0)",
                [$projectId, $userId]
            );

            Database::commit();

            // Notification to advisor
            try {
                Database::execute(
                    "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'project', 'โครงงานใหม่รอการอนุมัติ', ?, ?)",
                    [$advisor_id, "มีข้อเสนอโครงงาน: {$title_th}", "/advisor/projects/{$projectId}"]
                );
            } catch (\Exception $e) {
            }

            Session::flash('success', 'ส่งข้อเสนอโครงงานสำเร็จ รอครูที่ปรึกษาอนุมัติ');

            // Phase 5: Badge event triggers
            try {
                BadgeService::onEvent($userId, 'project_joined');
                BadgeService::onEvent($userId, 'project_leaded');
                EXPService::award($userId, 'project_created');

                foreach ($teammates as $tId) {
                    BadgeService::onEvent((int) $tId, 'project_joined');
                    EXPService::award((int) $tId, 'project_created');
                }
            } catch (\Throwable $e) {
                App::log("Badge check after project create failed: " . $e->getMessage(), 'WARNING');
            }

            App::redirect('projects');

        } catch (\Exception $e) {
            if (Database::inTransaction())
                Database::rollback();
            App::log("Project store failed: " . $e->getMessage(), 'ERROR');
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
            App::redirect('projects/create');
        }
    }

    /**
     * ดูรายละเอียดโครงงาน
     * Phase 9: Uses JOIN-based eager loads — no per-row queries.
     */
    public function show($id)
    {
        $userId = Auth::id();

        $memberCheck = Database::fetch(
            "SELECT * FROM project_members WHERE project_id = ? AND student_id = ?",
            [$id, $userId]
        );

        if (!$memberCheck && !Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เข้าถึงโครงงานนี้');
            App::redirect('projects');
        }

        $project = Database::fetch(
            "SELECT p.*, u.full_name as advisor_name
             FROM projects p
             LEFT JOIN users u ON p.advisor_id = u.id
             WHERE p.id = ?",
            [$id]
        );

        $members = Database::fetchAll(
            "SELECT pm.student_id, u.username, COALESCE(u.full_name, u.username) as full_name, pm.role_in_project
             FROM project_members pm
             JOIN users u ON pm.student_id = u.id
             WHERE pm.project_id = ?",
            [$id]
        );

        $documents = [];
        try {
            $documents = Database::fetchAll(
                "SELECT * FROM project_documents WHERE project_id = ? ORDER BY created_at DESC",
                [$id]
            );
        } catch (\Throwable $e) {
            $documents = [];
        }

        $logbooks = Database::fetchAll(
            "SELECT l.*, u.username as student_name
             FROM logbooks l
             JOIN users u ON l.student_id = u.id
             WHERE l.project_id = ? ORDER BY l.created_at DESC",
            [$id]
        );

        $this->view('projects/show', [
            'title' => 'รายละเอียดโครงงาน: ' . htmlspecialchars($project['title_th']),
            'project' => $project,
            'members' => $members,
            'documents' => $documents,
            'logbooks' => $logbooks
        ]);
    }

    /**
     * ส่งออกโครงงานเข้าสู่ Portfolio (Integrated Synergy)
     * Phase 9: Single JOIN for member+project data; batch INSERT for collaborators.
     * Previously: 2 queries to get project + members, then N INSERT queries for collaborators.
     * Now: 1 JOIN query, 1 batch INSERT for all collaborators regardless of team size.
     */
    public function exportToPortfolio($id)
    {
        $userId = Auth::id();
        $projectId = (int) $id;

        // Eager-load member check + project data in single JOIN query
        // Previously: separate query for project_members, then separate query for projects
        $member = Database::fetch(
            "SELECT pm.*, p.title_th, p.abstract_th, p.project_code
             FROM project_members pm
             JOIN projects p ON pm.project_id = p.id
             WHERE pm.project_id = ? AND pm.student_id = ? AND pm.role_in_project = 'leader'",
            [$projectId, $userId]
        );

        if (!$member) {
            Session::flash('error', 'เฉพาะผู้นำกลุ่มเท่านั้นที่มีสิทธิ์ส่งออกโครงงานนี้');
            App::redirect("projects/{$projectId}");
            return;
        }

        $exists = Database::fetch(
            "SELECT id FROM portfolio_items WHERE student_id = (SELECT id FROM student_profiles WHERE user_id = ?) AND title = ?",
            [$userId, $member['title_th']]
        );
        if ($exists) {
            Session::flash('info', 'โครงงานนี้ถูกส่งออกไปยัง Portfolio เรียบร้อยแล้ว');
            App::redirect("projects/{$projectId}");
            return;
        }

        try {
            Database::beginTransaction();

            $studentProfile = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);
            $studentId = $studentProfile['id'];

            $portfolioId = Database::insert(
                "INSERT INTO portfolio_items (student_id, title, category_id, description, status, verification_status, event_date)
                 VALUES (?, ?, ?, ?, 'published', 'pending', ?)",
                [
                    $studentId,
                    $member['title_th'],
                    1,
                    $member['abstract_th'] . "\n\n(ข้อมูลถูกส่งออกจากระบบติดตามโครงงาน รหัส: " . $member['project_code'] . ")",
                    date('Y-m-d')
                ]
            );

            // Eager-load all teammates in single query, mapping user_id to student_profile.id
            $teammates = Database::fetchAll(
                "SELECT pm.student_id as user_id, COALESCE(sp.id, pm.student_id) as profile_id
                 FROM project_members pm
                 LEFT JOIN student_profiles sp ON sp.user_id = pm.student_id
                 WHERE pm.project_id = ? AND pm.student_id != ?",
                [$projectId, $userId]
            );

            if (!empty($teammates)) {
                // Batch INSERT replaces N individual INSERT queries
                // Previously: foreach($members) { Database::execute("INSERT ...") } = N queries
                // Now: single INSERT with multiple VALUE rows = 1 query
                $collabRows = [];
                $collabValues = [];
                foreach ($teammates as $t) {
                    $collabRows[] = "(?, ?, 'pending')";
                    $collabValues[] = $portfolioId;
                    $collabValues[] = (int) $t['profile_id'];
                }

                Database::execute(
                    "INSERT INTO portfolio_collaborators (portfolio_id, student_id, acceptance_status)
                     VALUES " . implode(', ', $collabRows),
                    $collabValues
                );

                // Notifications (no per-row DB queries — Notifier handles internally)
                foreach ($teammates as $t) {
                    Notifier::send(
                        (int) $t['user_id'],
                        'collaboration',
                        '🤝 คำเชิญร่วมผลงานกลุ่ม (Auto-Export)',
                        "ผู้นำกลุ่มได้ส่งออกโครงงาน \"{$member['title_th']}\" เข้าสู่ Portfolio โปรดยืนยันการร่วมทำ",
                        "portfolio/{$portfolioId}"
                    );
                }
            }

            Database::commit();
            Session::flash('success', 'ส่งออกโครงงานเข้าสู่ Portfolio ของทีมเรียบร้อยแล้ว! ✨');
            App::redirect("portfolio/{$portfolioId}");

        } catch (\Exception $e) {
            if (Database::inTransaction())
                Database::rollback();
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            App::redirect("projects/{$projectId}");
        }
    }

    /**
     * ส่งออกรายงานความก้าวหน้าโครงงานเป็น PDF (Phase 3)
     * Phase 9: Already uses JOIN-based queries — no changes needed.
     */
    public function exportPdf($id)
    {
        $userId = Auth::id();
        $projectId = (int) $id;

        $isMember = Database::fetch(
            "SELECT 1 FROM project_members WHERE project_id = ? AND student_id = ?",
            [$projectId, $userId]
        );
        if (!$isMember && !Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ส่งออกรายงานของโครงงานนี้');
            App::redirect("projects/{$projectId}");
            return;
        }

        $project = Database::fetch(
            "SELECT p.*,
                    COALESCE(u.full_name, u.username) AS advisor_name,
                    s.name AS subject_name
             FROM projects p
             LEFT JOIN users u ON p.advisor_id = u.id
             LEFT JOIN project_subjects s ON p.subject_id = s.id
             WHERE p.id = ?",
            [$projectId]
        );

        if (!$project) {
            Session::flash('error', 'ไม่พบข้อมูลโครงงาน');
            App::redirect('projects');
            return;
        }

        $members = Database::fetchAll(
            "SELECT COALESCE(u.full_name, u.username) AS full_name, u.username, pm.role_in_project
             FROM project_members pm
             JOIN users u ON pm.student_id = u.id
             WHERE pm.project_id = ?
             ORDER BY FIELD(pm.role_in_project, 'leader', 'member'), u.username",
            [$projectId]
        );

        $logbooks = Database::fetchAll(
            "SELECT l.*, COALESCE(u.full_name, u.username) AS student_name
             FROM logbooks l
             JOIN users u ON l.student_id = u.id
             WHERE l.project_id = ?
             ORDER BY l.created_at ASC",
            [$projectId]
        );

        $documents = [];
        try {
            $documents = Database::fetchAll(
                "SELECT * FROM project_documents WHERE project_id = ? ORDER BY created_at ASC",
                [$projectId]
            );
        } catch (\Throwable $e) {
            $documents = [];
        }

        $totalHours = 0.0;
        foreach ($logbooks as $lb) {
            $totalHours += (float) ($lb['hours_spent'] ?? 0);
        }
        $stats = [
            'log_count' => count($logbooks),
            'doc_count' => count($documents),
            'total_hours' => $totalHours,
        ];

        try {
            PDFService::renderView('pdf/project_progress_report', [
                'project' => $project,
                'members' => $members,
                'logbooks' => $logbooks,
                'documents' => $documents,
                'stats' => $stats,
                'issuedAt' => PDFService::thaiDate(),
            ], [
                'title' => 'รายงานความก้าวหน้าโครงงาน ' . ($project['project_code'] ?? ''),
                'filename' => 'ProjectReport_' . ($project['project_code'] ?? $projectId) . '_' . date('Ymd'),
                'mode' => 'I',
                'watermark' => $project['status'] === 'completed' ? '' : 'DRAFT',
            ]);
        } catch (\Throwable $e) {
            App::log('PDF export failed: ' . $e->getMessage(), 'ERROR');
            Session::flash('error', 'ไม่สามารถสร้างเอกสาร PDF ได้: ' . $e->getMessage());
            App::redirect("projects/{$projectId}");
        }
    }
}
