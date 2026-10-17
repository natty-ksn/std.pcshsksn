-- ============================================
-- Migration: 010_analytics_readiness.sql
-- Description: ตารางรองรับ Early Warning, TCAS Readiness, Student 360° Profile
-- Created: 2026-04-03
-- ============================================

-- คะแนนความพร้อม (Readiness Scores) — ใช้สำหรับ Student 360° + TCAS Predictor
CREATE TABLE IF NOT EXISTS `readiness_scores` (
    `id`                  INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`          INT NOT NULL,
    `score_type`          ENUM('overall','academic','portfolio','skill','career_fit','engagement') NOT NULL,
    `score`               DECIMAL(5,2) NOT NULL,
    `max_score`           DECIMAL(5,2) DEFAULT 100.00,
    `details`             JSON COMMENT 'รายละเอียด breakdown แต่ละด้าน',
    `risk_level`          ENUM('low','medium','high','critical') DEFAULT 'low' COMMENT 'สำหรับ Early Warning',
    `calculated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_type` (`score_type`),
    INDEX `idx_risk` (`risk_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TCAS Prediction Results
CREATE TABLE IF NOT EXISTS `tcas_predictions` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `prediction_year` VARCHAR(9) NOT NULL,
    `overall_score`   DECIMAL(5,2) COMMENT 'คะแนนความพร้อมรวม 0-100',
    `component_scores` JSON COMMENT 'คะแนนแต่ละองค์ประกอบ (GPA, Portfolio, Awards, etc.)',
    `predicted_universities` JSON COMMENT 'มหาวิทยาลัยที่คาดการณ์ว่าติด',
    `gaps`            JSON COMMENT 'ช่องว่างที่ต้องเติม',
    `recommendations` TEXT COMMENT 'คำแนะนำเพื่อเพิ่มคะแนน',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_year` (`prediction_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Engagement Tracking — ใช้สำหรับ Analytics Dashboard
CREATE TABLE IF NOT EXISTS `engagement_metrics` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `metric_date`     DATE NOT NULL,
    `login_count`     INT DEFAULT 0,
    `portfolio_views` INT DEFAULT 0,
    `portfolio_edits` INT DEFAULT 0,
    `logbook_entries` INT DEFAULT 0,
    `comments_made`   INT DEFAULT 0,
    `projects_active` INT DEFAULT 0,
    `time_spent_min`  INT DEFAULT 0 COMMENT 'เวลาที่ใช้งานเป็นนาที',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_date` (`student_id`, `metric_date`),
    INDEX `idx_date` (`metric_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- School-wide Analytics Cache — pre-computed สำหรับ dashboard ผู้บริหาร
CREATE TABLE IF NOT EXISTS `analytics_cache` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `metric_key`      VARCHAR(100) NOT NULL COMMENT 'ชื่อ metric เช่น total_students, avg_gpa',
    `metric_value`    JSON NOT NULL COMMENT 'ค่า metric',
    `filters`         JSON COMMENT 'filter ที่ใช้ เช่น class_year=5',
    `computed_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at`      TIMESTAMP NULL COMMENT 'เวลาที่จะ expire cache',
    INDEX `idx_key` (`metric_key`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
