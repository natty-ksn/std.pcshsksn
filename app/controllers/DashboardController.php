<?php
/**
 * DashboardController — หน้า Dashboard หลักของแต่ละ Role
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $role = Auth::role();

        switch ($role) {
            case 'student':
                $this->studentDashboard();
                break;
            case 'teacher':
                $this->teacherDashboard();
                break;
            case 'admin':
                $this->adminDashboard();
                break;
            case 'executive':
                $this->executiveDashboard();
                break;
            case 'parent':
                $this->parentDashboard();
                break;
            default:
                // Break the redirect loop: If we are here, something is wrong with the role session.
                App::log("Unknown role found in Dashboard: " . ($role ?? 'NULL'), 'ERROR');
                Auth::logout();
                Session::flash('error', 'เซสชันของคุณไม่สมบูรณ์ หรือคุณไม่ได้รับสิทธิ์เข้าถึงหน้านี้ กรุณาล็อกอินใหม่อีกครั้ง');
                App::redirect('login');
        }
    }

    /**
     * Student Dashboard
     */
    private function studentDashboard(): void
    {
        $userId = Auth::id();

        // ดึงข้อมูลโปรไฟล์
        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        $studentId = $profile['id'];

        // สถิติผลงาน
        $stats = [
            'total_works' => Database::fetch(
                "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND status = 'published'",
                [$studentId]
            )['count'] ?? 0,

            'featured_works' => Database::fetch(
                "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND is_featured = 1",
                [$studentId]
            )['count'] ?? 0,

            'total_goals' => Database::fetch(
                "SELECT COUNT(*) as count FROM student_goals WHERE student_id = ?",
                [$studentId]
            )['count'] ?? 0,

            'achieved_goals' => Database::fetch(
                "SELECT COUNT(*) as count FROM student_goals WHERE student_id = ? AND status = 'achieved'",
                [$studentId]
            )['count'] ?? 0,

            'used_space' => Database::fetch(
                "SELECT SUM(file_size) as total FROM portfolio_items WHERE student_id = ?",
                [$studentId]
            )['total'] ?? 0,
        ];

        // GPA
        $gpaResult = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / SUM(credit), 2) as gpa 
             FROM academic_records WHERE student_id = ?",
            [$studentId]
        );
        $stats['gpa'] = $gpaResult['gpa'] ?? 0;

        // ผลงานล่าสุด (พร้อม Category และ Skills ใน Query เดียว ลด N+1)
        $recentWorks = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon,
                    GROUP_CONCAT(DISTINCT sm.skill_name SEPARATOR ', ') as skillset
             FROM portfolio_items pi 
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id 
             LEFT JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             LEFT JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             GROUP BY pi.id
             ORDER BY pi.created_at DESC LIMIT 6",
            [$studentId]
        );

        // เป้าหมาย
        $activeGoals = Database::fetchAll(
            "SELECT * FROM student_goals WHERE student_id = ? AND status IN ('planning','inprogress') ORDER BY deadline ASC LIMIT 5",
            [$studentId]
        );

        // 1. ดึงคะแนนความพร้อม (Readiness Score) ที่ได้รับผลพยากรณ์จาก AI/Heuristic
        $overallReadiness = Database::fetch(
            "SELECT * FROM readiness_scores WHERE student_id = ? AND score_type = 'overall'",
            [$studentId]
        );

        // 2. หากยังไม่มีผล หรือผลเก่าเกินไป ให้รัน 'Quick Gauge' (การประเมินเบื้องต้น)
        $calculatedAt = $overallReadiness['calculated_at'] ?? null;
        $isExpired = true;
        if ($calculatedAt) {
            $isExpired = (strtotime($calculatedAt) < strtotime('-24 hours'));
        }
        if (!$overallReadiness || $isExpired) {
            // ดึงเป้าหมายหลัก
            $primaryGoal = Database::fetch(
                "SELECT * FROM student_goals WHERE student_id = ? AND status IN ('planning','inprogress') ORDER BY updated_at DESC LIMIT 1",
                [$studentId]
            );

            $readinessResult = $this->calculateQuickReadiness($studentId, $primaryGoal, $stats['gpa']);
            $readiness = $readinessResult;
        } else {
            // Read all score types
            $scores = Database::fetchAll(
                "SELECT * FROM readiness_scores WHERE student_id = ?",
                [$studentId]
            );
            $readiness = [
                'overall_pct' => 0,
                'academic_score' => 0,
                'portfolio_score' => 0,
                'skill_score' => 0,
                'analysis_data' => '{}',
            ];
            foreach ($scores as $s) {
                if ($s['score_type'] === 'overall')
                    $readiness['overall_pct'] = (int) $s['score'];
                if ($s['score_type'] === 'academic')
                    $readiness['academic_score'] = (int) $s['score'];
                if ($s['score_type'] === 'portfolio')
                    $readiness['portfolio_score'] = (int) $s['score'];
                if ($s['score_type'] === 'skill')
                    $readiness['skill_score'] = (int) $s['score'];
                if ($s['details'])
                    $readiness['analysis_data'] = $s['details'];
            }
        }

        // 3. เตรียมข้อมูลความสนใจ (Skill Radar)
        $skills = Database::fetchAll(
            "SELECT item, proficiency FROM student_interests WHERE student_id = ? AND type != 'hobby' LIMIT 5",
            [$studentId]
        );

        // --- Phase 3: Radar Chart Gap Analysis ---
        $radarData = null;
        $primaryGoalFull = Database::fetch(
            "SELECT sg.target, gt.radar_target, gt.track_name 
             FROM student_goals sg
             LEFT JOIN goal_templates gt ON (sg.target LIKE CONCAT('%', gt.track_code, '%') OR gt.track_name LIKE CONCAT('%', sg.target, '%') OR gt.track_name = sg.target)
             WHERE sg.student_id = ? AND sg.status IN ('planning','inprogress') 
             ORDER BY sg.updated_at DESC LIMIT 1",
            [$studentId]
        );

        if ($primaryGoalFull && $primaryGoalFull['radar_target']) {
            $targetObj = json_decode($primaryGoalFull['radar_target'], true);
            $riasecRecord = Database::fetch("SELECT result_data FROM student_assessments WHERE user_id = ? AND assessment_type = 'riasec' ORDER BY created_at DESC LIMIT 1", [$userId]);

            $riasecData = $riasecRecord ? json_decode($riasecRecord['result_data'], true) : [];

            $labels = ['R (Realistic)', 'I (Investigative)', 'A (Artistic)', 'S (Social)', 'E (Enterprising)', 'C (Conventional)'];
            $mapKeys = ['R', 'I', 'A', 'S', 'E', 'C'];

            $studentScores = [];
            $targetScores = [];

            foreach ($mapKeys as $key) {
                // If the target JSON uses just the letter
                $targetScores[] = isset($targetObj[$key]) ? (int) $targetObj[$key] : 0;
                $studentScores[] = (int) ($riasecData['scores'][$key] ?? $riasecData[$key] ?? 0);
            }

            $radarData = [
                'goal_name' => $primaryGoalFull['track_name'] ?: $primaryGoalFull['target'],
                'labels' => $labels,
                'student_scores' => $studentScores,
                'target_scores' => $targetScores
            ];
        }

        // 4. คืนค่าตัวแปรดั้งเดิมที่จำเป็น
        $latestAI = Database::fetch(
            "SELECT * FROM ai_recommendations WHERE student_id = ? ORDER BY generated_at DESC LIMIT 1",
            [$studentId]
        );

        $badges = Database::fetchAll(
            "SELECT bm.* FROM user_badges ub 
             JOIN badges_master bm ON ub.badge_code = bm.code 
             WHERE ub.user_id = ? ORDER BY ub.earned_at DESC",
            [$userId]
        );

        $unreadNotifs = Database::fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        )['count'] ?? 0;

        $categoryStats = Database::fetchAll(
            "SELECT pc.name, pc.icon, pc.color, COUNT(pi.id) as count 
             FROM portfolio_categories pc 
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.student_id = ? AND pi.status = 'published'
             WHERE pc.is_active = 1 
             GROUP BY pc.id ORDER BY count DESC",
            [$studentId]
        );

        $this->view('dashboard.student', [
            'profile' => $profile,
            'stats' => $stats,
            'recentWorks' => $recentWorks,
            'activeGoals' => $activeGoals,
            'latestAI' => $latestAI,
            'badges' => $badges,
            'unreadNotifs' => $unreadNotifs,
            'readiness' => $readiness,
            'skills' => $skills,
            'categoryStats' => $categoryStats,
            'radarData' => $radarData,
        ]);
    }

    /**
     * ระบบคำนวณเข็มทิศความพรัอมเบื้องต้น (Mastery Quick Gauge)
     * Refactored (Phase 9): Uses single eager-load query instead of 4 separate queries.
     */
    private function calculateQuickReadiness(int $studentId, ?array $goal, float $gpa): array
    {
        // Eager-load all metrics in a single query instead of 4 separate queries
        $metrics = Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM portfolio_items WHERE student_id = ? AND status = 'published') AS port_count,
                (SELECT COUNT(*) FROM student_interests WHERE student_id = ? AND type != 'hobby') AS skill_count",
            [$studentId, $studentId]
        );

        $portCount = (int) ($metrics['port_count'] ?? 0);
        $skillCount = (int) ($metrics['skill_count'] ?? 0);

        // 1. Academic Readiness (35%)
        $targetGpa = 3.00;
        if ($goal) {
            $tCode = (mb_strpos($goal['target'], 'แพทย์') !== false || mb_strpos($goal['target'], 'เภสัช') !== false) ? 'med' :
                ((mb_strpos($goal['target'], 'วิศว') !== false || mb_strpos($goal['target'], 'com') !== false) ? 'eng' : 'sci');
            $template = Database::fetch("SELECT min_gpa FROM goal_templates WHERE track_code = ?", [$tCode]);
            if ($template)
                $targetGpa = (float) $template['min_gpa'];
        }
        $academicRaw = min(100, ($gpa / $targetGpa) * 100);

        // 2. Portfolio Readiness (40%)
        $portRaw = min(100, ($portCount / 10) * 100);

        // 3. Skill Readiness (25%)
        $skillRaw = min(100, ($skillCount / 5) * 100);

        $overall = round(($academicRaw * 0.35) + ($portRaw * 0.40) + ($skillRaw * 0.25));

        $result = [
            'overall_pct' => $overall,
            'academic_score' => round($academicRaw),
            'portfolio_score' => round($portRaw),
            'skill_score' => round($skillRaw),
            'analysis_data' => json_encode(['target_gpa' => $targetGpa, 'port_items' => $portCount])
        ];

        // Batch insert all readiness scores in a single multi-row INSERT
        $types = [
            ['overall', $overall],
            ['academic', round($academicRaw)],
            ['portfolio', round($portRaw)],
            ['skill', round($skillRaw)],
        ];

        // Build multi-row INSERT to replace 4 individual queries
        $rows = [];
        $values = [];
        foreach ($types as $t) {
            $risk = $t[1] >= 70 ? 'low' : ($t[1] >= 40 ? 'medium' : ($t[1] >= 20 ? 'high' : 'critical'));
            $rows[] = "(?, ?, ?, 100.00, ?, ?)";
            $values = array_merge($values, [$studentId, $t[0], $t[1], $result['analysis_data'], $risk]);
        }

        Database::execute(
            "INSERT INTO readiness_scores (student_id, score_type, score, max_score, details, risk_level)
             VALUES " . implode(', ', $rows) . "
             ON DUPLICATE KEY UPDATE score=VALUES(score), risk_level=VALUES(risk_level), calculated_at=NOW()",
            $values
        );

        return $result;
    }

    /**
     * Teacher Dashboard
     */
    private function teacherDashboard(): void
    {
        $teacherId = Auth::id();

        // สถิติข้อมูลรวม
        $stats = [
            'total_students' => Database::fetch("SELECT COUNT(sp.id) as count FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE u.role = 'student'")['count'] ?? 0,
            'total_works' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE status = 'published'")['count'] ?? 0,
            'recent_uploads' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
        ];

        // --- Phase 1: Action Required Dashboard ---
        // 1. Portfolio รอการตรวจ (ที่ยังเป็น pending และยังไม่มีคอมเมนต์จากครูคนนี้)
        $pendingPortfolios = Database::fetchAll(
            "SELECT pi.id, pi.title, sp.full_name, pi.created_at, sp.avatar_path, sp.student_code
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             LEFT JOIN teacher_comments tc ON tc.portfolio_id = pi.id AND tc.teacher_id = ?
             WHERE pi.status = 'published' AND pi.verification_status = 'pending' AND tc.id IS NULL
             ORDER BY pi.created_at DESC LIMIT 5",
            [$teacherId]
        );

        // 2. เอกสารโครงงานรอตรวจ (Project Documents)
        $pendingDocuments = Database::fetchAll(
            "SELECT pd.id as doc_id, pd.document_type, pd.title, p.id as project_id, p.title_th as project_title, sp.full_name as submitter, pd.created_at
             FROM project_documents pd
             JOIN projects p ON pd.project_id = p.id
             JOIN users u ON pd.uploaded_by = u.id
             JOIN student_profiles sp ON u.id = sp.user_id
             WHERE pd.review_status = 'pending' AND (p.advisor_id = ? OR p.co_advisor_id = ?)
             ORDER BY pd.created_at ASC LIMIT 5",
            [$teacherId, $teacherId]
        );

        // 3. Milestone ความคืบหน้าโครงงาน (ถ้ามีโมดูลนี้)
        $pendingMilestones = Database::fetchAll(
            "SELECT ms.id, m.phase_name, p.title_th as project_title, sp.full_name as submitter, ms.submitted_at
             FROM milestone_submissions ms
             JOIN milestones m ON ms.milestone_id = m.id
             JOIN projects p ON m.project_id = p.id
             JOIN users u ON ms.submitted_by = u.id
             JOIN student_profiles sp ON u.id = sp.user_id
             WHERE ms.status = 'submitted' AND (p.advisor_id = ? OR p.co_advisor_id = ?)
             ORDER BY ms.submitted_at ASC LIMIT 5",
            [$teacherId, $teacherId]
        );

        // 4. นักเรียนกลุ่มเสี่ยง (Early Warning System)
        $riskStudents = Database::fetchAll(
            "SELECT * FROM vw_early_warning WHERE risk_level IN ('high', 'critical') ORDER BY FIELD(risk_level, 'critical', 'high'), avg_gpa ASC LIMIT 5"
        );

        // นักเรียนที่มีผลงานล่าสุด
        $recentStudents = Database::fetchAll(
            "SELECT sp.id, sp.full_name, sp.student_code, sp.class_year, sp.class_room, sp.avatar_path,
                    pi.title as last_work, pi.created_at as work_date
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             WHERE pi.status = 'published'
             ORDER BY pi.created_at DESC LIMIT 10"
        );

        $this->view('dashboard.teacher', [
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'pendingPortfolios' => $pendingPortfolios,
            'pendingDocuments' => $pendingDocuments,
            'pendingMilestones' => $pendingMilestones,
            'riskStudents' => $riskStudents,
        ]);
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard(): void
    {
        $stats = [
            'total_users' => Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'active_users' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'] ?? 0,
            'total_students' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'] ?? 0,
            'total_teachers' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")['count'] ?? 0,
            'total_works' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items")['count'] ?? 0,
            'active_today' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE DATE(last_login) = CURDATE()")['count'] ?? 0,
            'today_logs' => Database::fetch("SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'total_awards' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE award != '' AND award IS NOT NULL")['count'] ?? 0,
        ];

        $ewsSummary = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        $ewsRows = Database::fetchAll("SELECT risk_level, COUNT(*) as cnt FROM vw_early_warning GROUP BY risk_level");
        foreach ($ewsRows as $r) {
            if (isset($ewsSummary[$r['risk_level']])) {
                $ewsSummary[$r['risk_level']] = (int) $r['cnt'];
            }
        }

        $recentLogs = Database::fetchAll(
            "SELECT al.*, u.username, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 8"
        );

        // 1. ผลสัมฤทธิ์ทางการเรียนแยกตามชั้นปี (Academic Health)
        $gpaByYear = Database::fetchAll(
            "SELECT sp.class_year, ROUND(SUM(ar.grade * ar.credit) / SUM(ar.credit), 2) as avg_gpa
             FROM academic_records ar
             JOIN student_profiles sp ON ar.student_id = sp.id
             GROUP BY sp.class_year ORDER BY sp.class_year ASC"
        );

        // 2. แผนผังบุคลิกภาพรวมของโรงเรียน (School DNA - RIASEC)
        $riasecStats = Database::fetchAll(
            "SELECT dominant_trait, COUNT(*) as count 
             FROM student_assessments 
             WHERE assessment_type = 'riasec' 
             GROUP BY dominant_trait ORDER BY count DESC LIMIT 6"
        );

        // 3. หมวดหมู่ผลงานที่โดดเด่น (Portfolio Focus)
        $categoryFocus = Database::fetchAll(
            "SELECT pc.name, COUNT(pi.id) as count 
             FROM portfolio_categories pc 
             JOIN portfolio_items pi ON pc.id = pi.category_id 
             GROUP BY pc.id ORDER BY count DESC LIMIT 5"
        );

        // 4. คณะ/อาชีพยอดฮิต (Career Ambitions)
        $topGoals = Database::fetchAll(
            "SELECT target, COUNT(*) as count 
             FROM student_goals 
             WHERE status = 'inprogress' OR status = 'planning'
             GROUP BY target ORDER BY count DESC LIMIT 5"
        );

        $this->view('dashboard.admin', [
            'stats' => $stats,
            'ewsSummary' => $ewsSummary,
            'recentLogs' => $recentLogs,
            'gpaByYear' => $gpaByYear,
            'riasecStats' => $riasecStats,
            'categoryFocus' => $categoryFocus,
            'topGoals' => $topGoals
        ]);
    }

    /**
     * Executive Dashboard (ผอ. / รอง ผอ.) — Data-Driven Management
     */
    private function executiveDashboard(): void
    {
        // ===== KPIs with Targets & Trends =====
        $stats = [
            'total_students' => Database::fetch("SELECT COUNT(sp.id) as count FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE u.role = 'student'")['count'] ?? 0,
            'total_works' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE status='published'")['count'] ?? 0,
            'total_awards' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE achievement_level IN ('national', 'international')")['count'] ?? 0,
            'active_today' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE role='student' AND DATE(last_login) = CURDATE()")['count'] ?? 0,
        ];

        // Trends (compare with last month)
        $lastMonthWorks = Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE status='published' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0;
        $stats['works_trend'] = $lastMonthWorks > 0 ? round((($stats['total_works'] - $lastMonthWorks) / max($lastMonthWorks, 1)) * 100) : 0;

        // ===== Early Warning Summary =====
        $riskCounts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'critical' => 0
        ];
        $riskData = Database::fetchAll("SELECT risk_level, COUNT(*) as cnt FROM vw_early_warning GROUP BY risk_level");
        foreach ($riskData as $r) {
            if (isset($riskCounts[$r['risk_level']]))
                $riskCounts[$r['risk_level']] = (int) $r['cnt'];
        }
        $totalStudents = $stats['total_students'];
        $stats['risk_pct'] = $totalStudents > 0 ? round((($riskCounts['high'] + $riskCounts['critical']) / $totalStudents) * 100) : 0;

        // ===== Portfolio Health Analytics =====
        $portfolioHealth = [
            'avg_per_student' => $stats['total_students'] > 0 ? round($stats['total_works'] / $stats['total_students'], 1) : 0,
            'with_file_pct' => Database::fetch("SELECT ROUND(COUNT(CASE WHEN file_path IS NOT NULL THEN 1 END) / COUNT(*) * 100, 1) as pct FROM portfolio_items WHERE status='published'")['pct'] ?? 0,
            'featured_pct' => Database::fetch("SELECT ROUND(COUNT(CASE WHEN is_featured=1 THEN 1 END) / COUNT(*) * 100, 1) as pct FROM portfolio_items WHERE status='published'")['pct'] ?? 0,
            'by_category' => Database::fetchAll(
                "SELECT pc.name, pc.icon, COUNT(pi.id) as count
                 FROM portfolio_categories pc
                 LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.status='published'
                 GROUP BY pc.id ORDER BY count DESC"
            ),
        ];

        // ===== Academic Performance =====
        $gpaByYear = Database::fetchAll(
            "SELECT sp.class_year, ROUND(AVG(ar.grade), 2) as avg_gpa, COUNT(DISTINCT sp.id) as student_count
             FROM academic_records ar
             JOIN student_profiles sp ON ar.student_id = sp.id
             GROUP BY sp.class_year ORDER BY sp.class_year ASC"
        );
        $overallGpa = Database::fetch("SELECT ROUND(AVG(grade), 2) as avg_gpa FROM academic_records")['avg_gpa'] ?? 0;

        // ===== TCAS Readiness Distribution =====
        $readinessDist = Database::fetchAll(
            "SELECT risk_level, COUNT(DISTINCT student_id) as cnt
             FROM readiness_scores WHERE score_type = 'overall'
             GROUP BY risk_level"
        );
        $readinessSummary = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        foreach ($readinessDist as $r) {
            if (isset($readinessSummary[$r['risk_level']]))
                $readinessSummary[$r['risk_level']] = (int) $r['cnt'];
        }

        // ===== Student Engagement (Last 6 months) =====
        $engagementTrend = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(DISTINCT student_id) as active_students, COUNT(*) as new_works
             FROM portfolio_items WHERE status='published' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        );

        // ===== Project Pipeline =====
        $projectPipeline = Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM projects GROUP BY status"
        );

        // ===== Top Performing Students =====
        $topStudents = Database::fetchAll(
            "SELECT sp.id, sp.full_name, sp.student_code, sp.class_year, sp.class_room,
                    COUNT(pi.id) as works, COUNT(CASE WHEN pi.achievement_level IN ('national','international') THEN 1 END) as awards,
                    ROUND(AVG(ar.grade), 2) as gpa
             FROM student_profiles sp
             JOIN portfolio_items pi ON sp.id = pi.student_id AND pi.status='published'
             LEFT JOIN academic_records ar ON sp.id = ar.student_id
             GROUP BY sp.id ORDER BY awards DESC, works DESC LIMIT 5"
        );

        // ===== Action Items for Executive =====
        $actionItems = [];
        if ($riskCounts['critical'] > 0) {
            $actionItems[] = ['priority' => 'high', 'icon' => '🔴', 'message' => "นักเรียน {$riskCounts['critical']} คน มีความเสี่ยงวิกฤต — ต้องการการดูแลเร่งด่วน", 'link' => 'analytics/early-warning'];
        }
        if ($portfolioHealth['avg_per_student'] < 5) {
            $actionItems[] = ['priority' => 'medium', 'icon' => '📁', 'message' => "ค่าเฉลี่ยผลงานต่อนักเรียนเพียง {$portfolioHealth['avg_per_student']} รายการ — ส่งเสริมให้นักเรียนเพิ่มผลงาน", 'link' => 'admin/reports'];
        }
        if ($overallGpa < 3.0) {
            $actionItems[] = ['priority' => 'high', 'icon' => '📚', 'message' => "GPA เฉลี่ย {$overallGpa} ต่ำกว่าเกณฑ์ 3.0 — ควรทบทวนแผนการสอน", 'link' => 'admin/reports'];
        }

        $position = Database::fetch("SELECT position FROM users WHERE id = ?", [Auth::id()]);
        $execPosition = $position['position'] ?? 'ผู้อำนวยการโรงเรียน';

        $this->view('dashboard.executive', [
            'stats' => $stats,
            'position' => $execPosition,
            'riskCounts' => $riskCounts,
            'portfolioHealth' => $portfolioHealth,
            'gpaByYear' => $gpaByYear,
            'overallGpa' => $overallGpa,
            'readinessSummary' => $readinessSummary,
            'engagementTrend' => $engagementTrend,
            'projectPipeline' => $projectPipeline,
            'topStudents' => $topStudents,
            'actionItems' => $actionItems,
            'riasecStats' => Database::fetchAll(
                "SELECT dominant_trait, COUNT(*) as count
                 FROM student_assessments WHERE assessment_type = 'riasec'
                 GROUP BY dominant_trait ORDER BY count DESC LIMIT 6"
            ),
            'categoryFocus' => Database::fetchAll(
                "SELECT pc.name, pc.icon, COUNT(pi.id) as count
                 FROM portfolio_categories pc
                 JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.status='published'
                 GROUP BY pc.id ORDER BY count DESC LIMIT 5"
            ),
        ]);
    }

    /**
     * Executive One-Page Dashboard (The Executive Page)
     */
    public function executiveSummary(): void
    {
        Auth::requireRole('executive', 'admin');

        $stats = [
            'total_students' => Database::fetch("SELECT COUNT(sp.id) as count FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE u.role = 'student'")['count'] ?? 0,
            'total_works' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE status = 'published'")['count'] ?? 0,
            'excellence_awards' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE achievement_level IN ('national', 'international')")['count'] ?? 0,
            'at_risk_count' => Database::fetch("SELECT COUNT(DISTINCT sp.id) as count FROM student_profiles sp 
                                                JOIN users u ON sp.user_id = u.id
                                                LEFT JOIN portfolio_items pi ON sp.id = pi.student_id AND pi.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                WHERE u.role = 'student' AND pi.id IS NULL")['count'] ?? 0,
        ];

        $hallOfFame = Database::fetchAll(
            "SELECT pi.title, pi.award, pi.achievement_level, sp.full_name, sp.class_year
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             WHERE pi.achievement_level IN ('national', 'international')
             ORDER BY pi.achievement_level DESC, pi.created_at DESC LIMIT 5"
        );

        $gradeDist = Database::fetchAll("SELECT sp.class_year, ROUND(AVG(grade), 2) as avg_grade FROM academic_records ar JOIN student_profiles sp ON ar.student_id = sp.id GROUP BY sp.class_year");

        $this->view('dashboard.executive_summary', [
            'stats' => $stats,
            'hallOfFame' => $hallOfFame,
            'gradeDist' => $gradeDist
        ]);
    }

    /**
     * ระบบสัญญาณเตือนล่วงหน้า (Early Warning System)
     */
    public function executiveEarlyWarning(): void
    {
        Auth::requireRole('executive', 'admin');

        $atRisk = Database::fetchAll(
            "SELECT sp.id, sp.full_name, sp.student_code, sp.class_year, sp.class_room, 
                    MAX(pi.created_at) as last_activity,
                    (SELECT COUNT(*) FROM portfolio_items WHERE student_id = sp.id) as total_works
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             LEFT JOIN portfolio_items pi ON sp.id = pi.student_id
             WHERE u.role = 'student'
             GROUP BY sp.id
             HAVING last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY) OR last_activity IS NULL
             ORDER BY last_activity ASC"
        );

        $this->view('dashboard.executive_ews', ['atRisk' => $atRisk]);
    }

    /**
     * ระบบจำลองความสำเร็จ (TCAS Prediction)
     */
    public function executiveTcasPrediction(): void
    {
        Auth::requireRole('executive', 'admin');

        $prediction = Database::fetchAll(
            "SELECT sp.id, sp.full_name, sp.class_year, sp.class_room,
                    (SELECT ROUND(SUM(grade * credit)/SUM(credit), 2) FROM academic_records WHERE student_id = sp.id) as gpa,
                    (SELECT COUNT(*) FROM portfolio_items WHERE student_id = sp.id AND is_featured = 1) as featured_count,
                    (SELECT COUNT(*) FROM portfolio_items WHERE student_id = sp.id AND achievement_level IN ('national', 'international')) as national_count,
                    (SELECT GROUP_CONCAT(target SEPARATOR ', ') FROM student_goals WHERE student_id = sp.id AND status = 'inprogress') as targets
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE u.role = 'student'
             ORDER BY gpa DESC, national_count DESC LIMIT 30"
        );

        $this->view('dashboard.executive_tcas', ['prediction' => $prediction]);
    }

    /**
     * ตารางธาตุความสำเร็จ (Excellence Reporting)
     */
    public function executiveExcellenceReport(): void
    {
        Auth::requireRole('executive', 'admin');

        $awards = Database::fetchAll(
            "SELECT pi.*, sp.full_name, sp.student_code, sp.class_year, pc.name as category_name
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.achievement_level IN ('international', 'national', 'province', 'district', 'regional')
             ORDER BY FIELD(pi.achievement_level, 'international', 'national', 'province', 'district', 'regional'), pi.event_date DESC"
        );

        // Aggregated Stats for Charts
        $stats = [
            'by_level' => Database::fetchAll(
                "SELECT achievement_level as label, COUNT(*) as count 
                 FROM portfolio_items 
                 WHERE achievement_level IN ('international', 'national', 'province', 'district', 'regional')
                 GROUP BY achievement_level"
            ),
            'by_category' => Database::fetchAll(
                "SELECT pc.name as label, COUNT(pi.id) as count 
                 FROM portfolio_categories pc 
                 JOIN portfolio_items pi ON pc.id = pi.category_id 
                 WHERE pi.achievement_level IN ('international', 'national', 'province', 'district', 'regional')
                 GROUP BY pc.id ORDER BY count DESC LIMIT 5"
            ),
            'total_students' => Database::fetch(
                "SELECT COUNT(DISTINCT student_id) as count 
                 FROM portfolio_items 
                 WHERE achievement_level IN ('international', 'national', 'province', 'district', 'regional')"
            )['count'] ?? 0
        ];

        $this->view('dashboard.executive_excellence', [
            'awards' => $awards,
            'stats' => $stats
        ]);
    }

    /**
     * Executive Showcase Console — Virtual Gallery สำหรับนำเสนอผลงานเด่นต่อแขก/ผู้บริหาร
     * แสดงเฉพาะผลงานที่ครู Verify แล้ว และจัดเรียงตามคะแนน + ความเด่น
     */
    public function executiveShowcase(): void
    {
        Auth::requireRole('executive', 'admin');

        $category = $this->query('category');
        $level = $this->query('level');

        $where = "pi.status = 'published' AND pi.verification_status = 'verified'";
        $params = [];
        if ($category) {
            $where .= " AND pi.category_id = ?";
            $params[] = (int) $category;
        }
        if ($level) {
            $where .= " AND pi.achievement_level = ?";
            $params[] = $level;
        }

        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, pc.color as category_color,
                    sp.full_name, sp.student_code, sp.class_year, sp.class_room
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             JOIN student_profiles sp ON pi.student_id = sp.id
             WHERE {$where}
             ORDER BY pi.is_featured DESC,
                      FIELD(pi.achievement_level,'international','national','province','district','school'),
                      pi.points_awarded DESC,
                      pi.created_at DESC
             LIMIT 60",
            $params
        );

        $stats = [
            'verified_count' => Database::fetch(
                "SELECT COUNT(*) as c FROM portfolio_items WHERE verification_status='verified' AND status='published'"
            )['c'] ?? 0,
            'featured_count' => Database::fetch(
                "SELECT COUNT(*) as c FROM portfolio_items WHERE verification_status='verified' AND is_featured=1 AND status='published'"
            )['c'] ?? 0,
            'student_count' => Database::fetch(
                "SELECT COUNT(DISTINCT student_id) as c FROM portfolio_items WHERE verification_status='verified' AND status='published'"
            )['c'] ?? 0,
            'avg_score' => Database::fetch(
                "SELECT ROUND(AVG(points_awarded),1) as a FROM portfolio_items WHERE verification_status='verified' AND points_awarded IS NOT NULL"
            )['a'] ?? 0,
        ];

        $categories = Database::fetchAll("SELECT id, name FROM portfolio_categories WHERE is_active = 1 ORDER BY sort_order, name");

        $this->view('dashboard.executive_showcase', [
            'works' => $works,
            'stats' => $stats,
            'categories' => $categories,
            'category' => $category,
            'level' => $level,
        ]);
    }

    /**
     * Parent Dashboard
     */
    private function parentDashboard(): void
    {
        $this->view('dashboard.parent');
    }
}
