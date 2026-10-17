<?php
/**
 * System Health Check & Migration Status
 * 
 * ตรวจสอบสุขภาพระบบและสถานะ Migration
 * Usage: php scripts/health_check.php
 */

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('VIEW_PATH', ROOT_PATH . '/views');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load core
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Env.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/App.php';

// Load .env
Env::load(ROOT_PATH . '/.env');

// Start output
echo "=============================================================\n";
echo "  PCSHSKSN e-Portfolio — System Health Check\n";
echo "  วันที่: " . date('Y-m-d H:i:s') . "\n";
echo "=============================================================\n\n";

$hasError = false;

// ==========================================
// 1. PHP Version Check
// ==========================================
echo "[1/10] PHP Version Check\n";
$phpVersion = PHP_VERSION;
$phpMin = '8.0.0';

if (version_compare($phpVersion, $phpMin, '>=')) {
    echo "  ✅ PHP {$phpVersion} (ต้องการ {$phpMin}+)\n";
} else {
    echo "  ❌ PHP {$phpVersion} ต่ำกว่าที่ต้องการ ({$phpMin}+)\n";
    $hasError = true;
}

// ==========================================
// 2. Required PHP Extensions
// ==========================================
echo "\n[2/10] Required PHP Extensions\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'mbstring', 'zip', 'openssl'];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✅ {$ext}\n";
    } else {
        echo "  ❌ {$ext} (ไม่ติดตั้ง)\n";
        $hasError = true;
    }
}

// ==========================================
// 3. Database Connection
// ==========================================
echo "\n[3/10] Database Connection\n";
$pdo = null;
try {
    // Initialize database
    $dbHost = Env::get('DB_HOST', '127.0.0.1');
    $dbName = Env::get('DB_NAME', Env::get('DB_DATABASE', 'pcshsksn'));
    $dbUser = Env::get('DB_USER', Env::get('DB_USERNAME', 'root'));
    $dbPass = Env::get('DB_PASS', Env::get('DB_PASSWORD', ''));

    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    echo "  ✅ เชื่อมต่อฐานข้อมูลสำเร็จ ({$dbName}@{$dbHost})\n";
} catch (PDOException $e) {
    echo "  ❌ ไม่สามารถเชื่อมต่อฐานข้อมูล: " . $e->getMessage() . "\n";
    echo "  ⚠️  กำลังสร้างฐานข้อมูล {$dbName}...\n";
    try {
        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "  ✅ สร้างฐานข้อมูล {$dbName} สำเร็จ\n";

        // Reconnect with database
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    } catch (PDOException $e2) {
        echo "  ❌ ไม่สามารถสร้างฐานข้อมูล: " . $e2->getMessage() . "\n";
        $hasError = true;
    }
}

// ==========================================
// 4. Migration Status
// ==========================================
echo "\n[4/10] Migration Status\n";
if (!$pdo) {
    echo "  ⏭️  ข้าม — ไม่มี database connection\n";
} else {
    try {
        // Check if migrations table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
        if ($stmt->rowCount() > 0) {
            $migrations = $pdo->query("SELECT migration, batch FROM migrations ORDER BY migration ASC")->fetchAll(PDO::FETCH_ASSOC);

            echo "  ✅ Migration table exists\n";
            echo "  📋 รันแล้ว " . count($migrations) . " migrations:\n";

            $migrationNumbers = array_map(fn($m) => (int) explode('_', $m['migration'])[0], $migrations);

            // Check for migration 015 specifically
            if (in_array(15, $migrationNumbers)) {
                echo "  ✅ Migration 015 (verification_status) รันแล้ว\n";

                // Verify column exists
                $checkCol = $pdo->query("SHOW COLUMNS FROM portfolio_items LIKE 'verification_status'");
                if ($checkCol->rowCount() > 0) {
                    echo "  ✅ Column verification_status มีอยู่ใน portfolio_items\n";
                } else {
                    echo "  ❌ Column verification_status ไม่มีใน portfolio_items (ต้องรัน migration 015 ใหม่)\n";
                    $hasError = true;
                }

                // Check index
                $checkIdx = $pdo->query("SHOW INDEX FROM portfolio_items WHERE Key_name = 'idx_verification_status'");
                if ($checkIdx->rowCount() > 0) {
                    echo "  ✅ Index idx_verification_status มีอยู่แล้ว\n";
                } else {
                    echo "  ⚠️  Index idx_verification_status ยังไม่มี (ไม่เป็นไร แต่ควรมี)\n";
                }
            } else {
                echo "  ❌ Migration 015 ยังไม่ได้รัน — ต้องรันก่อน production!\n";
                echo "     คำสั่ง: php core/MigrationRunner.php up 015\n";
                $hasError = true;
            }

            // Show all migrations
            foreach ($migrations as $mig) {
                echo "    - {$mig['migration']} (Batch {$mig['batch']})\n";
            }

        } else {
            echo "  ❌ Migration table ไม่มี — ยังไม่ได้รัน migration เลย\n";
            $hasError = true;
        }
    } catch (Exception $e) {
        echo "  ❌ ตรวจสอบ migration ไม่สำเร็จ: " . $e->getMessage() . "\n";
        $hasError = true;
    }
} // end if $pdo

// ==========================================
// 5. Upload Directory Permissions
// ==========================================
echo "\n[5/10] Upload Directory Permissions\n";
$uploadDirs = [
    UPLOAD_PATH,
    ROOT_PATH . '/uploads/avatars',
    ROOT_PATH . '/uploads/portfolios',
    ROOT_PATH . '/uploads/documents',
];

