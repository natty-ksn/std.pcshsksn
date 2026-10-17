-- ============================================
-- Seed: 001_default_data.sql
-- Description: ข้อมูลเริ่มต้น — หมวดหมู่ผลงาน, Badge, Skill Categories, Admin account
-- Created: 2026-04-03
-- ============================================

-- หมวดหมู่ผลงาน
INSERT IGNORE INTO `portfolio_categories` (`name`, `slug`, `icon`, `color`, `sort_order`) VALUES
    ('โครงงานวิทยาศาสตร์', 'science-project', '🔬', '#8b5cf6', 1),
    ('คณิตศาสตร์', 'mathematics', '📐', '#3b82f6', 2),
    ('เทคโนโลยี & คอมพิวเตอร์', 'technology', '💻', '#06b6d4', 3),
    ('ภาษา & วรรณกรรม', 'language', '📚', '#10b981', 4),
    ('ศิลปะ & ดนตรี', 'art-music', '🎨', '#f59e0b', 5),
    ('กีฬา', 'sports', '⚽', '#ef4444', 6),
    ('จิตอาสา & บำเพ็ญประโยชน์', 'volunteer', '💚', '#22c55e', 7),
    ('ผู้นำ & กิจกรรมนักเรียน', 'leadership', '👑', '#a855f7', 8),
    ('ทุนการศึกษา & รางวัล', 'scholarship-award', '🏆', '#eab308', 10),
    ('วิจัย & สิ่งประดิษฐ์', 'research', '🧪', '#6366f1', 11),
    ('อื่นๆ', 'others', '📋', '#64748b', 99);

-- หมวดหมู่ทักษะ
INSERT IGNORE INTO `skill_categories` (`name`, `source`) VALUES
    ('ทักษะวิทยาศาสตร์', 'PCSHSKN'),
    ('ทักษะคณิตศาสตร์', 'PCSHSKN'),
    ('ทักษะเทคโนโลยี', 'PCSHSKN'),
    ('ทักษะการสื่อสาร', 'PCSHSKN'),
    ('ทักษะการคิดวิเคราะห์', 'PCSHSKN'),
    ('ทักษะการทำงานร่วมกัน', 'PCSHSKN'),
    ('ทักษะการนำเสนอ', 'PCSHSKN'),
    ('ทักษะการวิจัย', 'PCSHSKN');

-- Badges
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES
    ('first_portfolio', 'ผลงานแรก', 'เพิ่มผลงานแรกเข้าสู่ระบบ', '🌟', '{"type":"count","field":"portfolio_items","threshold":1}'),
    ('rising_star', 'ดาวรุ่ง', 'มีผลงาน 5 รายการ', '⭐', '{"type":"count","field":"portfolio_items","threshold":5}'),
    ('all_rounder', 'รอบรู้', 'มีผลงานครบทุกหมวดหมู่', '🏅', '{"type":"categories","threshold":10}'),
    ('tech_master', 'นักเทคโนโลยี', 'มีผลงานเทคโนโลยี 10+', '💻', '{"type":"count","field":"category","value":"technology","threshold":10}'),
    ('science_champ', 'นักวิทย์', 'มีผลงานวิทยาศาสตร์ระดับชาติ', '🔬', '{"type":"achievement","level":"national","category":"science"}'),
    ('team_player', 'นักทำงานร่วมกัน', 'เข้าร่วม 3 โครงงาน', '🤝', '{"type":"count","field":"collaborations","threshold":3}'),
    ('consistent', 'ต่อเนื่อง', 'อัปเดต portfolio ทุกเดือน 3 เดือน', '📈', '{"type":"streak","months":3}'),
    ('mentor', 'พี่เลี้ยง', 'เป็นที่ปรึกษา 5 โครงงาน', '🧑‍🏫', '{"type":"count","field":"advisor","threshold":5}');

-- Goal Templates (Career Tracks)
INSERT IGNORE INTO `goal_templates` (`track_code`, `track_name`, `min_gpa`, `requirements`, `recommendations`, `radar_target`) VALUES
    ('ENG_CS', 'วิศวกรรมคอมพิวเตอร์', 3.00, 'GPA ≥ 3.0, Portfolio หมวด Tech ≥ 5, รางวัลแข่งขันเขียนโปรแกรม', 
     'เข้าร่วมแข่งขันเขียนโปรแกรม, สร้างโปรเจค Open Source, เรียนรู้ AI/ML',
     '{"I":9,"A":6,"E":5,"S":3,"R":7,"C":4}'),
    ('ENG_ALL', 'วิศวกรรมทุกสาขา', 3.00, 'GPA ≥ 3.0, Portfolio หมวดวิทย์ ≥ 5, มีความเป็นผู้นำ',
     'เข้าร่วมโครงงานวิทยาศาสตร์, ฝึกทักษะคณิตศาสตร์ขั้นสูง',
     '{"I":8,"A":5,"E":6,"S":4,"R":8,"C":5}'),
    ('SCI_ALL', 'วิทยาศาสตร์ทุกสาขา', 2.75, 'GPA ≥ 2.75, Portfolio หมวดวิทย์ ≥ 3, มีผลงานวิจัย',
     'เข้าร่วมงานวิจัยกับครู, เขียนบทความวิทยาศาสตร์',
     '{"I":9,"A":5,"E":4,"S":3,"R":9,"C":6}'),
    ('MED', 'แพทยศาสตร์', 3.50, 'GPA ≥ 3.5, วิทย์ ≥ 3.5, คณิต ≥ 3.5, กิจกรรมจิตอาสา ≥ 50 ชม.',
     'เข้าร่วมกิจกรรมจิตอาสา ศึกษาดูแลสุขภาพ',
     '{"I":7,"A":4,"E":5,"S":9,"R":6,"C":5}'),
    ('BUS_IT', 'บริหารธุรกิจ/ไอที', 2.75, 'GPA ≥ 2.75, Portfolio หมวดเทคโนโลยี/ผู้นำ, ทักษะการสื่อสาร',
     'ฝึกทักษะการนำเสนอ, เข้าร่วมกิจกรรมผู้นำ',
     '{"I":6,"A":5,"E":8,"S":7,"R":4,"C":6}'),
    ('ART_DES', 'ศิลปะ/ออกแบบ', 2.50, 'GPA ≥ 2.5, Portfolio หมวดศิลปะ ≥ 5, มีผลงานสร้างสรรค์',
     'สร้างผลงานศิลปะหลากหลาย, เข้าร่วมแข่งขันศิลปะ',
     '{"I":7,"A":9,"E":5,"S":5,"R":5,"C":4}');

-- Admin Account (password: admin1234)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `role`) VALUES
    ('admin', 'admin@pcshsksn.ac.th', '$2y$12$LJ3m4ks5Ozp8Xzv9q4j6UeTYKMjX5z5X5MrvQqUBwX5WnX9Zp5E3G', 'admin');
