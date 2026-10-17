<?php
/**
 * DataExportService — PDPA/GDPR-compliant Personal Data Export
 *
 * Packages all user-owned data into a structured .ZIP archive containing:
 *   - user_base_data.json (machine-readable JSON export)
 *   - Linked portfolio files (images, documents) streamed efficiently
 *
 * @package  PCSHSKSN e-Portfolio
 * @category Services
 */
class DataExportService
{
    /**
     * Directory where exported ZIP files are temporarily stored.
     */
    private const EXPORT_DIR = UPLOAD_PATH . 'exports';

    /**
     * Maximum lifetime for export files before cleanup (24 hours).
     */
    private const EXPORT_MAX_LIFETIME = 86400;

    /**
     * Export all personal data for a given user into a ZIP archive.
     *
     * @param int $userId The user whose data will be exported
     * @return string Absolute path to the generated ZIP file
     * @throws RuntimeException If export directory cannot be created or ZIP cannot be opened
     */
    public function exportUserData(int $userId): string
    {
        $this->ensureExportDirectory();
        $this->cleanupStaleExports();

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            throw new RuntimeException("User not found: ID {$userId}");
        }

        $studentProfile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        $studentId = $studentProfile ? (int) $studentProfile['id'] : null;

        // Build structured export data
        $exportData = $this->buildExportData($user, $studentProfile, $studentId, $userId);

        // Create ZIP file
        $zipPath = $this->createZipArchive($exportData, $studentId, $userId);

