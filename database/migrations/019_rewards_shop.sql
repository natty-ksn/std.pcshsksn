-- ================================================
-- Migration 019: Rewards Shop (Phase 4.1 — Points Shop MVP)
-- ================================================
-- สร้างระบบร้านค้าแลกของรางวัล นักเรียนสามารถใช้จ่าย Points
-- ที่สะสมมาเพื่อแลกธีมพอร์ตโฟลิโอ, เหรียญพิเศษ, หรือสิทธิ์ Premium
-- ================================================

-- 1. ตารางรายการของรางวัลในร้าน
CREATE TABLE IF NOT EXISTS `rewards_shop` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `points_cost` INT NOT NULL DEFAULT 0,
    `type` ENUM('theme', 'badge', 'privilege') NOT NULL DEFAULT 'theme',
    `image_icon` VARCHAR(100) DEFAULT NULL COMMENT 'Emoji or icon identifier',
    `stock` INT DEFAULT -1 COMMENT '-1 = unlimited, 0 = out of stock, >0 = remaining',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `metadata` JSON DEFAULT NULL COMMENT 'Extra config: theme CSS, badge code, privilege key',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_type` (`type`),
    INDEX `idx_active_cost` (`is_active`, `points_cost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Rewards Shop catalog — items students can redeem with points';

-- 2. ตารางบันทึกการแลกของรางวัล
CREATE TABLE IF NOT EXISTS `user_reward_redemptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `reward_id` INT NOT NULL,
    `points_spent` INT NOT NULL,
    `status` ENUM('pending', 'granted', 'revoked', 'expired') NOT NULL DEFAULT 'pending',
    `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `notes` TEXT DEFAULT NULL,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reward_id`) REFERENCES `rewards_shop`(`id`) ON DELETE CASCADE,

    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_redeemed_at` (`redeemed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tracks all user redemptions from the Rewards Shop';

-- 3. Seed Data — 3 MVP Items
INSERT INTO `rewards_shop` (`name`, `description`, `points_cost`, `type`, `image_icon`, `stock`, `is_active`, `metadata`) VALUES
('🌑 Dark Matter Theme',
 'ปลดล็อกธีม Dark Matter สำหรับหน้า Portfolio ของคุณ — ดีไซน์ระดับ Premium ที่เปลี่ยนพื้นหลังเป็นสีเข้มหรูหรา พร้อม Accent สี Neon Indigo และ Efek Glow ที่ดูมีระดับ',
 500,
 'theme',
 '🌑',
 -1,
 1,
 JSON_OBJECT('theme_key', 'dark_matter', 'css_vars', JSON_OBJECT('--bg-primary', '#0f172a', '--bg-card', '#1e293b', '--text-primary', '#e2e8f0', '--accent-glow', '#6366f1'))
),

('🏅 Golden Scholar Badge',
 'เหรียญตรา Golden Scholar สุด Exclusive ที่แสดงถึงความสำเร็จระดับสูง — เหรียญนี้จะปรากฏบน Portfolio และหน้า Profile ของคุณพร้อม Effect ประดับทองเรืองแสง',
 300,
 'badge',
 '🏅',
 -1,
 1,
 JSON_OBJECT('badge_code', 'golden_scholar', 'badge_tier', 'gold', 'display_name', 'Golden Scholar', 'glow_color', '#ffd700')
),

('🚀 Premium UI Unlock',
 'ปลดล็อกฟีเจอร์ UI พิเศษ: Custom Banner, Advanced Layout, Animated Card Effects และ Priority Showcase บนหน้า Dashboard ของโรงเรียน — ทำให้ Portfolio ของคุณโดดเด่นกว่าใคร',
 800,
 'privilege',
 '🚀',
 50,
 1,
 JSON_OBJECT('privilege_key', 'premium_ui', 'duration_days', 30, 'features', JSON_ARRAY('custom_banner', 'advanced_layout', 'animated_cards', 'priority_showcase'))
);
