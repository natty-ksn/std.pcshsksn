USE pccks_eportfolio;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. ล้างข้อมูลเก่าสำหรับการ Demo (ลบเฉพาะตารางใหม่ที่สร้าง)
-- ============================================
TRUNCATE TABLE logbooks;
TRUNCATE TABLE project_documents;
TRUNCATE TABLE project_members;
TRUNCATE TABLE projects;
TRUNCATE TABLE project_subjects;

-- ============================================
-- 2. จำลองสร้างบัญชีครูที่ปรึกษา (อ.สมชาย) และนักเรียน (สายสมุทร)
-- รหัสผ่านคือ "password" (ที่ถูก hash ด้วย bcrypt default)
-- ============================================
INSERT INTO users (id, username, email, password, role, is_active) 
VALUES 
(901, 'Kru_Somchai', 'somchai@pcshsksn.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 1),
(902, 'Saisamhut_S', '64001@pcshsksn.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1)
ON DUPLICATE KEY UPDATE username=VALUES(username);

INSERT IGNORE INTO student_profiles (user_id, student_code, full_name, class_year)
VALUES (902, '64001', 'นายสายสมุทร นักวิจัยจิ๋ว', 4);

-- ============================================
-- 3. จำลองรายวิชา
-- ============================================
INSERT INTO project_subjects (id, code, name, class_year, semester, academic_year, teacher_id)
VALUES (1, 'ว30291', 'โครงงานวิทยาศาสตร์ 1', 4, 1, '2569', 901);

-- ============================================
-- 4. จำลองโครงงาน 2 เรื่อง (In Progress กับ Pending)
-- ============================================
INSERT INTO projects (id, project_code, title_th, title_en, project_type, subject_id, abstract_th, status, advisor_id, academic_year, semester)
VALUES 
(101, 'PRJ2026-0001', 'การพัฒนาพลาสติกชีวภาพจากเปลือกสับปะรดและสารสกัดขมิ้นชัน', 'Development of Bioplastics from Pineapple Peels and Turmeric Extract', 'experimental', 1, 'โครงงานนี้มุ่งเน้นการแก้ปัญหาขยะพลาสติก โดยการนำเปลือกสับปะรดซึ่งเป็นขยะทางการเกษตรมาสกัดเซลลูโลส และผสมสารสกัดจากขมิ้นชันเพื่อจำกัดการเจริญเติบโตของแบคทีเรีย ทำให้ได้แผ่นฟิล์มที่ย่อยสลายได้และยืดอายุอาหาร', 'in_progress', 901, '2026', 1),
(102, 'PRJ2026-0002', 'ระบบ AI ตรวจจับความผิดปกติของเซลล์พืชในกล้องจุลทรรศน์', 'AI Pathology Detection in Plant Cells Microscopic Images', 'invention', 1, 'การพัฒนาโมเดล Deep Learning ด้วย ResNet-50 ในการจำแนกชนิดของเซลล์ที่ติดเชื้อไวรัสจากภาพถ่ายสไลด์เนื้อเยื่อ โดยมีความแม่นยำสูงถึง 98% สามารถช่วยลดภาระของนักวิจัยทางการเกษตรได้', 'proposal_submitted', 901, '2026', 1);

-- ============================================
-- 5. จำลองสมาชิก
-- ============================================
INSERT INTO project_members (project_id, student_id, role_in_project)
VALUES 
(101, 902, 'leader'),
(102, 902, 'leader');

-- ============================================
-- 6. จำลองการอัปโหลดเอกสาร (สอดคล้องกับ GCS)
-- ============================================
INSERT INTO project_documents (project_id, uploaded_by, document_type, title, file_path_gcs, version, review_status, created_at)
VALUES 
(101, 902, 'proposal', 'ข้อเสนอโครงงานฉบับที่ 1', 'gcs://pcshsksn-bucket/2026/prj-0001/proposal_v1.pdf', 1, 'approved', '2026-01-10 10:00:00'),
(101, 902, 'proposal', 'ข้อเสนอโครงงานฉบับสมบูรณ์ (แก้ไขตามอาจารย์สั่ง)', 'gcs://pcshsksn-bucket/2026/prj-0001/proposal_v2.pdf', 2, 'approved', '2026-01-15 14:30:00'),
(101, 902, 'chapter1', 'ร่างบทที่ 1 บทนำ', 'gcs://pcshsksn-bucket/2026/prj-0001/chapter1_draft.pdf', 1, 'pending', '2026-02-05 09:15:00'),
(102, 902, 'proposal', 'ข้อเสนอโครงงานระบบ AI', 'gcs://pcshsksn-bucket/2026/prj-0002/proposal_ai.pdf', 1, 'pending', NOW());

-- ============================================
-- 7. จำลอง Logbook บันทึกการทำงานให้ Project 101
-- ============================================
INSERT INTO logbooks (project_id, student_id, activity_detail, hours_spent, teacher_comment, created_at)
VALUES 
(101, 902, 'สกัดเซลลูโลสจากเปลือกสับปะรดโดยใช้กระบวนการทางเคมี (NaOH) ได้เซลลูโลสแห้งบริสุทธิ์จำนวน 50 กรัม', 4.5, 'ดีมาก บันทึกอัตราส่วนสารเคมีและอุณหภูมิที่ใช้เก็บไว้เป็น Data ด้วยนะครับ', '2026-01-20 16:00:00'),
(101, 902, 'สกัดน้ำมันหอมระเหยและสารเคอร์คูมินจากขมิ้นชันด้วยวิธี Soxhlet Extraction เพื่อทนสอบสารยับยั้งแบคทีเรีย เตรียมการขั้นต่อไป', 5.0, NULL, '2026-01-27 15:30:00'),
(101, 902, 'นำแผ่นฟิล์มมาขึ้นรูปด้วยวิธีการ Casting (การหล่อขึ้นรูป) และทดสอบความยืดหยุ่นครั้งที่ 1 (แผ่นฟิล์มยังเปราะและฉีกขาดง่าย)', 3.5, NULL, '2026-02-03 14:00:00');

SET FOREIGN_KEY_CHECKS = 1;
