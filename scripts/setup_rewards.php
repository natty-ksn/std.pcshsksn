<?php
include 'core/Env.php';
$host = 'localhost';
$name = 'pccks_eportfolio';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    // 1. Create rewards table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        points_required INT DEFAULT 0,
        category ENUM('digital', 'academic', 'privilege', 'physical') DEFAULT 'digital',
        image_path VARCHAR(255),
        stock_quantity INT DEFAULT -1, -- -1 for unlimited
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Create user_rewards table (Redemptions)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reward_id INT NOT NULL,
        redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Seed some initial rewards
    $check = $pdo->query("SELECT COUNT(*) FROM rewards")->fetchColumn();
    if ($check == 0) {
        $rewards = [
            ['Legendary Profile Frame', 'เปลี่ยนกรอบรูปโปรไฟล์เป็นสีทองหรูหรา แสดงความเป็นระดับแนวหน้าของโรงเรียน', 5000, 'digital', 'frame_gold.png'],
            ['Portfolio Spotlight (1 Week)', 'ดันผลงานของคุณขึ้นหน้าแรกของระบบเป็นเวลา 1 สัปดาห์ เพื่อให้ทุกคนได้เห็นความสำเร็จ', 2500, 'academic', 'spotlight.png'],
            ['Exclusive AI Career Insight', 'ปลดล็อกระบบ AI วิเคราะห์เส้นทางอาชีพเชิงลึกด้วยคะแนน RIASEC และ GPA ของคุณ', 1500, 'academic', 'ai_premium.png'],
            ['Early Lunch Pass (Single Use)', 'คูปองไม่ต้องต่อแถวโรงอาหารสำหรับมื้อกลางวัน 1 ครั้ง (เฉพาะวันศุกร์)', 3000, 'privilege', 'lunch_pass.png'],
            ['PCSHS KSN Limited Sticker', 'สติกเกอร์รุ่นพิเศษสำหรับติดโน้ตบุ๊กหรือแท็บเล็ตที่มีเฉพาะผู้ที่สะสมผลงานเท่านั้น', 1000, 'physical', 'sticker.png'],
            ['Lab Equipment Booking Priority', 'สิทธิ์ในการจองอุปกรณ์แล็บก่อนใครเป็นเวลา 1 เดือน', 4000, 'privilege', 'lab_priority.png']
        ];

        $stmt = $pdo->prepare("INSERT INTO rewards (title, description, points_required, category, image_path) VALUES (?, ?, ?, ?, ?)");
        foreach ($rewards as $r) {
            $stmt->execute($r);
        }
        echo "Initial rewards seeded.\n";
    }

    echo "SUCCESS: Reward system tables created and seeded.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
