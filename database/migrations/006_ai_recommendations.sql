-- ============================================
-- Migration: 006_ai_recommendations.sql
-- Description: ระบบ AI recommendations และการแจ้งเตือน
-- Created: 2026-04-03
-- ============================================

-- AI Recommendations
CREATE TABLE IF NOT EXISTS `ai_recommendations` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `analysis_type`   ENUM('portfolio_gap','study_guide','career_match','skill_suggest') NOT NULL,
    `prompt_context`  TEXT,
    `response_data`   JSON,
    `generated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_read`         TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- การแจ้งเตือน
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`     INT NOT NULL,
    `type`        VARCHAR(50) NOT NULL,
    `title`       VARCHAR(200) NOT NULL,
    `message`     TEXT,
    `link`        VARCHAR(500),
    `is_read`     TINYINT(1) DEFAULT 0,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_read` (`user_id`, `is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio Share Links
CREATE TABLE IF NOT EXISTS `portfolio_shares` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `share_token`     VARCHAR(64) UNIQUE NOT NULL,
    `share_type`      ENUM('public','token','password') DEFAULT 'token',
    `password_hash`   VARCHAR(255),
    `allowed_sections` JSON,
    `expires_at`      DATETIME,
    `view_count`      INT DEFAULT 0,
    `is_active`       TINYINT(1) DEFAULT 1,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
