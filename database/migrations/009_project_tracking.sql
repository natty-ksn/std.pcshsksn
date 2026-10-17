-- ============================================
-- Migration: 009_project_tracking.sql
-- Description: ระบบติดตามโครงงาน — Projects, Members, Milestones, Documents, Evaluations
-- Created: 2026-04-03
-- ============================================

-- วิชาโครงงาน
CREATE TABLE IF NOT EXISTS `project_subjects` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `code`            VARCHAR(20) NOT NULL,
    `name`            VARCHAR(200) NOT NULL,
    `class_year`      TINYINT NOT NULL,
    `semester`        TINYINT NOT NULL,
    `academic_year`   VARCHAR(9) NOT NULL,
    `teacher_id`      INT,
    `description`     TEXT,
    `is_active`       TINYINT(1) DEFAULT 1,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`),
    INDEX `idx_year` (`academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลหลักโครงงาน
CREATE TABLE IF NOT EXISTS `projects` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_code`    VARCHAR(30) UNIQUE NOT NULL,
    `title_th`        VARCHAR(300) NOT NULL,
    `title_en`        VARCHAR(300),
    `project_type`    ENUM('experimental','survey','invention','applied','theoretical') NOT NULL,
    `subject_id`      INT,
    `abstract_th`     TEXT,
    `abstract_en`     TEXT,
    `status`          ENUM('draft','proposal_submitted','proposal_approved','proposal_revision','proposal_rejected','in_progress', 'chapter_review','presentation_ready','completed','archived') DEFAULT 'draft',
    `ethics_status`   ENUM('not_required', 'pending', 'approved', 'rejected') DEFAULT 'not_required',
    `budget_requested` DECIMAL(10,2) DEFAULT 0.00,
    `budget_approved`  DECIMAL(10,2) DEFAULT 0.00,
    `advisor_id`      INT NOT NULL,
    `co_advisor_id`   INT,
    `external_expert` VARCHAR(200),
    `academic_year`   VARCHAR(9) NOT NULL,
    `semester`        TINYINT NOT NULL DEFAULT 1,
    `class_year`      TINYINT,
    `class_room`      VARCHAR(10),
    `start_date`      DATE,
    `expected_end`    DATE,
    `actual_end`      DATE,
    `overall_score`   DECIMAL(5,2),
    `tags`            JSON,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `project_subjects`(`id`),
    FOREIGN KEY (`advisor_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`co_advisor_id`) REFERENCES `users`(`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_advisor` (`advisor_id`),
    FULLTEXT INDEX `idx_search` (`title_th`, `title_en`, `abstract_th`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- สมาชิกโครงงาน
CREATE TABLE IF NOT EXISTS `project_members` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_id`      INT NOT NULL,
    `student_id`      INT NOT NULL,
    `role_in_project` ENUM('leader','member','secretary') DEFAULT 'member',
    `joined_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_member` (`project_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- บันทึกการทำงาน (Logbooks)
CREATE TABLE IF NOT EXISTS `logbooks` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_id`      INT NOT NULL,
    `student_id`      INT NOT NULL,
    `activity_detail` TEXT NOT NULL,
    `hours_spent`     DECIMAL(4,2),
    `teacher_comment` TEXT,
    `is_verified`     TINYINT(1) DEFAULT 0,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`),
    INDEX `idx_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- จุดหมาย (Milestones)
CREATE TABLE IF NOT EXISTS `milestones` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_id`      INT NOT NULL,
    `phase_name`      VARCHAR(200) NOT NULL,
    `description`     TEXT,
    `milestone_order` TINYINT DEFAULT 1,
    `due_date`        DATE,
    `completed_date`  DATE,
    `status`          ENUM('pending','in_progress','submitted','approved','overdue') DEFAULT 'pending',
    `progress_pct`    TINYINT DEFAULT 0,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    INDEX `idx_project` (`project_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เอกสารและการตรวจ
CREATE TABLE IF NOT EXISTS `project_documents` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_id`      INT NOT NULL,
    `uploaded_by`     INT NOT NULL,
    `document_type`   ENUM('proposal','chapter1','chapter2','chapter3','chapter4','chapter5','full_report','presentation','poster','appendix','other') NOT NULL,
    `chapter_number`  TINYINT,
    `title`           VARCHAR(200),
    `file_path_gcs`   VARCHAR(500) NOT NULL,
    `file_size`       INT DEFAULT 0,
    `file_type`       VARCHAR(50),
    `version`         INT DEFAULT 1,
    `review_status`   ENUM('pending','reviewing','approved','revision_needed','rejected') DEFAULT 'pending',
    `reviewed_by`     INT,
    `reviewed_at`     DATETIME,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`),
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- การให้คะแนนโครงงาน
CREATE TABLE IF NOT EXISTS `project_evaluations` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `project_id`      INT NOT NULL,
    `evaluator_id`    INT NOT NULL,
    `eval_type`       ENUM('proposal','progress','chapter','presentation','final') NOT NULL,
    `scores`          JSON,
    `total_score`     DECIMAL(5,2),
    `feedback`        TEXT,
    `result`          ENUM('pass','fail','revise') DEFAULT 'pass',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`evaluator_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
