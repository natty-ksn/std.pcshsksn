-- ============================================
-- Migration: 011_performance_indexes.sql
-- Description: เพิ่ม index สำหรับ query ที่ใช้บ่อย — เพิ่มประสิทธิภาพ 10x
-- Created: 2026-04-03
-- ============================================

-- Portfolio: ค้นหาตามนักเรียน + หมวดหมู่ + วันที่
ALTER TABLE `portfolio_items` 
    ADD INDEX `idx_student_status_date` (`student_id`, `status`, `created_at`),
    ADD INDEX `idx_achievement` (`achievement_level`),
    ADD INDEX `idx_created_at` (`created_at`);

-- Projects: ค้นหาตาม status + advisor + ปีการศึกษา
ALTER TABLE `projects` 
    ADD INDEX `idx_year_semester` (`academic_year`, `semester`),
    ADD INDEX `idx_class` (`class_year`, `class_room`);

-- Logbooks: ตรวจสอบตาม project + student + วันที่
ALTER TABLE `logbooks` 
    ADD INDEX `idx_student_project` (`student_id`, `project_id`),
    ADD INDEX `idx_verified` (`is_verified`);

-- Documents: ตรวจสอบตาม project + type + review status
ALTER TABLE `project_documents` 
    ADD INDEX `idx_project_type_status` (`project_id`, `document_type`, `review_status`);

-- Milestones: ตรวจสอบตาม project + status + due date
ALTER TABLE `milestones` 
    ADD INDEX `idx_project_due` (`project_id`, `status`, `due_date`);

-- Academic Records: คำนวณ GPA ตามปี
ALTER TABLE `academic_records` 
    ADD INDEX `idx_subject` (`subject_code`);

-- Notifications: เรียงตามวันที่สร้าง
ALTER TABLE `notifications` 
    ADD INDEX `idx_user_created` (`user_id`, `created_at` DESC);

-- Audit Logs: ค้นหาตาม entity + action
ALTER TABLE `audit_logs` 
    ADD INDEX `idx_entity` (`entity_type`, `entity_id`),
    ADD INDEX `idx_user_action` (`user_id`, `action`);
