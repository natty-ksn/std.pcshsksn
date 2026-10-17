-- ============================================
-- Migration: 001_core_users_and_auth.sql
-- Description: ตารางผู้ใช้งาน ระบบ authentication และ authorization
-- Created: 2026-04-03
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `username`        VARCHAR(50) UNIQUE NOT NULL,
    `email`           VARCHAR(100) UNIQUE NOT NULL,
    `password`        VARCHAR(255) NOT NULL,
    `full_name`       VARCHAR(150),
    `position`        VARCHAR(150),
    `role`            ENUM('student','teacher','admin','parent','executive') NOT NULL DEFAULT 'student',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `last_login`      DATETIME NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_role` (`role`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
