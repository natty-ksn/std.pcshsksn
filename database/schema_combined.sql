-- ============================================
-- PCCKS e-Portfolio Database Schema
-- Version: 1.0
-- Date: 2026-03-15
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- สร้าง Database
CREATE DATABASE IF NOT EXISTS pccks_eportfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pccks_eportfolio;

-- =====================
-- 1. ตารางผู้ใช้งาน
-- =====================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    username        VARCHAR(50) UNIQUE NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    full_name       VARCHAR(150),
    position        VARCHAR(150),
    role            ENUM('student','teacher','admin','parent','executive') NOT NULL DEFAULT 'student',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    last_login      DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =====================
-- 2. โปรไฟล์นักเรียน
-- =====================
DROP TABLE IF EXISTS student_profiles;
CREATE TABLE student_profiles (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT UNIQUE NOT NULL,
    student_code    VARCHAR(20) UNIQUE,
    full_name       VARCHAR(150) NOT NULL,
    nickname        VARCHAR(50),
    class_year      TINYINT NOT NULL DEFAULT 1,
    class_room      VARCHAR(10),
    date_of_birth   DATE,
    phone           VARCHAR(15),
    avatar_path     VARCHAR(255),
    bio             TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_class (class_year, class_room)
) ENGINE=InnoDB;

