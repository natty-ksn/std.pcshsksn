<?php
/**
 * DocumentController
 * สำหรับจัดการชั้นข้อมูล (Metadata) ของเอกสารและสื่อประสมโครงงาน
 */
class DocumentController extends Controller
{
    /**
     * รับข้อมูล Metadata หลังจากไฟล์บินเข้า Google Cloud Storage สำเร็จ
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect('projects');
        }

        CSRF::check();

        $userId = Auth::id();
        $projectId = $_POST['project_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $documentType = $_POST['document_type'] ?? '';
        $gcsPath = trim($_POST['file_path_gcs'] ?? '');

        if (!$projectId || empty($title) || empty($documentType) || empty($gcsPath)) {
            Session::flash('error', 'ข้อมูลไม่ครบถ้วน (ไม่พบเส้นทางไฟล์จาก Cloud Storage)');
            App::redirect("projects/{$projectId}");
            return;
        }

        // Security: Check if current user is a member, advisor, or admin of this project
        $isAuthorized = false;
        if (Auth::hasRole('admin')) {
            $isAuthorized = true;
        } else {
            $member = Database::fetch(
                "SELECT 1 FROM project_members WHERE project_id = ? AND student_id = ?",
                [$projectId, $userId]
            );
            if ($member) {
                $isAuthorized = true;
            } else {
                $advisor = Database::fetch(
                    "SELECT 1 FROM projects WHERE id = ? AND (advisor_id = ? OR co_advisor_id = ?)",
                    [$projectId, $userId, $userId]
                );
                if ($advisor) {
                    $isAuthorized = true;
                }
            }
        }

        if (!$isAuthorized) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เพิ่มเอกสารในโครงงานนี้');
            return;
        }

        try {
            Database::beginTransaction();

            // หา Version ล่าสุดของไฟล์ประเภทเดียวกันในโปรเจกต์นี้
            $lastVersionRow = Database::fetch(
                "SELECT MAX(version) as max_version FROM project_documents WHERE project_id = ? AND document_type = ?",
                [$projectId, $documentType]
            );
            $newVersion = ($lastVersionRow['max_version'] ?? 0) + 1;

            // บันทึก Path ไฟล์ลงฐานข้อมูล
            Database::insert(
                "INSERT INTO project_documents (project_id, uploaded_by, document_type, title, file_path_gcs, version, review_status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                [$projectId, $userId, $documentType, $title, $gcsPath, $newVersion]
            );

            // แจ้งเตือนครูว่ามีการส่งเอกสารใหม่
            $project = Database::fetch("SELECT title_th, advisor_id FROM projects WHERE id = ?", [$projectId]);
            if ($project && $project['advisor_id']) {
                Database::insert(
                    "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'document_new', 'อัปเดตเอกสารโครงงานใหม่', ?, ?)",
                    [
                        $project['advisor_id'], 
                        "มีการส่งไฟล์ {$title} (v{$newVersion}) สำหรับโครงงาน: {$project['title_th']}", 
                        "advisor/projects/{$projectId}"
                    ]
                );
            }

            Database::commit();
            Session::flash('success', 'บันทึกไฟล์ไปที่คลังข้อมูล GCS และอัปเดตระบบเรียบร้อย (Version: v' . $newVersion . ')!');

        } catch (Exception $e) {
            Database::rollback();
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึกเอกสาร: ' . $e->getMessage());
        }

        App::redirect("projects/{$projectId}");
    }
}
