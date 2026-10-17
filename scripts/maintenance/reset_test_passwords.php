<?php
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::connect();
    
    $password = password_hash('pccks1234', PASSWORD_BCRYPT, ['cost' => 12]);

    $usernames = ['teacher01', '64001'];

    foreach ($usernames as $u) {
        Database::execute(
            "UPDATE users SET password = ? WHERE username = ?",
            [$password, $u]
        );
    }
    
    echo "<h1>✅ รีเซ็ตรหัสผ่านสำหรับ teacher01 และ 64001 เป็น 'pccks1234' เรียบร้อยแล้ว!</h1>";
} catch (Exception $e) {
    echo "<h1>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h1>";
}
