<?php
/**
 * PCSHSKSN e-Portfolio — Emergency Administrative Rescue Tool
 * ใช้สำหรับกู้คืนหรือยกระดับสิทธิ์ผู้ใช้บน Production (รันครั้งเดียวแล้วลบออก)
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Env.php';
require_once __DIR__ . '/../app/core/Session.php';

// Initialize Environmet
Env::load(__DIR__ . '/../');

// 🛡️ ความปลอดภัยขั้นสูง: ต้องระบุพาสเวิร์ดฉุกเฉิน (จะตั้งค่าไว้ที่นี่)
$RESCUE_CODE = 'PCCKS2026_RESCUE'; 

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $username = $_POST['username'] ?? '';
    $targetRole = $_POST['role'] ?? 'admin';

    if ($code === $RESCUE_CODE) {
        try {
            // 1. ตรวจสอบว่าผู้ใช้คนนี้มีตัวตนไหม
            $user = Database::fetch("SELECT id, username, role FROM users WHERE username = ?", [$username]);
            
            if ($user) {
                // 2. อัปเกรดสิทธิ์ทันที
                Database::execute("UPDATE users SET role = ? WHERE id = ?", [$targetRole, $user['id']]);
                
                // 3. หากเป็น Executive/Admin ให้สร้างชื่อจริงพื้นฐานไว้ให้ถ้ายังไม่มี
                Database::execute("UPDATE users SET full_name = ? WHERE id = ? AND (full_name IS NULL OR full_name = '')", 
                    ['ผู้บริหาร/แอดมินระบบ', $user['id']]);

                $status = 'success';
                $message = "👑 ยกระดับผู้ใช้ [{$username}] สู่ตำแหน่ง [{$targetRole}] สำเร็จแล้วครับ! กรุณากด Refresh หน้าจอหลักเพื่อรับอำนาจบริหารครับ";
            } else {
                $status = 'error';
                $message = "❌ ไม่พบชื่อผู้ใช้ [{$username}] ในระบบครับ กรุณาตรวจสอบอีกครั้ง";
            }
        } catch (Exception $e) {
            $status = 'error';
            $message = "🚨 ล้มเหลว: " . $e->getMessage();
        }
    } else {
        $status = 'error';
        $message = "🛑 รหัสผ่านกู้ภัยไม่ถูกต้องครับ";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Administrative Rescue Tool — PCSHSKSN</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .rescue-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); width: 100%; max-width: 450px; border-top: 8px solid #1e3a8a; }
        h1 { color: #1e3a8a; font-size: 1.5rem; margin-bottom: 10px; text-align: center; }
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; font-family: 'Sarabun'; }
        button { width: 100%; padding: 14px; background: #1e3a8a; color: white; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 15px; font-size: 1rem; }
        button:hover { background: #172554; }
    </style>
</head>
<body>
    <div class="rescue-card">
        <h1>🛠️ เครื่องมือกู้คืนสิทธิ์ผู้บริหาร</h1>
        <p style="text-align: center; color: #64748b; font-size: 0.85rem; margin-bottom: 25px;">(กรุณาลบไฟล์นี้ออกจาก Server หลังจากใช้งานสำเร็จ)</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $status ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <label style="font-size: 0.85rem; font-weight: bold; color: #475569;">รหัสผ่านกู้ภัย (Emergency Code)</label>
            <input type="password" name="code" placeholder="ป้อนรหัสผ่านกู้ภัย..." required>
            
            <label style="font-size: 0.85rem; font-weight: bold; color: #475569;">ชื่อผู้ใช้ที่ต้องการยกระดับ (Username)</label>
            <input type="text" name="username" placeholder="เช่น nattapol.sa" required>
            
            <label style="font-size: 0.85rem; font-weight: bold; color: #475569;">ตำแหน่งที่ต้องการ (Target Role)</label>
            <select name="role">
                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                <option value="executive">Executive (ผู้บริหาร)</option>
                <option value="teacher">Teacher (คุณครู)</option>
            </select>

            <button type="submit">🛡️ ยืนยันการยกระดับสิทธิ์</button>
        </form>
    </div>
</body>
</html>
