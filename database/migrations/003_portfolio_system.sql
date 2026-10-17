-- ============================================
-- Migration: 003_portfolio_system.sql
-- Description: ระบบ Portfolio — หมวดหมู่ ผลงาน และผู้ร่วมโครงงาน
-- Created: 2026-04-03
-- ============================================

-- หมวดหมู่ผลงาน
CREATE TABLE IF NOT EXISTS `portfolio_categories` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(100) UNIQUE,
    `icon`        VARCHAR(50) DEFAULT '📁',
    `color`       VARCHAR(7) DEFAULT '#6366f1',
    `sort_order`  INT DEFAULT 0,
    `is_active`   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ผลงาน Portfolio
CREATE TABLE IF NOT EXISTS `portfolio_items` (
    `id`                  INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`          INT NOT NULL,
    `category_id`         INT,
    `title`               VARCHAR(200) NOT NULL,
    `description`         TEXT,
    `achievement_level`   ENUM('school','district','province','national','international'),
    `award`               VARCHAR(200),
    `event_date`          DATE,
    `file_path`           VARCHAR(500),
    `thumbnail`           VARCHAR(500),
    `file_size`           INT DEFAULT 0,
    `is_featured`         TINYINT(1) DEFAULT 0,
    `tags`                JSON,
    `view_count`          INT DEFAULT 0,
    `status`              ENUM('draft','published','archived') DEFAULT 'published',
    `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `portfolio_categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_status` (`status`),
    FULLTEXT INDEX `idx_search` (`title`, `description`, `award`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ผู้ร่วมโครงงาน
CREATE TABLE IF NOT EXISTS `portfolio_collaborators` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `portfolio_id`    INT NOT NULL,
    `student_id`      INT NOT NULL,
    `acceptance_status` ENUM('pending','accepted','rejected') DEFAULT 'pending',
    `responded_at`    TIMESTAMP NULL,
    `role_in_project` VARCHAR(100),
    `reflection`      TEXT,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_collab` (`portfolio_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
