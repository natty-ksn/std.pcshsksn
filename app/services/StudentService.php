<?php
/**
 * StudentService — Business Logic สำหรับจัดการข้อมูลนักเรียน
 * 
 * รองรับ: Student 360° Profile, Early Warning, TCAS Readiness
 */
class StudentService
{
    /**
     * Get complete Student 360° profile
     */
    public static function getStudent360(int $userId): array
    {
        // Try view first
        $profile = Database::fetch("SELECT * FROM vw_student_360 WHERE user_id = ?", [$userId]);

        if ($profile) {
            // Enrich with additional data
            $profile['academic_records'] = self::getAcademicRecords($userId);
            $profile['portfolio_items'] = self::getPortfolioSummary($userId);
            $profile['goals'] = self::getGoals($userId);
            $profile['interests'] = self::getInterests($userId);
            $profile['projects'] = self::getProjects($userId);
            $profile['badges'] = self::getBadges($userId);
            $profile['assessments'] = self::getAssessments($userId);
            $profile['readiness_scores'] = self::getReadinessScores($userId);
            $profile['profile_completeness'] = self::calculateProfileCompleteness($userId);

            return $profile;
        }

        // Fallback: build manually
        return self::buildStudent360Fallback($userId);
    }

    /**
     * Get academic records with GPA calculation
     */
    public static function getAcademicRecords(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) return [];

        $records = Database::fetchAll(
            "SELECT * FROM academic_records 
             WHERE student_id = ? 
             ORDER BY academic_year DESC, semester DESC",
            [$studentId]
        );

        // Calculate GPA per year
        $gpaByYear = [];
        foreach ($records as $record) {
            $year = $record['academic_year'];
            if (!isset($gpaByYear[$year])) {
                $gpaByYear[$year] = ['grades' => [], 'credits' => 0];
            }
            $gpaByYear[$year]['grades'][] = $record['grade'];
            $gpaByYear[$year]['credits'] += $record['credit'];
        }

        $gpas = [];
        foreach ($gpaByYear as $year => $data) {
            $gpas[$year] = round(array_sum($data['grades']) / count($data['grades']), 2);
        }