foreach ($uploadDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "  ✅ {$dir} (เขียนได้)\n";
        } else {
            echo "  ❌ {$dir} (เขียนไม่ได้ — chmod 775)\n";
            $hasError = true;
        }
    } else {
        echo "  ⚠️  {$dir} (ไม่มี — กำลังสร้าง...)\n";
        @mkdir($dir, 0775, true);
    }
}

// ==========================================
// 6. Font Installation
// ==========================================
echo "\n[6/10] Sarabun Font Installation (สำหรับ PDF)\n";
$fontDir = ROOT_PATH . '/storage/fonts';
$requiredFonts = [
    'Sarabun-Regular.ttf',
    'Sarabun-Bold.ttf',
    'Sarabun-Italic.ttf',
    'Sarabun-BoldItalic.ttf',
];

if (is_dir($fontDir)) {
    foreach ($requiredFonts as $font) {
        $fontPath = $fontDir . '/' . $font;
        if (file_exists($fontPath)) {
            echo "  ✅ {$font}\n";
        } else {
            echo "  ❌ {$font} (ไม่มี — PDF จะใช้ Garuda แทน)\n";
        }
    }
} else {
    echo "  ❌ storage/fonts/ ไม่มี — กำลังสร้าง...\n";
    @mkdir($fontDir, 0775, true);
    echo "  ⚠️  ต้องดาวน์โหลดฟอนต์จาก Google Fonts และวางใน {$fontDir}\n";
}

// ==========================================
// 7. Vendor Dependencies
// ==========================================
echo "\n[7/10] Vendor Dependencies\n";
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    echo "  ✅ Composer autoload มีอยู่แล้ว\n";

    if (class_exists('\Mpdf\Mpdf')) {
        echo "  ✅ mPDF ติดตั้งแล้ว\n";
    } else {
        echo "  ⚠️  mPDF ยังไม่ได้ติดตั้ง — รัน: composer install\n";
    }

    if (file_exists(ROOT_PATH . '/vendor/bin/phpunit')) {
        echo "  ✅ PHPUnit ติดตั้งแล้ว (สำหรับ testing)\n";
    } else {
        echo "  ⚠️  PHPUnit ยังไม่ได้ติดตั้ง — รัน: composer require --dev phpunit/phpunit\n";
    }
} else {
    echo "  ❌ vendor/autoload.php ไม่มี — รัน: composer install\n";
    $hasError = true;
}

// ==========================================
// 8. .env File Validation
// ==========================================
echo "\n[8/10] .env File Validation\n";
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    echo "  ✅ .env file มีอยู่แล้ว\n";

    $envContent = file_get_contents($envFile);
    $requiredVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_DEBUG'];

    foreach ($requiredVars as $var) {
        if (strpos($envContent, "{$var}=") !== false) {
            echo "  ✅ {$var} ตั้งค่าแล้ว\n";
        } else {
            echo "  ❌ {$var} ยังไม่ได้ตั้งค่า\n";
            $hasError = true;
        }
    }
} else {
    echo "  ❌ .env file ไม่มี — คัดลอก .env.example เป็น .env\n";
    $hasError = true;
}

// ==========================================
// 9. Log Directory
// ==========================================
echo "\n[9/10] Log Directory\n";
$logDir = ROOT_PATH . '/logs';
if (is_dir($logDir)) {
    if (is_writable($logDir)) {
        echo "  ✅ logs/ เขียนได้\n";
    } else {
        echo "  ❌ logs/ เขียนไม่ได้ — chmod 775\n";
        $hasError = true;
    }
} else {
    echo "  ⚠️  logs/ ไม่มี — กำลังสร้าง...\n";
    @mkdir($logDir, 0775, true);
}

// ==========================================
// 10. Database Table Integrity
// ==========================================
echo "\n[10/10] Database Table Integrity\n";
try {
    $requiredTables = [
        'users',
        'student_profiles',
        'portfolio_items',
        'portfolio_categories',
        'portfolio_skills',
        'projects',
        'project_members',
        'project_documents',
        'logbooks',
        'student_goals',
        'student_interests',
        'academic_records',
        'badges_master',
        'user_badges',
        'notifications',
        'teacher_comments',
        'ai_field_analysis_cache',
        'migrations'
    ];

    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredTables as $table) {
        if (in_array($table, $existingTables)) {
            echo "  ✅ {$table}\n";
        } else {
            echo "  ❌ {$table} ไม่มี — ต้องรัน migration ที่เกี่ยวข้อง\n";
            $hasError = true;
        }
    }

    echo "\n  📊 ตารางทั้งหมด: " . count($existingTables) . " ตาราง\n";

} catch (Exception $e) {
    echo "  ❌ ตรวจสอบ tables ไม่สำเร็จ: " . $e->getMessage() . "\n";
    $hasError = true;
}

// ==========================================
// Summary
// ==========================================
echo "\n=============================================================\n";
if ($hasError) {
    echo "  ⚠️  พบปัญหาบางอย่าง — กรุณาแก้ไขก่อน Production\n";
    echo "=============================================================\n";
    exit(1);
} else {
    echo "  ✅ ระบบพร้อมใช้งาน — ไม่มีปัญหาที่พบ\n";
    echo "=============================================================\n";
    exit(0);
}
