<?php
/**
 * Api\StudentController — Student 360° Profile & AI Analysis Endpoints
 *
 * Provides mobile-friendly JSON endpoints for fetching the complete
 * student 360° profile, TCAS AI analysis, and portfolio data.
 *
 * All responses are strict JSON via BaseApiController.
 *
 * @package  PCSHSKSN e-Portfolio
 * @category API Controllers
 */
class ApiStudentController extends BaseApiController
{
    // ============================================================
    // STUDENT 360° PROFILE
    // ============================================================

    /**
     * GET /api/v1/student/profile
     *
     * Returns the complete Student 360° profile:
     *   - Basic profile info
     *   - Academic records & GPA
     *   - Portfolio items (paginated)
     *   - Goals, interests, badges
     *   - Projects
     *   - Profile completeness score
     *
     * Query params: ?page=1&per_page=20
     * Requires: Bearer Token (read)
     */
    public function getProfile(): void
    {
        $this->validateBearerToken(['read']);

        // Use StudentService::getStudent360() for the canonical data shape
        $profile360 = StudentService::getStudent360($this->authenticatedUserId);

        if (!$profile360 || empty($profile360['student_code'])) {
            $this->jsonError('Student profile not found', 404, 'Please complete your profile setup first');
        }

        // Paginate portfolio items if requested
        $page = max(1, (int) ($this->query('page') ?: 1));
        $perPage = min(max(1, (int) ($this->query('per_page') ?: 20)), self::MAX_PER_PAGE);

        $studentId = (int) ($profile360['id'] ?? 0);
        $totalPortfolio = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items WHERE student_id = ? AND status = 'published'",
            [$studentId]
        )['cnt'];

        $portfolioItems = Database::fetchAll(
            "SELECT pi.id, pi.title, pi.description, pi.category_id, pi.achievement_level,
                    pi.award, pi.event_date, pi.file_path, pi.thumbnail, pi.is_featured,
                    pi.view_count, pi.status, pi.tags, pi.created_at,
                    pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC
             LIMIT ? OFFSET ?",
            [$studentId, $perPage, ($page - 1) * $perPage]
        );

        // Decode JSON fields
        $portfolioItems = array_map(function ($item) {
            if (!empty($item['tags'])) {
                $item['tags'] = json_decode($item['tags'], true) ?: [];
            }
            return $item;
        }, $portfolioItems);

        // Build clean API response (strip internal fields)
        $apiProfile = [
            'basic_info' => [
                'full_name' => $profile360['full_name'] ?? null,
                'nickname' => $profile360['nickname'] ?? null,
                'student_code' => $profile360['student_code'] ?? null,
                'class_year' => $profile360['class_year'] ?? null,
                'class_room' => $profile360['class_room'] ?? null,
                'date_of_birth' => $profile360['date_of_birth'] ?? null,
                'bio' => $profile360['bio'] ?? null,
                'avatar_url' => !empty($profile360['avatar_path']) ? url('uploads/' . $profile360['avatar_path']) : null,
            ],
            'academics' => [
                'gpa' => (float) ($profile360['academic_records']['overall_gpa'] ?? 0),
                'records_by_year' => $profile360['academic_records']['by_year'] ?? [],
            ],
            'portfolio_summary' => [
                'total_published' => (int) ($profile360['portfolio_items']['count'] ?? 0),
                'by_category' => $profile360['portfolio_items']['by_category'] ?? [],
                'by_achievement_level' => $profile360['portfolio_items']['by_level'] ?? [],
                'items' => $portfolioItems,
            ],
            'goals' => $profile360['goals'] ?? [],
            'interests' => $profile360['interests'] ?? [],
            'projects' => $profile360['projects'] ?? [],
            'badges' => $profile360['badges'] ?? [],
            'profile_completeness' => $profile360['profile_completeness']['total'] ?? 0,
            'readiness_scores' => $profile360['readiness_scores'] ?? [],
        ];

