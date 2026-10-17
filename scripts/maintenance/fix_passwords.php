<?php
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

Env::load(__DIR__ . '/../.env');

$studentHash = password_hash('student1234', PASSWORD_BCRYPT, ['cost' => 12]);
$teacherHash = password_hash('teacher1234', PASSWORD_BCRYPT, ['cost' => 12]);
$adminHash = password_hash('admin1234', PASSWORD_BCRYPT, ['cost' => 12]);

try {
    Database::execute("UPDATE users SET password = ? WHERE username = 'admin'", [$adminHash]);
    Database::execute("UPDATE users SET password = ? WHERE username = '64001'", [$studentHash]);
    Database::execute("UPDATE users SET password = ? WHERE username = 'teacher01'", [$teacherHash]);

    echo "<h1>✅ รีเซ็ตรหัสผ่านทั้งหมดเรียบร้อย!</h1>";
    echo "<ul>
            <li>Admin: admin / admin1234</li>
            <li>Student: 64001 / student1234</li>
            <li>Teacher: teacher01 / teacher1234</li>
          </ul>";
} catch (Exception $e) {
    echo "<h1>❌ Error: " . $e->getMessage() . "</h1>";
}
echo "<p><a href='index.php'>กลับไปหน้า Login</a></p>";
