-- ============================================================
-- Phase 5: Dynamic Gamification — Database Patch
-- ============================================================
-- เพิ่ม badge definitions ใหม่ + ปรับโครงสร้างสำหรับระบบ Dynamic Badges
-- ============================================================

-- 1. เพิ่มคอลัมน์ tier และ color ใน badges_master (optional — BadgeService เก็บในโค้ด)
ALTER TABLE `badges_master`
    ADD COLUMN IF NOT EXISTS `tier` VARCHAR(20) DEFAULT 'bronze' AFTER `criteria`,
    ADD COLUMN IF NOT EXISTS `color` VARCHAR(7) DEFAULT '#cd7f32' AFTER `tier`,
    ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `color`;

-- 2. Seed: Dynamic Badge Definitions (25 badges across 5 tiers)
--    หมายเหตุ: BadgeService เก็บ logic ใน PHP — SQL นี้สำหรับแสดงใน UI เท่านั้น
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`, `tier`, `color`) VALUES
    -- BRONZE (5)
    ('bronze_first_step', 'ก้าวแรก', 'เพิ่มผลงานแรกเข้าสู่ระบบ', '🌟', '{"event":"portfolio_created"}', 'bronze', '#cd7f32'),
    ('bronze_explorer', 'นักสำรวจ', 'ทำแบบประเมิน RIASEC สำเร็จ', '🧭', '{"event":"riasec_taken"}', 'bronze', '#cd7f32'),
    ('bronze_goal_setter', 'นักตั้งเป้าหมาย', 'ตั้งเป้าหมายการศึกษาอย่างน้อย 1 รายการ', '🎯', '{"event":"goal_set"}', 'bronze', '#cd7f32'),
    ('bronze_logbook_starter', 'นักบันทึกมือใหม่', 'บันทึก Logbook ครั้งแรก', '📝', '{"event":"logbook_added"}', 'bronze', '#cd7f32'),
    ('bronze_team_joiner', 'สมาชิกทีม', 'เข้าร่วมโครงงานครั้งแรก', '🤝', '{"event":"project_joined"}', 'bronze', '#cd7f32'),

    -- SILVER (7)
    ('silver_rising_star', 'ดาวรุ่ง', 'มีผลงาน 5 รายการ', '⭐', '{"threshold":5}', 'silver', '#c0c0c0'),
    ('silver_social_butterfly', 'นักสื่อสาร', 'มีผลงานหมวดภาษา 2+', '🗣️', '{"category":"language","min":2}', 'silver', '#c0c0c0'),
    ('silver_tech_savvy', 'สายเทค', 'มีผลงานเทคโนโลยี 3+', '💻', '{"category":"technology","min":3}', 'silver', '#c0c0c0'),
    ('silver_volunteer', 'จิตอาสา', 'มีผลงานจิตอาสา 2+', '💚', '{"category":"volunteer","min":2}', 'silver', '#c0c0c0'),
    ('silver_logbook_10', 'นักบันทึกมุ่งมั่น', 'บันทึก Logbook 10 ครั้ง', '📓', '{"logbook_count":10}', 'silver', '#c0c0c0'),
    ('silver_researcher', 'นักวิจัย', 'มีผลงานวิจัย 1+', '🔬', '{"category":"research","min":1}', 'silver', '#c0c0c0'),
    ('silver_leader', 'ผู้นำ', 'เป็นหัวหน้าทีม 1 โครงงาน', '👑', '{"leader_count":1}', 'silver', '#c0c0c0'),

    -- GOLD (6)
    ('gold_all_rounder', 'รอบรู้', 'มีผลงานครบ 5 หมวดหมู่', '🏅', '{"categories":5}', 'gold', '#ffd700'),
    ('gold_science_champ', 'นักวิทย์ดีเด่น', 'ผลงานวิทย์ 5+ และระดับ province+', '🧪', '{"category":"science-project","min":5,"achievement":"province"}', 'gold', '#ffd700'),
    ('gold_math_wizard', 'อัจฉริยะคณิต', 'ผลงานคณิต 3+ และ GPA ≥ 3.5', '📐', '{"category":"mathematics","min":3,"subject_gpa":3.5}', 'gold', '#ffd700'),
    ('gold_consistent', 'นักสู้ต่อเนื่อง', 'เพิ่มผลงาน 3 เดือนติด', '📈', '{"streak_months":3}', 'gold', '#ffd700'),
    ('gold_award_winner', 'ผู้พิชิตรางวัล', 'มีรางวัล 3+', '🏆', '{"awards":3}', 'gold', '#ffd700'),
    ('gold_logbook_master', 'เจ้าแห่ง Logbook', 'สะสม 50 ชั่วโมง', '⏱️', '{"logbook_hours":50}', 'gold', '#ffd700'),

    -- PLATINUM (4)
    ('platinum_national_hero', 'วีรบุรุษระดับชาติ', 'ผลงานระดับชาติ', '🇹🇭', '{"achievement":"national"}', 'platinum', '#e5e4e2'),
    ('platinum_international', 'ระดับโลก', 'ผลงานระดับนานาชาติ', '🌍', '{"achievement":"international"}', 'platinum', '#e5e4e2'),
    ('platinum_perfect_portfolio', 'พอร์ตสมบูรณ์', '15+ ผลงาน, 8 หมวดหมู่, 5 รางวัล, GPA 3.5+', '💎', '{"portfolio":15,"categories":8,"awards":5,"gpa":3.5}', 'platinum', '#e5e4e2'),
    ('platinum_ai_champion', 'AI Champion', 'ความพร้อม ≥ 90% จาก AI', '🤖', '{"ai_readiness":90}', 'platinum', '#e5e4e2'),

    -- SECRET (3)
    ('secret_night_owl', 'นกฮูกเที่ยงคืน', 'เพิ่มผลงานระหว่าง 00:00-04:00', '🦉', '{"late_night":true}', 'secret', '#8b5cf6'),
    ('secret_multi_project', 'จอมยุทธ์พหุโครงงาน', 'สมาชิก 5 โครงงานพร้อมกัน', '🐙', '{"active_projects":5}', 'secret', '#8b5cf6'),
    ('secret_skill_master', 'ปรมาจารย์ทักษะ', 'ทักษะ 10+ ที่ระดับ ≥ 3', '🥋', '{"skills_mastered":10}', 'secret', '#8b5cf6');

-- 3. AI-based badges (Phase 4 integration)
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`, `tier`, `color`) VALUES
    ('ai_field_ready', 'พร้อมสู่สนามจริง', 'ความพร้อม ≥ 70% ในสาขาที่เหมาะสม', '🚀', '{"ai_readiness":70}', 'gold', '#ffd700'),
    ('ai_gap_fighter', 'นักสู้ช่องว่าง', 'ลดช่องว่าง ≥ 50%', '⚔️', '{"ai_gap_reduction":50}', 'gold', '#ffd700'),
    ('ai_portfolio_a', 'ผลงานเกรด A', 'คุณภาพพอร์ตเฉลี่ยระดับ A', '📝', '{"audit_grade":"A"}', 'gold', '#ffd700');
