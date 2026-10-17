<?php
/**
 * ApiController — JSON API for AJAX calls
 */
class ApiController extends Controller
{
    public function stats(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::json(['error' => 'No profile'], 404);
            return;
        }

        $total = Database::fetch("SELECT COUNT(*) as c FROM portfolio_items WHERE student_id = ? AND status = 'published'", [$profile['id']])['c'] ?? 0;
        $gpa = Database::fetch("SELECT ROUND(SUM(grade*credit)/NULLIF(SUM(credit),0),2) as gpa FROM academic_records WHERE student_id = ?", [$profile['id']])['gpa'] ?? 0;

        App::json(['total_works' => $total, 'gpa' => $gpa]);
    }

    public function searchPortfolio(): void
    {
        $q = $this->query('q');
        if (strlen($q) < 2) {
            App::json([]);
            return;
        }

        $results = Database::fetchAll(
            "SELECT pi.id, pi.title, pc.icon FROM portfolio_items pi LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.title LIKE ? AND pi.status = 'published' LIMIT 10",
            ["%{$q}%"]
        );
        App::json($results);
    }

    public function notificationCount(): void
    {
        $count = Database::fetch("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0", [Auth::id()])['c'] ?? 0;
        $recent = Database::fetchAll(
            "SELECT id, type, title, message, link, is_read, created_at 
             FROM notifications WHERE user_id = ? 
             ORDER BY created_at DESC LIMIT 5",
            [Auth::id()]
        );
        App::json(['count' => $count, 'notifications' => $recent]);
    }

    public function searchStudents(): void
    {
        $q = $this->query('q');
        if (strlen($q) < 2) {
            App::json([]);
            return;
        }

        $results = Database::fetchAll(
            "SELECT u.id as user_id, sp.full_name, sp.class_year, sp.class_room, sp.avatar_path
             FROM users u
             JOIN student_profiles sp ON u.id = sp.user_id
             WHERE (sp.full_name LIKE ? OR sp.student_code LIKE ?) 
             AND u.role = 'student'
             ORDER BY sp.full_name ASC LIMIT 10",
            ["%{$q}%", "{$q}%"]
        );
        App::json($results);
    }
}
