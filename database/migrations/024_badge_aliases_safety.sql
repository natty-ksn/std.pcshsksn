-- Migration 024: Badge Master Aliases Safety
-- Ensures all referenced badge codes exist in badges_master to satisfy Foreign Key constraints on user_badges(badge_code)

INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES
('first_upload', 'ผลงานแรก', 'เพิ่มผลงานแรกเข้าสู่ระบบ (Legacy Alias)', '🌟', '{"type":"count","field":"portfolio_items","threshold":1}'),
('five_works', 'ดาวรุ่ง', 'มีผลงาน 5 รายการ (Legacy Alias)', '⭐', '{"type":"count","field":"portfolio_items","threshold":5}'),
('ten_works', 'รอบรู้', 'มีผลงาน 10 รายการ (Legacy Alias)', '🏅', '{"type":"count","field":"portfolio_items","threshold":10}'),
('twenty_works', 'เชี่ยวชาญ', 'มีผลงาน 20 รายการ (Legacy Alias)', '🏆', '{"type":"count","field":"portfolio_items","threshold":20}'),
('goal_setter', 'นักตั้งเป้าหมาย', 'ตั้งเป้าหมายการศึกษา (Legacy Alias)', '🎯', '{"type":"count","field":"goals","threshold":1}'),
('goal_achiever', 'ผู้บรรลุเป้าหมาย', 'ทำเป้าหมายสำเร็จ (Legacy Alias)', '🥇', '{"type":"count","field":"goals_completed","threshold":1}');
