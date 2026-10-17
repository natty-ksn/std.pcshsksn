<?php
/**
 * DataExportController — PDPA/GDPR Data Export Download Handler
 *
 * Orchestrates the personal data export process:
 *   1. Verifies authentication
 *   2. Delegates to DataExportService
 *   3. Logs the action to AuditLog
 *   4. Streams the ZIP file to the browser
 *   5. Cleans up the temporary file immediately
 *
 * @package  PCSHSKSN e-Portfolio
 * @category Controllers
 */
class DataExportController extends Controller
{
    /**
     * Generate and download the user's personal data export ZIP.
     */
    public function download(): void
    {
        $userId = Auth::id();

        if (!$userId) {
            Session::flash('error', 'กรุณาเข้าสู่ระบบก่อนดาวน์โหลดข้อมูล');
            App::redirect('login');
            return;
        }

        try {
            // Generate the export archive
            $service = new DataExportService();
            $zipPath = $service->exportUserData($userId);

            if (!file_exists($zipPath)) {
                throw new RuntimeException('Export file was not created');
            }

            // Log the data export action to the immutable audit log
            AuditLog::log(
                $userId,
                'data.exported',
                'user',
                $userId,
                null,
                ['export_file' => basename($zipPath), 'exported_at' => date('Y-m-d H:i:s')]
            );

            // Determine safe download filename
            $downloadName = 'pcshsksn_export_' . date('YmdHis') . '.zip';

            // Set HTTP headers for file download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . filesize($zipPath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Flush output buffers to ensure clean file streaming
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Stream file bytes to the browser
            readfile($zipPath);
            flush();

            // Clean up the temporary ZIP file immediately after successful download
            @unlink($zipPath);

            exit;

        } catch (Exception $e) {
            App::log("Data export failed for user {$userId}: " . $e->getMessage(), 'ERROR');
            Session::flash('error', 'ไม่สามารถดาวน์โหลดข้อมูลได้: ' . $e->getMessage());
            App::redirect('profile/360');
        }
    }
}
