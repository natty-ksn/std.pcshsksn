-- ============================================
-- Migration: 016_performance_indexes_part2.sql
-- Description: Performance optimization per phase 9 (Indexes, API Rate Limits, Cache tweaks)
-- Created: 2026-04-07
-- ============================================

-- เพิ่ม rate limits table (Task 9.3)
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NOT NULL,
    `endpoint` VARCHAR(150) NOT NULL,
    `requests` INT DEFAULT 1,
    `window_start` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_endpoint` (`ip_address`, `endpoint`),
    INDEX `idx_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- แก้ไข analytics_cache metric_key ให้เป็น UNIQUE เพื่อรองรับ ON DUPLICATE KEY UPDATE (Task 9.1)
ALTER TABLE `analytics_cache` 
DROP INDEX IF EXISTS `idx_key`,
ADD UNIQUE INDEX `idx_key_unique` (`metric_key`);

-- เพิ่ม Indexes สำหรับเพิ่ม Performance (Task 9.4)
-- Dashboard: ลดภาระตอนดึง portfolio ล่าสุด
ALTER TABLE `portfolio_items` ADD INDEX IF NOT EXISTS `idx_dashboard` (`student_id`, `verification_status`, `created_at`);

-- Teacher Review: ค้นหา projects ตามอาจารย์ที่ปรึกษา
ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_teacher_review` (`advisor_id`, `status`);

-- Timeline: ดึง logbook ได้รวดเร็ว
ALTER TABLE `logbooks` ADD INDEX IF NOT EXISTS `idx_timeline` (`student_id`, `created_at`);

-- Notifications: inbox query
ALTER TABLE `notifications` ADD INDEX IF NOT EXISTS `idx_inbox` (`user_id`, `is_read`, `created_at`);

-- ROLLBACK: 
-- ALTER TABLE `notifications` DROP INDEX `idx_inbox`;
-- ALTER TABLE `logbooks` DROP INDEX `idx_timeline`;
-- ALTER TABLE `projects` DROP INDEX `idx_teacher_review`;
-- ALTER TABLE `portfolio_items` DROP INDEX `idx_dashboard`;
-- ALTER TABLE `analytics_cache` DROP INDEX `idx_key_unique`, ADD INDEX `idx_key` (`metric_key`);
-- DROP TABLE IF EXISTS `api_rate_limits`;
