<?php
namespace Core\EventListeners;

use Core\EventDispatcher;
use Core\Cache;

class CacheInvalidationListener
{
    /**
     * Register all cache invalidation events.
     */
    public static function register()
    {
        // --- Dashboard / Portfolio Events ---
        EventDispatcher::listen('portfolio.created', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::forget("school_analytics_summary");
            Cache::invalidateTags(['school_dashboard_summary', 'school_analytics']);
            Cache::forget("teacher_pending_reviews");
        });

        EventDispatcher::listen('portfolio.updated', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_dashboard_summary']);
        });

        EventDispatcher::listen('portfolio.verified', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::forget("school_analytics_summary");
            Cache::invalidateTags(['school_dashboard_summary', 'school_analytics']);
            Cache::forget("teacher_pending_reviews");
        });

        EventDispatcher::listen('portfolio.rejected', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::forget("teacher_pending_reviews");
        });

        EventDispatcher::listen('portfolio.deleted', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_dashboard_summary', 'school_analytics']);
        });

        // --- Project Events ---
        EventDispatcher::listen('project.completed', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_dashboard_summary', 'school_analytics']);
        });

        EventDispatcher::listen('project.created', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
        });

        // --- Assessment / TCAS Events ---
        EventDispatcher::listen('assessment.completed', function ($studentId) {
            Cache::forget("readiness_tcas:{$studentId}");
            Cache::forget("early_warning:{$studentId}");
            Cache::invalidateTags(['school_analytics']);
        });

        EventDispatcher::listen('grade.changed', function ($studentId) {
            Cache::forget("readiness_tcas:{$studentId}");
            Cache::forget("early_warning:{$studentId}");
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_analytics']);
        });

        // --- Student Profile Events ---
        EventDispatcher::listen('student.registered', function ($studentId) {
            Cache::forget("school_analytics_summary");
            Cache::invalidateTags(['school_dashboard_summary', 'school_analytics']);
        });

        EventDispatcher::listen('profile.updated', function ($studentId) {
            Cache::forget("dashboard_stats:{$studentId}");
        });

        // --- Gamification Events ---
        EventDispatcher::listen('badge.earned', function ($studentId, $badgeId) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_analytics']);
        });

        EventDispatcher::listen('level.up', function ($studentId, $newLevel) {
            Cache::forget("dashboard_stats:{$studentId}");
            Cache::invalidateTags(['school_analytics']);
        });

        // --- Wildcard: catch any portfolio event for school-level summary ---
        EventDispatcher::listen('portfolio.*', function ($eventName, $studentId) {
            // Always invalidate the school-wide analytics summary on any portfolio action
            Cache::forget("school_analytics_summary");
        });
    }
}
