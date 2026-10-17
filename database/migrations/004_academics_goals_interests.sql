-- ============================================
-- Migration: 004_academics_goals_interests.sql
-- Description: ผลการเรียน เป้าหมายการศึกษา และความสนใจ
-- Created: 2026-04-03
-- ============================================

-- ผลการเรียน
CREATE TABLE IF NOT EXISTS `academic_records` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `subject`         VARCHAR(100) NOT NULL,
    `subject_code`    VARCHAR(20),
    `semester`        TINYINT NOT NULL,
    `academic_year`   VARCHAR(9) NOT NULL,
    `grade`           DECIMAL(3,2),
    `credit`          TINYINT DEFAULT 1,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student_year` (`student_id`, `academic_year`),
    UNIQUE KEY `unique_record` (`student_id`, `subject_code`, `semester`, `academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ความสนใจ & ความถนัด
CREATE TABLE IF NOT EXISTS `student_interests` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`  INT NOT NULL,
    `type`        ENUM('interest','talent','hobby') NOT NULL,
    `item`        VARCHAR(200) NOT NULL,
    `proficiency` TINYINT DEFAULT 3,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เป้าหมายการศึกษาต่อ
CREATE TABLE IF NOT EXISTS `student_goals` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `goal_type`       ENUM('university','major','career','shortterm','longterm') NOT NULL,
    `target`          VARCHAR(300) NOT NULL,
    `target_details`  TEXT,
    `target_score`    DECIMAL(5,2),
    `current_score`   DECIMAL(5,2),
    `deadline`        DATE,
    `status`          ENUM('planning','inprogress','achieved','cancelled') DEFAULT 'planning',
    `notes`           TEXT,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- แม่แบบเป้าหมายอาชีพ
CREATE TABLE IF NOT EXISTS `goal_templates` (
    `id`               INT PRIMARY KEY AUTO_INCREMENT,
    `track_code`       VARCHAR(50) UNIQUE,
    `track_name`       VARCHAR(255),
    `min_gpa`          DECIMAL(3,2),
    `requirements`     TEXT,
    `recommendations`  TEXT,
    `radar_target`     TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
