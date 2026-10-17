-- ============================================
-- PCSHSKSN e-Portfolio: Institutional Advancement Patch
-- Target: Supporting High-Level Career & Academic Mapping
-- ============================================

-- 1. อัปเกรดตาราง Profile เพื่อรองรับ CEFR และฝึกงาน
ALTER TABLE student_profiles 
ADD COLUMN cefr_level ENUM('None','A1','A2','B1','B2','C1','C2') DEFAULT 'None' AFTER class_room,
ADD COLUMN internship_history TEXT AFTER cefr_level,
ADD COLUMN activity_summary TEXT AFTER internship_history;

-- 2. อัปเกรดตารางผลงาน เพื่อรองรับ Poster และโครงงานในชั้นเรียน
ALTER TABLE portfolio_items 
ADD COLUMN poster_path VARCHAR(255) AFTER file_path,
ADD COLUMN classroom_subject VARCHAR(150) AFTER poster_path;

-- 3. อัปเกรดและบรรจุแม่แบบเป้าหมาย (Goal Templates) เพื่อการเทียบเคียง (Gauging)
TRUNCATE TABLE goal_templates;
INSERT INTO goal_templates (track_code, track_name, min_gpa, requirements, recommendations, radar_target) VALUES
('med', 'สายวิทยาศาสตร์สุขภาพ (แพทย์/ทันตะ/เภสัช)', 3.50, 
 '{"โครงงานวิทยาศาสตร์": 2, "จิตอาสา & บำเพ็ญประโยชน์": 3, "ทุนการศึกษา & รางวัล": 1}', 
 '["ควรรักษาระดับ GPA ให้ไม่ต่ำกว่า 3.50", "สะสมชั่วโมงจิตอาสาในสถานพยาบาล", "ทำโครงงานนวัตกรรมด้านชีวภาพ"]',
 '{"Academic": 95, "Research": 90, "English": 85, "Volunteer": 90}'),

('eng', 'สายวิศวกรรมศาสตร์และเทคโนโลยี', 3.00, 
 '{"โครงงานวิทยาศาสตร์": 2, "เทคโนโลยี & คอมพิวเตอร์": 2, "วิจัย & สิ่งประดิษฐ์": 1}', 
 '["เน้นการทำโครงงานสื่อนวัตกรรมด้าน IoT หรือ AI", "เข้าร่วมการแข่งขันหุ่นยนต์หรือเขียนโปรแกรม", "เตรียมพื้นฐานฟิสิกส์และแคลคูลัส"]',
 '{"Academic": 85, "Research": 95, "English": 80, "Innovation": 95}'),

('sci', 'สายวิทยาศาสตร์ธรรมชาติและวิจัย', 3.25, 
 '{"โครงงานวิทยาศาสตร์": 3, "วิจัย & สิ่งประดิษฐ์": 2, "ทุนการศึกษา & รางวัล": 1}', 
 '["เน้นกระบวนการแก้ปัญหาด้วยวิธีทางวิทยาศาสตร์", "เข้าร่วมค่ายโอลิมปิกวิชาการ", "ฝึกงานในห้องปฏิบัติการวิจัย"]',
 '{"Academic": 90, "Research": 100, "English": 85, "Innovation": 85}');

-- 4. เตรียมตารางสำหรับจัดเก็บผลการวิเคราะห์ AI รายครั้ง (เพื่อความเร็วในการแสดงผลเกจ)
CREATE TABLE IF NOT EXISTS readiness_scores (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT UNIQUE NOT NULL,
    overall_pct     TINYINT DEFAULT 0,
    academic_score  TINYINT DEFAULT 0,
    portfolio_score TINYINT DEFAULT 0,
    skill_score     TINYINT DEFAULT 0,
    analysis_data   JSON,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;
