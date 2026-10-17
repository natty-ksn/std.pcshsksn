<?php
/**
 * AdvisorController
 * จัดการกระบวนการของครูที่ปรึกษาโครงงาน (อนุมัติ, ตรวจสอบ, คอมเมนต์)
 */
class AdvisorController extends Controller
{
    /**
     * แผงควบคุมครูที่ปรึกษา (รายการโครงงานที่ดูแล)
     */
    public function index()
    {
        // ตรวจสอบว่าเป็นครูหรือไม่ (ถ้าไม่ใช่ให้เด้งออก)
        if (!Auth::hasAnyRole(['admin', 'teacher'])) {
            Session::flash('error', 'เฉพาะครูที่ปรึกษาเท่านั้นที่สามารถเข้าถึงได้');
            App::redirect('dashboard');
        }

        $userId = Auth::id();

        // โครงงานที่รับผิดชอบ
        $projects = Database::fetchAll(
            "SELECT p.*, s.name as subject_name, 
                    (SELECT COUNT(id) FROM project_members pm WHERE pm.project_id = p.id) as total_members,
                    (SELECT COUNT(id) FROM project_documents pd WHERE pd.project_id = p.id AND pd.review_status = 'pending') as pending_docs
             FROM projects p
             LEFT JOIN project_subjects s ON p.subject_id = s.id
             WHERE p.advisor_id = ? OR p.co_advisor_id = ?
             ORDER BY 
                CASE WHEN status = 'proposal_submitted' THEN 1 ELSE 2 END, 
                p.updated_at DESC",
            [$userId, $userId]
        );

        // สรุปสถิติสำหรับครู
        $stats = [
            'total' => count($projects),
            'action_required' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];

        foreach ($projects as $proj) {
            if (in_array($proj['status'], ['proposal_submitted', 'chapter_review'])) {
                $stats['action_required']++;
            } elseif ($proj['status'] === 'completed') {
                $stats['completed']++;
            } else {
                $stats['in_progress']++;
            }
        }

        $this->view('advisor/projects/index', [
            'title' => 'ระบบที่ปรึกษาโครงงาน | รายการที่รับผิดชอบ',
            'projects' => $projects,
            'stats' => $stats
        ]);
    }

    /**
     * หน้าพิจารณารายละเอียดโครงงาน (ตรวจเอกสาร/คอมเมนต์)
     */
    public function show($id)
    {
        if (!Auth::hasAnyRole(['admin', 'teacher'])) {
            App::redirect('dashboard');
        }

        $project = Database::fetch(
            "SELECT p.*, s.name as subject_name 
             FROM projects p 
             LEFT JOIN project_subjects s ON p.subject_id = s.id 
             WHERE p.id = ?",
            [$id]
        );

        if (!$project) {
            Session::flash('error', 'ไม่พบข้อมูลโครงงาน');
            App::redirect('advisor/projects');
        }

        // Members
        $members = Database::fetchAll(
            "SELECT u.username, u.email, sp.full_name, pm.role_in_project 
             FROM project_members pm 
             JOIN users u ON pm.student_id = u.id 
             LEFT JOIN student_profiles sp ON u.id = sp.user_id
             WHERE pm.project_id = ?",
            [$id]
        );

        // Documents needing review
        $documents = Database::fetchAll(
            "SELECT * FROM project_documents WHERE project_id = ? ORDER BY created_at DESC",
            [$id]
        );

        $this->view('advisor/projects/show', [
            'title' => 'พิจารณาโครงงาน: ' . htmlspecialchars($project['project_code']),
            'project' => $project,
            'members' => $members,
            'documents' => $documents
        ]);
    }

