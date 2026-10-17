<?php
/**
 * LogbookController
 * สำหรับจัดการแบบบันทึกการทำงานนักเรียน (Weekly Status Report)
 */
class LogbookController extends Controller
{
    /**
     * เก็บข้อมูลบันทึกรายสัปดาห์ของนักเรียนลงฐานข้อมูล
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect('projects');
        }

        // Validate Security
        CSRF::check();

        $userId = Auth::id();
        $projectId = $_POST['project_id'] ?? null;
        $activity = trim($_POST['activity_detail'] ?? '');
        $hours = floatval($_POST['hours_spent'] ?? 0);

        if (!$projectId || empty($activity) || $hours <= 0) {
            Session::flash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน รวมถึงจำนวนชั่วโมงที่สมเหตุสมผล');
            App::redirect("projects/{$projectId}");
            return;
        }

        // Check if student is in this project
        $isMember = Database::fetch("SELECT id FROM project_members WHERE project_id = ? AND student_id = ?", [$projectId, $userId]);
        if (!$isMember) {
            Session::flash('error', 'คุณไม่ใช่สมาชิกโครงงานนี้!');
            App::redirect('projects');
            return;
        }

        try {
            // บันทึก Logbook
            Database::insert(
                "INSERT INTO logbooks (project_id, student_id, activity_detail, hours_spent) VALUES (?, ?, ?, ?)",
                [$projectId, $userId, $activity, $hours]
            );

            // อัปเดต timestamp งานล่าสุดของโครงงาน
            Database::execute("UPDATE projects SET updated_at = NOW() WHERE id = ?", [$projectId]);

            Session::flash('success', 'บันทึกการทำงานประจำสัปดาห์ (Logbook) สำเร็จ!');

            // Phase 5: Badge event trigger — logbook_added
            try {
                BadgeService::onEvent($userId, 'logbook_added');
                EXPService::award($userId, 'logbook_added');
            } catch (\Throwable $e) {
                App::log("Badge check after logbook failed: " . $e->getMessage(), 'WARNING');
            }

            // ตัวเลือกเสริม: ไม่ต้องแจ้งเตือนครูทุกครั้งที่กรอก logbook เพื่อลด Spam รบกวนครู

        } catch (Exception $e) {
            Session::flash('error', 'บันทึกล้มเหลว: ' . $e->getMessage());
        }

        App::redirect("projects/{$projectId}");
    }
}
