<?php
/**
 * PublicController — จัดการหน้า Public Profile (Showcase)
 */
class PublicController extends Controller
{
    public function profile($code): void
    {
        // Check if system allows sharing
        if (Settings::get('allow_sharing') != '1') {
            die('ระบบปิดการแชร์ผลงานสาธารณะชั่วคราว');
        }

        // Find student profile by code
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE student_code = ?", [$code]);
        if (!$profile) {
            http_response_code(404);
            die('ไม่พบประวัตินักเรียน หรือรหัสไม่ถูกต้อง');
        }

        $studentId = $profile['id'];

        // Fetch Featured Portfolio Items
        $featuredWorks = Database::fetchAll(
            "SELECT p.*, c.name as category_name, c.icon as category_icon 
             FROM portfolio_items p 
             LEFT JOIN portfolio_categories c ON p.category_id = c.id 
             WHERE p.student_id = ? AND p.status = 'published' AND p.is_featured = 1
             ORDER BY p.event_date DESC",
            [$studentId]
        );

        // Fetch GPA Summarized
        $gpa = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa 
             FROM academic_records 
             WHERE student_id = ?",
            [$studentId]
        )['gpa'] ?? 'N/A';

        // Load specific layout for public profile (or render directly)
        $this->view('public.profile', [
            'profile' => $profile,
            'featuredWorks' => $featuredWorks,
            'gpa' => $gpa
        ], 'blank'); // Use a blank layout or a specific public layout
    }
}