    /**
     * ดำเนินการอนุมัติ / ปฏิเสธ ข้อเสนอโครงงาน
     */
    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect('advisor/projects');
        }

        // Validate Security
        CSRF::check();

        $action = $_POST['action'] ?? '';
        $comment = $_POST['feedback'] ?? '';

        $project = Database::fetch("SELECT * FROM projects WHERE id = ?", [$id]);

        if (!$project) {
            Session::flash('error', 'ไม่พบโครงงาน');
            App::redirect('advisor/projects');
        }

        // Security: Ensure current user is teacher/admin and is the designated advisor of this project
        if (!Auth::hasRole('admin')) {
            $isAdvisor = Database::fetch(
                "SELECT 1 FROM projects WHERE id = ? AND (advisor_id = ? OR co_advisor_id = ?)",
                [$id, Auth::id(), Auth::id()]
            );
            if (!$isAdvisor) {
                ErrorHandler::forbidden('คุณไม่มีสิทธิ์อนุมัติหรือจัดการโครงงานนี้ (เฉพาะครูที่ปรึกษาโครงงาน)');
                return;
            }
        }

        $newStatus = $project['status'];
        $notificationMsg = "";

        if ($action === 'approve_proposal') {
            $newStatus = 'proposal_approved';
            $notificationMsg = "ข้อเสนอโครงงาน {$project['title_th']} ได้รับการอนุมัติแล้ว!";
        } elseif ($action === 'reject_proposal' || $action === 'revise_proposal') {
            $newStatus = 'proposal_revision';
            $notificationMsg = "ข้อเสนอโครงงาน {$project['title_th']} ต้องได้รับการแก้ไข กรุณาอ่านข้อเสนอแนะ";
        } elseif ($action === 'start_progress') {
            $newStatus = 'in_progress';
            $notificationMsg = "โครงงาน {$project['title_th']} เข้าสู่ขั้นตอนดำเนินการแล้ว";
        } elseif ($action === 'complete_project') {
            $newStatus = 'completed';
            $notificationMsg = "โครงงาน {$project['title_th']} เสร็จสมบูรณ์แล้ว!";
        }

        try {
            Database::beginTransaction();

            Database::execute(
                "UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $id]
            );

            // Use Notifier for event-driven notifications
            if ($action === 'approve_proposal') {
                Notifier::projectApproved($id, Auth::id());

                // Award EXP to all project members when proposal is approved
                $members = Database::fetchAll(
                    "SELECT student_id FROM project_members WHERE project_id = ?",
                    [$id]
                );

                foreach ($members as $member) {
                    $memberId = (int) $member['student_id'];
                    EXPService::award($memberId, 'project_created');

                    try {
                        BadgeService::onEvent($memberId, 'project_joined');
                    } catch (\Throwable $e) {
                        App::log("Badge check on project approval failed: " . $e->getMessage(), 'WARNING');
                    }
                }

            } elseif ($action === 'reject_proposal') {
                Notifier::projectRejected($id, Auth::id(), $comment);

            } elseif ($action === 'revise_proposal') {
                Notifier::projectRevision($id, Auth::id(), $comment);

            } elseif ($action === 'complete_project') {
                $members = Database::fetchAll(
                    "SELECT student_id FROM project_members WHERE project_id = ?",
                    [$id]
                );

                foreach ($members as $member) {
                    $memberId = (int) $member['student_id'];
                    EXPService::award($memberId, 'project_completed');
                    Notifier::send(
                        $memberId,
                        'system.project_complete',
                        '🎉 โครงงานเสร็จสมบูรณ์',
                        "โครงงาน {$project['title_th']} เสร็จสมบูรณ์แล้ว!",
                        "projects/{$id}"
                    );
                }
            }

            // หากมีคอมเมนต์เก็บลง Logbook
            if (!empty($comment)) {
                Database::execute(
                    "INSERT INTO logbooks (project_id, student_id, activity_detail, hours_spent, teacher_comment)
                     VALUES (?, ?, 'ข้อเสนอแนะจากการตรวจพิจารณาโครงงาน', 0, ?)",
                    [$id, Auth::id(), $comment]
                );
            }

            Database::commit();
            Session::flash('success', 'บันทึกสถานะโครงงานเรียบร้อยแล้ว');

        } catch (Exception $e) {
            Database::rollback();
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        App::redirect("advisor/projects/{$id}");
    }

    /**
     * ตรวจสอบเอกสารโครงงาน (Approve / Revision Required)
     */
    public function reviewDocument($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect('advisor/projects');
            return;
        }

        CSRF::check();

        $docId = (int) $id;
        $status = $this->input('review_status', 'approved');
        $comment = trim($this->rawInput('comment') ?? '');

        if (!in_array($status, ['approved', 'revision_required', 'pending'], true)) {
            $status = 'approved';
        }

        $doc = Database::fetch("SELECT * FROM project_documents WHERE id = ?", [$docId]);
        if (!$doc) {
            Session::flash('error', 'ไม่พบเอกสารที่ต้องการตรวจ');
            App::redirect('advisor/projects');
            return;
        }

        $projectId = (int) $doc['project_id'];

        if (!Auth::hasRole('admin')) {
            $isAdvisor = Database::fetch(
                "SELECT 1 FROM projects WHERE id = ? AND (advisor_id = ? OR co_advisor_id = ?)",
                [$projectId, Auth::id(), Auth::id()]
            );
            if (!$isAdvisor) {
                ErrorHandler::forbidden('เฉพาะครูที่ปรึกษาโครงงานเท่านั้นที่สามารถตรวจเอกสารนี้ได้');
                return;
            }
        }

        Database::execute(
            "UPDATE project_documents SET review_status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
            [$status, Auth::id(), $docId]
        );

        if ($comment !== '') {
            Database::execute(
                "INSERT INTO logbooks (project_id, student_id, activity_detail, hours_spent, teacher_comment)
                 VALUES (?, ?, ?, 0, ?)",
                [$projectId, Auth::id(), "ตรวจเอกสาร: {$doc['title']} (v{$doc['version']})", $comment]
            );
        }

        $statusLabel = ($status === 'approved') ? '✅ อนุมัติแล้ว' : '⚠️ ต้องแก้ไข';
        Notifier::send(
            (int) $doc['uploaded_by'],
            'document_reviewed',
            "ผลการตรวจเอกสารโครงงาน ({$statusLabel})",
            "เอกสาร \"{$doc['title']}\" ได้รับการตรวจโดยครูที่ปรึกษาแล้ว" . ($comment ? ": {$comment}" : ''),
            "projects/{$projectId}"
        );

        Session::flash('success', 'บันทึกผลการตรวจเอกสารเรียบร้อยแล้ว');
        App::redirect("advisor/projects/{$projectId}");
    }
}
