<?php
/**
 * PCSHSKSN e-Portfolio: Database Doctor & Auto-Repair Tool
 * เครื่องมือตรวจสอบ วิเคราะห์ และซ่อมแซม/ปรับปรุงฐานข้อมูลอัตโนมัติผ่านหน้าเว็บ
 * 
 * เข้าใช้งานผ่าน: https://std.pcshsksn.ac.th/db_doctor.php
 * ความปลอดภัย: ต้อง Login เป็น Admin หรือระบุรหัสผ่านฐานข้อมูล (DB_PASS จาก .env)
 */

declare(strict_types=1);

// กำหนด Root Path และโหลดการตั้งค่าระบบ
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('CORE_PATH')) {
    define('CORE_PATH', ROOT_PATH . '/core');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// โหลด Environment, Database และ Core Classes
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Env.php';
require_once CORE_PATH . '/App.php';
require_once CORE_PATH . '/Database.php';

Env::load(ROOT_PATH . '/.env');

// ============================================================================
// 1. ความปลอดภัย (Authentication & Authorization)
// ============================================================================
$dbPass = Env::get('DB_PASS', '');
$doctorKey = Env::get('DB_DOCTOR_KEY', $dbPass);
$appKey = Env::get('APP_KEY', '');

$isAdminLoggedIn = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$inputKey = $_REQUEST['key'] ?? ($_POST['doctor_key'] ?? ($_SESSION['db_doctor_auth'] ?? ''));

$isAuthorized = $isAdminLoggedIn || (
    !empty($inputKey) && (
        $inputKey === $dbPass || 
        (!empty($doctorKey) && $inputKey === $doctorKey) || 
        (!empty($appKey) && $inputKey === $appKey)
    )
);

if ($isAuthorized && !empty($inputKey)) {
    $_SESSION['db_doctor_auth'] = $inputKey;
}

// ตรวจสอบ format JSON
$isJson = isset($_GET['format']) && $_GET['format'] === 'json';

if (!$isAuthorized) {
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Unauthorized: Please provide valid key or login as admin'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    renderLockScreen();
    exit;
}

// ============================================================================
// 2. การกำหนดค่า Schema มาตรฐาน (Healing Rules & Definitions)
// ============================================================================

// ตารางหลักที่ต้องมีในระบบ
$requiredTables = [
    'users' => 'ตารางผู้ใช้งานระบบ (นักเรียน ครู แอดมิน ผู้บริหาร)',
    'student_profiles' => 'ข้อมูลประวัตินักเรียนและผลสัมฤทธิ์',
    'portfolio_categories' => 'หมวดหมู่ผลงานแฟ้มสะสม',
    'portfolio_items' => 'ผลงาน/กิจกรรมที่บันทึก',
    'portfolio_skills' => 'ความเชื่อมโยงทักษะกับผลงาน',
    'portfolio_collaborators' => 'เพื่อนร่วมจัดทำผลงาน',
    'skills_master' => 'ฐานข้อมูลทักษะและสมรรถนะ',
    'skill_categories' => 'หมวดหมู่ทักษะ',
    'projects' => 'โครงงานวิทยาศาสตร์และนวัตกรรม',
    'project_members' => 'สมาชิกผู้ร่วมทำโครงงาน',
    'project_documents' => 'เอกสารและแบบฟอร์มโครงงาน',
    'logbooks' => 'บันทึกความก้าวหน้าโครงงาน (Logbooks)',
    'badges_master' => 'ฐานข้อมูลเข็มตราความสำเร็จ (Gamification)',
    'user_badges' => 'เข็มตราที่ผู้ใช้ได้รับ',
    'rewards' => 'ของรางวัลในระบบ Rewards Shop',
    'user_reward_redemptions' => 'ประวัติการแลกของรางวัล (Reward Redemptions)',
    'student_goals' => 'เป้าหมายการศึกษาและมหาวิทยาลัย',
    'student_assessments' => 'แบบประเมินตนเอง RIASEC / ความถนัด',
    'student_interests' => 'ความสนใจในอาชีพและสาขาวิชา',
    'ai_recommendations' => 'ประวัติคำแนะนำจาก AI Advisor',
    'notifications' => 'การแจ้งเตือนผู้ใช้งาน',
    'audit_logs' => 'ประวัติการดำเนินกิจกรรมในระบบ',
    'settings' => 'การตั้งค่าระบบ e-Portfolio',
    'migrations' => 'ประวัติการประมวลผล Version Database Migration'
];

// คอลัมน์สำคัญที่ต้องมีในแต่ละตาราง (Auto-heal missing columns)
$requiredColumns = [
    'portfolio_items' => [
        'external_link' => "VARCHAR(500) NULL COMMENT 'ลิงก์ผลงานภายนอก'",
        'poster_path' => "VARCHAR(255) NULL COMMENT 'ไฟล์โปสเตอร์ผลงาน'",
        'classroom_subject' => "VARCHAR(150) NULL COMMENT 'รายวิชาที่เกี่ยวข้อง'",
        'verification_status' => "ENUM('pending','verified','rejected') DEFAULT 'pending' COMMENT 'สถานะการตรวจ'",
        'points_awarded' => "DECIMAL(5,2) DEFAULT NULL COMMENT 'คะแนนประเมิน'",
        'is_featured' => "TINYINT(1) DEFAULT 0 COMMENT 'ผลงานเด่น'",
        'view_count' => "INT DEFAULT 0 COMMENT 'จำนวนผู้เข้าชม'"
    ],
    'student_profiles' => [
        'cefr_level' => "ENUM('None','A1','A2','B1','B2','C1','C2') DEFAULT 'None' COMMENT 'ระดับภาษาอังกฤษ'",
        'internship_history' => "TEXT NULL COMMENT 'ประวัติการฝึกงาน/ฝึกประสบการณ์'",
        'activity_summary' => "TEXT NULL COMMENT 'สรุปกิจกรรมเด่น'",
        'gpa' => "DECIMAL(3,2) DEFAULT 0.00 COMMENT 'เกรดเฉลี่ยสะสม'"
    ],
    'users' => [
        'exp' => "INT DEFAULT 0 COMMENT 'คะแนนประสบการณ์สะสม'",
        'level' => "INT DEFAULT 1 COMMENT 'ระดับผู้ใช้'",
        'points' => "INT DEFAULT 0 COMMENT 'แต้มแลกรางวัล'",
        'google_id' => "VARCHAR(100) NULL COMMENT 'Google OAuth ID'",
        'avatar' => "VARCHAR(255) NULL COMMENT 'รูปโปรไฟล์'"
    ],
    'projects' => [
        'progress_percent' => "INT DEFAULT 0 COMMENT 'ความก้าวหน้าโครงงาน'",
        'advisor_approved' => "TINYINT(1) DEFAULT 0 COMMENT 'ครูที่ปรึกษารับรอง'",
        'advisor_feedback' => "TEXT NULL COMMENT 'ความคิดเห็นครูที่ปรึกษา'"
    ]
];

// รหัสเข็มตราจำเป็นใน badges_master เพื่อป้องกัน Foreign Key Violation
$requiredBadges = [
    'first_upload' => ['name' => 'ผลงานแรก', 'description' => 'เพิ่มผลงานแรกเข้าสู่ระบบ (Legacy Alias)', 'icon' => '🌟', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":1}'],
    'five_works' => ['name' => 'ดาวรุ่ง', 'description' => 'มีผลงาน 5 รายการ (Legacy Alias)', 'icon' => '⭐', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":5}'],
    'ten_works' => ['name' => 'รอบรู้', 'description' => 'มีผลงาน 10 รายการ (Legacy Alias)', 'icon' => '🏅', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":10}'],
    'twenty_works' => ['name' => 'เชี่ยวชาญ', 'description' => 'มีผลงาน 20 รายการ (Legacy Alias)', 'icon' => '🏆', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":20}'],
    'goal_setter' => ['name' => 'นักตั้งเป้าหมาย', 'description' => 'ตั้งเป้าหมายการศึกษา (Legacy Alias)', 'icon' => '🎯', 'criteria' => '{"type":"count","field":"goals","threshold":1}'],
    'goal_achiever' => ['name' => 'ผู้บรรลุเป้าหมาย', 'description' => 'ทำเป้าหมายสำเร็จ (Legacy Alias)', 'icon' => '🥇', 'criteria' => '{"type":"count","field":"goals_completed","threshold":1}'],
    'first_portfolio' => ['name' => 'ผลงานแรก', 'description' => 'บันทึกผลงานแรกเข้าสู่ระบบ', 'icon' => '🌟', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":1}'],
    'rising_star' => ['name' => 'ดาวรุ่งดวงใหม่', 'description' => 'บันทึกผลงานครบ 5 รายการ', 'icon' => '⭐', 'criteria' => '{"type":"count","field":"portfolio_items","threshold":5}'],
    'bronze_first_step' => ['name' => 'ก้าวแรก', 'description' => 'เพิ่มผลงานแรกเข้าสู่ระบบ', 'icon' => '🌟', 'criteria' => '{"tier":"bronze","type":"dynamic"}'],
    'bronze_goal_setter' => ['name' => 'นักตั้งเป้าหมาย', 'description' => 'ตั้งเป้าหมายการศึกษาอย่างน้อย 1 รายการ', 'icon' => '🎯', 'criteria' => '{"tier":"bronze","type":"dynamic"}'],
    'silver_rising_star' => ['name' => 'ดาวรุ่ง', 'description' => 'มีผลงาน 5 รายการ', 'icon' => '⭐', 'criteria' => '{"tier":"silver","type":"dynamic"}']
];

// ============================================================================
// 3. ฟังก์ชันประมวลผลการตรวจสอบและซ่อมแซม (Core Logic)
// ============================================================================

$action = $_POST['action'] ?? ($_GET['action'] ?? 'check');
$results = [
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s'),
    'db_info' => [],
    'summary' => ['total' => 0, 'passed' => 0, 'warnings' => 0, 'fixed' => 0, 'errors' => 0],
    'checks' => [],
    'logs' => []
];

try {
    $pdo = Database::connect();
    
    // ดึงข้อมูล Database
    $dbHost = Env::get('DB_HOST', 'localhost');
    $dbName = Env::get('DB_NAME', 'pcshsksn_ticket');
    $mysqlVer = $pdo->query("SELECT VERSION() as ver")->fetch()['ver'] ?? 'Unknown';
    $results['db_info'] = [
        'host' => $dbHost,
        'database' => $dbName,
        'version' => $mysqlVer,
        'charset' => 'utf8mb4'
    ];

    // ตรวจสอบ migrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL DEFAULT 1,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ----------------------------------------------------
    // CHECK 1: ตรวจสอบตารางทั้งหมด
    // ----------------------------------------------------
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($requiredTables as $tbl => $desc) {
        $results['summary']['total']++;
        $tableExists = in_array($tbl, $existingTables, true);

        if ($tableExists) {
            $results['summary']['passed']++;
            $results['checks'][] = [
                'type' => 'table',
                'name' => "ตาราง {$tbl}",
                'desc' => $desc,
                'status' => 'passed',
                'msg' => 'พบตารางพร้อมใช้งาน'
            ];
        } else {
            if ($action === 'repair') {
                $tableDefinitions = [
                    'logbooks' => "CREATE TABLE IF NOT EXISTS `logbooks` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `project_id` int(11) NOT NULL,
                        `student_id` int(11) NOT NULL,
                        `activity_detail` text NOT NULL,
                        `hours_spent` decimal(4,2) DEFAULT NULL,
                        `teacher_comment` text DEFAULT NULL,
                        `is_verified` tinyint(1) DEFAULT 0,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `idx_project` (`project_id`),
                        KEY `idx_student_project` (`student_id`,`project_id`),
                        KEY `idx_verified` (`is_verified`),
                        KEY `idx_timeline` (`student_id`,`created_at`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    'user_reward_redemptions' => "CREATE TABLE IF NOT EXISTS `user_reward_redemptions` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `reward_id` int(11) NOT NULL,
                        `points_spent` int(11) NOT NULL,
                        `status` enum('pending','granted','revoked','expired') NOT NULL DEFAULT 'pending',
                        `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `notes` text DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `reward_id` (`reward_id`),
                        KEY `idx_user` (`user_id`),
                        KEY `idx_status` (`status`),
                        KEY `idx_redeemed_at` (`redeemed_at`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ];

                if (isset($tableDefinitions[$tbl])) {
                    try {
                        $pdo->exec($tableDefinitions[$tbl]);
                        $results['summary']['fixed']++;
                        $results['checks'][] = [
                            'type' => 'table',
                            'name' => "ตาราง {$tbl}",
                            'desc' => $desc,
                            'status' => 'fixed',
                            'msg' => 'ซ่อมแซมสำเร็จ: สร้างตารางเรียบร้อยแล้ว'
                        ];
                        $results['logs'][] = "✅ สร้างตาราง `{$tbl}` สำเร็จ";
                    } catch (PDOException $e) {
                        $results['summary']['errors']++;
                        $results['checks'][] = [
                            'type' => 'table',
                            'name' => "ตาราง {$tbl}",
                            'desc' => $desc,
                            'status' => 'error',
                            'msg' => 'สร้างตารางล้มเหลว: ' . $e->getMessage()
                        ];
                    }
                } else {
                    $results['summary']['warnings']++;
                    $results['checks'][] = [
                        'type' => 'table',
                        'name' => "ตาราง {$tbl}",
                        'desc' => $desc,
                        'status' => 'warning',
                        'msg' => 'ยังไม่พบตารางนี้ในฐานข้อมูล'
                    ];
                }
            } else {
                $results['summary']['warnings']++;
                $results['checks'][] = [
                    'type' => 'table',
                    'name' => "ตาราง {$tbl}",
                    'desc' => $desc,
                    'status' => 'warning',
                    'msg' => 'ยังไม่พบตารางนี้ในฐานข้อมูล (กดซ่อมแซมเพื่อสร้าง)'
                ];
            }
        }
    }

    // ----------------------------------------------------
    // CHECK 2: ตรวจสอบคอลัมน์สำคัญที่มักตกหล่น
    // ----------------------------------------------------
    foreach ($requiredColumns as $tbl => $cols) {
        if (!in_array($tbl, $existingTables, true)) {
            continue;
        }

        $existingCols = $pdo->query("SHOW COLUMNS FROM `{$tbl}`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($cols as $colName => $colDef) {
            $results['summary']['total']++;
            $colExists = in_array($colName, $existingCols, true);

            if ($colExists) {
                $results['summary']['passed']++;
                $results['checks'][] = [
                    'type' => 'column',
                    'name' => "{$tbl}.{$colName}",
                    'desc' => "คอลัมน์ {$colName} ในตาราง {$tbl}",
                    'status' => 'passed',
                    'msg' => 'มีคอลัมน์นี้อยู่แล้ว'
                ];
            } else {
                if ($action === 'repair') {
                    try {
                        $pdo->exec("ALTER TABLE `{$tbl}` ADD COLUMN `{$colName}` {$colDef}");
                        $results['summary']['fixed']++;
                        $results['checks'][] = [
                            'type' => 'column',
                            'name' => "{$tbl}.{$colName}",
                            'desc' => "คอลัมน์ {$colName} ในตาราง {$tbl}",
                            'status' => 'fixed',
                            'msg' => 'ซ่อมแซมสำเร็จ: เพิ่มคอลัมน์เรียบร้อยแล้ว'
                        ];
                        $results['logs'][] = "✅ เพิ่มคอลัมน์ `{$colName}` ให้ตาราง `{$tbl}` สำเร็จ";
                    } catch (PDOException $e) {
                        $results['summary']['errors']++;
                        $results['checks'][] = [
                            'type' => 'column',
                            'name' => "{$tbl}.{$colName}",
                            'desc' => "คอลัมน์ {$colName} ในตาราง {$tbl}",
                            'status' => 'error',
                            'msg' => 'เพิ่มคอลัมน์ล้มเหลว: ' . $e->getMessage()
                        ];
                    }
                } else {
                    $results['summary']['warnings']++;
                    $results['checks'][] = [
                        'type' => 'column',
                        'name' => "{$tbl}.{$colName}",
                        'desc' => "คอลัมน์ {$colName} ในตาราง {$tbl}",
                        'status' => 'warning',
                        'msg' => 'ยังไม่มีคอลัมน์นี้ (กดปุ่มซ่อมแซมเพื่อเพิ่มอัตโนมัติ)'
                    ];
                }
            }
        }
    }

    // ----------------------------------------------------
    // CHECK 3: ตรวจสอบและซ่อมแซมเข็มตรา (Badges Master Aliases)
    // ----------------------------------------------------
    if (in_array('badges_master', $existingTables, true)) {
        $existingBadgeCodes = $pdo->query("SELECT code FROM badges_master")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredBadges as $bCode => $bData) {
            $results['summary']['total']++;
            $badgeExists = in_array($bCode, $existingBadgeCodes, true);

            if ($badgeExists) {
                $results['summary']['passed']++;
                $results['checks'][] = [
                    'type' => 'badge',
                    'name' => "เข็มตรา: {$bCode}",
                    'desc' => $bData['name'] . ' (' . $bData['description'] . ')',
                    'status' => 'passed',
                    'msg' => 'พบรหัสเข็มตราใน badges_master'
                ];
            } else {
                if ($action === 'repair') {
                    try {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO badges_master (code, name, description, icon, criteria) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$bCode, $bData['name'], $bData['description'], $bData['icon'], $bData['criteria']]);
                        $results['summary']['fixed']++;
                        $results['checks'][] = [
                            'type' => 'badge',
                            'name' => "เข็มตรา: {$bCode}",
                            'desc' => $bData['name'],
                            'status' => 'fixed',
                            'msg' => 'ซ่อมแซมสำเร็จ: บรรจุรหัสเข็มตราลงใน badges_master เรียบร้อย'
                        ];
                        $results['logs'][] = "✅ เพิ่มรหัสเข็มตรา `{$bCode}` ลงใน `badges_master`";
                    } catch (PDOException $e) {
                        $results['summary']['errors']++;
                        $results['checks'][] = [
                            'type' => 'badge',
                            'name' => "เข็มตรา: {$bCode}",
                            'desc' => $bData['name'],
                            'status' => 'error',
                            'msg' => 'บันทึกล้มเหลว: ' . $e->getMessage()
                        ];
                    }
                } else {
                    $results['summary']['warnings']++;
                    $results['checks'][] = [
                        'type' => 'badge',
                        'name' => "เข็มตรา: {$bCode}",
                        'desc' => $bData['name'],
                        'status' => 'warning',
                        'msg' => 'ไม่มีรหัสนี้ในฐานข้อมูล (อาจทำให้เกิด Foreign Key Error เมื่อนักเรียนได้เหรียญ)'
                    ];
                }
            }
        }
    }

    // ----------------------------------------------------
    // CHECK 4: ตรวจสอบ Database Views (vw_early_warning, vw_student_360)
    // ----------------------------------------------------
    $views = ['vw_early_warning', 'vw_student_360'];
    foreach ($views as $viewName) {
        $results['summary']['total']++;
        $viewCheck = $pdo->query("SHOW TABLES LIKE '{$viewName}'")->rowCount();

        if ($viewCheck > 0) {
            $results['summary']['passed']++;
            $results['checks'][] = [
                'type' => 'view',
                'name' => "Database View: {$viewName}",
                'desc' => 'มุมมองข้อมูลสำหรับ Analytics และ Early Warning',
                'status' => 'passed',
                'msg' => 'พบ View พร้อมใช้งาน'
            ];
        } else {
            if ($action === 'repair') {
                // รันไฟล์ migration 012 สำหรับ views
                $mig012 = ROOT_PATH . '/database/migrations/012_analytics_views.sql';
                if (file_exists($mig012)) {
                    executeSqlScript($pdo, file_get_contents($mig012));
                    $results['summary']['fixed']++;
                    $results['checks'][] = [
                        'type' => 'view',
                        'name' => "Database View: {$viewName}",
                        'desc' => 'สร้างมุมมองข้อมูลใหม่',
                        'status' => 'fixed',
                        'msg' => 'สร้าง View สำเร็จเรียบร้อย'
                    ];
                    $results['logs'][] = "✅ สร้าง View `{$viewName}` เรียบร้อย";
                }
            } else {
                $results['summary']['warnings']++;
                $results['checks'][] = [
                    'type' => 'view',
                    'name' => "Database View: {$viewName}",
                    'desc' => 'มุมมองข้อมูลสรุปภาพรวม',
                    'status' => 'warning',
                    'msg' => 'ยังไม่มี View นี้ในระบบ'
                ];
            }
        }
    }

    // ----------------------------------------------------
    // CHECK 5: ตรวจสอบและประมวลผล Versioned Migrations (001 - 024)
    // ----------------------------------------------------
    $migrationsDir = ROOT_PATH . '/database/migrations';
    if (is_dir($migrationsDir)) {
        $files = glob(str_replace('\\', '/', $migrationsDir) . '/???_*.sql');
        sort($files);

        $executedMigrations = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $results['summary']['total']++;
            $isApplied = in_array($filename, $executedMigrations, true);

            if ($isApplied) {
                $results['summary']['passed']++;
                $results['checks'][] = [
                    'type' => 'migration',
                    'name' => "Migration: {$filename}",
                    'desc' => 'การปรับปรุงฐานข้อมูลตามลำดับเวอร์ชัน',
                    'status' => 'passed',
                    'msg' => 'บันทึกประมวลผลแล้ว'
                ];
            } else {
                if ($action === 'repair' || $action === 'migrate_all') {
                    try {
                        $sql = file_get_contents($filePath);
                        executeSqlScript($pdo, $sql);

                        // บันทึกลงตาราง migrations
                        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, 1) ON DUPLICATE KEY UPDATE executed_at = CURRENT_TIMESTAMP");
                        $stmt->execute([$filename]);

                        $results['summary']['fixed']++;
                        $results['checks'][] = [
                            'type' => 'migration',
                            'name' => "Migration: {$filename}",
                            'desc' => 'ประมวลผลสคริปต์ปรับปรุงฐานข้อมูล',
                            'status' => 'fixed',
                            'msg' => 'รัน Migration เรียบร้อยแล้ว'
                        ];
                        $results['logs'][] = "🚀 ประมวลผล Migration `{$filename}` สำเร็จ";
                    } catch (\Throwable $e) {
                        $results['summary']['errors']++;
                        $results['checks'][] = [
                            'type' => 'migration',
                            'name' => "Migration: {$filename}",
                            'desc' => 'ประมวลผล Migration',
                            'status' => 'error',
                            'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                        ];
                        $results['logs'][] = "❌ Migration `{$filename}` ผิดพลาด: " . $e->getMessage();
                    }
                } else {
                    $results['summary']['warnings']++;
                    $results['checks'][] = [
                        'type' => 'migration',
                        'name' => "Migration: {$filename}",
                        'desc' => 'การปรับปรุงฐานข้อมูล',
                        'status' => 'warning',
                        'msg' => 'ยังไม่ได้บันทึกหรือรัน Migration นี้'
                    ];
                }
            }
        }
    }

    if ($action === 'repair') {
        // อัปเดตชื่อโรงเรียนในตาราง settings ให้ถูกต้องเป็น มุกดาหาร
        try {
            $pdo->exec("UPDATE settings SET setting_value = 'โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร' WHERE setting_key = 'school_name' OR setting_value LIKE '%กาฬสินธุ์%'");
            $results['logs'][] = "🏫 ปรับปรุงชื่อโรงเรียนในตาราง settings เป็น 'โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร' เรียบร้อย";
        } catch (\Throwable $e) {
            // ignore if settings table not ready
        }

        $results['logs'][] = "🎉 การซ่อมแซมและปรับปรุงฐานข้อมูลเสร็จสมบูรณ์เรียบร้อย";
    }

} catch (PDOException $e) {
    $results['summary']['errors']++;
    $results['logs'][] = "💥 Database Error: " . $e->getMessage();
}

// Helper รันสคริปต์ SQL แบบแยกคำสั่งอย่างปลอดภัย
function executeSqlScript(PDO $pdo, string $sql): void
{
    // ลบ Comments
    $lines = explode("\n", $sql);
    $queries = [];
    $current = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
            continue;
        }
        $current .= ' ' . $trimmed;
        if (str_ends_with($trimmed, ';')) {
            $queries[] = trim($current);
            $current = '';
        }
    }

    foreach ($queries as $query) {
        $q = trim($query);
        if (!empty($q)) {
            try {
                $pdo->exec($q);
            } catch (PDOException $e) {
                // ข้ามข้อผิดพลาดกรณีคอลัมน์หรือตารางมีอยู่แล้ว
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate column') || str_contains($msg, 'already exists') || str_contains($msg, 'Duplicate key')) {
                    continue;
                }
                throw $e;
            }
        }
    }
}

// ส่งออก JSON ถ้าต้องการ
if ($isJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// 4. แสดงผลหน้าจอ Web Interface (UI)
// ============================================================================
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Doctor & Auto-Repair — PCSHSKSN e-Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-hover: #047857;
            --primary-light: #ecfdf5;
            --accent: #2563eb;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            padding: 24px 16px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header-card {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.2);
            position: relative;
            overflow: hidden;
        }
        .header-card h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-card p {
            color: #a7f3d0;
            font-size: 15px;
        }
        .db-badge-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }
        .db-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-family: 'JetBrains Mono', monospace;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .stat-card .num {
            font-size: 32px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 4px;
        }
        .stat-card .label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .color-pass { color: var(--primary); }
        .color-warning { color: var(--warning); }
        .color-fixed { color: var(--accent); }
        .color-danger { color: var(--danger); }

        .actions-panel {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px 24px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Sarabun', sans-serif;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: #f1f5f9;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin: 24px 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .check-table {
            width: 100%;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }
        .check-table th {
            background: #f8fafc;
            padding: 12px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        .check-table td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .check-table tr:last-child td {
            border-bottom: none;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .pill-passed { background: #dcfce7; color: #166534; }
        .pill-warning { background: #fef3c7; color: #92400e; }
        .pill-fixed { background: #dbeafe; color: #1e40af; }
        .pill-error { background: #fee2e2; color: #991b1b; }

        .terminal-log {
            background: #0f172a;
            color: #38bdf8;
            border-radius: 12px;
            padding: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.8;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #1e293b;
        }
        .log-entry { margin-bottom: 4px; }
        .log-entry.success { color: #4ade80; }
        .log-entry.error { color: #f87171; }
        .footer-note {
            text-align: center;
            margin-top: 32px;
            font-size: 13px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header-card">
        <h1>🩺 Database Doctor & Auto-Repair</h1>
        <p>เครื่องมือตรวจวินิจฉัย ซ่อมแซม และปรับปรุงโครงสร้างฐานข้อมูลอัตโนมัติ — PCSHSKSN e-Portfolio</p>
        <div class="db-badge-strip">
            <span class="db-badge">Host: <?= htmlspecialchars($results['db_info']['host'] ?? '-') ?></span>
            <span class="db-badge">Database: <?= htmlspecialchars($results['db_info']['database'] ?? '-') ?></span>
            <span class="db-badge">MySQL: <?= htmlspecialchars($results['db_info']['version'] ?? '-') ?></span>
            <span class="db-badge">Charset: utf8mb4</span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="num"><?= $results['summary']['total'] ?></div>
            <div class="label">รายการตรวจทั้งหมด</div>
        </div>
        <div class="stat-card">
            <div class="num color-pass"><?= $results['summary']['passed'] ?></div>
            <div class="label">สมบูรณ์แล้ว (OK)</div>
        </div>
        <div class="stat-card">
            <div class="num color-warning"><?= $results['summary']['warnings'] ?></div>
            <div class="label">พบจุดต้องปรับปรุง</div>
        </div>
        <div class="stat-card">
            <div class="num color-fixed"><?= $results['summary']['fixed'] ?></div>
            <div class="label">ซ่อมแซมสำเร็จในรอบนี้</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions-panel">
        <div>
            <strong style="font-size: 16px;">โหมดการทำงาน: </strong>
            <span style="color: var(--text-muted);">
                <?= $action === 'repair' ? '⚡ กำลังซ่อมแซมและปรับปรุงฐานข้อมูลอัตโนมัติ' : '🔍 โหมดตรวจสอบ (Read-Only Inspection)' ?>
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="?action=check" class="btn btn-secondary">
                🔄 ตรวจสอบใหม่อีกครั้ง
            </a>
            <form method="POST" action="?action=repair" style="display: inline;">
                <input type="hidden" name="action" value="repair">
                <button type="submit" class="btn btn-primary" onclick="return confirm('ยืนยันให้ระบบปรับปรุงและซ่อมแซมฐานข้อมูลอัตโนมัติหรือไม่?');">
                    ⚡ ซ่อมแซมและปรับปรุงอัตโนมัติ (Auto-Repair)
                </button>
            </form>
        </div>
    </div>

    <!-- Logs if any -->
    <?php if (!empty($results['logs'])): ?>
        <h3 class="section-title">📜 บันทึกการประมวลผล (Execution Log)</h3>
        <div class="terminal-log">
            <?php foreach ($results['logs'] as $log): ?>
                <div class="log-entry"><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Diagnostic Details Table -->
    <h3 class="section-title">📋 ผลการตรวจสอบโครงสร้างและองค์ประกอบ (Diagnostic Audit)</h3>
    <table class="check-table">
        <thead>
            <tr>
                <th style="width: 25%;">องค์ประกอบ (Item)</th>
                <th style="width: 35%;">รายละเอียด</th>
                <th style="width: 15%;">สถานะ</th>
                <th style="width: 25%;">ผลการประเมิน</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results['checks'] as $chk): ?>
                <tr>
                    <td>
                        <strong style="font-family: 'JetBrains Mono', monospace; font-size: 13px;">
                            <?= htmlspecialchars($chk['name']) ?>
                        </strong>
                    </td>
                    <td style="color: var(--text-muted);">
                        <?= htmlspecialchars($chk['desc']) ?>
                    </td>
                    <td>
                        <?php if ($chk['status'] === 'passed'): ?>
                            <span class="status-pill pill-passed">✓ ผ่าน</span>
                        <?php elseif ($chk['status'] === 'fixed'): ?>
                            <span class="status-pill pill-fixed">⚡ ซ่อมแซมแล้ว</span>
                        <?php elseif ($chk['status'] === 'warning'): ?>
                            <span class="status-pill pill-warning">⚠️ ต้องปรับปรุง</span>
                        <?php else: ?>
                            <span class="status-pill pill-error">✕ ข้อผิดพลาด</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($chk['msg']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-note">
        PCSHSKSN e-Portfolio System • พัฒนาสำหรับโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร
    </div>
</div>

</body>
</html>
<?php

// หน้าจอ Lock Screen ป้องกันบุคคลภายนอก
function renderLockScreen(): void
{
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันสิทธิ์ความปลอดภัย — Database Doctor</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .lock-box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 36px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        h2 { font-size: 20px; margin-bottom: 8px; }
        p { font-size: 14px; color: #94a3b8; margin-bottom: 24px; line-height: 1.5; }
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 8px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #059669;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover { background: #047857; }
        .hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="lock-box">
        <div class="icon">🔒</div>
        <h2>ระบบความปลอดภัย Database Doctor</h2>
        <p>หน้านี้ใช้สำหรับตรวจและปรับปรุงฐานข้อมูลโดยเฉพาะ กรุณาระบุรหัสผ่านฐานข้อมูล (DB_PASS จาก .env) หรือเข้าสู่ระบบด้วยบัญชีแอดมิน</p>
        <form method="POST">
            <input type="password" name="doctor_key" placeholder="ระบุ DB_PASS หรือ Security Key..." required autofocus>
            <button type="submit">ยืนยันตัวตนเข้าใช้งาน</button>
        </form>
        <div class="hint">
            💡 คุณสามารถเข้าสู่ระบบในฐานะ Admin ที่หน้าเว็บหลักก่อน แล้วกลับมาเปิดหน้านี้ได้โดยไม่ต้องกรอกรหัสผ่าน
        </div>
    </div>
</body>
</html>
<?php
}
