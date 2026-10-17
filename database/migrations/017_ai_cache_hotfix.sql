-- ============================================================
-- Phase 9: AI Field Analysis Cache — Hotfix Schema
-- ============================================================
-- Purpose: Centralized cache table for AI field analysis results
--          Eliminates redundant per-student AI computation queries
--          Supports eager loading of readiness scores across dashboards
-- ============================================================
-- Run: SOURCE database/migrations/017_ai_cache_hotfix.sql;
-- ============================================================

CREATE TABLE IF NOT EXISTS `ai_field_analysis_cache` (
    `id`              INT PRIMARY KEY AUTO_INCREMENT,
    `student_id`      INT NOT NULL,
    `ai_model`        VARCHAR(100) NOT NULL DEFAULT 'heuristic_v1' COMMENT 'Model/version that generated this cache',
    `field_scores_json` JSON NOT NULL COMMENT 'All readiness scores as JSON: {overall, academic, portfolio, skill, riasec, ...}',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at`      TIMESTAMP NULL COMMENT 'Cache expiry — NULL means no expiry',
    INDEX `idx_student` (`student_id`),
    INDEX `idx_model` (`ai_model`),
    INDEX `idx_expires` (`expires_at`),
    UNIQUE KEY `uk_student_model` (`student_id`, `ai_model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Eager-loaded AI analysis cache to eliminate N+1 readiness queries';

-- ============================================================
-- Migration Notes:
-- - If ai_field_analysis_cache already exists (from 013), this
--   adds the standardized schema with ai_model + expires_at
-- - The UNIQUE KEY ensures one cache entry per student+model
-- - expires_at enables automatic cache invalidation logic
-- ============================================================
