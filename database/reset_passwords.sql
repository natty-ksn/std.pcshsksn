USE pccks_eportfolio;

-- แก้ไขชุดกุญแจหลัก (Password Hash for '12345') ให้ถูกต้องทุุดบัญชี
SET @pass12345Cache = '$2y$10$8mx7lEZ8y8x8hCYOIb1mQ.Wxb0eU.yPN/Q/e3CLvRpo98h9pA1jDS';

-- อัปเดตรายคนไปเลยเพื่อความปลอดภัยสูงสุด
UPDATE users SET password = @pass12345Cache WHERE username IN ('admin', '64001', 'teacher01', 'vd002');

-- เช็ค/สร้าง admin ใหม่ ถ้าโดนลบไป (ป้องกัน ID กระโดด)
INSERT INTO users (id, username, password, full_name, role, email, is_active)
VALUES (1, 'admin', @pass12345Cache, 'แอดมินระบบหลัก', 'admin', 'admin@pcshsksn.ac.th', 1)
ON DUPLICATE KEY UPDATE password=@pass12345Cache, full_name=VALUES(full_name), role=VALUES(role);

-- เช็ค/สร้าง นักเรียนใหม่ ถ้าโดนลบไป
INSERT INTO users (id, username, password, full_name, role, email, is_active)
VALUES (2, '64001', @pass12345Cache, 'นายสมชาย ใจดีถ้วน', 'student', '64001@pcshsksn.ac.th', 1)
ON DUPLICATE KEY UPDATE password=@pass12345Cache, full_name=VALUES(full_name), role=VALUES(role);

-- เช็ค/สร้าง ครูใหม่ ถ้าโดนลบไป
INSERT INTO users (id, username, password, full_name, role, email, is_active)
VALUES (3, 'teacher01', @pass12345Cache, 'คุณครูทดสอบ', 'teacher', 'teacher01@pcshsksn.ac.th', 1)
ON DUPLICATE KEY UPDATE password=@pass12345Cache, full_name=VALUES(full_name), role=VALUES(role);

-- เช็ค/สร้าง ผู้บริหารใหม่ ถ้าโดนลบไป
INSERT INTO users (id, username, password, full_name, role, email, is_active)
VALUES (4, 'vd002', @pass12345Cache, 'ผู้อำนวยการโรงเรียน', 'executive', 'vd002@pcshsksn.ac.th', 1)
ON DUPLICATE KEY UPDATE password=@pass12345Cache, full_name=VALUES(full_name), role=VALUES(role);
