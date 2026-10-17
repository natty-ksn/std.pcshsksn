<?php
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

Env::load(__DIR__ . '/../.env');

$password = 'admin1234';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $updated = Database::execute("UPDATE users SET password = ? WHERE username = 'admin'", [$hash]);

    if ($updated) {
        echo "<h1>✅ รีเซ็ตรหัสผ่าน Admin เรียบร้อย!</h1>";
        echo "<p>Username: <strong>admin</strong></p>";
        echo "<p>Password: <strong>admin1234</strong></p>";
        echo "<p>Hash ใหม่: $hash</p>";
    } else {
        echo "<h1>❌ ไม่พบผู้ใช้ admin ในฐานข้อมูล</h1>";
    }
} catch (Exception $e) {
    echo "<h1>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h1>";
}
echo "<p><a href='index.php'>กลับไปหน้า Login</a></p>";
