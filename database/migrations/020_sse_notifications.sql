-- ================================================
-- Migration 020: Real-Time Notifications (SSE Support)
-- ================================================
-- เพิ่ม index และ column สำหรับรองรับ Server-Sent Events (SSE)
-- เพื่อให้ SSE stream สามารถ query notification ล่าสุดได้อย่างมีประสิทธิภาพ
-- ================================================

-- 1. เพิ่ม column is_pushed เพื่อติดตามว่า notification ถูก push ไปยัง client แล้วหรือยัง
--    ป้องกันการส่งซ้ำเมื่อ SSE reconnect
ALTER TABLE `notifications`
    ADD COLUMN `is_pushed` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '0 = ยังไม่ push ผ่าน SSE, 1 = push แล้ว'
        AFTER `is_read`;

-- 2. Composite index สำหรับ SSE polling query
--    Query pattern: WHERE user_id = ? AND is_pushed = 0 ORDER BY created_at DESC
ALTER TABLE `notifications`
    ADD INDEX `idx_user_pushed` (`user_id`, `is_pushed`, `created_at`);

-- 3. Index สำหรับ badge real-time check (user_badges)
--    ใช้ในการดึงเหรียญใหม่ที่เพิ่งได้รับ
ALTER TABLE `user_badges`
    ADD INDEX `idx_user_earned` (`user_id`, `earned_at`);
