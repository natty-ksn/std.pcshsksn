<?php
/**
 * Admin Integrity Check
 * ตรวจสอบความสมบูรณ์ของบัญชีแอดมินบนเซิร์ฟเวอร์จริง
 */
require_once __DIR__ . '/../public/index.php';

echo "<h2>🕵️ Admin Integrity Check</h2>";

try {
    $db = Database::connect();
    $admin = Database::fetch("SELECT * FROM users WHERE username = 'admin'");
    
    echo "<b>1. Database Check:</b> ✅ Connected!<br>";
    
    if ($admin) {
        echo "<b>2. Admin Account:</b> ✅ Found! (Role: {$admin['role']})<br>";
        
        // ทดสอบรหัสผ่าน 'admin1234'
        if (password_verify('admin1234', $admin['password'])) {
            echo "<b style='color:green;'>3. Password Test ('admin1234'):</b> ✅ Match! ทุกอย่างปกติ<br>";
        } else {
            echo "<b style='color:red;'>3. Password Test ('admin1234'):</b> ❌ Mis-match! (รหัสผ่านไม่ตรง)<br>";
            echo "<i>พยายามรีเซ็ตรหัสผ่านใหม่เป็น 'admin1234'...</i><br>";
            $newHash = password_hash('admin1234', PASSWORD_BCRYPT, ['cost' => 12]);
            Database::query("UPDATE users SET password = ? WHERE id = ?", [$newHash, $admin['id']]);
            echo "✅ Password Re-hashed! ลองล็อกอินอีกครั้งครับ<br>";
        }
    } else {
        echo "<b style='color:red;'>2. Admin Account:</b> ❌ Not Found! (ไม่พบแอดมินในระบบ)<br>";
        // สร้างแอดมินใหม่เลยถ้าต้องการ
    }

    echo "<b>4. Session Check:</b> ";
    Session::set('debug_key', 'it_works');
    if (Session::get('debug_key') === 'it_works') {
        echo "✅ Session is Working!<br>";
    } else {
        echo "❌ Session is FAILING! (เบราว์เซอร์ไม่เซฟข้อมูล)<br>";
    }

    echo "<hr><a href='login'>🔑 ไปที่หน้า Login</a>";

} catch (Exception $e) {
    echo "<b style='color:red;'>❌ Error:</b> " . $e->getMessage();
}
