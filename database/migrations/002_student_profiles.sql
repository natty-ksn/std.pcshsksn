-- ============================================
-- Migration: 002_student_profiles.sql
-- Description: โปรไฟล์นักเรียน ข้อมูลส่วนตัว และห้องเรียน
-- Created: 2026-04-03
-- ============================================

CREATE TABLE IF NOT EXISTS `student_profiles` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`         INT UNIQUE NOT NULL,
    `student_code`    VARCHAR(20) UNIQUE,
    `full_name`       VARCHAR(150) NOT NULL,
    `nickname`        VARCHAR(50),
    `class_year`      TINYINT NOT NULL DEFAULT 1,
    `class_room`      VARCHAR(10),
    `date_of_birth`   DATE,
    `phone`           VARCHAR(15),
    `avatar_path`     VARCHAR(255),
    `bio`             TEXT,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_class` (`class_year`, `class_room`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
