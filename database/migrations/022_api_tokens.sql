-- ================================================
-- Migration 022: API Token Authentication
-- ================================================
-- ตารางเก็บ API Tokens สำหรับ Mobile App / Third-party clients
-- รองรับการทำ Token Revocation, Expiry, และ Audit
-- ================================================

CREATE TABLE IF NOT EXISTS `api_tokens` (
    `id`            INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`       INT NOT NULL,
    `token_hash`    VARCHAR(255) NOT NULL COMMENT 'SHA-256 hash ของ token จริง (เก็บเฉพาะ hash)',
    `token_prefix`  VARCHAR(8) NOT NULL COMMENT '8 ตัวแรกของ token สำหรับแสดงให้ผู้ใช้เห็น',
    `name`          VARCHAR(100) NOT NULL DEFAULT 'Mobile App' COMMENT 'ชื่อ token (เช่น iPhone 15, Android Tablet)',
    `abilities`     JSON DEFAULT NULL COMMENT 'สิทธิ์ของ token: ["read","write","admin"]',
    `last_used_at`  DATETIME NULL,
    `expires_at`    DATETIME NULL COMMENT 'NULL = ไม่มีวันหมดอายุ',
    `revoked_at`    DATETIME NULL COMMENT 'เวลาที่ถูกเพิกถอน',
    `ip_address`    VARCHAR(45) NULL COMMENT 'IP ที่สร้าง token',
    `user_agent`    VARCHAR(500) NULL COMMENT 'User-Agent ที่สร้าง token',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_revoked` (`revoked_at`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='API Token management for mobile/third-party Bearer token authentication';
