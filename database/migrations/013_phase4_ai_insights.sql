-- ============================================================
-- Phase 4: AI Insights & Field Readiness — Database Patch
-- ============================================================
-- รันไฟล์นี้หากต้องการเพิ่มตาราง/คอลัมน์ใหม่สำหรับ Phase 4
-- หมายเหตุ: AIService.php ใช้ข้อมูลที่มีอยู่แล้วเป็นหลัก
--           ไม่มีตารางใหม่ที่ "จำเป็น" ต้องมี
-- ============================================================

-- 1. เพิ่มคอลัมน์ is_verified, verification_status, points_awarded ใน portfolio_items
--    ใช้สำหรับกรองผลงานที่ครูตรวจสอบแล้ว และระบบ AI/Gap Analysis
ALTER TABLE `portfolio_items`
    ADD COLUMN IF NOT EXISTS `is_verified` TINYINT(1) DEFAULT 0 AFTER `is_featured`,
    ADD COLUMN IF NOT EXISTS `verification_status` ENUM('pending','verified','rejected') DEFAULT 'pending' AFTER `is_verified`,
    ADD COLUMN IF NOT EXISTS `verified_by` INT NULL AFTER `verification_status`,
    ADD COLUMN IF NOT EXISTS `verified_at` TIMESTAMP NULL AFTER `verified_by`,
    ADD COLUMN IF NOT EXISTS `points_awarded` INT DEFAULT 0 AFTER `verified_at`,
    ADD INDEX IF NOT EXISTS `idx_verification_status` (`verification_status`, `status`);

-- 2. ตารางเก็บประวัติการวิเคราะห์ AI (สำหรับ cache และ audit trail)
CREATE TABLE IF NOT EXISTS `ai_field_analysis_cache` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `track_code`      VARCHAR(50) NOT NULL,
    `readiness_pct`   DECIMAL(5,2) DEFAULT 0,
    `scores`          JSON,
    `gaps`            JSON,
    `strengths`       JSON,
    `calculated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_track` (`track_code`),
    INDEX `idx_calculated` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางเก็บการแจ้งเตือน Gap Analysis (Push notifications)
CREATE TABLE IF NOT EXISTS `ai_gap_notifications` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `gap_type`        VARCHAR(50) NOT NULL COMMENT 'category, skill, gpa, riasec, logbook, award, achievement',
    `gap_message`     TEXT,
    `track_code`      VARCHAR(50),
    `priority`        ENUM('high','medium','low') DEFAULT 'medium',
    `is_resolved`     TINYINT(1) DEFAULT 0,
    `resolved_at`     TIMESTAMP NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_priority` (`priority`, `is_resolved`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. View สำหรับดูภาพรวมความพร้อมรายสาขาวิชา
CREATE OR REPLACE VIEW `vw_field_readiness_summary` AS
SELECT
    sp.id AS student_id,
    sp.full_name,
    sp.class_year,
    sp.class_room,
    COALESCE(ROUND(AVG(ar.grade), 2), 0) AS gpa,
    COUNT(DISTINCT pi.id) AS total_works,
    COUNT(DISTINCT CASE WHEN pi.achievement_level IS NOT NULL THEN pi.id END) AS works_with_level,
    COUNT(DISTINCT CASE WHEN pi.award IS NOT NULL AND pi.award != '' THEN pi.id END) AS award_count,
    COALESCE(SUM(lb.hours_spent), 0) AS total_logbook_hours,
    (SELECT dominant_trait FROM student_assessments sa WHERE sa.user_id = sp.user_id AND sa.assessment_type = 'riasec' ORDER BY sa.created_at DESC LIMIT 1) AS riasec_dominant
FROM student_profiles sp
LEFT JOIN academic_records ar ON sp.id = ar.student_id
LEFT JOIN portfolio_items pi ON sp.id = pi.student_id AND pi.status = 'published'
LEFT JOIN logbooks lb ON sp.id = lb.student_id
GROUP BY sp.id, sp.full_name, sp.class_year, sp.class_room;
