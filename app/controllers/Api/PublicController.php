<?php
/**
 * Api\PublicController — Public (No-Auth) API Endpoints
 *
 * Provides read-only access to public data without authentication.
 * Used for shared portfolio links and public student lookup.
 *
 * @package  PCSHSKSN e-Portfolio
 * @category API Controllers
 */
class ApiPublicController extends BaseApiController
{
    /**
     * GET /api/v1/share/{token}
     *
     * Fetch a shared portfolio by its share token.
     * No authentication required.
     */
    public function getSharedPortfolio(string $token): void
    {
        $share = Database::fetch(
            "SELECT ps.*, sp.full_name, sp.nickname, sp.student_code,
                    sp.class_year, sp.class_room, sp.avatar_path, sp.bio,
                    u.username, u.email, u.role
             FROM portfolio_shares ps
             JOIN student_profiles sp ON ps.student_id = sp.id
             JOIN users u ON sp.user_id = u.id
             WHERE ps.share_token = ?
               AND (ps.expires_at IS NULL OR ps.expires_at > NOW())
               AND u.is_active = 1",
            [$token]
        );

        if (!$share) {
            $this->jsonError('Shared portfolio not found or expired', 404);
        }

        // Fetch portfolio items
        $items = Database::fetchAll(
            "SELECT pi.id, pi.title, pi.description, pi.achievement_level,
                    pi.award, pi.event_date, pi.thumbnail, pi.is_featured,
                    pi.tags, pi.created_at,
                    pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC
             LIMIT 50",
            [(int) $share['student_id']]
        );

        $items = array_map(function ($item) {
            if (!empty($item['tags'])) {
                $item['tags'] = json_decode($item['tags'], true) ?: [];
            }
            if (!empty($item['thumbnail'])) {
                $item['thumbnail_url'] = url('uploads/' . $item['thumbnail']);
            }
            return $item;
        }, $items);

        // Clean sensitive data
        unset($share['email']);

        $this->jsonResponse([
            'shared_by' => [
                'full_name' => $share['full_name'],
                'student_code' => $share['student_code'],
                'class_year' => $share['class_year'],
                'class_room' => $share['class_room'],
                'bio' => $share['bio'],
                'avatar_url' => !empty($share['avatar_path']) ? url('uploads/' . $share['avatar_path']) : null,
            ],
            'portfolio_items' => $items,
            'shared_at' => $share['created_at'],
            'expires_at' => $share['expires_at'],
        ], 'Shared portfolio retrieved');
    }

    /**
     * GET /api/v1/public/student/{code}
     *
     * Fetch a student's public profile by student code.
     * Only returns basic info + published portfolio count.
     * No authentication required.
     */
    public function getStudentByCode(string $code): void
    {
        $student = Database::fetch(
            "SELECT sp.full_name, sp.nickname, sp.student_code,
                    sp.class_year, sp.class_room, sp.avatar_path, sp.bio,
                    (SELECT COUNT(*) FROM portfolio_items pi
                     WHERE pi.student_id = sp.id AND pi.status = 'published') as portfolio_count
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE sp.student_code = ? AND u.is_active = 1",
            [$code]
        );

        if (!$student) {
            $this->jsonError('Student not found', 404);
        }

        if (!empty($student['avatar_path'])) {
            $student['avatar_url'] = url('uploads/' . $student['avatar_path']);
        }

        $this->jsonResponse($student, 'Student profile retrieved');
    }

    /**
     * GET /api/v1/auth/status
     *
     * Public API health check.
     */
    public function status(): void
    {
        $this->jsonResponse([
            'status' => 'online',
            'api_version' => 'v1',
            'app_name' => Env::get('APP_NAME', 'PCSHSKSN e-Portfolio'),
            'timestamp' => date('Y-m-d H:i:s'),
        ], 'API is running');
    }
}