-- =====================
-- 3. หมวดหมู่ผลงาน
-- =====================
DROP TABLE IF EXISTS portfolio_categories;
CREATE TABLE portfolio_categories (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) UNIQUE,
    icon        VARCHAR(50) DEFAULT '📁',
    color       VARCHAR(7) DEFAULT '#6366f1',
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- =====================
-- 4. ผลงาน Portfolio
-- =====================
DROP TABLE IF EXISTS portfolio_items;
CREATE TABLE portfolio_items (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    student_id          INT NOT NULL,
    category_id         INT,
    title               VARCHAR(200) NOT NULL,
    description         TEXT,
    achievement_level   ENUM('school','district','province','national','international'),
    award               VARCHAR(200),
    event_date          DATE,
    file_path           VARCHAR(500),
    thumbnail           VARCHAR(500),
    file_size           INT DEFAULT 0,
    is_featured         TINYINT(1) DEFAULT 0,
    tags                JSON,
    view_count          INT DEFAULT 0,
    status              ENUM('draft','published','archived') DEFAULT 'published',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES portfolio_categories(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_status (status),
    FULLTEXT idx_search (title, description, award)
) ENGINE=InnoDB;

-- =====================
-- 5. ผู้ร่วมโครงงาน (Group Work)
-- =====================
DROP TABLE IF EXISTS portfolio_collaborators;
CREATE TABLE portfolio_collaborators (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_id    INT NOT NULL,
    student_id      INT NOT NULL,
    role_in_project VARCHAR(100),
    reflection      TEXT,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collab (portfolio_id, student_id)
) ENGINE=InnoDB;

-- =====================
-- 6. ผลการเรียน
-- =====================
DROP TABLE IF EXISTS academic_records;
CREATE TABLE academic_records (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    subject         VARCHAR(100) NOT NULL,
    subject_code    VARCHAR(20),
    semester        TINYINT NOT NULL,
    academic_year   VARCHAR(9) NOT NULL,
    grade           DECIMAL(3,2),
    credit          TINYINT DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    INDEX idx_student_year (student_id, academic_year),
    UNIQUE KEY unique_record (student_id, subject_code, semester, academic_year)
) ENGINE=InnoDB;

-- =====================
-- 7. ความสนใจ & ความถนัด
-- =====================
DROP TABLE IF EXISTS student_interests;
CREATE TABLE student_interests (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    student_id  INT NOT NULL,
    type        ENUM('interest','talent','hobby') NOT NULL,
    item        VARCHAR(200) NOT NULL,
    proficiency TINYINT DEFAULT 3,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    INDEX idx_student (student_id)
) ENGINE=InnoDB;

-- =====================
-- 8. เป้าหมายการศึกษาต่อ
-- =====================
DROP TABLE IF EXISTS student_goals;
CREATE TABLE student_goals (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    goal_type       ENUM('university','major','career','shortterm','longterm') NOT NULL,
    target          VARCHAR(300) NOT NULL,
    target_details  TEXT,
    target_score    DECIMAL(5,2),
    current_score   DECIMAL(5,2),
    deadline        DATE,
    status          ENUM('planning','inprogress','achieved','cancelled') DEFAULT 'planning',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================
-- 9. หมวดหมู่ทักษะ
-- =====================
DROP TABLE IF EXISTS skill_categories;
CREATE TABLE skill_categories (
    id      INT PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL,
    source  VARCHAR(100) DEFAULT 'PCCKS'
) ENGINE=InnoDB;

-- =====================
-- 10. ทักษะมาตรฐาน
-- =====================
DROP TABLE IF EXISTS skills_master;
CREATE TABLE skills_master (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    category_id     INT,
    skill_name      VARCHAR(150) NOT NULL,
    description     TEXT,
    is_approved     TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================
-- 11. ทักษะ -> ผลงาน
-- =====================
DROP TABLE IF EXISTS portfolio_skills;
CREATE TABLE portfolio_skills (
    portfolio_id    INT NOT NULL,
    skill_id        INT NOT NULL,
    proficiency     TINYINT DEFAULT 3,
    PRIMARY KEY (portfolio_id, skill_id),
    FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills_master(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================
-- 12. AI Recommendations
-- =====================
DROP TABLE IF EXISTS ai_recommendations;
CREATE TABLE ai_recommendations (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    analysis_type   ENUM('portfolio_gap','study_guide','career_match','skill_suggest') NOT NULL,
    prompt_context  TEXT,
    response_data   JSON,
    generated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read         TINYINT(1) DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    INDEX idx_student (student_id)
) ENGINE=InnoDB;

-- =====================
-- 13. การแจ้งเตือน
-- =====================
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    type        VARCHAR(50) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    message     TEXT,
    link        VARCHAR(500),
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================
-- 14. Portfolio Share Links
-- =====================
DROP TABLE IF EXISTS portfolio_shares;
CREATE TABLE portfolio_shares (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    share_token     VARCHAR(64) UNIQUE NOT NULL,
    share_type      ENUM('public','token','password') DEFAULT 'token',
    password_hash   VARCHAR(255),
    allowed_sections JSON,
    expires_at      DATETIME,
    view_count      INT DEFAULT 0,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================
-- 15. PDPA Consent
-- =====================
DROP TABLE IF EXISTS pdpa_consents;
CREATE TABLE pdpa_consents (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    consent_type    VARCHAR(50) NOT NULL,
    is_consented    TINYINT(1) DEFAULT 0,
    consented_at    DATETIME,
    withdrawn_at    DATETIME,
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_consent (user_id, consent_type)
) ENGINE=InnoDB;

-- =====================
-- 16. Audit Logs
-- =====================
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT,
    action      VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id   INT,
    old_value   JSON,
    new_value   JSON,
    ip_address  VARCHAR(45),
    user_agent  VARCHAR(500),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================
-- 17. Badges (Gamification)
-- =====================
DROP TABLE IF EXISTS badges_master;
CREATE TABLE badges_master (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    code        VARCHAR(50) UNIQUE NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    icon        VARCHAR(50),
    criteria    JSON
) ENGINE=InnoDB;

DROP TABLE IF EXISTS user_badges;
CREATE TABLE user_badges (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    badge_code  VARCHAR(50) NOT NULL,
    earned_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_code) REFERENCES badges_master(code),
    UNIQUE KEY unique_badge (user_id, badge_code)
) ENGINE=InnoDB;

-- =====================
-- 18. Alumni Records
-- =====================
DROP TABLE IF EXISTS alumni_records;
CREATE TABLE alumni_records (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    graduation_year VARCHAR(4),
    university      VARCHAR(200),
    faculty         VARCHAR(200),
    major           VARCHAR(200),
    admission_type  ENUM('tcas1_portfolio','tcas2_quota','tcas3_admission','tcas4_direct','other'),
    current_status  ENUM('studying','graduated','working') DEFAULT 'studying',
    career          VARCHAR(200),
    is_public       TINYINT(1) DEFAULT 0,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================
-- 19. Teacher Comments
-- =====================
DROP TABLE IF EXISTS teacher_comments;
CREATE TABLE teacher_comments (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_id    INT NOT NULL,
    teacher_id      INT NOT NULL,
    comment         TEXT NOT NULL,
    rating          TINYINT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================
-- 20. แบบประเมินตนเอง
-- =====================
DROP TABLE IF EXISTS student_assessments;
CREATE TABLE student_assessments (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    assessment_type VARCHAR(50) DEFAULT 'riasec',
    dominant_trait  VARCHAR(10),
    result_data     JSON,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- =====================
-- 21. แม่แบบเป้าหมายอาชีพ (Goal Templates)
-- =====================
DROP TABLE IF EXISTS goal_templates;
CREATE TABLE goal_templates (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    track_code       VARCHAR(50) UNIQUE,
    track_name       VARCHAR(255),
    min_gpa          DECIMAL(3,2),
    requirements     TEXT,
    recommendations  TEXT,
    radar_target     TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มคอลัมน์แคชให้ student_goals
ALTER TABLE student_goals ADD COLUMN analysis_cache TEXT;
ALTER TABLE student_goals ADD COLUMN analyzed_at DATETIME;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SEED DATA
-- ============================================

-- หมวดหมู่ผลงาน
INSERT INTO portfolio_categories (name, slug, icon, color, sort_order) VALUES
    ('โครงงานวิทยาศาสตร์', 'science-project', '🔬', '#8b5cf6', 1),
    ('คณิตศาสตร์', 'mathematics', '📐', '#3b82f6', 2),
    ('เทคโนโลยี & คอมพิวเตอร์', 'technology', '💻', '#06b6d4', 3),
    ('ภาษา & วรรณกรรม', 'language', '📚', '#10b981', 4),
    ('ศิลปะ & ดนตรี', 'art-music', '🎨', '#f59e0b', 5),
    ('กีฬา', 'sports', '⚽', '#ef4444', 6),
    ('จิตอาสา & บำเพ็ญประโยชน์', 'volunteer', '💚', '#22c55e', 7),
    ('ผู้นำ & กิจกรรมนักเรียน', 'leadership', '👑', '#a855f7', 8),
    ('ค่าย & อบรม', 'camp-training', '🏕️', '#f97316', 9),
    ('ทุนการศึกษา & รางวัล', 'scholarship-award', '🏆', '#eab308', 10),
    ('วิจัย & สิ่งประดิษฐ์', 'research', '🧪', '#6366f1', 11),
    ('อื่นๆ', 'others', '📋', '#64748b', 99);

-- หมวดหมู่ทักษะ
INSERT INTO skill_categories (name, source) VALUES
    ('ทักษะศตวรรษที่ 21', 'สพฐ.'),
    ('ทักษะ STEM', 'สสวท.'),
    ('ทักษะกระบวนการทางวิทยาศาสตร์', 'PCCKS'),
    ('ทักษะด้านภาษา', 'PCCKS'),
    ('ทักษะด้านเทคโนโลยี', 'PCCKS'),
    ('ทักษะชีวิต & สังคม', 'PCCKS');

-- ทักษะมาตรฐาน
INSERT INTO skills_master (category_id, skill_name, description) VALUES
    -- ทักษะศตวรรษที่ 21
    (1, 'การคิดเชิงวิพากษ์ (Critical Thinking)', 'ความสามารถในการวิเคราะห์และประเมินข้อมูล'),
    (1, 'ความคิดสร้างสรรค์ (Creativity)', 'ความสามารถในการสร้างสรรค์สิ่งใหม่'),
    (1, 'การสื่อสาร (Communication)', 'ทักษะการสื่อสารทั้งพูดและเขียน'),
    (1, 'การทำงานร่วมกัน (Collaboration)', 'ความสามารถในการทำงานเป็นทีม'),
    -- ทักษะ STEM
    (2, 'การออกแบบเชิงวิศวกรรม', 'กระบวนการคิดเชิงวิศวกรรมเพื่อแก้ปัญหา'),
    (2, 'การเขียนโปรแกรม (Coding)', 'ทักษะการเขียนโค้ดและพัฒนาซอฟต์แวร์'),
    (2, 'การวิเคราะห์ข้อมูล (Data Analysis)', 'ความสามารถในการวิเคราะห์และแปลผลข้อมูล'),
    (2, 'การสร้างแบบจำลอง (Modeling)', 'สร้างแบบจำลองทางวิทยาศาสตร์และคณิตศาสตร์'),
    -- ทักษะกระบวนการทางวิทยาศาสตร์
    (3, 'การสังเกต', 'ทักษะการสังเกตปรากฏการณ์'),
    (3, 'การตั้งสมมติฐาน', 'ความสามารถในการตั้งสมมติฐานที่ทดสอบได้'),
    (3, 'การทดลอง', 'ทักษะการออกแบบและดำเนินการทดลอง'),
    (3, 'การนำเสนอผลงาน', 'ทักษะการนำเสนอผลงานวิจัย'),
    -- ทักษะด้านภาษา
    (4, 'ภาษาอังกฤษ', 'ทักษะภาษาอังกฤษ ฟัง พูด อ่าน เขียน'),
    (4, 'ภาษาจีน', 'ทักษะภาษาจีน'),
    (4, 'ภาษาญี่ปุ่น', 'ทักษะภาษาญี่ปุ่น'),
    (4, 'การเขียนเชิงวิชาการ', 'ทักษะการเขียนรายงาน บทความวิชาการ'),
    -- ทักษะด้านเทคโนโลยี
    (5, 'AI & Machine Learning', 'ความรู้เรื่อง AI และการเรียนรู้ของเครื่อง'),
    (5, 'Web Development', 'ทักษะการพัฒนาเว็บไซต์'),
    (5, 'IoT & Robotics', 'ทักษะด้าน Internet of Things และหุ่นยนต์'),
    (5, 'Graphic Design', 'ทักษะการออกแบบกราฟิก'),
    -- ทักษะชีวิต
    (6, 'ความเป็นผู้นำ (Leadership)', 'ทักษะการเป็นผู้นำ'),
    (6, 'การจัดการเวลา', 'ทักษะการบริหารเวลา'),
    (6, 'จิตอาสา', 'จิตสำนึกในการช่วยเหลือสังคม'),
    (6, 'การแก้ปัญหา (Problem Solving)', 'ทักษะการแก้ปัญหาอย่างเป็นระบบ');

-- Badges
INSERT INTO badges_master (code, name, description, icon, criteria) VALUES
    ('first_upload', 'นักสะสมมือใหม่', 'อัพโหลดผลงานชิ้นแรก', '🌱', '{"type":"portfolio_count","value":1}'),
    ('five_works', 'นักสะสมขยัน', 'มีผลงาน 5 ชิ้น', '⭐', '{"type":"portfolio_count","value":5}'),
    ('ten_works', 'นักสะสมมืออาชีพ', 'มีผลงาน 10 ชิ้น', '🌟', '{"type":"portfolio_count","value":10}'),
    ('twenty_works', 'Portfolio Master', 'มีผลงาน 20 ชิ้น', '💫', '{"type":"portfolio_count","value":20}'),
    ('national_award', 'เพชรแห่งชาติ', 'ได้รางวัลระดับชาติ', '🇹🇭', '{"type":"achievement_level","value":"national"}'),
    ('intl_award', 'ดาวสากล', 'ได้รางวัลระดับนานาชาติ', '🌏', '{"type":"achievement_level","value":"international"}'),
    ('goal_setter', 'ผู้วางแผน', 'ตั้งเป้าหมายชิ้นแรก', '🎯', '{"type":"goal_count","value":1}'),
    ('goal_achiever', 'ผู้พิชิตเป้าหมาย', 'บรรลุเป้าหมายแรก', '🏆', '{"type":"goal_achieved","value":1}'),
    ('profile_complete', 'โปรไฟล์สมบูรณ์', 'กรอกข้อมูลโปรไฟล์ครบ 100%', '✅', '{"type":"profile_complete","value":100}'),
    ('all_categories', 'นักสะสมรอบด้าน', 'มีผลงานครบทุกหมวด', '🎖️', '{"type":"category_count","value":5}');

-- สร้าง Admin Account (password: admin1234)
INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@pccks.ac.th', '$2y$12$LJ3m4ks5Ozp8Xzv9q4j6UeTYKMjX5z5X5MrvQqUBwX5WnX9Zp5E3G', 'admin');

-- สร้างตัวอย่าง นักเรียน (password: student1234)
INSERT INTO users (username, email, password, role) VALUES
    ('64001', 'student01@pccks.ac.th', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO student_profiles (user_id, student_code, full_name, nickname, class_year, class_room, bio)
VALUES (2, '64001', 'สมชาย ใจดี', 'เพชร', 5, '1', 'นักเรียนสาย STEM ชอบเขียนโปรแกรมและทำโครงงานวิทยาศาสตร์');

-- สร้างตัวอย่าง ครู (password: teacher1234)
INSERT INTO users (username, email, password, role) VALUES
    ('teacher01', 'teacher01@pccks.ac.th', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

-- แม่แบบเป้าหมายอาชีพ (Goal Templates Seed)
INSERT INTO goal_templates (track_code, track_name, min_gpa, requirements, recommendations, radar_target) VALUES
    ('med', 'สายวิทยาศาสตร์สุขภาพ (แพทยศาสตร์/พยาบาล/สาธารณสุข)', 3.50,
     '{"โครงงานวิทยาศาสตร์": 3, "จิตอาสา & บำเพ็ญประโยชน์": 5}',
     '["⚕️ **เน้นเกรด:** สำหรับคณะสายแพทย์ ควรดึง GPAX ให้ถึง 3.50 ขึ้นไป", "🔬 **งานวิจัย:** ต้องการโครงงานวิทยาศาสตร์เพิ่ม (เน้นด้านชีววิทยา/เคมี)", "🤝 **จิตอาสา:** ควรเพิ่มกิจกรรมเพื่อสังคมเพื่อสะสมชั่วโมงจิตสาธารณะ"]',
     '{"R": 3, "I": 9, "A": 2, "S": 9, "E": 4, "C": 6}'),
    ('eng', 'สายวิศวกรรมศาสตร์และเทคโนโลยี', 3.00,
     '{"นวัตกรรม & สิ่งประดิษฐ์": 4, "การแข่งขัน & ทักษะวิชาการ": 3}',
     '["⚙️ **เน้นเกรด:** พยายามรักษาเกรดในรายวิชา ฟิสิกส์ และ คณิตศาสตร์ ให้สูงกว่า 3.00", "💡 **นวัตกรรม:** ต้องการผลงานสิ่งประดิษฐ์หรือ Coding เพิ่มเติม", "🏆 **การแข่งขัน:** แนะนำให้ลงสมัครสนามแข่งขันวิชาการเพื่อให้เห็นความโดดเด่น"]',
     '{"R": 8, "I": 9, "A": 4, "S": 2, "E": 6, "C": 8}'),
    ('sci', 'สายวิทยาศาสตร์บริสุทธิ์/วิจัย', 3.30,
     '{"โครงงานวิทยาศาสตร์": 5}',
     '["🧪 **งานวิจัย:** คุณเป็นเด็กจุฬาภรณ์ฯ ควรมีโครงงานวิจัยในพอร์ตอย่างน้อย 5 ชิ้น", "📉 **วิชาการ:** รักษาเกรดรายวิชาวิทยาศาสตร์ทุกตัวให้โดดเด่น"]',
     '{"R": 4, "I": 10, "A": 3, "S": 4, "E": 4, "C": 7}'),
    ('art', 'สายสถาปัตย์ อาคาร ศิลปกรรมและภาษา', 2.50,
     '{"ศิลปะ & การออกแบบ": 5}',
     '["🎨 **พอร์ตอาร์ต:** ควรสะสมงานสเก็ตช์หรือการออกแบบที่แสดงความคิดสร้างสรรค์", "🗣️ **ทักษะ:** พัฒนาภาษาที่สามเพื่อยื่นคะแนนเสริม"]',
     '{"R": 2, "I": 4, "A": 10, "S": 7, "E": 5, "C": 4}');
-- ============================================
-- PCSHSKS Project Tracking System Schema
-- Version: 1.0
-- Description: ส่วนขยายจากฐานข้อมูล pccks_eportfolio เดิม
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE pccks_eportfolio;

-- =====================
-- 1. วิชาโครงงาน (Subjects)
-- =====================
DROP TABLE IF EXISTS project_subjects;
CREATE TABLE project_subjects (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(200) NOT NULL,
    class_year      TINYINT NOT NULL,
    semester        TINYINT NOT NULL,
    academic_year   VARCHAR(9) NOT NULL,
    teacher_id      INT,
    description     TEXT,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    INDEX idx_year (academic_year, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 2. ข้อมูลหลักโครงงาน (Projects)
-- แนะนำโดย SA: รองรับข้อมูลเครือข่าย/ผู้เชี่ยวชาญ และงบประมาณ/จริยธรรมการวิจัย (IRB)
-- =====================
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_code    VARCHAR(30) UNIQUE NOT NULL,
    title_th        VARCHAR(300) NOT NULL,
    title_en        VARCHAR(300),
    project_type    ENUM('experimental','survey','invention','applied','theoretical') NOT NULL,
    subject_id      INT,
    abstract_th     TEXT,
    abstract_en     TEXT,
    
    -- Status Workflow
    status          ENUM(
        'draft','proposal_submitted','proposal_approved','proposal_revision',
        'proposal_rejected','in_progress','chapter_review','presentation_ready',
        'completed','archived'
    ) DEFAULT 'draft',
    
    -- Ethics & Budget (การพิจารณาจริยธรรมวิจัยและงบประมาณ)
    ethics_status   ENUM('not_required', 'pending', 'approved', 'rejected') DEFAULT 'not_required',
    budget_requested DECIMAL(10,2) DEFAULT 0.00,
    budget_approved  DECIMAL(10,2) DEFAULT 0.00,
    
    -- Advising Team
    advisor_id      INT NOT NULL,
    co_advisor_id   INT,
    external_expert VARCHAR(200),
    
    -- Academic Timeline
    academic_year   VARCHAR(9) NOT NULL,
    semester        TINYINT NOT NULL DEFAULT 1,
    class_year      TINYINT,
    class_room      VARCHAR(10),
    start_date      DATE,
    expected_end    DATE,
    actual_end      DATE,
    overall_score   DECIMAL(5,2),
    tags            JSON,
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (subject_id) REFERENCES project_subjects(id),
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (co_advisor_id) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_advisor (advisor_id),
    INDEX idx_year (academic_year, semester),
    FULLTEXT idx_search (title_th, title_en, abstract_th)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 3. สมาชิกโครงงาน (Project Members)
-- =====================
DROP TABLE IF EXISTS project_members;
CREATE TABLE project_members (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    student_id      INT NOT NULL,
    role_in_project ENUM('leader','member','secretary') DEFAULT 'member',
    joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (project_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 4. บันทึกการทำงาน (Logbooks) - จาก SA Blueprint
-- =====================
DROP TABLE IF EXISTS logbooks;
CREATE TABLE logbooks (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    student_id      INT NOT NULL,
    activity_detail TEXT NOT NULL,
    hours_spent     DECIMAL(4,2),
    teacher_comment TEXT,
    is_verified     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 5. จุดหมาย (Milestones) & การส่งงาน
-- =====================
DROP TABLE IF EXISTS milestones;
CREATE TABLE milestones (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    phase_name      VARCHAR(200) NOT NULL,
    description     TEXT,
    milestone_order TINYINT DEFAULT 1,
    due_date        DATE,
    completed_date  DATE,
    status          ENUM('pending','in_progress','submitted','approved','overdue') DEFAULT 'pending',
    progress_pct    TINYINT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS milestone_submissions;
CREATE TABLE milestone_submissions (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    milestone_id    INT NOT NULL,
    submitted_by    INT NOT NULL,
    note            TEXT,
    file_path_gcs   VARCHAR(500),
    advisor_comment TEXT,
    status          ENUM('submitted','revise','accepted') DEFAULT 'submitted',
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 6. เอกสารและการตรวจ (Project Documents & Reviews)
-- =====================
DROP TABLE IF EXISTS project_documents;
CREATE TABLE project_documents (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    uploaded_by     INT NOT NULL,
    document_type   ENUM(
        'proposal','chapter1','chapter2','chapter3','chapter4','chapter5',
        'full_report','presentation','poster','appendix','other'
    ) NOT NULL,
    chapter_number  TINYINT,
    title           VARCHAR(200),
    file_path_gcs   VARCHAR(500) NOT NULL,
    file_size       INT DEFAULT 0,
    file_type       VARCHAR(50),      -- e.g., 'application/pdf', 'video/mp4'
    version         INT DEFAULT 1,    -- Version Control ตาม SA Blueprint
    review_status   ENUM('pending','reviewing','approved','revision_needed','rejected') DEFAULT 'pending',
    reviewed_by     INT,
    reviewed_at     DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_project (project_id),
    INDEX idx_status (review_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS document_comments;
CREATE TABLE document_comments (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    document_id     INT NOT NULL,
    commenter_id    INT NOT NULL,
    comment         TEXT NOT NULL,
    comment_type    ENUM('general','suggestion','correction','praise') DEFAULT 'general',
    page_number     INT,
    is_resolved     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES project_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (commenter_id) REFERENCES users(id),
    INDEX idx_document (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 7. การให้คะแนนโครงงาน (Project Reviews/Evaluations)
-- =====================
DROP TABLE IF EXISTS project_evaluations;
CREATE TABLE project_evaluations (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT NOT NULL,
    evaluator_id    INT NOT NULL,      -- ผู้ประเมิน
    eval_type       ENUM('proposal','progress','chapter','presentation','final') NOT NULL,
    scores          JSON,              -- เก็บเกณฑ์และคะแนนแต่ละข้อ
    total_score     DECIMAL(5,2),
    feedback        TEXT,
    result          ENUM('pass','fail','revise') DEFAULT 'pass',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================
-- 8. คลังโครงงาน (Project Repository & Knowledge Bank)
-- =====================
DROP TABLE IF EXISTS project_repository;
CREATE TABLE project_repository (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    project_id      INT UNIQUE NOT NULL,
    keywords        TEXT,
    category        VARCHAR(100),
    summary         TEXT,
    poster_path_gcs VARCHAR(500),
    report_path_gcs VARCHAR(500),
    video_path_gcs  VARCHAR(500),
    view_count      INT DEFAULT 0,
    download_count  INT DEFAULT 0,
    is_featured     TINYINT(1) DEFAULT 0,
    is_public       TINYINT(1) DEFAULT 1,
    published_at    DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FULLTEXT idx_search (keywords, summary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- End of Schema
-- ============================================
