-- ================================================
-- Migration 021: AI TCAS Gap Analysis Cache
-- ================================================
-- ตารางเก็บผลลัพธ์การวิเคราะห์ช่องว่าง TCAS จาก Gemini AI
-- มีอายุ 30 วันแล้ว expire เพื่อให้ AI วิเคราะห์ใหม่
-- ================================================

CREATE TABLE IF NOT EXISTS `ai_tcas_gap_analysis` (
    `id`                INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`        INT NOT NULL,
    `target_faculty`    VARCHAR(100) NOT NULL COMMENT 'e.g. medicine, engineering, computer_science',
    `ai_model`          VARCHAR(100) NOT NULL DEFAULT 'gemini-2.0-flash',
    `analysis_json`     JSON NOT NULL COMMENT 'Full Gemini JSON response parsed',
    `overall_readiness` DECIMAL(5,2) DEFAULT 0 COMMENT 'Overall readiness percentage 0-100',
    `is_fallback`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = used rule-based fallback, 0 = Gemini AI',
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at`        TIMESTAMP NULL COMMENT '30 days from created_at',

    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `uk_student_faculty_model` (`student_id`, `target_faculty`, `ai_model`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cache for AI-generated TCAS Gap Analysis results (30-day TTL)';