        return [
            'records' => $records,
            'gpa_by_year' => $gpas,
            'overall_gpa' => !empty($records) ? round(array_sum(array_column($records, 'grade')) / count($records), 2) : 0,
            'total_credits' => array_sum(array_column($records, 'credit')),
            'subject_count' => count($records),
        ];
    }

    /**
     * Get portfolio summary
     */
    public static function getPortfolioSummary(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) return ['count' => 0, 'items' => [], 'by_category' => [], 'by_level' => []];

        $items = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, pc.color as category_color
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC",
            [$studentId]
        );

        $byCategory = [];
        $byLevel = [];
        foreach ($items as $item) {
            $cat = $item['category_name'] ?? 'อื่นๆ';
            $byCategory[$cat] = ($byCategory[$cat] ?? 0) + 1;

            $level = $item['achievement_level'] ?? 'none';
            $byLevel[$level] = ($byLevel[$level] ?? 0) + 1;
        }

        return [
            'count' => count($items),
            'items' => array_slice($items, 0, 10), // Latest 10
            'by_category' => $byCategory,
            'by_level' => $byLevel,
            'featured_count' => count(array_filter($items, fn($i) => $i['is_featured'])),
        ];
    }

    /**
     * Get student goals
     */
    public static function getGoals(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) return [];

        return Database::fetchAll(
            "SELECT * FROM student_goals WHERE student_id = ? ORDER BY created_at DESC",
            [$studentId]
        );
    }

    /**
     * Get student interests
     */
    public static function getInterests(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) return [];

        $interests = Database::fetchAll(
            "SELECT * FROM student_interests WHERE student_id = ? ORDER BY proficiency DESC",
            [$studentId]
        );

        $grouped = [];
        foreach ($interests as $interest) {
            $type = $interest['type'];
            $grouped[$type][] = $interest;
        }

        return $grouped;
    }

    /**
     * Get student projects
     */
    public static function getProjects(int $userId): array
    {
        $projects = Database::fetchAll(
            "SELECT p.*, pm.role_in_project
             FROM projects p
             JOIN project_members pm ON p.id = pm.project_id
             WHERE pm.student_id = ?
             ORDER BY p.created_at DESC",
            [$userId]
        );

        $statusLabels = [
            'draft' => 'ร่าง',
            'proposal_submitted' => 'เสนอโครงงานแล้ว',
            'proposal_approved' => 'อนุมัติแล้ว',
            'in_progress' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสมบูรณ์',
            'archived' => 'เก็บถาวร',
        ];

        foreach ($projects as &$project) {
            $project['status_label'] = $statusLabels[$project['status']] ?? $project['status'];
        }

        return $projects;
    }

    /**
     * Get earned badges
     */
    public static function getBadges(int $userId): array
    {
        return Database::fetchAll(
            "SELECT ub.*, bm.name, bm.description, bm.icon, bm.criteria
             FROM user_badges ub
             JOIN badges_master bm ON ub.badge_code = bm.code
             WHERE ub.user_id = ?
             ORDER BY ub.earned_at DESC",
            [$userId]
        );
    }

    /**
     * Get RIASEC assessment
     */
    public static function getAssessments(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM student_assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        );
    }

    /**
     * Get readiness scores
     */
    public static function getReadinessScores(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) return [];

        return Database::fetchAll(
            "SELECT * FROM readiness_scores WHERE student_id = ? ORDER BY calculated_at DESC",
            [$studentId]
        );
    }

    /**
     * Calculate profile completeness (0-100)
     */
    public static function calculateProfileCompleteness(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        $score = 0;
        $details = [];

        // Basic info (20 points)
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [$userId]);
        if ($profile) {
            $filled = 0;
            $total = 6;
            if (!empty($profile['full_name'])) $filled++;
            if (!empty($profile['nickname'])) $filled++;
            if (!empty($profile['avatar_path'])) $filled++;
            if (!empty($profile['bio'])) $filled++;
            if (!empty($profile['phone'])) $filled++;
            if (!empty($profile['date_of_birth'])) $filled++;
            $basicScore = round(($filled / $total) * 20);
            $score += $basicScore;
            $details['basic_info'] = ['score' => $basicScore, 'max' => 20, 'filled' => $filled, 'total' => $total];
        }

        // Academic records (20 points)
        if ($studentId) {
            $recordCount = Database::fetch("SELECT COUNT(*) as cnt FROM academic_records WHERE student_id = ?", [$studentId])['cnt'];
            $academicScore = min($recordCount * 2, 20);
            $score += $academicScore;
            $details['academics'] = ['score' => $academicScore, 'max' => 20, 'records' => $recordCount];
        }

        // Portfolio (25 points)
        if ($studentId) {
            $portfolioCount = Database::fetch("SELECT COUNT(*) as cnt FROM portfolio_items WHERE student_id = ? AND status = 'published'", [$studentId])['cnt'];
            $portfolioScore = min($portfolioCount * 3, 25);
            $score += $portfolioScore;
            $details['portfolio'] = ['score' => $portfolioScore, 'max' => 25, 'items' => $portfolioCount];
        }

        // Goals (15 points)
        if ($studentId) {
            $goalCount = Database::fetch("SELECT COUNT(*) as cnt FROM student_goals WHERE student_id = ?", [$studentId])['cnt'];
            $goalScore = min($goalCount * 5, 15);
            $score += $goalScore;
            $details['goals'] = ['score' => $goalScore, 'max' => 15, 'count' => $goalCount];
        }

        // Assessment (10 points)
        $assessCount = Database::fetch("SELECT COUNT(*) as cnt FROM student_assessments WHERE user_id = ?", [$userId])['cnt'];
        $assessScore = min($assessCount * 10, 10);
        $score += $assessScore;
        $details['assessment'] = ['score' => $assessScore, 'max' => 10, 'completed' => $assessCount > 0];

        // Interests (10 points)
        if ($studentId) {
            $interestCount = Database::fetch("SELECT COUNT(*) as cnt FROM student_interests WHERE student_id = ?", [$studentId])['cnt'];
            $interestScore = min($interestCount * 2, 10);
            $score += $interestScore;
            $details['interests'] = ['score' => $interestScore, 'max' => 10, 'count' => $interestCount];
        }

        return [
            'total' => min($score, 100),
            'details' => $details,
        ];
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
     * Fallback: Build Student 360 without view
     */
    private static function buildStudent360Fallback(int $userId): array
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [$userId]);

        return [
            'user_id' => $userId,
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'full_name' => $profile['full_name'] ?? $user['full_name'] ?? '',
            'nickname' => $profile['nickname'] ?? '',
            'student_code' => $profile['student_code'] ?? '',
            'class_year' => $profile['class_year'] ?? 0,
            'class_room' => $profile['class_room'] ?? '',
            'avatar_path' => $profile['avatar_path'] ?? null,
            'bio' => $profile['bio'] ?? '',
            'academic_records' => self::getAcademicRecords($userId),
            'portfolio_items' => self::getPortfolioSummary($userId),
            'goals' => self::getGoals($userId),
            'interests' => self::getInterests($userId),
            'projects' => self::getProjects($userId),
            'badges' => self::getBadges($userId),
            'assessments' => self::getAssessments($userId),
            'readiness_scores' => self::getReadinessScores($userId),
            'profile_completeness' => self::calculateProfileCompleteness($userId),
        ];
    }
}
