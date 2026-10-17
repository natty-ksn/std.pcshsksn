<?php
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::connect();

    $settings = [
        ['academic_year', '2569', 'ปีการศึกษาปัจจุบัน'],
        ['semester', '1', 'เทอมปัจจุบัน'],
        ['school_name', 'โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์', 'ชื่อโรงเรียน'],
        ['allow_sharing', '1', 'อนุญาตให้นักเรียนแชร์แฟ้มสะสมผลงาน (0=ปิด, 1=เปิด)']
    ];

    foreach ($settings as $s) {
        Database::execute(
            "UPDATE settings SET setting_value = ?, description = ? WHERE setting_key = ?",
            [$s[1], $s[2], $s[0]]
        );
    }

    echo "<h1>✅ แก้ไขปัญหาตัวอักษรอ่านไม่ได้เรียบร้อยแล้ว!</h1>";
} catch (Exception $e) {
    echo "<h1>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h1>";
}
echo "<p><a href='admin/settings'>กลับไปหน้าตั้งค่า</a></p>";
