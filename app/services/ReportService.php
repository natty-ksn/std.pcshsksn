<?php
/**
 * ReportService — Business Logic สำหรับ Analytics & Reports
 * 
 * รองรับ: Early Warning, School Analytics, TCAS Prediction
 */
class ReportService
{
    /**
     * Get Early Warning data for all students
     */
    public static function getEarlyWarningData(array $filters = []): array
    {
        $sql = "SELECT * FROM vw_early_warning WHERE 1=1";
        $params = [];

        if (!empty($filters['class_year'])) {
            $sql .= " AND class_year = ?";
            $params[] = $filters['class_year'];
        }
        if (!empty($filters['class_room'])) {
            $sql .= " AND class_room = ?";
            $params[] = $filters['class_room'];
        }
        if (!empty($filters['risk_level'])) {
            $sql .= " AND risk_level = ?";
            $params[] = $filters['risk_level'];
        }

        $sql .= " ORDER BY total_score ASC"; // Lowest score (highest risk) first

        $students = Database::fetchAll($sql, $params);

        // Enrich with recommendations
        foreach ($students as &$student) {
            $student['recommendations'] = self::getRiskRecommendations($student);
        }

        return $students;
    }

    /**
     * Get recommendations based on risk factors
     */
    public static function getRiskRecommendations(array $student): array
    {
        $recommendations = [];

        if ($student['gpa_score'] < 15) {
            $recommendations[] = [
                'type' => 'academic',
                'priority' => 'high',
                'message' => 'GPA ต่ำ — ควรพบที่ปรึกษาเพื่อวางแผนการเรียน',
                'icon' => '📚',
            ];
        }

        if ($student['portfolio_score'] < 10) {
            $recommendations[] = [
                'type' => 'portfolio',
                'priority' => 'high',
                'message' => 'ผลงานน้อย — ควรเพิ่มผลงานอย่างน้อย 5 รายการ',
                'icon' => '📁',
            ];
        }

        if ($student['activity_score'] < 15) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'message' => 'ไม่มีการอัปเดตผลงานล่าสุด 30 วัน — ควรเพิ่มผลงานใหม่',
                'icon' => '📝',
            ];
        }

        if ($student['goal_score'] < 15) {
            $recommendations[] = [
                'type' => 'goals',
                'priority' => 'medium',
                'message' => 'ยังไม่มีเป้าหมายการศึกษา — ควรกำหนดเป้าหมาย',
                'icon' => '🎯',
            ];
        }

        if ($student['project_score'] < 10) {
            $recommendations[] = [
                'type' => 'projects',
                'priority' => 'medium',
                'message' => 'ไม่ได้เข้าร่วมโครงการ — ควรเข้าร่วมอย่างน้อย 1 โครงงาน',
                'icon' => '🔬',
            ];
        }

        if ($student['logbook_score'] < 5) {
            $recommendations[] = [
                'type' => 'logbook',
                'priority' => 'low',
                'message' => 'บันทึก logbook น้อย — ควรบันทึกกิจกรรมสม่ำเสมอ',
                'icon' => '📓',
            ];
        }

        return $recommendations;
    }

    /**
     * Get school-wide analytics summary
     */
    public static function getSchoolAnalytics(): array
    {
        $summary = Database::fetch("SELECT * FROM vw_school_analytics");

        if (!$summary) {
            // Fallback calculations
            $summary = [
                'total_students' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE role='student' AND is_active=1")['cnt'],
                'total_teachers' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE role='teacher' AND is_active=1")['cnt'],
                'total_portfolios' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM portfolio_items WHERE status='published'")['cnt'],
                'active_students_30d' => (int) Database::fetch("SELECT COUNT(DISTINCT student_id) as cnt FROM portfolio_items WHERE status='published' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['cnt'],
                'active_projects' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE status IN ('draft','proposal_submitted','proposal_approved','in_progress')")['cnt'],
                'completed_projects' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE status='completed'")['cnt'],
                'avg_gpa' => round((float) Database::fetch("SELECT AVG(grade) as avg_gpa FROM academic_records")['avg_gpa'], 2),
                'logbooks_30d' => (int) Database::fetch("SELECT COUNT(*) as cnt FROM logbooks WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['cnt'],
                'risk_low_count' => 0,
                'risk_medium_count' => 0,
                'risk_high_count' => 0,
                'risk_critical_count' => 0,
            ];
        }

        // Add portfolio distribution by category
        $summary['portfolio_by_category'] = Database::fetchAll(
            "SELECT pc.name, pc.icon, pc.color, COUNT(pi.id) as count
             FROM portfolio_categories pc
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.status = 'published'
             GROUP BY pc.id
             ORDER BY count DESC"
        );

        // Add class distribution
        $summary['students_by_class'] = Database::fetchAll(
            "SELECT sp.class_year, sp.class_room, COUNT(*) as count
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE u.role = 'student'
             GROUP BY sp.class_year, sp.class_room
             ORDER BY sp.class_year, sp.class_room"
        );

        // Add engagement trend (last 6 months)
        $summary['engagement_trend'] = Database::fetchAll(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(DISTINCT student_id) as active_students,
                COUNT(*) as portfolio_additions
             FROM portfolio_items 
             WHERE status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month
             ORDER BY month"
        );

        // Add project status distribution
        $summary['project_status_dist'] = Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM projects GROUP BY status"
        );

        return $summary ?? [];
    }

    /**
     * Calculate TCAS Readiness for a student
     */
    public static function calculateTCASReadiness(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) {
            return ['error' => 'ไม่พบโปรไฟล์นักเรียน'];
        }

        $components = [];
        $totalScore = 0;

        // 1. GPA Score (25 points)
        $gpa = Database::fetch(
            "SELECT AVG(grade) as gpa FROM academic_records WHERE student_id = ?",
            [$studentId]
        )['gpa'] ?? 0;

        $gpaScore = $gpa >= 3.5 ? 25 : ($gpa >= 3.0 ? 20 : ($gpa >= 2.5 ? 15 : ($gpa >= 2.0 ? 10 : 5)));
        $totalScore += $gpaScore;
        $components['gpa'] = [
            'score' => $gpaScore,
            'max' => 25,
            'value' => round($gpa, 2),
            'status' => $gpa >= 3.0 ? 'pass' : 'warning',
            'label' => 'GPA',
        ];

        // 2. Portfolio (20 points)
        $portfolioCount = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items WHERE student_id = ? AND status = 'published'",
            [$studentId]
        )['cnt'];

        $portfolioScore = min($portfolioCount * 2, 20);
        $totalScore += $portfolioScore;
        $components['portfolio'] = [
            'score' => $portfolioScore,
            'max' => 20,
            'value' => $portfolioCount,
            'status' => $portfolioCount >= 10 ? 'pass' : ($portfolioCount >= 5 ? 'warning' : 'fail'),
            'label' => 'จำนวนผลงาน',
        ];

        // 3. Awards (15 points)
        $awards = Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items WHERE student_id = ? AND award IS NOT NULL AND award != ''",
            [$studentId]
        )['cnt'];

        $awardScore = min($awards * 3, 15);
        $totalScore += $awardScore;
        $components['awards'] = [
            'score' => $awardScore,
            'max' => 15,
            'value' => $awards,
            'status' => $awards >= 3 ? 'pass' : ($awards >= 1 ? 'warning' : 'fail'),
            'label' => 'รางวัล',
        ];

        // 4. Research (10 points)
        $research = Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items pi 
             JOIN portfolio_categories pc ON pi.category_id = pc.id 
             WHERE pi.student_id = ? AND pc.slug = 'research' AND pi.status = 'published'",
            [$studentId]
        )['cnt'];

        $researchScore = min($research * 5, 10);
        $totalScore += $researchScore;
        $components['research'] = [
            'score' => $researchScore,
            'max' => 10,
            'value' => $research,
            'status' => $research >= 1 ? 'pass' : 'warning',
            'label' => 'ผลงานวิจัย',
        ];

        // 5. Leadership (10 points)
        $leadership = Database::fetch(
            "SELECT COUNT(*) as cnt FROM project_members WHERE student_id = ? AND role_in_project = 'leader'",
            [$userId]
        )['cnt'];

        $leadershipScore = min($leadership * 5, 10);
        $totalScore += $leadershipScore;
        $components['leadership'] = [
            'score' => $leadershipScore,
            'max' => 10,
            'value' => $leadership,
            'status' => $leadership >= 1 ? 'pass' : 'warning',
            'label' => 'ความเป็นผู้นำ',
        ];

        // 6. RIASEC Match (10 points)
        $assessment = Database::fetch(
            "SELECT dominant_trait FROM student_assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );
        $riasecScore = $assessment ? 10 : 0;
        $totalScore += $riasecScore;
        $components['riasec'] = [
            'score' => $riasecScore,
            'max' => 10,
            'value' => $assessment['dominant_trait'] ?? null,
            'status' => $assessment ? 'pass' : 'warning',
            'label' => 'RIASEC Assessment',
        ];

        // 7. Logbook Hours (10 points)
        $logbookHours = Database::fetch(
            "SELECT COALESCE(SUM(hours_spent), 0) as total FROM logbooks WHERE student_id = ?",
            [$userId]
        )['total'];

        $logbookScore = $logbookHours >= 50 ? 10 : ($logbookHours >= 20 ? 7 : ($logbookHours >= 10 ? 4 : 1));
        $totalScore += $logbookScore;
        $components['logbook'] = [
            'score' => $logbookScore,
            'max' => 10,
            'value' => round($logbookHours, 1),
            'status' => $logbookHours >= 20 ? 'pass' : 'warning',
            'label' => 'ชั่วโมง Logbook',
        ];

        // Generate gaps and recommendations
        $gaps = [];
        $recommendations = [];
        foreach ($components as $key => $comp) {
            if ($comp['status'] === 'fail') {
                $gaps[] = $comp['label'] . ' ต่ำกว่าเกณฑ์';
                $recommendations[] = self::getRecommendationForComponent($key);
            } elseif ($comp['status'] === 'warning') {
                $recommendations[] = self::getRecommendationForComponent($key, true);
            }
        }

        // Save to database
        Database::execute(
            "INSERT INTO tcas_predictions 
             (student_id, prediction_year, overall_score, component_scores, gaps, recommendations) 
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
             overall_score = VALUES(overall_score),
             component_scores = VALUES(component_scores),
             gaps = VALUES(gaps),
             recommendations = VALUES(recommendations)",
            [
                $studentId,
                date('Y'),
                $totalScore,
                json_encode($components, JSON_UNESCAPED_UNICODE),
                json_encode($gaps, JSON_UNESCAPED_UNICODE),
                implode("\n", $recommendations),
            ]
        );

        return [
            'overall_score' => $totalScore,
            'max_score' => 100,
            'components' => $components,
            'gaps' => $gaps,
            'recommendations' => $recommendations,
            'level' => $totalScore >= 80 ? 'excellent' : ($totalScore >= 60 ? 'good' : ($totalScore >= 40 ? 'fair' : 'needs_improvement')),
        ];
    }

    /**
     * Get recommendation for a component
     */
    private static function getRecommendationForComponent(string $key, bool $improve = false): string
    {
        $recommendations = [
            'gpa' => $improve ? 'พยายามเพิ่ม GPA ให้ได้อย่างน้อย 3.0' : 'GPA ต่ำมาก — ควรพบที่ปรึกษาเพื่อวางแผนการเรียน',
            'portfolio' => $improve ? 'เพิ่มผลงานอีก 5 รายการเพื่อให้ถึงเกณฑ์' : 'Portfolio น้อยมาก — เริ่มเพิ่มผลงานอย่างน้อย 10 รายการ',
            'awards' => $improve ? 'เข้าร่วมแข่งขันวิชาการเพื่อรับรางวัล' : 'ไม่มีรางวัล — ลงแข่งขันวิชาการระดับโรงเรียนขึ้นไป',
            'research' => $improve ? 'เข้าร่วมงานวิจัยเพิ่มเติม' : 'ไม่มีผลงานวิจัย — เข้าร่วมโครงการวิจัยกับครู',
            'leadership' => $improve ? 'รับบทบาท leader ในโครงการถัดไป' : 'ไม่มีบทบาทผู้นำ — รับตำแหน่ง leader ในโครงงาน',
            'riasec' => $improve ? '' : 'ยังไม่ทำแบบประเมิน RIASEC — ทำแบบประเมินตนเอง',
            'logbook' => $improve ? 'บันทึกกิจกรรมอย่างสม่ำเสมอ' : 'ชั่วโมง logbook น้อยมาก — บันทึกกิจกรรมทุกสัปดาห์',
        ];

        return $recommendations[$key] ?? '';
    }

    /**
     * Get student profile ID from user ID
     */
    private static function getStudentProfileId(int $userId): ?int
    {
        $result = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);
        return $result ? (int) $result['id'] : null;
    }

    /**
     * Cache analytics data (Deprecated: use \Core\Cache::put instead)
     */
    public static function cacheAnalytics(string $key, array $value, int $ttlMinutes = 60): void
    {
        \Core\Cache::put($key, $value, $ttlMinutes * 60, ['legacy_cache']);
    }

    /**
     * Get cached analytics data (Deprecated: use \Core\Cache::get instead)
     */
    public static function getCachedAnalytics(string $key): ?array
    {
        return \Core\Cache::get($key);
    }
}
