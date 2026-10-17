-- ============================================
-- Migration: 007_security_audit.sql
-- Description: PDPA consent, audit logs และระบบความปลอดภัย
-- Created: 2026-04-03
-- ============================================

-- PDPA Consent
CREATE TABLE IF NOT EXISTS `pdpa_consents` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`         INT NOT NULL,
    `consent_type`    VARCHAR(50) NOT NULL,
    `is_consented`    TINYINT(1) DEFAULT 0,
    `consented_at`    DATETIME,
    `withdrawn_at`    DATETIME,
    `ip_address`      VARCHAR(45),
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_consent` (`user_id`, `consent_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`     INT,
    `action`      VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50),
    `entity_id`   INT,
    `old_value`   JSON,
    `new_value`   JSON,
    `ip_address`  VARCHAR(45),
    `user_agent`  VARCHAR(500),
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
