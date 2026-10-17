<?php
/**
 * AnalyticsController — จัดการ Data Products ทั้งหมด
 * 
 * - Early Warning System
 * - TCAS Readiness Predictor
 * - School Analytics Dashboard
 */
class AnalyticsController extends Controller
{
    /**
     * Early Warning System — แสดงรายการนักเรียนที่มีความเสี่ยง
     */
    public function earlyWarning(): void
    {
        if (!Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        $filters = [];
        if ($this->query('class_year'))
            $filters['class_year'] = (int) $this->query('class_year');
        if ($this->query('class_room'))
            $filters['class_room'] = $this->query('class_room');
        if ($this->query('risk_level'))
            $filters['risk_level'] = $this->query('risk_level');

        // Generate data with Cache
        // Cache early warning parameters (key varies by filters) for 1 hour (3600 seconds)
        // Note: Task 9.1 says early warning cache = 1 hour
        $cacheKey = 'early_warning_data_' . md5(json_encode($filters));
        $students = \Core\Cache::remember($cacheKey, 3600, function() use ($filters) {
            return ReportService::getEarlyWarningData($filters);
        }, ['early_warning_summary']);

        // Summary stats
        $total = count($students);
        $riskCounts = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        foreach ($students as $s) {
            if (isset($riskCounts[$s['risk_level']])) {
                $riskCounts[$s['risk_level']]++;
            }
        }

        $this->view('analytics/early_warning', [
            'title' => '🚨 ระบบแจ้งเตือนนักเรียนที่มีความเสี่ยง | Early Warning System',
            'students' => $students,
            'total' => $total,
            'riskCounts' => $riskCounts,
            'filters' => $filters,
        ]);
    }

    /**
     * Notify risk to advisor/teacher
     */
    public function notifyRisk(): void
    {
        if (!Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
            $this->json(['success' => false, 'error' => 'ไม่มีสิทธิ์'], 403);
            return;
        }

        CSRF::check();
        $studentId = (int) $this->input('student_id');
        $riskLevel = $this->input('risk_level', 'medium');

        // Find the user ID from student profile (supports both student_profiles.id and users.id)
        $user = Database::fetch(
            "SELECT user_id FROM student_profiles WHERE id = ? OR user_id = ? ORDER BY (user_id = ?) DESC LIMIT 1",
            [$studentId, $studentId, $studentId]
        );
        if ($user) {
            Notifier::earlyWarningAlert((int) $user['user_id'], $riskLevel);
            $this->json(['success' => true, 'message' => 'ส่งแจ้งเตือนเรียบร้อยแล้ว']);
        } else {
            $this->json(['success' => false, 'error' => 'ไม่พบนักเรียน']);
        }
    }

    /**
     * TCAS Readiness Predictor
     */
    public function tcasReadiness(): void
    {
        if (!Auth::hasAnyRole(['student', 'admin', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้');
            return;
        }

        if (Auth::hasAnyRole(['executive', 'admin']) && !Auth::hasRole('student')) {
            App::redirect('executive/tcas');
            return;
        }

        // Cache TCAS readiness data for 2 hours (7200 seconds)
        // Key is specific to the student ID
        $userId = Auth::id();
        $cacheKey = "readiness_tcas:{$userId}";
        $readiness = \Core\Cache::remember($cacheKey, 7200, function() use ($userId) {
            return ReportService::calculateTCASReadiness($userId);
        }, ["readiness_tcas"]);

        if (isset($readiness['error'])) {
            // User doesn't have a student profile (e.g., executive/admin account)
            Session::flash('warning', 'หน้านี้สำหรับนักเรียนเท่านั้น — กรุณาเข้าใช้งานด้วยบัญชีนักเรียน');
            App::redirect('dashboard');
            return;
        }

        // Get student's goals for target matching
        $goals = StudentService::getGoals(Auth::id());

        $this->view('analytics/tcas_readiness', [
            'title' => '🎯 TCAS Readiness Predictor | วิเคราะห์ความพร้อมเข้ามหาวิทยาลัย',
            'readiness' => $readiness,
            'goals' => $goals,
        ]);
    }

    /**
     * School Analytics Dashboard — สำหรับผู้บริหาร
     */
    public function schoolDashboard(): void
    {
        if (!Auth::hasAnyRole(['admin', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        // Cache for 30 minutes (1800 seconds)
        $data = \Core\Cache::remember('school_dashboard_summary', 1800, function() {
            return ReportService::getSchoolAnalytics();
        }, ['school_dashboard_summary', 'school_analytics']);

        $this->view('analytics/school_dashboard', [
            'title' => '📊 PCSHSKSN e-Portfolio Analytics Dashboard',
            'data' => $data,
        ]);
    }

    /**
     * Cron job: Check milestones and send notifications
     * Called by system cron: curl http://yourdomain/cron/milestone-check
     */
    public function cronMilestones(): void
    {
        // Simple auth via query param (in production, use a secret token)
        $token = $_GET['token'] ?? '';
        $secret = Env::get('CRON_TOKEN', 'default-secret-change-me');

        if (empty($secret) || empty($token) || !hash_equals($secret, $token)) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        Notifier::checkMilestones();
        echo 'OK — Milestone check completed at ' . date('Y-m-d H:i:s');
    }

    /**
     * Export Executive Report as PDF (Phase 3 — Premium Executive Analytics)
     */
    public function exportExecutiveReport(): void
    {
        if (!Auth::hasAnyRole(['admin', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        // Get fresh analytics data for report
        $data = ReportService::getSchoolAnalytics();

        // Render PDF with premium template
        try {
            PDFService::renderView('analytics/pdf/executive_report', [
                'data' => $data,
                'report_date' => PDFService::thaiDate(),
            ], [
                'title' => 'รายงานสรุปผลการดำเนินงานระดับองค์กร',
                'filename' => 'Executive_Report_' . date('Ymd_Hi'),
                'mode' => 'D',
                'watermark' => 'CONFIDENTIAL',
            ]);
        } catch (\Throwable $e) {
            App::log('Executive PDF export failed: ' . $e->getMessage(), 'ERROR');
            Session::flash('error', 'ไม่สามารถสร้างเอกสาร PDF ได้: ' . $e->getMessage());
            App::redirect('analytics/dashboard');
        }
    }
}
