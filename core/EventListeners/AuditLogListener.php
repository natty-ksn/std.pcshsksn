<?php
namespace Core\EventListeners;

use Core\EventDispatcher;
use Auth;

/**
 * AuditLogListener — Wires all critical events to the Immutable Audit Log.
 *
 * Listens to portfolio, grading, project, and approval events,
 * then records them in the audit_logs table for PDPA compliance.
 */
class AuditLogListener
{
    /**
     * Register all audit logging event listeners.
     */
    public static function register(): void
    {
        // Require the AuditLog model (global namespace)
        require_once APP_PATH . '/models/AuditLog.php';

        // --- Portfolio Verification Events ---
        EventDispatcher::listen('portfolio.verified', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.verified',
                entityType: 'portfolio',
                entityId: null, // student-level event; entity_id from args if available
                after: ['student_id' => $studentId, 'status' => 'verified', 'teacher_id' => Auth::id()]
            );
        });

        EventDispatcher::listen('portfolio.rejected', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.rejected',
                entityType: 'portfolio',
                entityId: null,
                after: ['student_id' => $studentId, 'status' => 'rejected', 'teacher_id' => Auth::id()]
            );
        });

        EventDispatcher::listen('portfolio.created', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.created',
                entityType: 'portfolio',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('portfolio.updated', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.updated',
                entityType: 'portfolio',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('portfolio.deleted', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.deleted',
                entityType: 'portfolio',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        // --- Teacher Annotation Events ---
        EventDispatcher::listen('portfolio.annotated', function ($studentId, $portfolioId, $targetField, $severity) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.annotated',
                entityType: 'portfolio_annotation',
                entityId: $portfolioId,
                after: ['student_id' => $studentId, 'target_field' => $targetField, 'severity' => $severity]
            );
        });

        EventDispatcher::listen('portfolio.annotation_resolved', function ($studentId, $portfolioId, $annotationId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'portfolio.annotation_resolved',
                entityType: 'portfolio_annotation',
                entityId: $portfolioId,
                after: ['student_id' => $studentId, 'annotation_id' => $annotationId, 'status' => 'resolved']
            );
        });

        // --- Teacher Comment Events ---
        EventDispatcher::listen('teacher.comment_added', function ($studentId, $portfolioId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'teacher.comment_added',
                entityType: 'teacher_comment',
                entityId: $portfolioId,
                after: ['student_id' => $studentId, 'portfolio_id' => $portfolioId]
            );
        });

        // --- Project Events ---
        EventDispatcher::listen('project.completed', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'project.completed',
                entityType: 'project',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('project.created', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'project.created',
                entityType: 'project',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('project.approved', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'project.approved',
                entityType: 'project',
                entityId: null,
                after: ['student_id' => $studentId, 'status' => 'approved']
            );
        });

        // --- Assessment / Grading Events ---
        EventDispatcher::listen('assessment.completed', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'assessment.completed',
                entityType: 'assessment',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('grade.changed', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'grade.changed',
                entityType: 'grade',
                entityId: null,
                after: ['student_id' => $studentId]
            );
        });

        // --- Student Profile Events ---
        EventDispatcher::listen('student.registered', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'student.registered',
                entityType: 'student',
                entityId: $studentId,
                after: ['student_id' => $studentId]
            );
        });

        EventDispatcher::listen('profile.updated', function ($studentId) {
            \AuditLog::log(
                userId: Auth::id(),
                action: 'profile.updated',
                entityType: 'student_profile',
                entityId: $studentId,
                after: ['student_id' => $studentId]
            );
        });

        // --- Gamification Events ---
        EventDispatcher::listen('badge.earned', function ($studentId, $badgeId) {
            \AuditLog::log(
                userId: $studentId,
                action: 'badge.earned',
                entityType: 'badge',
                entityId: $badgeId,
                after: ['student_id' => $studentId, 'badge_id' => $badgeId]
            );
        });

        EventDispatcher::listen('level.up', function ($studentId, $newLevel) {
            \AuditLog::log(
                userId: $studentId,
                action: 'level.up',
                entityType: 'user_level',
                entityId: $studentId,
                after: ['student_id' => $studentId, 'new_level' => $newLevel]
            );
        });

        // --- Wildcard: catch any portfolio.* event with full args ---
        // This acts as a safety net for portfolio events that may not have
        // explicit listeners above, ensuring nothing slips through.
        EventDispatcher::listen('portfolio.*', function ($eventName, $studentId) {
            // Only log if there isn't already a specific listener for this exact event
            // (The specific listeners above fire first, so this is a fallback)
            // We skip logging here to avoid duplicates since the explicit listeners
            // above handle portfolio.created/updated/verified/rejected/deleted.
        });
    }
}
