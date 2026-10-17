<?php
/**
 * ============================================
 * PCSHSKSN e-Portfolio — Smart Installer v2.0
 * ============================================
 * รองรับทั้ง XAMPP (Local) และ Production Server
 * เปิด URL นี้ครั้งเดียวเพื่อสร้าง Database
 */

// ป้องกันรัน install ซ้ำ
$lockFile = __DIR__ . '/../.installed';

echo '<!DOCTYPE html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>PCSHSKSN e-Portfolio — ติดตั้งระบบ</title>';
echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap" rel="stylesheet">';
echo '<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Noto Sans Thai", sans-serif; background: linear-gradient(135deg, #1e1b4b, #4338ca); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .container { background: white; border-radius: 20px; padding: 40px; max-width: 750px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
    h1 { font-size: 1.5rem; margin-bottom: 20px; text-align: center; }
    h2 { font-size: 1.1rem; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb; color: #4338ca; }
    .step { padding: 10px 14px; margin: 6px 0; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
    .step.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .step.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .step.warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .step.info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .btn { display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #6366f1, #4338ca); color: white; border: none; border-radius: 10px; font-family: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; margin-top: 16px; }
    .btn:hover { opacity: 0.9; }
    pre { background: #f1f5f9; padding: 12px; border-radius: 8px; font-size: 0.8rem; overflow-x: auto; margin: 8px 0; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .badge-ok { background: #dcfce7; color: #166534; }
    .badge-fail { background: #fee2e2; color: #991b1b; }
</style></head><body><div class="container">';

echo '<div style="text-align:center; margin-bottom: 20px;"><span style="font-size: 3rem;">🎓</span></div>';
echo '<h1>PCSHSKSN e-Portfolio — ติดตั้งระบบ v2.0</h1>';

// Security: ไม่อนุญาตให้ติดตั้งซ้ำผ่านเว็บ (เดิม ?force=1 จะรัน schema ใหม่และ reset รหัสผ่านเริ่มต้น)
// หากต้องการติดตั้งใหม่จริง ให้ลบไฟล์ .installed บนเซิร์ฟเวอร์ด้วยตนเองก่อน
if (file_exists($lockFile)) {
    http_response_code(403);
    echo '<div class="step info">✅ ระบบได้ถูกติดตั้งแล้ว</div>';
    echo '<p style="text-align:center; margin-top: 16px;"><a href="../" class="btn">🏠 เข้าสู่ระบบ</a></p>';
    echo '<p style="text-align:center; margin-top: 8px; font-size: 0.8rem; color: #94a3b8;">เพื่อความปลอดภัย กรุณาลบโฟลเดอร์ <code>install/</code> ออกจากเซิร์ฟเวอร์</p>';
    echo '</div></body></html>';
    exit;
}

// ========================================
// PHASE 1: ตรวจสอบ Server Requirements
// ========================================
echo '<h2>📋 ขั้นตอนที่ 1: ตรวจสอบความพร้อมของเซิร์ฟเวอร์</h2>';

$requirements = [
    ['PHP >= 8.0', version_compare(PHP_VERSION, '8.0.0', '>='), 'PHP ' . PHP_VERSION],
    ['PDO Extension', extension_loaded('pdo'), ''],
    ['PDO MySQL', extension_loaded('pdo_mysql'), ''],
    ['MBString', extension_loaded('mbstring'), ''],
    ['JSON', extension_loaded('json'), ''],
    ['cURL', extension_loaded('curl'), ''],
    ['FileInfo', extension_loaded('fileinfo'), ''],
    ['GD / Image', extension_loaded('gd'), ''],
];

$allOk = true;
foreach ($requirements as [$name, $ok, $note]) {
    $badge = $ok ? '<span class="badge badge-ok">ผ่าน</span>' : '<span class="badge badge-fail">ไม่ผ่าน</span>';
    $cls = $ok ? 'success' : 'error';
    $extra = $note ? " ($note)" : '';
    echo "<div class='step $cls'>$badge $name$extra</div>";
    if (!$ok) $allOk = false;
}

if (!$allOk) {
    echo '<div class="step error">❌ เซิร์ฟเวอร์ยังไม่พร้อม กรุณาติดตั้ง Extension ที่ขาดก่อน</div>';
    echo '</div></body></html>';
    exit;
}

// ========================================
// PHASE 2: ตรวจสอบ Permissions
// ========================================
echo '<h2>📂 ขั้นตอนที่ 2: ตรวจสอบ & สร้างโฟลเดอร์</h2>';

$dirs = [
    'uploads', 'uploads/portfolio', 'uploads/portfolio/thumbs',
    'uploads/avatars', 'uploads/projects', 'uploads/certificates',
    'uploads/documents', 'logs', 'tmp', 'tmp/ratelimit', 'tmp/cache'
];
foreach ($dirs as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (!is_dir($fullPath)) {
        @mkdir($fullPath, 0755, true);
    }
    $writable = is_writable($fullPath);
    $cls = $writable ? 'success' : 'error';
    $icon = $writable ? '✅' : '❌';
    echo "<div class='step $cls'>$icon $dir " . ($writable ? '(เขียนได้)' : '(ไม่สามารถเขียนได้ — กรุณาตั้ง chmod 755)') . "</div>";
}

// ========================================
// PHASE 3: เชื่อมต่อฐานข้อมูล
// ========================================
echo '<h2>🗄️ ขั้นตอนที่ 3: ติดตั้งฐานข้อมูล</h2>';

require_once __DIR__ . '/../core/Env.php';
$envFile = file_exists(__DIR__ . '/../.env') ? (__DIR__ . '/../.env') : (__DIR__ . '/../.env.production');
Env::load($envFile);

$host   = Env::get('DB_HOST', 'localhost');
$port   = Env::get('DB_PORT', '3306');
$user   = Env::get('DB_USERNAME', Env::get('DB_USER', 'root'));
$pass   = Env::get('DB_PASS', Env::get('DB_PASSWORD', ''));
$dbName = Env::get('DB_DATABASE', Env::get('DB_NAME', 'pccks_eportfolio'));

$steps = [];
$hasError = false;

// Step 3.1 & 3.2: เชื่อมต่อฐานข้อมูล (รองรับ Shared Host ที่ไม่มีสิทธิ์ CREATE DATABASE)
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $steps[] = ['success', "✅ เชื่อมต่อฐานข้อมูล '{$dbName}' สำเร็จ ({$host}:{$port})"];
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");
        $steps[] = ['success', "✅ สร้างและเชื่อมต่อฐานข้อมูล '{$dbName}' สำเร็จ"];
    } catch (PDOException $e2) {
        $steps[] = ['error', '❌ ไม่สามารถเชื่อมต่อฐานข้อมูล: ' . $e->getMessage()];
        $hasError = true;
    }
}

if (!$hasError) {
    // Step 3.3: Run SQL schema
    $sqlFile = __DIR__ . '/../database/schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $lines = explode("\n", $sql);
        $executed = 0;
        $skipped = 0;
        $tempLine = "";

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $tempLine .= $line . "\n";

            if (str_ends_with($trimmed, ';')) {
                $stmt = trim($tempLine);
                if (stripos($stmt, 'CREATE DATABASE') !== false || stripos($stmt, 'USE ') === 0) {
                    $tempLine = "";
                    continue;
                }

                try {
                    $pdo->exec($stmt);
                    $executed++;
                } catch (PDOException $e) {
                    $skipped++;
                }
                $tempLine = "";
            }
        }

        $steps[] = ['success', "✅ ติดตั้งโครงสร้างฐานข้อมูลสำเร็จ ({$executed} คำสั่ง, ข้าม {$skipped})"];
    } else {
        $steps[] = ['error', '❌ ไม่พบไฟล์ database/schema.sql'];
        $hasError = true;
    }
}

if (!$hasError) {
    // Step 3.4: สร้างรหัสผ่านเริ่มต้น & ซิงค์ค่า AI Key จาก .env ลงตาราง settings
    $adminPass   = password_hash('admin1234', PASSWORD_BCRYPT, ['cost' => 10]);
    $studentPass = password_hash('student1234', PASSWORD_BCRYPT, ['cost' => 10]);
    $teacherPass = password_hash('teacher1234', PASSWORD_BCRYPT, ['cost' => 10]);

    try {
        $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$adminPass]);
        $pdo->prepare("UPDATE users SET password = ? WHERE username = '64001'")->execute([$studentPass]);
        $pdo->prepare("UPDATE users SET password = ? WHERE username = 'teacher01'")->execute([$teacherPass]);

        $aiKey = Env::get('GEMINI_API_KEY', '');
        $aiModel = Env::get('GEMINI_MODEL', 'openai/gpt-oss-120b');
        if ($aiKey !== '') {
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'gemini_api_key'")->execute([$aiKey]);
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'gemini_model'")->execute([$aiModel]);
        }
        $steps[] = ['success', '✅ ตั้งค่าบัญชีผู้ใช้เริ่มต้นและระบบ AI สำเร็จ'];
    } catch (PDOException $e) {
        $steps[] = ['warn', '⚠️ ไม่สามารถอัพเดทข้อมูลเริ่มต้นบางส่วน: ' . $e->getMessage()];
    }

    // Create lock file
    file_put_contents($lockFile, json_encode([
        'installed_at' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'server' => php_uname(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $steps[] = ['success', '✅ ติดตั้งเสร็จสมบูรณ์!'];
}

// ========================================
// แสดงผลลัพธ์
// ========================================
foreach ($steps as [$type, $msg]) {
    echo "<div class='step {$type}'>{$msg}</div>";
}

if (!$hasError) {
    // ========================================
    // PHASE 4: Security Checklist
    // ========================================
    echo '<h2>🔐 ขั้นตอนที่ 4: รายการตรวจสอบความปลอดภัย</h2>';
    
    $isProduction = Env::get('APP_ENV', 'development') === 'production';
    $debugOff = Env::get('APP_DEBUG', 'true') === 'false';
    $hasApiKey = !empty(Env::get('GEMINI_API_KEY', ''));

    $secChecks = [
        ['APP_ENV=production', $isProduction, 'ตั้งค่า APP_ENV=production ใน .env'],
        ['APP_DEBUG=false', $debugOff, 'ตั้งค่า APP_DEBUG=false ใน .env'],
        ['Gemini API Key', $hasApiKey, 'ใส่ GEMINI_API_KEY ใน .env เพื่อเปิดใช้ AI'],
    ];

    foreach ($secChecks as [$name, $ok, $tip]) {
        if ($ok) {
            echo "<div class='step success'>✅ $name</div>";
        } else {
            echo "<div class='step warn'>⚠️ $name — $tip</div>";
        }
    }

    echo '<div style="background: #f0fdf4; border: 2px solid #22c55e; border-radius: 12px; padding: 20px; margin-top: 20px;">';
    echo '<h3 style="margin-bottom: 12px;">🎉 ติดตั้งสำเร็จ!</h3>';
    echo '<p style="margin-bottom: 12px;">บัญชีทดสอบ:</p>';
    echo '<pre>';
    echo "👤 Admin:    username: admin       password: admin1234\n";
    echo "🎓 นักเรียน:  username: 64001       password: student1234\n";
    echo "👩‍🏫 ครู:       username: teacher01   password: teacher1234";
    echo '</pre>';
    echo '<p style="text-align:center;"><a href="../" class="btn">🏠 เข้าสู่ระบบ</a></p>';
    echo '</div>';
} else {
    echo '<div style="background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 20px; margin-top: 20px;">';
    echo '<h3>❌ การติดตั้งมีข้อผิดพลาด</h3>';
    echo '<p>กรุณาตรวจสอบ:</p>';
    echo '<ul style="padding-left: 20px; margin-top: 8px;">';
    echo '<li>MySQL/MariaDB เปิดใช้งานแล้วหรือไม่?</li>';
    echo '<li>ไฟล์ .env มีค่า DB_HOST / DB_USER / DB_PASS ถูกต้องหรือไม่?</li>';
    echo '<li>User มีสิทธิ์ CREATE DATABASE หรือไม่?</li>';
    echo '</ul>';
    echo '</div>';
}

echo '</div></body></html>';
