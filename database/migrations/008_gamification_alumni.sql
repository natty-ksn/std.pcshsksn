-- ============================================
-- Migration: 008_gamification_alumni.sql
-- Description: ระบบ Badge (Gamification) และ Alumni tracking
-- Created: 2026-04-03
-- ============================================

-- Badges Master
CREATE TABLE IF NOT EXISTS `badges_master` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `code`        VARCHAR(50) UNIQUE NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `description` VARCHAR(255),
    `icon`        VARCHAR(50),
    `criteria`    JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Badges
CREATE TABLE IF NOT EXISTS `user_badges` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`     INT NOT NULL,
    `badge_code`  VARCHAR(50) NOT NULL,
    `earned_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`badge_code`) REFERENCES `badges_master`(`code`),
    UNIQUE KEY `unique_badge` (`user_id`, `badge_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alumni Records
CREATE TABLE IF NOT EXISTS `alumni_records` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `graduation_year` VARCHAR(4),
    `university`      VARCHAR(200),
    `faculty`         VARCHAR(200),
    `major`           VARCHAR(200),
    `admission_type`  ENUM('tcas1_portfolio','tcas2_quota','tcas3_admission','tcas4_direct','other'),
    `current_status`  ENUM('studying','graduated','working') DEFAULT 'studying',
    `career`          VARCHAR(200),
    `is_public`       TINYINT(1) DEFAULT 0,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teacher Comments
CREATE TABLE IF NOT EXISTS `teacher_comments` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `portfolio_id`    INT NOT NULL,
    `teacher_id`      INT NOT NULL,
    `comment`         TEXT NOT NULL,
    `rating`          TINYINT,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
