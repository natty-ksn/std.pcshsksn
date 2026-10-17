-- ============================================================
-- Migration: 018_audit_logs_system.sql
-- Description: Immutable Centralized Audit Logging System
--              Records all critical actions for Data Integrity
--              and PDPA Compliance
--              Immutable: INSERT-only, no UPDATE/DELETE allowed
-- ============================================================
-- Run: SOURCE database/migrations/018_audit_logs_system.sql;
-- ============================================================

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id`         INT NULL COMMENT 'User who performed the action (NULL for system actions)',
    `action`          VARCHAR(100) NOT NULL COMMENT 'Action code, e.g. portfolio.verified, grade.changed',
    `entity_type`     VARCHAR(50) NULL COMMENT 'Entity type, e.g. portfolio, grade, project, user',
    `entity_id`       INT NULL COMMENT 'Primary key of the affected entity',
    `before_values`   JSON NULL COMMENT 'State before the change (for audit trail)',
    `after_values`    JSON NULL COMMENT 'State after the change (for audit trail)',
    `ip_address`      VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 of the requester',
    `user_agent`      VARCHAR(500) NULL COMMENT 'Browser/client identifier',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_action` (`user_id`, `action`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_action_time` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Immutable audit log for PDPA compliance and data integrity tracking';

-- ============================================================
-- Security Notes:
-- - This table is INSERT-ONLY. No UPDATE or DELETE grants.
-- - Grant SELECT only to roles that need audit visibility.
-- - ip_address supports both IPv4 (VARCHAR 15) and IPv6 (VARCHAR 45).
-- - before_values and after_values store JSON snapshots for compliance.
-- ============================================================
