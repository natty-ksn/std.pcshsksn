-- ============================================
-- Migration: 005_skills_assessments.sql
-- Description: ระบบทักษะ การประเมินตนเอง และ RIASEC
-- Created: 2026-04-03
-- ============================================

-- หมวดหมู่ทักษะ
CREATE TABLE IF NOT EXISTS `skill_categories` (
    `id`      INT PRIMARY KEY AUTO_INCREMENT,
    `name`    VARCHAR(100) NOT NULL,
    `source`  VARCHAR(100) DEFAULT 'PCSHSKN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ทักษะมาตรฐาน
CREATE TABLE IF NOT EXISTS `skills_master` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `category_id`     INT,
    `skill_name`      VARCHAR(150) NOT NULL,
    `description`     TEXT,
    `is_approved`     TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`category_id`) REFERENCES `skill_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ทักษะ -> ผลงาน
CREATE TABLE IF NOT EXISTS `portfolio_skills` (
    `portfolio_id`    INT NOT NULL,
    `skill_id`        INT NOT NULL,
    `proficiency`     TINYINT DEFAULT 3,
    PRIMARY KEY (`portfolio_id`, `skill_id`),
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills_master`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- แบบประเมินตนเอง (RIASEC)
CREATE TABLE IF NOT EXISTS `student_assessments` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`         INT NOT NULL,
    `assessment_type` VARCHAR(50) DEFAULT 'riasec',
    `dominant_trait`  VARCHAR(10),
    `result_data`     JSON,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