        $this->jsonPaginated(
            $apiProfile,
            $totalPortfolio,
            $page,
            $perPage,
            'Student 360° profile retrieved'
        );
    }

    // ============================================================
    // PORTFOLIO ITEMS
    // ============================================================

    /**
     * GET /api/v1/student/portfolio
     *
     * List all published portfolio items for the authenticated student.
     * Query params: ?page=1&per_page=20&category=science-project
     * Requires: Bearer Token (read)
     */
    public function listPortfolio(): void
    {
        $this->validateBearerToken(['read']);

        $studentId = self::getStudentProfileId($this->authenticatedUserId);
        if (!$studentId) {
            $this->jsonError('Student profile not found', 404);
        }

        $page = max(1, (int) ($this->query('page') ?: 1));
        $perPage = min(max(1, (int) ($this->query('per_page') ?: 20)), self::MAX_PER_PAGE);
        $category = $this->query('category');

        $where = "WHERE pi.student_id = ? AND pi.status = 'published'";
        $params = [$studentId];

        if ($category) {
            $where .= " AND pc.slug = ?";
            $params[] = $category;
        }

        $total = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             {$where}",
            $params
        )['cnt'];

        $items = Database::fetchAll(
            "SELECT pi.id, pi.title, pi.description, pi.achievement_level,
                    pi.award, pi.event_date, pi.file_path, pi.thumbnail,
                    pi.is_featured, pi.view_count, pi.tags, pi.created_at,
                    pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             {$where}
             ORDER BY pi.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, ($page - 1) * $perPage]
        );

        $items = array_map(function ($item) {
            if (!empty($item['tags'])) {
                $item['tags'] = json_decode($item['tags'], true) ?: [];
            }
            // Add thumbnail/full URL if present
            if (!empty($item['thumbnail'])) {
                $item['thumbnail_url'] = url('uploads/' . $item['thumbnail']);
            }
            if (!empty($item['file_path'])) {
                $item['file_url'] = url('uploads/' . $item['file_path']);
            }
            return $item;
        }, $items);

        $this->jsonPaginated($items, $total, $page, $perPage, 'Portfolio items retrieved');
    }

    /**
     * GET /api/v1/student/portfolio/{id}
     *
     * Get a single portfolio item with full details.
     * Requires: Bearer Token (read)
     */
    public function getPortfolioItem(string $id): void
    {
        $this->validateBearerToken(['read']);

        $studentId = self::getStudentProfileId($this->authenticatedUserId);
        if (!$studentId) {
            $this->jsonError('Student profile not found', 404);
        }

        $item = Database::fetch(
            "SELECT pi.*, pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.id = ? AND pi.student_id = ?",
            [(int) $id, $studentId]
        );

        if (!$item) {
            $this->jsonError('Portfolio item not found', 404);
        }

        if (!empty($item['tags'])) {
            $item['tags'] = json_decode($item['tags'], true) ?: [];
        }

        // Fetch skills
        $item['skills'] = Database::fetchAll(
            "SELECT sm.skill_name, sm.description as skill_description, ps.proficiency
             FROM portfolio_skills ps
             JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE ps.portfolio_id = ?",
            [(int) $id]
        );

        // Add file URLs
        if (!empty($item['thumbnail'])) {
            $item['thumbnail_url'] = url('uploads/' . $item['thumbnail']);
        }
        if (!empty($item['file_path'])) {
            $item['file_url'] = url('uploads/' . $item['file_path']);
        }

        $this->jsonResponse($item, 'Portfolio item retrieved');
    }

    // ============================================================
    // TCAS AI ANALYSIS
    // ============================================================

    /**
     * GET /api/v1/student/tcas-analysis
     *
     * Get the AI-powered TCAS Gap Analysis for a target faculty.
     * Returns cached result if available (30-day TTL).
     *
     * Query params: ?faculty=medicine (required)
     * Supported: medicine, engineering, science, computing, liberal_arts, business, design
     * Requires: Bearer Token (read)
     */
    public function getTCASAnalysis(): void
    {
        $this->validateBearerToken(['read']);

        $faculty = $this->query('faculty');
        if (empty($faculty)) {
            $this->jsonError('Validation Failed', 422, [
                'faculty' => 'Required. One of: medicine, engineering, science, computing, liberal_arts, business, design',
            ]);
        }

        $result = AIService::generateTCASGapAnalysis($this->authenticatedUserId, $faculty);

        if (isset($result['error'])) {
            $this->jsonError('Analysis Failed', 400, $result['error']);
        }

        $this->jsonResponse([
            'analysis' => $result,
            'ai_generated' => $result['ai_generated'] ?? false,
            'cached' => $result['cached'] ?? false,
            'target_faculty' => $result['target_faculty'] ?? $faculty,
        ], 'TCAS Gap Analysis retrieved');
    }

    // ============================================================
    // GAMIFICATION STATS
    // ============================================================

    /**
     * GET /api/v1/student/stats
     *
     * Returns EXP, level, points, badges, and portfolio statistics.
     * Requires: Bearer Token (read)
     */
    public function getStats(): void
    {
        $this->validateBearerToken(['read']);

        // EXP/Points stats
        $expStats = EXPService::getUserStats($this->authenticatedUserId);

        // Portfolio stats
        $studentId = self::getStudentProfileId($this->authenticatedUserId);
        $portfolioCount = 0;
        $featuredCount = 0;
        if ($studentId) {
            $stats = Database::fetch(
                "SELECT COUNT(*) as total, SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured
                 FROM portfolio_items
                 WHERE student_id = ? AND status = 'published'",
                [$studentId]
            );
            $portfolioCount = (int) ($stats['total'] ?? 0);
            $featuredCount = (int) ($stats['featured'] ?? 0);
        }

        // Badge count
        $badgeCount = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM user_badges WHERE user_id = ?",
            [$this->authenticatedUserId]
        )['cnt'];

        $this->jsonResponse([
            'exp' => $expStats,
            'portfolio' => [
                'total_published' => $portfolioCount,
                'featured' => $featuredCount,
            ],
            'badges' => [
                'total_earned' => $badgeCount,
            ],
        ], 'Stats retrieved');
    }

    // ============================================================
    // NOTIFICATIONS
    // ============================================================

    /**
     * GET /api/v1/student/notifications
     *
     * List notifications for the authenticated user.
     * Query params: ?page=1&per_page=20&unread_only=0
     * Requires: Bearer Token (read)
     */
    public function getNotifications(): void
    {
        $this->validateBearerToken(['read']);

        $page = max(1, (int) ($this->query('page') ?: 1));
        $perPage = min(max(1, (int) ($this->query('per_page') ?: 20)), self::MAX_PER_PAGE);
        $unreadOnly = (bool) $this->query('unread_only');

        $where = "WHERE user_id = ?";
        $params = [$this->authenticatedUserId];

        if ($unreadOnly) {
            $where .= " AND is_read = 0";
        }

        $total = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM notifications {$where}",
            $params
        )['cnt'];

        $notifications = Database::fetchAll(
            "SELECT id, type, title, message, link, is_read, created_at
             FROM notifications
             {$where}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, ($page - 1) * $perPage]
        );

        $this->jsonPaginated($notifications, $total, $page, $perPage, 'Notifications retrieved');
    }

    /**
     * POST /api/v1/student/notifications/{id}/read
     *
     * Mark a notification as read.
     * Requires: Bearer Token (write)
     */
    public function markNotificationRead(string $id): void
    {
        $this->validateBearerToken(['write']);

        Database::execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [(int) $id, $this->authenticatedUserId]
        );

        $this->jsonResponse([], 'Notification marked as read');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Get the student_profiles.id for a given user_id.
     */
    private static function getStudentProfileId(int $userId): ?int
    {
        $result = Database::fetch(
            "SELECT id FROM student_profiles WHERE user_id = ?",
            [$userId]
        );
        return $result ? (int) $result['id'] : null;
    }
}