        return $zipPath;
    }

    /**
     * Build the complete export data structure.
     */
    private function buildExportData(array $user, ?array $studentProfile, ?int $studentId, int $userId): array
    {
        $exportData = [
            'export_metadata' => [
                'exported_at' => date('Y-m-d H:i:s'),
                'exported_by_user_id' => $userId,
                'application' => Env::get('APP_NAME', 'PCSHSKSN e-Portfolio'),
                'format_version' => '1.0',
                'data_protection_notice' => 'This export contains your personal data under PDPA/GDPR Article 20 (Right to Data Portability).',
            ],
            'user_account' => [
                'user_id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'position' => $user['position'],
                'is_active' => (bool) $user['is_active'],
                'last_login' => $user['last_login'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ],
        ];

        // Student profile
        if ($studentProfile) {
            $exportData['student_profile'] = [
                'student_id' => (int) $studentProfile['id'],
                'student_code' => $studentProfile['student_code'],
                'full_name' => $studentProfile['full_name'],
                'nickname' => $studentProfile['nickname'],
                'class_year' => $studentProfile['class_year'],
                'class_room' => $studentProfile['class_room'],
                'date_of_birth' => $studentProfile['date_of_birth'],
                'phone' => $studentProfile['phone'],
                'avatar_path' => $studentProfile['avatar_path'],
                'bio' => $studentProfile['bio'],
                'created_at' => $studentProfile['created_at'],
                'updated_at' => $studentProfile['updated_at'],
            ];
        }

        // Portfolio items
        if ($studentId) {
            $exportData['portfolio_items'] = $this->getPortfolioItems($studentId);
            $exportData['skills'] = $this->getSkills($studentId);
            $exportData['projects'] = $this->getProjects($userId);
            $exportData['goals'] = $this->getGoals($studentId);
            $exportData['badges'] = $this->getBadges($userId);
            $exportData['interests'] = $this->getInterests($studentId);
            $exportData['academic_records'] = $this->getAcademicRecords($studentId);
        } else {
            $exportData['portfolio_items'] = [];
            $exportData['skills'] = [];
            $exportData['projects'] = [];
            $exportData['goals'] = [];
            $exportData['badges'] = [];
            $exportData['interests'] = [];
            $exportData['academic_records'] = [];
        }

        return $exportData;
    }

    /**
     * Fetch all portfolio items for a student.
     */
    private function getPortfolioItems(int $studentId): array
    {
        $items = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ?
             ORDER BY pi.created_at DESC",
            [$studentId]
        );

        // Decode JSON fields for readability
        return array_map(function ($item) {
            if (!empty($item['tags'])) {
                $item['tags'] = json_decode($item['tags'], true) ?: [];
            }
            return $item;
        }, $items);
    }

    /**
     * Fetch all skills linked to portfolio items.
     */
    private function getSkills(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT ps.*, sm.skill_name, sm.description as skill_description, sc.name as category_name
             FROM portfolio_skills ps
             JOIN skills_master sm ON ps.skill_id = sm.id
             LEFT JOIN skill_categories sc ON sm.category_id = sc.id
             JOIN portfolio_items pi ON ps.portfolio_id = pi.id
             WHERE pi.student_id = ?
             ORDER BY sc.name, sm.skill_name",
            [$studentId]
        );
    }

    /**
     * Fetch all projects the user is a member of.
     */
    private function getProjects(int $userId): array
    {
        $projects = Database::fetchAll(
            "SELECT p.*, ps.name as subject_name, u.full_name as advisor_name,
                    pm.role_in_project
             FROM project_members pm
             JOIN projects p ON pm.project_id = p.id
             LEFT JOIN project_subjects ps ON p.subject_id = ps.id
             LEFT JOIN users u ON p.advisor_id = u.id
             WHERE pm.student_id = ?
             ORDER BY p.created_at DESC",
            [$userId]
        );

        // Decode JSON fields and fetch project members
        return array_map(function ($project) {
            if (!empty($project['tags'])) {
                $project['tags'] = json_decode($project['tags'], true) ?: [];
            }

            // Fetch team members
            $project['team_members'] = Database::fetchAll(
                "SELECT pm.*, u.full_name, u.username
                 FROM project_members pm
                 JOIN users u ON pm.student_id = u.id
                 WHERE pm.project_id = ?
                 ORDER BY pm.role_in_project",
                [(int) $project['id']]
            );

            return $project;
        }, $projects);
    }

    /**
     * Fetch all student goals.
     */
    private function getGoals(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT * FROM student_goals
             WHERE student_id = ?
             ORDER BY deadline",
            [$studentId]
        );
    }

    /**
     * Fetch all badges earned by the user.
     */
    private function getBadges(int $userId): array
    {
        return Database::fetchAll(
            "SELECT ub.*, bm.code, bm.name, bm.description, bm.icon, bm.criteria
             FROM user_badges ub
             JOIN badges_master bm ON ub.badge_code = bm.code
             WHERE ub.user_id = ?
             ORDER BY ub.earned_at DESC",
            [$userId]
        );
    }

    /**
     * Fetch student interests/talents/hobbies.
     */
    private function getInterests(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT * FROM student_interests
             WHERE student_id = ?
             ORDER BY type, proficiency DESC",
            [$studentId]
        );
    }

    /**
     * Fetch academic records.
     */
    private function getAcademicRecords(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT * FROM academic_records
             WHERE student_id = ?
             ORDER BY academic_year DESC, semester",
            [$studentId]
        );
    }

    /**
     * Create the ZIP archive with JSON data and physical files.
     */
    private function createZipArchive(array $exportData, ?int $studentId, int $userId): string
    {
        $timestamp = date('YmdHis');
        $zipFileName = "export_user_{$userId}_{$timestamp}.zip";
        $zipFullPath = self::EXPORT_DIR . '/' . $zipFileName;

        $zip = new ZipArchive();
        $result = $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new RuntimeException("Failed to create ZIP archive. Error code: {$result}");
        }

        // Add structured JSON
        $jsonData = json_encode($exportData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $zip->addFromString('user_base_data.json', $jsonData);

        // Add linked physical files from portfolio
        if ($studentId) {
            $this->addPortfolioFiles($zip, $studentId, $userId);
        }

        // Add avatar if exists
        $studentProfile = Database::fetch(
            "SELECT avatar_path FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        if (!empty($studentProfile['avatar_path'])) {
            $avatarFullPath = UPLOAD_PATH . $studentProfile['avatar_path'];
            if (file_exists($avatarFullPath)) {
                $zip->addFile($avatarFullPath, 'documents/avatar/' . basename($studentProfile['avatar_path']));
            }
        }

        // Add a README file explaining the export
        $readme = $this->generateReadMe($exportData);
        $zip->addFromString('README.txt', $readme);

        $zip->close();

        return $zipFullPath;
    }

    /**
     * Stream portfolio-linked files into the ZIP without loading them fully into RAM.
     */
    private function addPortfolioFiles(ZipArchive $zip, int $studentId, int $userId = 0): void
    {
        $items = Database::fetchAll(
            "SELECT id, file_path, thumbnail, title
             FROM portfolio_items
             WHERE student_id = ? AND (file_path IS NOT NULL OR thumbnail IS NOT NULL)",
            [$studentId]
        );

        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $safeTitle = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $item['title'] ?? "item_{$itemId}");
            $safeTitle = trim($safeTitle);
            if (empty($safeTitle)) {
                $safeTitle = "item_{$itemId}";
            }

            // Add main file
            if (!empty($item['file_path'])) {
                $fullPath = UPLOAD_PATH . $item['file_path'];
                if (file_exists($fullPath)) {
                    $zipPath = "documents/portfolio/{$safeTitle}/" . basename($item['file_path']);
                    $zip->addFile($fullPath, $zipPath);
                }
            }

            // Add thumbnail
            if (!empty($item['thumbnail'])) {
                $thumbFullPath = UPLOAD_PATH . $item['thumbnail'];
                if (file_exists($thumbFullPath)) {
                    $zipPath = "documents/portfolio/{$safeTitle}/thumb_" . basename($item['thumbnail']);
                    $zip->addFile($thumbFullPath, $zipPath);
                }
            }
        }

        // Add project documents
        $lookupUserId = $userId ?: $studentId;
        $projectDocs = Database::fetchAll(
            "SELECT pd.*, p.title_th
             FROM project_documents pd
             JOIN projects p ON pd.project_id = p.id
             JOIN project_members pm ON pm.project_id = p.id
             WHERE pm.student_id = ? AND pd.file_path_gcs IS NOT NULL",
            [$lookupUserId]
        );

        foreach ($projectDocs as $doc) {
            $docPath = $doc['file_path_gcs'] ?? '';
            $fullPath = UPLOAD_PATH . $docPath;
            if ($docPath !== '' && file_exists($fullPath)) {
                $safeProjectTitle = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $doc['title_th'] ?? "project_{$doc['project_id']}");
                $safeProjectTitle = trim($safeProjectTitle);
                if (empty($safeProjectTitle)) {
                    $safeProjectTitle = "project_{$doc['project_id']}";
                }
                $zipPath = "documents/projects/{$safeProjectTitle}/" . basename($docPath);
                $zip->addFile($fullPath, $zipPath);
            }
        }
    }

    /**
     * Generate a human-readable README for the export package.
     */
    private function generateReadMe(array $exportData): string
    {
        $meta = $exportData['export_metadata'];
        $user = $exportData['user_account'];

        $lines = [
            '===============================================',
            '  PCSHSKSN e-Portfolio — Personal Data Export',
            '===============================================',
            '',
            'Export Date : ' . $meta['exported_at'],
            'User ID     : ' . $user['user_id'],
            'Username    : ' . $user['username'],
            'Full Name   : ' . ($user['full_name'] ?? 'N/A'),
            'Email       : ' . $user['email'],
            '',
            '-----------------------------------------------',
            '  Contents',
            '-----------------------------------------------',
            '',
            'user_base_data.json',
            '  → Machine-readable JSON containing all your personal data',
            '    (profile, portfolio, skills, projects, goals, badges, etc.)',
            '',
            'documents/',
            '  → Uploaded files linked to your portfolio items and projects',
            '',
            'README.txt (this file)',
            '  → Human-readable description of this export package',
            '',
            '-----------------------------------------------',
            '  Data Protection Notice',
            '-----------------------------------------------',
            '',
            'This export was generated under your right to data portability',
            '(PDPA Section 31 / GDPR Article 20). The data contained herein',
            'belongs to you and should be handled securely.',
            '',
            'If you did not request this export, please contact your school',
            'administrator immediately.',
            '',
            '===============================================',
            '  ' . Env::get('APP_NAME', 'PCSHSKSN e-Portfolio'),
            '  © ' . date('Y') . ' — งานศูนย์ข้อมูลสารสนเทศ',
            '===============================================',
        ];

        return implode("\n", $lines);
    }

    /**
     * Ensure the export directory exists and is writable.
     */
    private function ensureExportDirectory(): void
    {
        if (!is_dir(self::EXPORT_DIR)) {
            if (!mkdir(self::EXPORT_DIR, 0755, true)) {
                throw new RuntimeException("Cannot create export directory: " . self::EXPORT_DIR);
            }
        }

        if (!is_writable(self::EXPORT_DIR)) {
            throw new RuntimeException("Export directory is not writable: " . self::EXPORT_DIR);
        }
    }

    /**
     * Remove stale export files older than the maximum lifetime.
     */
    private function cleanupStaleExports(): void
    {
        $files = glob(self::EXPORT_DIR . '/export_user_*.zip');
        if (!$files) {
            return;
        }

        $now = time();
        foreach ($files as $file) {
            if ($now - filemtime($file) > self::EXPORT_MAX_LIFETIME) {
                @unlink($file);
            }
        }
    }
}
