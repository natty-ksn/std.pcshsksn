-- ============================================================
-- PCSHSKSN e-Portfolio — Production Ready Database Dump
-- Generated at: 2026-10-17 09:40:49
-- Compatible with Shared Hosting (cPanel / DirectAdmin / phpMyAdmin)
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- Table: academic_records
DROP TABLE IF EXISTS `academic_records`;
CREATE TABLE IF NOT EXISTS `academic_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `semester` tinyint(4) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `grade` decimal(3,2) DEFAULT NULL,
  `credit` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_record` (`student_id`,`subject_code`,`semester`,`academic_year`),
  KEY `idx_student_year` (`student_id`,`academic_year`),
  KEY `idx_subject` (`subject_code`),
  CONSTRAINT `academic_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ai_field_analysis_cache
DROP TABLE IF EXISTS `ai_field_analysis_cache`;
CREATE TABLE IF NOT EXISTS `ai_field_analysis_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `ai_model` varchar(100) NOT NULL DEFAULT 'heuristic_v1' COMMENT 'Model/version that generated this cache',
  `field_scores_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'All readiness scores as JSON: {overall, academic, portfolio, skill, riasec, ...}' CHECK (json_valid(`field_scores_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Cache expiry — NULL means no expiry',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_model` (`student_id`,`ai_model`),
  KEY `idx_student` (`student_id`),
  KEY `idx_model` (`ai_model`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Eager-loaded AI analysis cache to eliminate N+1 readiness queries';

-- Table: ai_recommendations
DROP TABLE IF EXISTS `ai_recommendations`;
CREATE TABLE IF NOT EXISTS `ai_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `analysis_type` enum('portfolio_gap','study_guide','career_match','skill_suggest') NOT NULL,
  `prompt_context` text DEFAULT NULL,
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ai_tcas_gap_analysis
DROP TABLE IF EXISTS `ai_tcas_gap_analysis`;
CREATE TABLE IF NOT EXISTS `ai_tcas_gap_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `target_faculty` varchar(100) NOT NULL COMMENT 'e.g. medicine, engineering, computer_science',
  `ai_model` varchar(100) NOT NULL DEFAULT 'gemini-2.0-flash',
  `analysis_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Full Gemini JSON response parsed' CHECK (json_valid(`analysis_json`)),
  `overall_readiness` decimal(5,2) DEFAULT 0.00 COMMENT 'Overall readiness percentage 0-100',
  `is_fallback` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = used rule-based fallback, 0 = Gemini AI',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '30 days from created_at',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_faculty_model` (`student_id`,`target_faculty`,`ai_model`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_student` (`student_id`),
  CONSTRAINT `ai_tcas_gap_analysis_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache for AI-generated TCAS Gap Analysis results (30-day TTL)';

-- Table: alumni_records
DROP TABLE IF EXISTS `alumni_records`;
CREATE TABLE IF NOT EXISTS `alumni_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `graduation_year` varchar(4) DEFAULT NULL,
  `university` varchar(200) DEFAULT NULL,
  `faculty` varchar(200) DEFAULT NULL,
  `major` varchar(200) DEFAULT NULL,
  `admission_type` enum('tcas1_portfolio','tcas2_quota','tcas3_admission','tcas4_direct','other') DEFAULT NULL,
  `current_status` enum('studying','graduated','working') DEFAULT 'studying',
  `career` varchar(200) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `alumni_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: analytics_cache
DROP TABLE IF EXISTS `analytics_cache`;
CREATE TABLE IF NOT EXISTS `analytics_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_key` varchar(100) NOT NULL COMMENT 'Ó©èÓ©ÀÓ╣êÓ©¡ metric Ó╣ÇÓ©èÓ╣êÓ©Ö total_students, avg_gpa',
  `metric_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Ó©äÓ╣êÓ©▓ metric' CHECK (json_valid(`metric_value`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'filter Ó©ùÓ©ÁÓ╣êÓ╣âÓ©èÓ╣ë Ó╣ÇÓ©èÓ╣êÓ©Ö class_year=5' CHECK (json_valid(`filters`)),
  `computed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Ó╣ÇÓ©ºÓ©ÑÓ©▓Ó©ùÓ©ÁÓ╣êÓ©êÓ©░ expire cache',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_key_unique` (`metric_key`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: api_rate_limits
DROP TABLE IF EXISTS `api_rate_limits`;
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(150) NOT NULL,
  `requests` int(11) DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_endpoint` (`ip_address`,`endpoint`),
  KEY `idx_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: api_tokens
DROP TABLE IF EXISTS `api_tokens`;
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL COMMENT 'SHA-256 hash ของ token จริง (เก็บเฉพาะ hash)',
  `token_prefix` varchar(8) NOT NULL COMMENT '8 ตัวแรกของ token สำหรับแสดงให้ผู้ใช้เห็น',
  `name` varchar(100) NOT NULL DEFAULT 'Mobile App' COMMENT 'ชื่อ token (เช่น iPhone 15, Android Tablet)',
  `abilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'สิทธิ์ของ token: ["read","write","admin"]' CHECK (json_valid(`abilities`)),
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL COMMENT 'NULL = ไม่มีวันหมดอายุ',
  `revoked_at` datetime DEFAULT NULL COMMENT 'เวลาที่ถูกเพิกถอน',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP ที่สร้าง token',
  `user_agent` varchar(500) DEFAULT NULL COMMENT 'User-Agent ที่สร้าง token',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_user` (`user_id`),
  KEY `idx_revoked` (`revoked_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API Token management for mobile/third-party Bearer token authentication';

-- Table: audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_user_action` (`user_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: badges_master
DROP TABLE IF EXISTS `badges_master`;
CREATE TABLE IF NOT EXISTS `badges_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`criteria`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: document_comments
DROP TABLE IF EXISTS `document_comments`;
CREATE TABLE IF NOT EXISTS `document_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `commenter_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_type` enum('general','suggestion','correction','praise') DEFAULT 'general',
  `page_number` int(11) DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `commenter_id` (`commenter_id`),
  KEY `idx_document` (`document_id`),
  CONSTRAINT `document_comments_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `project_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_comments_ibfk_2` FOREIGN KEY (`commenter_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: engagement_metrics
DROP TABLE IF EXISTS `engagement_metrics`;
CREATE TABLE IF NOT EXISTS `engagement_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `metric_date` date NOT NULL,
  `login_count` int(11) DEFAULT 0,
  `portfolio_views` int(11) DEFAULT 0,
  `portfolio_edits` int(11) DEFAULT 0,
  `logbook_entries` int(11) DEFAULT 0,
  `comments_made` int(11) DEFAULT 0,
  `projects_active` int(11) DEFAULT 0,
  `time_spent_min` int(11) DEFAULT 0 COMMENT 'Ó╣ÇÓ©ºÓ©ÑÓ©▓Ó©ùÓ©ÁÓ╣êÓ╣âÓ©èÓ╣ëÓ©çÓ©▓Ó©ÖÓ╣ÇÓ©øÓ╣çÓ©ÖÓ©ÖÓ©▓Ó©ùÓ©Á',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_date` (`student_id`,`metric_date`),
  KEY `idx_date` (`metric_date`),
  CONSTRAINT `engagement_metrics_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: goal_templates
DROP TABLE IF EXISTS `goal_templates`;
CREATE TABLE IF NOT EXISTS `goal_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `track_code` varchar(50) DEFAULT NULL,
  `track_name` varchar(255) DEFAULT NULL,
  `min_gpa` decimal(3,2) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `radar_target` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `track_code` (`track_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: logbooks
DROP TABLE IF EXISTS `logbooks`;
CREATE TABLE IF NOT EXISTS `logbooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_detail` text NOT NULL,
  `hours_spent` decimal(4,2) DEFAULT NULL,
  `teacher_comment` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_student_project` (`student_id`,`project_id`),
  KEY `idx_verified` (`is_verified`),
  KEY `idx_timeline` (`student_id`,`created_at`),
  CONSTRAINT `logbooks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logbooks_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: milestone_submissions
DROP TABLE IF EXISTS `milestone_submissions`;
CREATE TABLE IF NOT EXISTS `milestone_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milestone_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `file_path_gcs` varchar(500) DEFAULT NULL,
  `advisor_comment` text DEFAULT NULL,
  `status` enum('submitted','revise','accepted') DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `milestone_id` (`milestone_id`),
  KEY `submitted_by` (`submitted_by`),
  CONSTRAINT `milestone_submissions_ibfk_1` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `milestone_submissions_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: milestones
DROP TABLE IF EXISTS `milestones`;
CREATE TABLE IF NOT EXISTS `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `phase_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `milestone_order` tinyint(4) DEFAULT 1,
  `due_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','submitted','approved','overdue') DEFAULT 'pending',
  `progress_pct` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_status` (`status`),
  KEY `idx_project_due` (`project_id`,`status`,`due_date`),
  CONSTRAINT `milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_pushed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = ยังไม่ push ผ่าน SSE, 1 = push แล้ว',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_created` (`created_at`),
  KEY `idx_user_created` (`user_id`,`created_at`),
  KEY `idx_inbox` (`user_id`,`is_read`,`created_at`),
  KEY `idx_user_pushed` (`user_id`,`is_pushed`,`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pdpa_consents
DROP TABLE IF EXISTS `pdpa_consents`;
CREATE TABLE IF NOT EXISTS `pdpa_consents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `consent_type` varchar(50) NOT NULL,
  `is_consented` tinyint(1) DEFAULT 0,
  `consented_at` datetime DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_consent` (`user_id`,`consent_type`),
  CONSTRAINT `pdpa_consents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_annotations
DROP TABLE IF EXISTS `portfolio_annotations`;
CREATE TABLE IF NOT EXISTS `portfolio_annotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `target_field` varchar(50) DEFAULT 'general',
  `snippet` text DEFAULT NULL,
  `severity` enum('info','suggestion','issue','critical') DEFAULT 'suggestion',
  `comment` text NOT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_portfolio` (`portfolio_id`),
  KEY `idx_resolved` (`is_resolved`),
  CONSTRAINT `portfolio_annotations_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portfolio_annotations_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_categories
DROP TABLE IF EXISTS `portfolio_categories`;
CREATE TABLE IF NOT EXISTS `portfolio_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `color` varchar(7) DEFAULT '#6366f1',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_collaborators
DROP TABLE IF EXISTS `portfolio_collaborators`;
CREATE TABLE IF NOT EXISTS `portfolio_collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `acceptance_status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `responded_at` timestamp NULL DEFAULT NULL,
  `role_in_project` varchar(100) DEFAULT NULL,
  `reflection` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_collab` (`portfolio_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `portfolio_collaborators_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portfolio_collaborators_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_items
DROP TABLE IF EXISTS `portfolio_items`;
CREATE TABLE IF NOT EXISTS `portfolio_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `achievement_level` enum('school','district','province','national','international') DEFAULT NULL,
  `award` varchar(200) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `view_count` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `points_awarded` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_status` (`status`),
  KEY `idx_student_status_date` (`student_id`,`status`,`created_at`),
  KEY `idx_achievement` (`achievement_level`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_verification_status` (`verification_status`,`status`),
  KEY `idx_dashboard` (`student_id`,`verification_status`,`created_at`),
  FULLTEXT KEY `idx_search` (`title`,`description`,`award`),
  CONSTRAINT `portfolio_items_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portfolio_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `portfolio_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_shares
DROP TABLE IF EXISTS `portfolio_shares`;
CREATE TABLE IF NOT EXISTS `portfolio_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `share_token` varchar(64) NOT NULL,
  `share_type` enum('public','token','password') DEFAULT 'token',
  `password_hash` varchar(255) DEFAULT NULL,
  `allowed_sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_sections`)),
  `expires_at` datetime DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `share_token` (`share_token`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `portfolio_shares_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: portfolio_skills
DROP TABLE IF EXISTS `portfolio_skills`;
CREATE TABLE IF NOT EXISTS `portfolio_skills` (
  `portfolio_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency` tinyint(4) DEFAULT 3,
  PRIMARY KEY (`portfolio_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `portfolio_skills_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portfolio_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills_master` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: project_documents
DROP TABLE IF EXISTS `project_documents`;
CREATE TABLE IF NOT EXISTS `project_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `document_type` enum('proposal','chapter1','chapter2','chapter3','chapter4','chapter5','full_report','presentation','poster','appendix','other') NOT NULL,
  `chapter_number` tinyint(4) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `file_path_gcs` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `file_type` varchar(50) DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `review_status` enum('pending','reviewing','approved','revision_needed','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_project_type_status` (`project_id`,`document_type`,`review_status`),
  CONSTRAINT `project_documents_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `project_documents_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: project_evaluations
DROP TABLE IF EXISTS `project_evaluations`;
CREATE TABLE IF NOT EXISTS `project_evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `eval_type` enum('proposal','progress','chapter','presentation','final') NOT NULL,
  `scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scores`)),
  `total_score` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `result` enum('pass','fail','revise') DEFAULT 'pass',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `evaluator_id` (`evaluator_id`),
  CONSTRAINT `project_evaluations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: project_members
DROP TABLE IF EXISTS `project_members`;
CREATE TABLE IF NOT EXISTS `project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `role_in_project` enum('leader','member','secretary') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member` (`project_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: project_repository
DROP TABLE IF EXISTS `project_repository`;
CREATE TABLE IF NOT EXISTS `project_repository` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `keywords` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `poster_path_gcs` varchar(500) DEFAULT NULL,
  `report_path_gcs` varchar(500) DEFAULT NULL,
  `video_path_gcs` varchar(500) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`),
  FULLTEXT KEY `idx_search` (`keywords`,`summary`),
  CONSTRAINT `project_repository_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: project_subjects
DROP TABLE IF EXISTS `project_subjects`;
CREATE TABLE IF NOT EXISTS `project_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `class_year` tinyint(4) NOT NULL,
  `semester` tinyint(4) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_year` (`academic_year`,`semester`),
  CONSTRAINT `project_subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: projects
DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_code` varchar(30) NOT NULL,
  `title_th` varchar(300) NOT NULL,
  `title_en` varchar(300) DEFAULT NULL,
  `project_type` enum('experimental','survey','invention','applied','theoretical') NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `abstract_th` text DEFAULT NULL,
  `abstract_en` text DEFAULT NULL,
  `status` enum('draft','proposal_submitted','proposal_approved','proposal_revision','proposal_rejected','in_progress','chapter_review','presentation_ready','completed','archived') DEFAULT 'draft',
  `ethics_status` enum('not_required','pending','approved','rejected') DEFAULT 'not_required',
  `budget_requested` decimal(10,2) DEFAULT 0.00,
  `budget_approved` decimal(10,2) DEFAULT 0.00,
  `advisor_id` int(11) NOT NULL,
  `co_advisor_id` int(11) DEFAULT NULL,
  `external_expert` varchar(200) DEFAULT NULL,
  `academic_year` varchar(9) NOT NULL,
  `semester` tinyint(4) NOT NULL DEFAULT 1,
  `class_year` tinyint(4) DEFAULT NULL,
  `class_room` varchar(10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expected_end` date DEFAULT NULL,
  `actual_end` date DEFAULT NULL,
  `overall_score` decimal(5,2) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`),
  KEY `subject_id` (`subject_id`),
  KEY `co_advisor_id` (`co_advisor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_advisor` (`advisor_id`),
  KEY `idx_year_semester` (`academic_year`,`semester`),
  KEY `idx_class` (`class_year`,`class_room`),
  KEY `idx_teacher_review` (`advisor_id`,`status`),
  FULLTEXT KEY `idx_search` (`title_th`,`title_en`,`abstract_th`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `project_subjects` (`id`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`co_advisor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: readiness_scores
DROP TABLE IF EXISTS `readiness_scores`;
CREATE TABLE IF NOT EXISTS `readiness_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `score_type` enum('overall','academic','portfolio','skill','career_fit','engagement') NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) DEFAULT 100.00,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ó©úÓ©▓Ó©óÓ©ÑÓ©░Ó╣ÇÓ©¡Ó©ÁÓ©óÓ©ö breakdown Ó╣üÓ©òÓ╣êÓ©ÑÓ©░Ó©öÓ╣ëÓ©▓Ó©Ö' CHECK (json_valid(`details`)),
  `risk_level` enum('low','medium','high','critical') DEFAULT 'low' COMMENT 'Ó©¬Ó©│Ó©½Ó©úÓ©▒Ó©Ü Early Warning',
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_type` (`student_id`,`score_type`),
  KEY `idx_student` (`student_id`),
  KEY `idx_type` (`score_type`),
  KEY `idx_risk` (`risk_level`),
  CONSTRAINT `readiness_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rewards
DROP TABLE IF EXISTS `rewards`;
CREATE TABLE IF NOT EXISTS `rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_required` int(11) DEFAULT 0,
  `category` enum('digital','academic','privilege','physical') DEFAULT 'digital',
  `image_path` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT -1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rewards_shop
DROP TABLE IF EXISTS `rewards_shop`;
CREATE TABLE IF NOT EXISTS `rewards_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `points_cost` int(11) NOT NULL DEFAULT 0,
  `type` enum('theme','badge','privilege') NOT NULL DEFAULT 'theme',
  `image_icon` varchar(100) DEFAULT NULL COMMENT 'Emoji or icon identifier',
  `stock` int(11) DEFAULT -1 COMMENT '-1 = unlimited, 0 = out of stock, >0 = remaining',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Extra config: theme CSS, badge code, privilege key' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_active_cost` (`is_active`,`points_cost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rewards Shop catalog — items students can redeem with points';

-- Table: settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: skill_categories
DROP TABLE IF EXISTS `skill_categories`;
CREATE TABLE IF NOT EXISTS `skill_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `source` varchar(100) DEFAULT 'PCCKS',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: skills_master
DROP TABLE IF EXISTS `skills_master`;
CREATE TABLE IF NOT EXISTS `skills_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `skill_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `skills_master_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `skill_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_assessments
DROP TABLE IF EXISTS `student_assessments`;
CREATE TABLE IF NOT EXISTS `student_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `assessment_type` varchar(50) DEFAULT 'riasec',
  `dominant_trait` varchar(10) DEFAULT NULL,
  `result_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `student_assessments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_goals
DROP TABLE IF EXISTS `student_goals`;
CREATE TABLE IF NOT EXISTS `student_goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `goal_type` enum('university','major','career','shortterm','longterm') NOT NULL,
  `target` varchar(300) NOT NULL,
  `target_details` text DEFAULT NULL,
  `target_score` decimal(5,2) DEFAULT NULL,
  `current_score` decimal(5,2) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('planning','inprogress','achieved','cancelled') DEFAULT 'planning',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `student_goals_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_interests
DROP TABLE IF EXISTS `student_interests`;
CREATE TABLE IF NOT EXISTS `student_interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `type` enum('interest','talent','hobby') NOT NULL,
  `item` varchar(200) NOT NULL,
  `proficiency` tinyint(4) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  CONSTRAINT `student_interests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_profiles
DROP TABLE IF EXISTS `student_profiles`;
CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `student_code` varchar(20) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `class_year` tinyint(4) NOT NULL DEFAULT 1,
  `class_room` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_code` (`student_code`),
  KEY `idx_class` (`class_year`,`class_room`),
  CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tcas_predictions
DROP TABLE IF EXISTS `tcas_predictions`;
CREATE TABLE IF NOT EXISTS `tcas_predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `prediction_year` varchar(9) NOT NULL,
  `overall_score` decimal(5,2) DEFAULT NULL COMMENT 'Ó©äÓ©░Ó╣üÓ©ÖÓ©ÖÓ©äÓ©ºÓ©▓Ó©íÓ©×Ó©úÓ╣ëÓ©¡Ó©íÓ©úÓ©ºÓ©í 0-100',
  `component_scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ó©äÓ©░Ó╣üÓ©ÖÓ©ÖÓ╣üÓ©òÓ╣êÓ©ÑÓ©░Ó©¡Ó©çÓ©äÓ╣îÓ©øÓ©úÓ©░Ó©üÓ©¡Ó©Ü (GPA, Portfolio, Awards, etc.)' CHECK (json_valid(`component_scores`)),
  `predicted_universities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ó©íÓ©½Ó©▓Ó©ºÓ©┤Ó©ùÓ©óÓ©▓Ó©ÑÓ©▒Ó©óÓ©ùÓ©ÁÓ╣êÓ©äÓ©▓Ó©öÓ©üÓ©▓Ó©úÓ©ôÓ╣îÓ©ºÓ╣êÓ©▓Ó©òÓ©┤Ó©ö' CHECK (json_valid(`predicted_universities`)),
  `gaps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ó©èÓ╣êÓ©¡Ó©çÓ©ºÓ╣êÓ©▓Ó©çÓ©ùÓ©ÁÓ╣êÓ©òÓ╣ëÓ©¡Ó©çÓ╣ÇÓ©òÓ©┤Ó©í' CHECK (json_valid(`gaps`)),
  `recommendations` text DEFAULT NULL COMMENT 'Ó©äÓ©│Ó╣üÓ©ÖÓ©░Ó©ÖÓ©│Ó╣ÇÓ©×Ó©ÀÓ╣êÓ©¡Ó╣ÇÓ©×Ó©┤Ó╣êÓ©íÓ©äÓ©░Ó╣üÓ©ÖÓ©Ö',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_year` (`prediction_year`),
  CONSTRAINT `tcas_predictions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: teacher_comments
DROP TABLE IF EXISTS `teacher_comments`;
CREATE TABLE IF NOT EXISTS `teacher_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `portfolio_id` (`portfolio_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `teacher_comments_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_comments_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_badges
DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE IF NOT EXISTS `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `badge_code` varchar(50) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_badge` (`user_id`,`badge_code`),
  KEY `badge_code` (`badge_code`),
  KEY `idx_user_earned` (`user_id`,`earned_at`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_code`) REFERENCES `badges_master` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_reward_redemptions
DROP TABLE IF EXISTS `user_reward_redemptions`;
CREATE TABLE IF NOT EXISTS `user_reward_redemptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_spent` int(11) NOT NULL,
  `status` enum('pending','granted','revoked','expired') NOT NULL DEFAULT 'pending',
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_id` (`reward_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_redeemed_at` (`redeemed_at`),
  CONSTRAINT `user_reward_redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_reward_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards_shop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks all user redemptions from the Rewards Shop';

-- Table: user_rewards
DROP TABLE IF EXISTS `user_rewards`;
CREATE TABLE IF NOT EXISTS `user_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reward_id` (`reward_id`),
  CONSTRAINT `user_rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_rewards_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `role` enum('student','teacher','admin','parent','executive') NOT NULL DEFAULT 'student',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `exp` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 1,
  `points` int(11) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Data for `settings` (6 rows)
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('1', 'academic_year', '2569', 'ปีการศึกษาปัจจุบัน', '2026-03-15 16:27:14');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('2', 'semester', '1', 'เทอมปัจจุบัน', '2026-03-15 16:27:14');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('3', 'school_name', 'โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์', 'ชื่อโรงเรียน', '2026-03-15 16:27:14');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('4', 'allow_sharing', '1', 'อนุญาตให้นักเรียนแชร์แฟ้มสะสมผลงาน (0=ปิด, 1=เปิด)', '2026-03-15 16:27:14');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('5', 'gemini_api_key', '', 'API Key สำหรับเชื่อมต่อระบบ AI (Google Gemini)', '2026-10-17 14:21:22');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES ('6', 'gemini_model', 'openai/gpt-oss-120b', 'โมเดล AI ที่ใช้ในการวิเคราะห์ข้อมูล (เช่น gemini-2.0-flash)', '2026-10-17 14:24:36');

-- Initial Data for `portfolio_categories` (11 rows)
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('1', 'โครงงานวิทยาศาสตร์', 'science-project', '🔬', '#8b5cf6', '1', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('2', 'คณิตศาสตร์', 'mathematics', '📐', '#3b82f6', '2', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('3', 'เทคโนโลยี & คอมพิวเตอร์', 'technology', '💻', '#06b6d4', '3', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('4', 'ภาษา & วรรณกรรม', 'language', '📚', '#10b981', '4', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('5', 'ศิลปะ & ดนตรี', 'art-music', '🎨', '#f59e0b', '5', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('6', 'กีฬา', 'sports', '⚽', '#ef4444', '6', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('7', 'จิตอาสา & บำเพ็ญประโยชน์', 'volunteer', '💚', '#22c55e', '7', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('8', 'ผู้นำ & กิจกรรมนักเรียน', 'leadership', '👑', '#a855f7', '8', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('9', 'ทุนการศึกษา & รางวัล', 'scholarship-award', '🏆', '#eab308', '10', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('10', 'วิจัย & สิ่งประดิษฐ์', 'research', '🧪', '#6366f1', '11', '1');
INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `icon`, `color`, `sort_order`, `is_active`) VALUES ('11', 'อื่นๆ', 'others', '📋', '#64748b', '99', '1');

-- Initial Data for `badges_master` (17 rows)
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('1', 'first_portfolio', 'Ó©£Ó©ÑÓ©çÓ©▓Ó©ÖÓ╣üÓ©úÓ©ü', 'Ó╣ÇÓ©×Ó©┤Ó╣êÓ©íÓ©£Ó©ÑÓ©çÓ©▓Ó©ÖÓ╣üÓ©úÓ©üÓ╣ÇÓ©éÓ╣ëÓ©▓Ó©¬Ó©╣Ó╣êÓ©úÓ©░Ó©ÜÓ©Ü', '­ƒîƒ', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":1}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('2', 'rising_star', 'Ó©öÓ©▓Ó©ºÓ©úÓ©©Ó╣êÓ©ç', 'Ó©íÓ©ÁÓ©£Ó©ÑÓ©çÓ©▓Ó©Ö 5 Ó©úÓ©▓Ó©óÓ©üÓ©▓Ó©ú', 'Ô¡É', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":5}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('3', 'all_rounder', 'Ó©úÓ©¡Ó©ÜÓ©úÓ©╣Ó╣ë', 'Ó©íÓ©ÁÓ©£Ó©ÑÓ©çÓ©▓Ó©ÖÓ©äÓ©úÓ©ÜÓ©ùÓ©©Ó©üÓ©½Ó©íÓ©ºÓ©öÓ©½Ó©íÓ©╣Ó╣ê', '­ƒÅà', '{\"type\":\"categories\",\"threshold\":10}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('4', 'tech_master', 'Ó©ÖÓ©▒Ó©üÓ╣ÇÓ©ùÓ©äÓ╣éÓ©ÖÓ╣éÓ©ÑÓ©óÓ©Á', 'Ó©íÓ©ÁÓ©£Ó©ÑÓ©çÓ©▓Ó©ÖÓ╣ÇÓ©ùÓ©äÓ╣éÓ©ÖÓ╣éÓ©ÑÓ©óÓ©Á 10+', '­ƒÆ╗', '{\"type\":\"count\",\"field\":\"category\",\"value\":\"technology\",\"threshold\":10}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('5', 'science_champ', 'Ó©ÖÓ©▒Ó©üÓ©ºÓ©┤Ó©ùÓ©óÓ╣î', 'Ó©íÓ©ÁÓ©£Ó©ÑÓ©çÓ©▓Ó©ÖÓ©ºÓ©┤Ó©ùÓ©óÓ©▓Ó©¿Ó©▓Ó©¬Ó©òÓ©úÓ╣îÓ©úÓ©░Ó©öÓ©▒Ó©ÜÓ©èÓ©▓Ó©òÓ©┤', '­ƒö¼', '{\"type\":\"achievement\",\"level\":\"national\",\"category\":\"science\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('6', 'team_player', 'Ó©ÖÓ©▒Ó©üÓ©ùÓ©│Ó©çÓ©▓Ó©ÖÓ©úÓ╣êÓ©ºÓ©íÓ©üÓ©▒Ó©Ö', 'Ó╣ÇÓ©éÓ╣ëÓ©▓Ó©úÓ╣êÓ©ºÓ©í 3 Ó╣éÓ©äÓ©úÓ©çÓ©çÓ©▓Ó©Ö', '­ƒñØ', '{\"type\":\"count\",\"field\":\"collaborations\",\"threshold\":3}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('7', 'consistent', 'Ó©òÓ╣êÓ©¡Ó╣ÇÓ©ÖÓ©ÀÓ╣êÓ©¡Ó©ç', 'Ó©¡Ó©▒Ó©øÓ╣ÇÓ©öÓ©ò portfolio Ó©ùÓ©©Ó©üÓ╣ÇÓ©öÓ©ÀÓ©¡Ó©Ö 3 Ó╣ÇÓ©öÓ©ÀÓ©¡Ó©Ö', '­ƒôê', '{\"type\":\"streak\",\"months\":3}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('8', 'mentor', 'Ó©×Ó©ÁÓ╣êÓ╣ÇÓ©ÑÓ©ÁÓ╣ëÓ©óÓ©ç', 'Ó╣ÇÓ©øÓ╣çÓ©ÖÓ©ùÓ©ÁÓ╣êÓ©øÓ©úÓ©ÂÓ©üÓ©®Ó©▓ 5 Ó╣éÓ©äÓ©úÓ©çÓ©çÓ©▓Ó©Ö', '­ƒºæÔÇì­ƒÅ½', '{\"type\":\"count\",\"field\":\"advisor\",\"threshold\":5}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('9', 'bronze_explorer', 'นักสำรวจ', 'ทำแบบประเมิน RIASEC สำเร็จ — รู้จักตัวเองคือจุดเริ่มต้นของความสำเร็จ', '🧭', '{\"tier\":\"bronze\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('10', 'bronze_first_step', 'ก้าวแรก', 'เพิ่มผลงานแรกเข้าสู่ระบบ — ทุกการเดินทางเริ่มจากก้าวแรก', '🌟', '{\"tier\":\"bronze\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('11', 'bronze_goal_setter', 'นักตั้งเป้าหมาย', 'ตั้งเป้าหมายการศึกษาอย่างน้อย 1 รายการ', '🎯', '{\"tier\":\"bronze\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('12', 'silver_rising_star', 'ดาวรุ่ง', 'มีผลงาน 5 รายการ — แสงสว่างที่กำลังเจิดจ้า', '⭐', '{\"tier\":\"silver\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('13', 'silver_researcher', 'นักวิจัย', 'มีผลงานวิจัย/สิ่งประดิษฐ์ อย่างน้อย 1 ชิ้น', '🔬', '{\"tier\":\"silver\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('14', 'gold_all_rounder', 'รอบรู้', 'มีผลงานครบ 5 หมวดหมู่ขึ้นไป — คนเดียวที่ทำทุกอย่างได้', '🏅', '{\"tier\":\"gold\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('15', 'gold_award_winner', 'ผู้พิชิตรางวัล', 'มีรางวัล/เกียรติบัตร อย่างน้อย 3 รายการ', '🏆', '{\"tier\":\"gold\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('16', 'platinum_national_hero', 'วีรบุรุษระดับชาติ', 'มีผลงานระดับชาติ (national) อย่างน้อย 1 รายการ — ชื่อเสียงระดับประเทศ', '🇹🇭', '{\"tier\":\"platinum\",\"type\":\"dynamic\"}');
INSERT INTO `badges_master` (`id`, `code`, `name`, `description`, `icon`, `criteria`) VALUES ('17', 'platinum_international', 'ระดับโลก', 'มีผลงานระดับนานาชาติ — สร้างชื่อเสียงให้โรงเรียน', '🌍', '{\"tier\":\"platinum\",\"type\":\"dynamic\"}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('first_upload', 'ผลงานแรก', 'เพิ่มผลงานแรกเข้าสู่ระบบ (Legacy Alias)', '🌟', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":1}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('five_works', 'ดาวรุ่ง', 'มีผลงาน 5 รายการ (Legacy Alias)', '⭐', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":5}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('ten_works', 'รอบรู้', 'มีผลงาน 10 รายการ (Legacy Alias)', '🏅', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":10}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('twenty_works', 'เชี่ยวชาญ', 'มีผลงาน 20 รายการ (Legacy Alias)', '🏆', '{\"type\":\"count\",\"field\":\"portfolio_items\",\"threshold\":20}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('goal_setter', 'นักตั้งเป้าหมาย', 'ตั้งเป้าหมายการศึกษา (Legacy Alias)', '🎯', '{\"type\":\"count\",\"field\":\"goals\",\"threshold\":1}');
INSERT IGNORE INTO `badges_master` (`code`, `name`, `description`, `icon`, `criteria`) VALUES ('goal_achiever', 'ผู้บรรลุเป้าหมาย', 'ทำเป้าหมายสำเร็จ (Legacy Alias)', '🥇', '{\"type\":\"count\",\"field\":\"goals_completed\",\"threshold\":1}');

-- Initial Data for `rewards` (6 rows)
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('1', 'Legendary Profile Frame', 'เปลี่ยนกรอบรูปโปรไฟล์เป็นสีทองหรูหรา แสดงความเป็นระดับแนวหน้าของโรงเรียน', '5000', 'digital', 'frame_gold.png', '-1', '1', '2026-04-07 10:45:27');
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('2', 'Portfolio Spotlight (1 Week)', 'ดันผลงานของคุณขึ้นหน้าแรกของระบบเป็นเวลา 1 สัปดาห์ เพื่อให้ทุกคนได้เห็นความสำเร็จ', '2500', 'academic', 'spotlight.png', '-1', '1', '2026-04-07 10:45:27');
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('3', 'Exclusive AI Career Insight', 'ปลดล็อกระบบ AI วิเคราะห์เส้นทางอาชีพเชิงลึกด้วยคะแนน RIASEC และ GPA ของคุณ', '1500', 'academic', 'ai_premium.png', '-1', '1', '2026-04-07 10:45:27');
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('4', 'Early Lunch Pass (Single Use)', 'คูปองไม่ต้องต่อแถวโรงอาหารสำหรับมื้อกลางวัน 1 ครั้ง (เฉพาะวันศุกร์)', '3000', 'privilege', 'lunch_pass.png', '-1', '1', '2026-04-07 10:45:27');
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('5', 'PCSHS KSN Limited Sticker', 'สติกเกอร์รุ่นพิเศษสำหรับติดโน้ตบุ๊กหรือแท็บเล็ตที่มีเฉพาะผู้ที่สะสมผลงานเท่านั้น', '1000', 'physical', 'sticker.png', '-1', '1', '2026-04-07 10:45:27');
INSERT INTO `rewards` (`id`, `title`, `description`, `points_required`, `category`, `image_path`, `stock_quantity`, `is_active`, `created_at`) VALUES ('6', 'Lab Equipment Booking Priority', 'สิทธิ์ในการจองอุปกรณ์แล็บก่อนใครเป็นเวลา 1 เดือน', '4000', 'privilege', 'lab_priority.png', '-1', '1', '2026-04-07 10:45:27');

-- Initial Data for `users` (53 rows)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('1', 'admin', 'admin@pccks.ac.th', '$2y$12$I6Gnma/f8OwItmvKEbjjZu29uQyDt7rFUOGCbHLgTR9JttgkmYa4W', NULL, NULL, 'admin', '1', '0', '1', '0', '2026-10-17 12:38:11', '2026-04-03 11:38:10', '2026-10-17 12:38:11');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('2', '64001', 'student01@pccks.ac.th', '$2y$12$4kaXAi4i2Kv6FWBcrGEEheoR9Yj5dly7ABqiWIlAt5UYB5on3b.wC', 'ทดสอบ นักเรียน', '', 'student', '1', '0', '1', '0', '2026-10-17 13:49:58', '2026-04-03 11:38:10', '2026-10-17 13:49:58');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('3', 'teacher01', 'teacher01@pccks.ac.th', '$2y$12$vSMJksvBMzHiK4HDTR40IOY42zvQSTazDcVGbU/RxDl3fQThmczdu', NULL, NULL, 'teacher', '1', '0', '1', '0', '2026-10-06 20:54:09', '2026-04-03 11:38:10', '2026-10-06 20:54:09');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('4', 'std1000', 'std1000@pccks-mock.ac.th', '$2y$10$e.opmsIQ1Vq4VVPk9q8p/eKwU.5pBJlZLv4mfex00jxuUGobfTB5u', 'สมชาย ใจดี', NULL, 'student', '1', '0', '1', '0', '2026-10-06 20:54:12', '2026-04-03 11:40:38', '2026-10-06 20:54:12');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('5', 'std1001', 'std1001@pccks-mock.ac.th', '$2y$10$4rVUnePQ0XkAC16qiMsC0uuZjHRq6oodyTr2QYdfIY8OrkflKCE3W', 'สมหญิง รักการเรียน', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:38', '2026-04-03 11:40:38');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('6', 'std1002', 'std1002@pccks-mock.ac.th', '$2y$10$FLvBKfRTE7CNlYM/WKBCbOyirrNVNtO0..SOCDmMFw/N2LE5/mheq', 'ปริญญา เรียนเก่ง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:38', '2026-04-03 11:40:38');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('7', 'std1003', 'std1003@pccks-mock.ac.th', '$2y$10$VPiczmCeCLPHUZF20842tOtpVUuWX3ZJG55/EIyUGu.ScggywZeKG', 'ณัฐพล มุ่งมั่น', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('8', 'std1004', 'std1004@pccks-mock.ac.th', '$2y$10$MvFAms2YeGKhSwg.meqBz.Pnudvbk.ZuMKrhJpeVTMgItrZQpY0Oa', 'ธนพล ปัญญาเลิศ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('9', 'std1005', 'std1005@pccks-mock.ac.th', '$2y$10$K4QziRRDZzjQFfiIjzp0l.JBcTMgOZs9S9aKCk/BjAtjZ9i6w25MK', 'วิไล วิจิตรศิลป์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('10', 'std1006', 'std1006@pccks-mock.ac.th', '$2y$10$fMqRSFB0bk1gEGo985A7rO8HNt7XlDTvcDTR.vn6KdPbte4N./H/i', 'กมลวรรณ ศรีสุวรรณ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('11', 'std1007', 'std1007@pccks-mock.ac.th', '$2y$10$3k1UveI4RZbg1r0yLw6oTust6iU5rQIkNEXH5EHWhfjJMT1y5wqu2', 'อภิสิทธิ์ รัตนโกสินทร์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('12', 'std1008', 'std1008@pccks-mock.ac.th', '$2y$10$v2RpASm2Di5aERLRhRk4muBOOX45zbwKsLKdC0NcF3qjjBdpk.gyu', 'สุรชัย ธรรมรักษา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('13', 'std1009', 'std1009@pccks-mock.ac.th', '$2y$10$khEyk.S2Q8AudOx0iibpXO8fBFXPH/Y.q7iZ.R7uUL/KBMobsQAvC', 'กิตติศักดิ์ วงษ์คำดี', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('14', 'std1010', 'std1010@pccks-mock.ac.th', '$2y$10$Gm7GC.gs6eGaDFs9PohwRuDx7KeuvOZHKz41LciggX0VlbdhgkW.e', 'นงนุช ศิริเวช', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('15', 'std1011', 'std1011@pccks-mock.ac.th', '$2y$10$lS35PrY7N6fTP.uMNbJmSubONOoezcYAe508siU7KN8bHJnSeD6XK', 'พรวิมล ประดิษฐ์งาน', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('16', 'std1012', 'std1012@pccks-mock.ac.th', '$2y$10$eCwYXjEzFN0OyUlXIUrBy.e8cvdgiMBFq4lMyeMrcGKXAZ/3yFuK6', 'จีรนันท์ วิชาการ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('17', 'std1013', 'std1013@pccks-mock.ac.th', '$2y$10$p95b0.z0nyZFYkhdPzxHsOSgD2eWkLbDts4GnPWYV3Rd.PPOT5z0G', 'พงศธร มีปัญญา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('18', 'std1014', 'std1014@pccks-mock.ac.th', '$2y$10$xETn0abGfgudpOYaMd5LJO9W02kDQBn28tKSpRY6MDHPiodVNJTv.', 'วรวุฒิ ใจซื่อ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('19', 'std1015', 'std1015@pccks-mock.ac.th', '$2y$10$PYnkNA5KBD5vJewZwh9YreYlPEwFwXY9EvNudepInhz0YYlSsucgy', 'ศิริพร ยอดเยี่ยม', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('20', 'std1016', 'std1016@pccks-mock.ac.th', '$2y$10$3cd8fVM1Dnj0Q7CajJFMGOPnllKZ4gXCSFsJU/jEjgqFOSqAQQcfW', 'ปิยะมาศ ก้าวหน้า', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('21', 'std1017', 'std1017@pccks-mock.ac.th', '$2y$10$36Wb4J51wiv8idSkrwSCK.1S7I0nsMbyUn55yKkALrwaPc9xEZfBy', 'ธนาวุฒิ รุ่งเรือง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('22', 'std1018', 'std1018@pccks-mock.ac.th', '$2y$10$J7gYvhmzttPOwJmXLlBxMugb4nSyQRE7NyW73lX/.Z0jSmgxSnaje', 'กิตติ เปี่ยมสุข', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('23', 'std1019', 'std1019@pccks-mock.ac.th', '$2y$10$/84TGiXbfFdJxepXc.Blce4oIKMk5F.EI2at6YsCCBNpysZvGVUMa', 'สิรินธร วัฒนศักดิ์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('24', 'std1020', 'std1020@pccks-mock.ac.th', '$2y$10$vA9QVzuJKPEdJRyc/h27pOp.oBdzsbZvBlH16owhxmAAYo0II9S42', 'อนุพงษ์ พงศ์ประยูร', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('25', 'std1021', 'std1021@pccks-mock.ac.th', '$2y$10$ZSqFGpSUFH0o9UURGhB4TeiNokCxoZvO3CEQpU.WsEPVpDbUBp01a', 'ภัทรพล อนันตศิลป์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('26', 'std1022', 'std1022@pccks-mock.ac.th', '$2y$10$6PUOmdt19eQiC3LgP7WSoeGaPxJVBMHVGoGu0.pSP4Jy5wkgd1Y7i', 'ชนิดา ชูชาติ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('27', 'std1023', 'std1023@pccks-mock.ac.th', '$2y$10$0v9aujswD1YCJ7gKbz14rOkw6rhWQs3w46n.Xx.4rNKtQxbv7dUlu', 'ธัญญารัตน์ ประเสริฐสุข', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('28', 'std1024', 'std1024@pccks-mock.ac.th', '$2y$10$Rii9VVG3HZg8/3.5xV2.1e/6h4YenjimnhYyKH6YtoVAC13yO582q', 'รุ่งอรุณ จิตบริสุทธิ์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('29', 'std1025', 'std1025@pccks-mock.ac.th', '$2y$10$UDnPFQOYrxyuRDXYvLZyvumHg8sOREaxbJbdxO.FOA3p5p0OA/faG', 'เกศินี ภักดี', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:45', '2026-04-03 11:40:45');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('30', 'std1026', 'std1026@pccks-mock.ac.th', '$2y$10$1fDQMSn4ox4WREzBoYFuauju1qq8YtcOgNatrWSuijB.TPeQtgtpa', 'โสภณ มั่นคง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:45', '2026-04-03 11:40:45');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('31', 'std1027', 'std1027@pccks-mock.ac.th', '$2y$10$Selb2IeFhLGLdAPDNYjspurX9jEBgAESnRmkeUEg2gfwdskodYpLW', 'มาร์ค กล้าหาญ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('32', 'std1028', 'std1028@pccks-mock.ac.th', '$2y$10$JT2ePrOa.0c6jfDMpCDM8OwG2QoOqkOrWfwqMjRKlVsOhpAqflqoa', 'เจมส์ สุโขทัย', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('33', 'std1029', 'std1029@pccks-mock.ac.th', '$2y$10$Cah0F9fsJgUlFRD979KjxuG6jW.hBFJU4dW2MwLPgWRkBJYM6c.FK', 'แอน อยุธยา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('34', 'std1030', 'std1030@pccks-mock.ac.th', '$2y$10$MAwujetItFoi6bummG0f5ed1OaLxsCZ/WYUe9Odb1R9Q0tRss4sTW', 'บดินทร์ เชียงใหม่', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('35', 'std1031', 'std1031@pccks-mock.ac.th', '$2y$10$9cQmFoKbnJ/xkw8w.wYxCewjYG9fTSLRIIYv9akjRBfSkSr9lnnq.', 'ดวงกมล ขอนแก่น', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('36', 'std1032', 'std1032@pccks-mock.ac.th', '$2y$10$9wxklgZZvMdhQszVskFhhObJbn4ow813sEiFlwr0xLtr5Eo5Gw0/G', 'จารุวรรณ รักถิ่น', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('37', 'std1033', 'std1033@pccks-mock.ac.th', '$2y$10$pZYAc3dKiSlvjHodnCJJt.CPLBku1gJZ5x7XmcS.PSPITqfgicnsC', 'นิรันดร์ พึ่งบุญ', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('38', 'std1034', 'std1034@pccks-mock.ac.th', '$2y$10$xDwcdkYPy5sWMq4hQ/ukO.9ylII8NnAXagbtXR8WgHkjxWCRjZ4TS', 'พิมพิลาศ มั่งคั่ง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('39', 'std1035', 'std1035@pccks-mock.ac.th', '$2y$10$L/4J1zYb5c/ZIQ./qCLHB.xSZix9JuVaxTFwDk515/w2U6LHIaP8y', 'ไพศาล ร่ำรวย', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('40', 'std1036', 'std1036@pccks-mock.ac.th', '$2y$10$inO3m/zucs1HCts2capyDubVn0upHzHEhQ.tkMzKBdndOXNY7SPme', 'รัตนา มหากุล', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('41', 'std1037', 'std1037@pccks-mock.ac.th', '$2y$10$nuuwTbg5hox3KI1jupJ/DOI5lA0gGI2PwV0Gu4gnEDgLYOI0urWRO', 'ยุพิน ยอดดี', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('42', 'std1038', 'std1038@pccks-mock.ac.th', '$2y$10$pizGvGLK9bhkT6qVh8FB5e3/mmiejcNhUqyhFwM4B/WgksCTqItxi', 'ชินกร ขยันยิ่ง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('43', 'std1039', 'std1039@pccks-mock.ac.th', '$2y$10$f0ygrUTrE1gLYYDILGdB8O.JzcL2.vXjcj.ladBw4fcaaaYpO3tau', 'นพดล ใจเย็น', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('44', 'std1040', 'std1040@pccks-mock.ac.th', '$2y$10$qEq4Ne2162aPcTUxz3Y5guQS.e/llYLyq.O3wa9ryrKUkCsWY/vPe', 'รุ่งนภา ใจกว้าง', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('45', 'std1041', 'std1041@pccks-mock.ac.th', '$2y$12$jv5du9ucfoq2TRpQJLiDi.d8rvfLcaPR12RNOmWnAxiIhqwsM561W', 'บุญส่ง รักษาดี', '', 'student', '1', '0', '1', '0', '2026-04-19 16:57:47', '2026-04-03 11:40:50', '2026-04-19 16:57:47');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('46', 'std1042', 'std1042@pccks-mock.ac.th', '$2y$10$VYb1GZxK0InHJRDqsmytbuM0oSiMP6WqhixaDWhzaQQiz.qPGnhe6', 'มาลี คงกระพัน', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('47', 'std1043', 'std1043@pccks-mock.ac.th', '$2y$10$2wRX/XANkU5yBaGCjG.2iuY2EivkWgLuANa0FvVnFHojotKgl6IG2', 'สมพงษ์ บุญรักษา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('48', 'std1044', 'std1044@pccks-mock.ac.th', '$2y$10$9oM/eLm69pFpqeYlDlEJXO21JgJ89.yFnLj.54L6nVjA0e6AfgY3W', 'อาทิตย์ เทวา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('49', 'std1045', 'std1045@pccks-mock.ac.th', '$2y$10$qiNIGfg2N7Pnd2tj305HUOcl2V6as5hEDZuTuJrdTMkYfUCGcKgHm', 'นภา นาคา', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('50', 'std1046', 'std1046@pccks-mock.ac.th', '$2y$10$jyRgaVD3gbW0H4vxjyoRW.1QWt1en7.r2Kb01yMWRku.6x375wVne', 'ขวัญใจ พิทักษ์', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('51', 'std1047', 'std1047@pccks-mock.ac.th', '$2y$10$42dvAR8Xfu2JmgN5gjWuL.xPyNxyICmv8i41goghmQ7u8wLUGHqS.', 'ประภาส สมนาม', NULL, 'student', '1', '0', '1', '0', NULL, '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('52', 'vd002', '', '$2y$12$kP9HJjrGFTx6ajuuovyyEuC4W4fJpQ9WbkL.DbDCV44Ar5MbRDIFC', 'ทดสอบ รองผู้อำนวยการ', NULL, 'executive', '1', '0', '1', '0', '2026-04-10 18:05:27', '2026-04-03 11:42:41', '2026-04-10 18:05:27');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `position`, `role`, `is_active`, `exp`, `level`, `points`, `last_login`, `created_at`, `updated_at`) VALUES ('53', 'natty851', 'nattapol.sa@pcshsksn.ac.th', '$2y$12$4OjwedAgdJBfwNfaJq1hr.gM3XjG47QUj6qp81s8ZEq2bgRXlpd.G', 'Nattapol Saisarlee', '', 'teacher', '1', '0', '1', '0', '2026-10-06 21:52:41', '2026-04-03 11:44:45', '2026-10-06 21:52:41');

-- Initial Data for `student_profiles` (49 rows)
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('1', '2', '64001', 'ทดสอบ นักเรียน', 'เพชร', '5', '1', NULL, '', 'avatars/file_69d8d0fdcdf167.40039802.png', 'นักเรียนสาย STEM ชอบเขียนโปรแกรมและทำโครงงานวิทยาศาสตร์', '2026-04-03 11:38:10', '2026-04-17 06:49:28');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('2', '4', 'M64001', 'สมชาย ใจดี', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:38', '2026-04-03 11:40:38');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('3', '5', 'M64002', 'สมหญิง รักการเรียน', NULL, '6', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:38', '2026-04-03 11:40:38');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('4', '6', 'M64003', 'ปริญญา เรียนเก่ง', NULL, '6', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:38', '2026-04-03 11:40:38');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('5', '7', 'M64004', 'ณัฐพล มุ่งมั่น', NULL, '4', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('6', '8', 'M64005', 'ธนพล ปัญญาเลิศ', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('7', '9', 'M64006', 'วิไล วิจิตรศิลป์', NULL, '5', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('8', '10', 'M64007', 'กมลวรรณ ศรีสุวรรณ', NULL, '6', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสถาปัตยกรรมศาสตร์', '2026-04-03 11:40:39', '2026-04-03 11:40:39');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('9', '11', 'M64008', 'อภิสิทธิ์ รัตนโกสินทร์', NULL, '4', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('10', '12', 'M64009', 'สุรชัย ธรรมรักษา', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('11', '13', 'M64010', 'กิตติศักดิ์ วงษ์คำดี', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิศวกรรมศาสตร์', '2026-04-03 11:40:40', '2026-04-03 11:40:40');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('12', '14', 'M64011', 'นงนุช ศิริเวช', NULL, '4', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('13', '15', 'M64012', 'พรวิมล ประดิษฐ์งาน', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะดนตรีและการแสดง', '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('14', '16', 'M64013', 'จีรนันท์ วิชาการ', NULL, '6', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('15', '17', 'M64014', 'พงศธร มีปัญญา', NULL, '5', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('16', '18', 'M64015', 'วรวุฒิ ใจซื่อ', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิศวกรรมศาสตร์', '2026-04-03 11:40:41', '2026-04-03 11:40:41');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('17', '19', 'M64016', 'ศิริพร ยอดเยี่ยม', NULL, '5', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิศวกรรมศาสตร์', '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('18', '20', 'M64017', 'ปิยะมาศ ก้าวหน้า', NULL, '5', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('19', '21', 'M64018', 'ธนาวุฒิ รุ่งเรือง', NULL, '4', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:42', '2026-04-03 11:40:42');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('20', '22', 'M64019', 'กิตติ เปี่ยมสุข', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('21', '23', 'M64020', 'สิรินธร วัฒนศักดิ์', NULL, '4', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('22', '24', 'M64021', 'อนุพงษ์ พงศ์ประยูร', NULL, '5', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิทยาศาสตร์', '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('23', '25', 'M64022', 'ภัทรพล อนันตศิลป์', NULL, '5', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:43', '2026-04-03 11:40:43');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('24', '26', 'M64023', 'ชนิดา ชูชาติ', NULL, '6', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('25', '27', 'M64024', 'ธัญญารัตน์ ประเสริฐสุข', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสถาปัตยกรรมศาสตร์', '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('26', '28', 'M64025', 'รุ่งอรุณ จิตบริสุทธิ์', NULL, '6', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิทยาศาสตร์', '2026-04-03 11:40:44', '2026-04-03 11:40:44');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('27', '29', 'M64026', 'เกศินี ภักดี', NULL, '5', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:45', '2026-04-03 11:40:45');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('28', '30', 'M64027', 'โสภณ มั่นคง', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:45', '2026-04-03 11:40:45');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('29', '31', 'M64028', 'มาร์ค กล้าหาญ', NULL, '5', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('30', '32', 'M64029', 'เจมส์ สุโขทัย', NULL, '5', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('31', '33', 'M64030', 'แอน อยุธยา', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:46', '2026-04-03 11:40:46');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('32', '34', 'M64031', 'บดินทร์ เชียงใหม่', NULL, '5', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('33', '35', 'M64032', 'ดวงกมล ขอนแก่น', NULL, '5', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะดนตรีและการแสดง', '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('34', '36', 'M64033', 'จารุวรรณ รักถิ่น', NULL, '6', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:47', '2026-04-03 11:40:47');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('35', '37', 'M64034', 'นิรันดร์ พึ่งบุญ', NULL, '4', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิทยาศาสตร์', '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('36', '38', 'M64035', 'พิมพิลาศ มั่งคั่ง', NULL, '4', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('37', '39', 'M64036', 'ไพศาล ร่ำรวย', NULL, '6', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะสาธารณสุขศาสตร์', '2026-04-03 11:40:48', '2026-04-03 11:40:48');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('38', '40', 'M64037', 'รัตนา มหากุล', NULL, '5', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('39', '41', 'M64038', 'ยุพิน ยอดดี', NULL, '6', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('40', '42', 'M64039', 'ชินกร ขยันยิ่ง', NULL, '6', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิศวกรรมศาสตร์', '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('41', '43', 'M64040', 'นพดล ใจเย็น', NULL, '4', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะนิติศาสตร์', '2026-04-03 11:40:49', '2026-04-03 11:40:49');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('42', '44', 'M64041', 'รุ่งนภา ใจกว้าง', NULL, '5', '4', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะมนุษยศาสตร์', '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('43', '45', 'std1041', 'บุญส่ง รักษาดี', NULL, '6', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:50', '2026-04-03 11:42:13');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('44', '46', 'M64043', 'มาลี คงกระพัน', NULL, '6', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะดนตรีและการแสดง', '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('45', '47', 'M64044', 'สมพงษ์ บุญรักษา', NULL, '6', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน วิทยาลัยการคอมพิวเตอร์', '2026-04-03 11:40:50', '2026-04-03 11:40:50');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('46', '48', 'M64045', 'อาทิตย์ เทวา', NULL, '5', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('47', '49', 'M64046', 'นภา นาคา', NULL, '4', '2', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะวิทยาศาสตร์', '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('48', '50', 'M64047', 'ขวัญใจ พิทักษ์', NULL, '5', '3', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:51', '2026-04-03 11:40:51');
INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `full_name`, `nickname`, `class_year`, `class_room`, `date_of_birth`, `phone`, `avatar_path`, `bio`, `created_at`, `updated_at`) VALUES ('49', '51', 'M64048', 'ประภาส สมนาม', NULL, '5', '1', NULL, NULL, NULL, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน คณะแพทยศาสตร์', '2026-04-03 11:40:51', '2026-04-03 11:40:51');

-- Initial Data for `migrations` (32 rows)
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('1', '001_core_users_and_auth.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('2', '002_student_profiles.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('3', '003_portfolio_system.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('4', '004_academics_goals_interests.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('5', '005_skills_assessments.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('6', '006_ai_recommendations.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('7', '007_security_audit.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('8', '008_gamification_alumni.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('9', '009_project_tracking.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('10', '010_analytics_readiness.sql', '1', '2026-04-07 17:27:37');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('11', '001_core_users_and_auth.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('12', '002_student_profiles.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('13', '003_portfolio_system.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('14', '004_academics_goals_interests.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('15', '005_skills_assessments.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('16', '006_ai_recommendations.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('17', '007_security_audit.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('18', '008_gamification_alumni.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('19', '009_project_tracking.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('20', '010_analytics_readiness.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('21', '011_performance_indexes.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('22', '012_analytics_views.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('23', '013_phase4_ai_insights.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('24', '014_phase5_gamification.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('25', '015_hotfix_verification_status.sql', '1', '2026-04-07 17:29:43');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('26', '016_performance_indexes_part2.sql', '2', '2026-04-09 12:33:10');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('27', '017_ai_cache_hotfix.sql', '3', '2026-04-09 12:55:45');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('28', '018_audit_logs_system.sql', '4', '2026-04-09 13:15:27');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('29', '019_rewards_shop.sql', '5', '2026-04-09 13:40:53');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('30', '020_sse_notifications.sql', '6', '2026-04-09 13:54:51');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('31', '021_ai_tcas_gap_cache.sql', '7', '2026-04-09 14:10:05');
INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES ('32', '022_api_tokens.sql', '8', '2026-04-09 14:40:32');

-- View: vw_early_warning
DROP TABLE IF EXISTS `vw_early_warning`;
DROP VIEW IF EXISTS `vw_early_warning`;
CREATE OR REPLACE VIEW `vw_early_warning` AS select `u`.`id` AS `user_id`,`sp`.`id` AS `student_id`,`u`.`username` AS `username`,`sp`.`student_code` AS `student_code`,`sp`.`full_name` AS `full_name`,`sp`.`nickname` AS `nickname`,`sp`.`class_year` AS `class_year`,`sp`.`class_room` AS `class_room`,`sp`.`avatar_path` AS `avatar_path`,coalesce(case when avg(`ar`.`grade`) is null then 0 when avg(`ar`.`grade`) >= 3.5 then 25 when avg(`ar`.`grade`) >= 3.0 then 20 when avg(`ar`.`grade`) >= 2.5 then 15 when avg(`ar`.`grade`) >= 2.0 then 8 else 3 end,0) AS `gpa_score`,coalesce(least(count(distinct `pi`.`id`) * 4,20),0) AS `portfolio_score`,coalesce(case when max(`pi`.`created_at`) > current_timestamp() - interval 30 day then 15 else 0 end,0) AS `activity_score`,coalesce(case when count(distinct `sg`.`id`) > 0 then 15 else 0 end,0) AS `goal_score`,coalesce(least(count(distinct `pm`.`project_id`) * 5,15),0) AS `project_score`,coalesce(case when count(distinct `lb`.`id`) >= 4 then 10 when count(distinct `lb`.`id`) >= 2 then 5 else 0 end,0) AS `logbook_score`,coalesce(case when avg(`ar`.`grade`) is null then 0 when avg(`ar`.`grade`) >= 3.5 then 25 when avg(`ar`.`grade`) >= 3.0 then 20 when avg(`ar`.`grade`) >= 2.5 then 15 when avg(`ar`.`grade`) >= 2.0 then 8 else 3 end,0) + coalesce(least(count(distinct `pi`.`id`) * 4,20),0) + coalesce(case when max(`pi`.`created_at`) > current_timestamp() - interval 30 day then 15 else 0 end,0) + coalesce(case when count(distinct `sg`.`id`) > 0 then 15 else 0 end,0) + coalesce(least(count(distinct `pm`.`project_id`) * 5,15),0) + coalesce(case when count(distinct `lb`.`id`) >= 4 then 10 when count(distinct `lb`.`id`) >= 2 then 5 else 0 end,0) AS `total_score`,case when coalesce(case when avg(`ar`.`grade`) >= 3.5 then 25 when avg(`ar`.`grade`) >= 3.0 then 20 when avg(`ar`.`grade`) >= 2.5 then 15 when avg(`ar`.`grade`) >= 2.0 then 8 else 3 end,0) + coalesce(least(count(distinct `pi`.`id`) * 4,20),0) + coalesce(case when max(`pi`.`created_at`) > current_timestamp() - interval 30 day then 15 else 0 end,0) + coalesce(case when count(distinct `sg`.`id`) > 0 then 15 else 0 end,0) + coalesce(least(count(distinct `pm`.`project_id`) * 5,15),0) + coalesce(case when count(distinct `lb`.`id`) >= 4 then 10 when count(distinct `lb`.`id`) >= 2 then 5 else 0 end,0) >= 70 then 'low' when coalesce(case when avg(`ar`.`grade`) >= 3.5 then 25 when avg(`ar`.`grade`) >= 3.0 then 20 when avg(`ar`.`grade`) >= 2.5 then 15 when avg(`ar`.`grade`) >= 2.0 then 8 else 3 end,0) + coalesce(least(count(distinct `pi`.`id`) * 4,20),0) + coalesce(case when max(`pi`.`created_at`) > current_timestamp() - interval 30 day then 15 else 0 end,0) + coalesce(case when count(distinct `sg`.`id`) > 0 then 15 else 0 end,0) + coalesce(least(count(distinct `pm`.`project_id`) * 5,15),0) + coalesce(case when count(distinct `lb`.`id`) >= 4 then 10 when count(distinct `lb`.`id`) >= 2 then 5 else 0 end,0) >= 40 then 'medium' when coalesce(case when avg(`ar`.`grade`) >= 3.5 then 25 when avg(`ar`.`grade`) >= 3.0 then 20 when avg(`ar`.`grade`) >= 2.5 then 15 when avg(`ar`.`grade`) >= 2.0 then 8 else 3 end,0) + coalesce(least(count(distinct `pi`.`id`) * 4,20),0) + coalesce(case when max(`pi`.`created_at`) > current_timestamp() - interval 30 day then 15 else 0 end,0) + coalesce(case when count(distinct `sg`.`id`) > 0 then 15 else 0 end,0) + coalesce(least(count(distinct `pm`.`project_id`) * 5,15),0) + coalesce(case when count(distinct `lb`.`id`) >= 4 then 10 when count(distinct `lb`.`id`) >= 2 then 5 else 0 end,0) >= 20 then 'high' else 'critical' end AS `risk_level` from ((((((`users` `u` join `student_profiles` `sp` on(`u`.`id` = `sp`.`user_id`)) left join `academic_records` `ar` on(`sp`.`id` = `ar`.`student_id`)) left join `portfolio_items` `pi` on(`sp`.`id` = `pi`.`student_id` and `pi`.`status` = 'published')) left join `student_goals` `sg` on(`sp`.`id` = `sg`.`student_id`)) left join `project_members` `pm` on(`u`.`id` = `pm`.`student_id`)) left join `logbooks` `lb` on(`u`.`id` = `lb`.`student_id`)) where `u`.`role` = 'student' group by `u`.`id`,`sp`.`id`;

-- View: vw_school_analytics
DROP TABLE IF EXISTS `vw_school_analytics`;
DROP VIEW IF EXISTS `vw_school_analytics`;
CREATE OR REPLACE VIEW `vw_school_analytics` AS select (select count(0) from `users` where `users`.`role` = 'student' and `users`.`is_active` = 1) AS `total_students`,(select count(0) from `users` where `users`.`role` = 'teacher' and `users`.`is_active` = 1) AS `total_teachers`,(select count(0) from `portfolio_items` where `portfolio_items`.`status` = 'published') AS `total_portfolios`,(select count(distinct `portfolio_items`.`student_id`) from `portfolio_items` where `portfolio_items`.`status` = 'published' and `portfolio_items`.`created_at` >= current_timestamp() - interval 30 day) AS `active_students_30d`,(select count(0) from `projects` where `projects`.`status` in ('draft','proposal_submitted','proposal_approved','in_progress','chapter_review','presentation_ready')) AS `active_projects`,(select count(0) from `projects` where `projects`.`status` = 'completed') AS `completed_projects`,(select round(avg(`academic_records`.`grade`),2) from `academic_records`) AS `avg_gpa`,(select count(0) from `logbooks` where `logbooks`.`created_at` >= current_timestamp() - interval 30 day) AS `logbooks_30d`,(select count(0) from `vw_early_warning` where `vw_early_warning`.`risk_level` = 'low') AS `risk_low_count`,(select count(0) from `vw_early_warning` where `vw_early_warning`.`risk_level` = 'medium') AS `risk_medium_count`,(select count(0) from `vw_early_warning` where `vw_early_warning`.`risk_level` = 'high') AS `risk_high_count`,(select count(0) from `vw_early_warning` where `vw_early_warning`.`risk_level` = 'critical') AS `risk_critical_count`;

-- View: vw_student_360
DROP TABLE IF EXISTS `vw_student_360`;
DROP VIEW IF EXISTS `vw_student_360`;
CREATE OR REPLACE VIEW `vw_student_360` AS select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`full_name` AS `user_full_name`,`u`.`role` AS `role`,`u`.`last_login` AS `last_login`,`sp`.`id` AS `profile_id`,`sp`.`student_code` AS `student_code`,`sp`.`full_name` AS `full_name`,`sp`.`nickname` AS `nickname`,`sp`.`class_year` AS `class_year`,`sp`.`class_room` AS `class_room`,`sp`.`avatar_path` AS `avatar_path`,`sp`.`bio` AS `bio`,`sp`.`date_of_birth` AS `date_of_birth`,`sp`.`phone` AS `phone`,count(distinct `ar`.`id`) AS `total_subjects`,round(avg(`ar`.`grade`),2) AS `gpa`,max(`ar`.`academic_year`) AS `latest_year`,count(distinct `pi`.`id`) AS `portfolio_count`,count(distinct case when `pi`.`is_featured` = 1 then `pi`.`id` end) AS `featured_count`,count(distinct case when `pi`.`achievement_level` in ('national','international') then `pi`.`id` end) AS `high_achievement_count`,max(`pi`.`created_at`) AS `last_portfolio_date`,count(distinct `pm`.`project_id`) AS `project_count`,count(distinct case when `pm`.`role_in_project` = 'leader' then `pm`.`project_id` end) AS `leader_count`,count(distinct `sg`.`id`) AS `goal_count`,count(distinct case when `sg`.`status` = 'achieved' then `sg`.`id` end) AS `achieved_goals`,`sa`.`dominant_trait` AS `riasec_type`,count(distinct `ub`.`badge_code`) AS `badge_count` from (((((((`users` `u` join `student_profiles` `sp` on(`u`.`id` = `sp`.`user_id`)) left join `academic_records` `ar` on(`sp`.`id` = `ar`.`student_id`)) left join `portfolio_items` `pi` on(`sp`.`id` = `pi`.`student_id` and `pi`.`status` = 'published')) left join `project_members` `pm` on(`u`.`id` = `pm`.`student_id`)) left join `student_goals` `sg` on(`sp`.`id` = `sg`.`student_id`)) left join `student_assessments` `sa` on(`u`.`id` = `sa`.`user_id`)) left join `user_badges` `ub` on(`u`.`id` = `ub`.`user_id`)) where `u`.`role` = 'student' group by `u`.`id`,`sp`.`id`;

SET FOREIGN_KEY_CHECKS = 1;
