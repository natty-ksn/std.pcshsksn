<?php
/**
 * ShareController — Public Portfolio Sharing
 */
class ShareController extends Controller
{
    public function render(string $token): void
    {
        $share = Database::fetch(
            "SELECT ps.*, sp.full_name, sp.student_code, sp.class_year, sp.class_room, sp.bio, sp.avatar_path
             FROM portfolio_shares ps JOIN student_profiles sp ON ps.student_id = sp.id
             WHERE ps.share_token = ? AND ps.is_active = 1 AND (ps.expires_at IS NULL OR ps.expires_at > NOW())",
            [$token]
        );

        if (!$share) {
            http_response_code(404);
            echo '<h2 style="text-align:center;padding:60px;">ลิงก์ไม่ถูกต้องหรือหมดอายุ</h2>';
            return;
        }

        Database::execute("UPDATE portfolio_shares SET view_count = view_count + 1 WHERE id = ?", [$share['id']]);

        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon FROM portfolio_items pi LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id WHERE pi.student_id = ? AND pi.status = 'published' ORDER BY pi.is_featured DESC, pi.created_at DESC",
            [$share['student_id']]
        );

        $this->view('share.view', ['share' => $share, 'works' => $works], 'auth');
    }
}
