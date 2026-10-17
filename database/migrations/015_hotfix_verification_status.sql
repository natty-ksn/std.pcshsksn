-- ============================================================
-- HOTFIX: Add verification_status column to portfolio_items
-- ============================================================
-- TeacherController::pendingReviews() fails because 
-- pi.verification_status column doesn't exist.
-- Migration 013 added is_verified (TINYINT) but the code 
-- expects verification_status (ENUM).
-- ============================================================

ALTER TABLE `portfolio_items`
    ADD COLUMN IF NOT EXISTS `verification_status` ENUM('pending','verified','rejected') DEFAULT 'pending' 
        AFTER `is_verified`,
    ADD COLUMN IF NOT EXISTS `points_awarded` INT DEFAULT 0 AFTER `verification_status`,
    ADD INDEX IF NOT EXISTS `idx_verification_status` (`verification_status`, `status`);

-- Sync existing: if is_verified = 1, set verification_status = 'verified'
UPDATE `portfolio_items` 
    SET verification_status = 'verified' 
    WHERE is_verified = 1 AND verification_status = 'pending';
