<?php
/**
 * PCSHSKSN e-Portfolio: Institutional Advancement Patch Applicator
 * ปรับปรุงฐานข้อมูลเพื่อรอบรับระบบเทียบเคียงเป้าหมาย (Gauges) และ AI ขั้นสูง
 */
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/App.php';

echo "============================================\n";
echo "  PCSHSKSN e-Portfolio: Advancement Patch\n";
echo "============================================\n\n";

try {
    $db = Database::connect();
    
    // 1. เพิ่มคอลัมน์ใน student_profiles (เช็คก่อนเพิ่มป้องกัน Error)
    echo "[1/4] Updating student_profiles table...\n";
    $columns = $db->query("SHOW COLUMNS FROM student_profiles")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('cefr_level', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN cefr_level ENUM('None','A1','A2','B1','B2','C1','C2') DEFAULT 'None' AFTER class_room");
        echo "  - Added 'cefr_level'\n";
    }
    if (!in_array('internship_history', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN internship_history TEXT AFTER cefr_level");
        echo "  - Added 'internship_history'\n";
    }
    if (!in_array('activity_summary', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN activity_summary TEXT AFTER internship_history");
        echo "  - Added 'activity_summary'\n";
    }

    // 2. เพิ่มคอลัมน์ใน portfolio_items
    echo "[2/4] Updating portfolio_items table...\n";
    $portCols = $db->query("SHOW COLUMNS FROM portfolio_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('poster_path', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN poster_path VARCHAR(255) AFTER file_path");
        echo "  - Added 'poster_path'\n";
    }
    if (!in_array('classroom_subject', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN classroom_subject VARCHAR(150) AFTER poster_path");
        echo "  - Added 'classroom_subject'\n";
    }

    // 3. เตรียมตาราง Readiness Scores (แคชความพร้อม)
    echo "[3/4] Creating readiness_scores table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS readiness_scores (
        id              INT PRIMARY KEY AUTO_INCREMENT,
        student_id      INT UNIQUE NOT NULL,
        overall_pct     TINYINT DEFAULT 0,
        academic_score  TINYINT DEFAULT 0,
        portfolio_score TINYINT DEFAULT 0,
        skill_score     TINYINT DEFAULT 0,
        analysis_data   JSON,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // 4. บรรจุแม่แบบเป้าหมาย (Goal Templates)
    echo "[4/4] Populating institutional Goal Templates...\n";
    $db->exec("TRUNCATE TABLE goal_templates");
    $stmt = $db->prepare("INSERT INTO goal_templates (track_code, track_name, min_gpa, requirements, recommendations, radar_target) VALUES (?, ?, ?, ?, ?, ?)");
    
    $templates = [
        ['med', 'สายวิทยาศาสตร์สุขภาพ (แพทย์/ทันตะ/เภสัช)', 3.50, 
         '{"โครงงานวิทยาศาสตร์": 2, "จิตอาสา & บำเพ็ญประโยชน์": 3, "ทุนการศึกษา & รางวัล": 1}', 
         '["ควรรักษาระดับ GPA ให้ไม่ต่ำกว่า 3.50", "สะสมชั่วโมงจิตอาสาในสถานพยาบาล", "ทำโครงงานนวัตกรรมด้านชีวภาพ"]',
         '{"Academic": 95, "Research": 90, "English": 85, "Volunteer": 90}'],
        ['eng', 'สายวิศวกรรมศาสตร์และเทคโนโลยี', 3.00, 
         '{"โครงงานวิทยาศาสตร์": 2, "เทคโนโลยี & คอมพิวเตอร์": 2, "วิจัย & สิ่งประดิษฐ์": 1}', 
         '["เน้นการทำโครงงานสื่อนวัตกรรมด้าน IoT หรือ AI", "เข้าร่วมการแข่งขันหุ่นยนต์หรือเขียนโปรแกรม", "เตรียมพื้นฐานฟิสิกส์และแคลคูลัส"]',
         '{"Academic": 85, "Research": 95, "English": 80, "Innovation": 95}'],
        ['sci', 'สายวิทยาศาสตร์ธรรมชาติและวิจัย', 3.25, 
         '{"โครงงานวิทยาศาสตร์": 3, "วิจัย & สิ่งประดิษฐ์": 2, "ทุนการศึกษา & รางวัล": 1}', 
         '["เน้นกระบวนการแก้ปัญหาด้วยวิธีทางวิทยาศาสตร์", "เข้าร่วมค่ายโอลิมปิกวิชาการ", "ฝึกงานในห้องปฏิบัติการวิจัย"]',
         '{"Academic": 90, "Research": 100, "English": 85, "Innovation": 85}']
    ];
    
    foreach ($templates as $t) {
        $stmt->execute($t);
    }

    // 5. เพิ่มคอลัมน์สำหรับการรับรอง (Verification & Rubrics)
    echo "[5/5] Updating portfolio_items for Verification...\n";
    $portCols = $db->query("SHOW COLUMNS FROM portfolio_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('verification_status', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER status");
        echo "  - Added 'verification_status'\n";
    }
    if (!in_array('points_awarded', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN points_awarded DECIMAL(5,2) DEFAULT NULL AFTER verification_status");
        echo "  - Added 'points_awarded'\n";
    }

    echo "\n[SUCCESS] Advancement Patch applied successfully!\n";

} catch (PDOException $e) {
    echo "\n[ERROR] Patch failed: " . $e->getMessage() . "\n";
}
