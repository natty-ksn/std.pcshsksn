<?php
/**
 * Notifier — Event-driven Notification System
 * 
 * ส่งการแจ้งเตือนอัตโนมัติจาก events ต่างๆ ในระบบ
 * 
 * ตัวอย่างการใช้งาน:
 *   Notifier::projectApproved($projectId, $studentId);
 *   Notifier::badgeAwarded($userId, 'first_portfolio');
 *   Notifier::commentAdded($portfolioId, $teacherId, $studentId);
 */
class Notifier
{
    // ========================================
    // Event Types
    // ========================================
    const TYPE_PROJECT_SUBMITTED = 'project.submitted';
    const TYPE_PROJECT_APPROVED = 'project.approved';
    const TYPE_PROJECT_REJECTED = 'project.rejected';
    const TYPE_PROJECT_REVISION = 'project.revision_needed';
    const TYPE_MILESTONE_DUE = 'milestone.due_soon';
    const TYPE_MILESTONE_OVERDUE = 'milestone.overdue';
    const TYPE_COMMENT_ADDED = 'comment.added';
    const TYPE_BADGE_AWARDED = 'badge.awarded';
    const TYPE_LOGBOOK_VERIFIED = 'logbook.verified';
    const TYPE_DOCUMENT_APPROVED = 'document.approved';
    const TYPE_DOCUMENT_REVISION = 'document.revision_needed';
    const TYPE_EARLY_WARNING = 'early_warning.alert';
    const TYPE_SYSTEM_MAINTENANCE = 'system.maintenance';

    /**
     * Send a notification
     */
    public static function send(int $userId, string $type, string $title, string $message = '', string $link = ''): void
    {
        Database::insert(
            "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)",
            [$userId, $type, $title, $message, $link]
        );

        App::log("NOTIFICATION: [{$type}] sent to user #{$userId}: {$title}", 'INFO');
    }

    /**
     * Send notification to multiple users
     */
    public static function sendBulk(array $userIds, string $type, string $title, string $message = '', string $link = ''): void
    {
        if (empty($userIds))
            return;

        Database::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                self::send((int) $userId, $type, $title, $message, $link);
            }
            Database::commit();
        } catch (Exception $e) {
            Database::rollback();
            App::log("NOTIFICATION BULK FAILED: " . $e->getMessage(), 'ERROR');
        }
    }

    // ========================================
    // Project Events
    // ========================================

    /**
     * Triggered when student submits a project proposal
     */
    public static function projectSubmitted(int $projectId, int $advisorId): void
    {
        $project = Database::fetch("SELECT title_th, project_code FROM projects WHERE id = ?", [$projectId]);
        self::send(
            $advisorId,
            self::TYPE_PROJECT_SUBMITTED,
            'มีโครงงานใหม่เสนอ',
            "โครงงาน \"{$project['title_th']}\" ({$project['project_code']}) ถูกเสนอแล้ว กรุณาตรวจสอบ",
            "/advisor/projects/{$projectId}"
        );
    }

    /**
     * Triggered when advisor approves a project
     */
    public static function projectApproved(int $projectId, int $advisorId): void
    {
        // Notify all project members
        $members = Database::fetchAll(
            "SELECT pm.student_id, p.title_th 
             FROM project_members pm 
             JOIN projects p ON pm.project_id = p.id 
             WHERE pm.project_id = ?",
            [$projectId]
        );

        foreach ($members as $member) {
            self::send(
                (int) $member['student_id'],
                self::TYPE_PROJECT_APPROVED,
                '✅ โครงงานอนุมัติแล้ว',
                "โครงงาน \"{$member['title_th']}\" ได้รับการอนุมัติแล้ว เริ่มดำเนินการได้!",
                "/projects/{$projectId}"
            );
        }

        // Audit log
        self::logAudit($advisorId, 'project.approved', 'projects', $projectId);
    }

    /**
     * Triggered when advisor rejects a project
     */
    public static function projectRejected(int $projectId, int $advisorId, string $reason = ''): void
    {
        $members = Database::fetchAll(
            "SELECT pm.student_id, p.title_th 
             FROM project_members pm 
             JOIN projects p ON pm.project_id = p.id 
             WHERE pm.project_id = ?",
            [$projectId]
        );

        foreach ($members as $member) {
            $msg = "โครงงาน \"{$member['title_th']}\" ไม่ผ่านการอนุมัติ";
            if ($reason)
                $msg .= "\nเหตุผล: {$reason}";
            self::send(
                (int) $member['student_id'],
                self::TYPE_PROJECT_REJECTED,
                '❌ โครงงานไม่ผ่านการอนุมัติ',
                $msg,
                "/projects/{$projectId}"
            );
        }

        self::logAudit($advisorId, 'project.rejected', 'projects', $projectId);
    }

    /**
     * Triggered when advisor requests revision
     */
    public static function projectRevision(int $projectId, int $advisorId, string $feedback = ''): void
    {
        $members = Database::fetchAll(
            "SELECT pm.student_id, p.title_th 
             FROM project_members pm 
             JOIN projects p ON pm.project_id = p.id 
             WHERE pm.project_id = ?",
            [$projectId]
        );

        foreach ($members as $member) {
            $msg = "โครงงาน \"{$member['title_th']}\" ต้องแก้ไข";
            if ($feedback)
                $msg .= "\nข้อเสนอแนะ: {$feedback}";
            self::send(
                (int) $member['student_id'],
                self::TYPE_PROJECT_REVISION,
                '🔄 โครงงานต้องแก้ไข',
                $msg,
                "/projects/{$projectId}"
            );
        }
    }

    // ========================================
    // Comment Events
    // ========================================

    /**
     * Triggered when teacher adds a comment to portfolio
     */
    public static function commentAdded(int $portfolioId, int $teacherId, int $studentId): void
    {
        $portfolio = Database::fetch("SELECT title FROM portfolio_items WHERE id = ?", [$portfolioId]);
        $teacher = Database::fetch("SELECT full_name FROM users WHERE id = ?", [$teacherId]);

        self::send(
            $studentId,
            self::TYPE_COMMENT_ADDED,
            '💬 มีความคิดเห็นใหม่',
            "{$teacher['full_name']} แสดงความคิดเห็นต่อ \"{$portfolio['title']}\"",
            "/portfolio/{$portfolioId}"
        );
    }

    // ========================================
    // Badge Events
    // ========================================

    /**
     * Triggered when a badge is awarded
     */
    public static function badgeAwarded(int $userId, string $badgeCode): void
    {
        $badge = Database::fetch("SELECT name, icon, description FROM badges_master WHERE code = ?", [$badgeCode]);

        if (!$badge) {
            return; // badge not found in master table, skip notification silently
        }

        self::send(
            $userId,
            self::TYPE_BADGE_AWARDED,
            "🏅 ได้รับเหรียญรางวัล {$badge['icon']} {$badge['name']}",
            "ขอแสดงความยินดี คุณได้รับเหรียญรางวัลความสำเร็จ \"{$badge['name']}\" " . ($badge['description'] ?? ''),
            '/rewards/badges'
        );
    }

    // ========================================
    // Milestone Events
    // ========================================

    /**
     * Check and notify about upcoming/overdue milestones
     * ควรเรียกใช้ผ่าน cron job ทุกวัน
     */
    public static function checkMilestones(): void
    {
        // Due soon (within 3 days)
        $dueSoon = Database::fetchAll(
            "SELECT m.*, p.title_th, pm.student_id 
             FROM milestones m
             JOIN projects p ON m.project_id = p.id
             JOIN project_members pm ON p.id = pm.project_id
             WHERE m.status IN ('pending', 'in_progress') 
             AND m.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
        );

        foreach ($dueSoon as $milestone) {
            self::send(
                (int) $milestone['student_id'],
                self::TYPE_MILESTONE_DUE,
                '⏰ กำหนดการใกล้ถึง',
                "จุดหมาย \"{$milestone['phase_name']}\" ของโครงงาน \"{$milestone['title_th']}\" จะถึงกำหนดในอีก 3 วัน",
                "/projects/{$milestone['project_id']}"
            );
        }

        // Overdue
        $overdue = Database::fetchAll(
            "SELECT m.*, p.title_th, pm.student_id 
             FROM milestones m
             JOIN projects p ON m.project_id = p.id
             JOIN project_members pm ON p.id = pm.project_id
             WHERE m.status IN ('pending', 'in_progress') 
             AND m.due_date < CURDATE()"
        );

        foreach ($overdue as $milestone) {
            self::send(
                (int) $milestone['student_id'],
                self::TYPE_MILESTONE_OVERDUE,
                '🚨 จุดหมายเกินกำหนด',
                "จุดหมาย \"{$milestone['phase_name']}\" ของโครงงาน \"{$milestone['title_th']}\" เกินกำหนดแล้ว",
                "/projects/{$milestone['project_id']}"
            );
        }
    }

    // ========================================
    // Logbook Events
    // ========================================

    /**
     * Triggered when teacher verifies a logbook
     */
    public static function logbookVerified(int $logbookId, int $teacherId, int $studentId): void
    {
        $logbook = Database::fetch(
            "SELECT lb.*, p.title_th FROM logbooks lb 
             JOIN projects p ON lb.project_id = p.id 
             WHERE lb.id = ?",
            [$logbookId]
        );

        self::send(
            $studentId,
            self::TYPE_LOGBOOK_VERIFIED,
            '✅ Logbook ได้รับการยืนยัน',
            "บันทึกกิจกรรมโครงงาน \"{$logbook['title_th']}\" ได้รับการตรวจสอบแล้ว",
            "/projects/{$logbook['project_id']}"
        );
    }

    // ========================================
    // Early Warning Events
    // ========================================

    /**
     * Triggered when a student is flagged as high/critical risk
     */
    public static function earlyWarningAlert(int $studentUserId, string $riskLevel): void
    {
        $riskMessage = match ($riskLevel) {
            'critical' => 'ระบบตรวจพบความก้าวหน้าทางการเรียนและผลงานอยู่ในเกณฑ์ที่ต้องได้รับการดูแลช่วยเหลืออย่างใกล้ชิด กรุณาติดต่อครูที่ปรึกษา',
            'high' => 'ระบบตรวจพบว่าผลสัมฤทธิ์และกิจกรรมสะสมยังไม่เป็นไปตามเป้าหมาย ขอแนะนำให้เข้าพบครูที่ปรึกษาเพื่อรับคำแนะนำ',
            default => 'ขอแนะนำให้ทบทวนความก้าวหน้าทางการเรียนและสะสมผลงานเพิ่มเติม หรือปรึกษาครูที่ปรึกษา',
        };

        // Notify the student
        self::send(
            $studentUserId,
            self::TYPE_EARLY_WARNING,
            '⚠️ ข้อความแจ้งเตือนการติดตามความก้าวหน้า',
            $riskMessage,
            '/goals'
        );

        // Notify advisor/teacher
        $advisor = Database::fetch(
            "SELECT p.advisor_id FROM project_members pm 
             JOIN projects p ON pm.project_id = p.id 
             WHERE pm.student_id = ? 
             AND p.advisor_id IS NOT NULL 
             LIMIT 1",
            [$studentUserId]
        );

        if ($advisor && !empty($advisor['advisor_id'])) {
            $student = Database::fetch(
                "SELECT full_name, student_code FROM student_profiles WHERE user_id = ?",
                [$studentUserId]
            );
            $studentName = $student['full_name'] ?? 'นักเรียน';
            $studentCode = $student['student_code'] ?? '';

            $riskLabelTh = match ($riskLevel) {
                'critical' => 'ภาวะวิกฤต (ต้องการช่วยเหลือทันที)',
                'high' => 'ต้องการช่วยเหลือเร่งด่วน',
                default => 'ต้องการการติดตาม',
            };

            self::send(
                (int) $advisor['advisor_id'],
                self::TYPE_EARLY_WARNING,
                "🚨 นักเรียนในความดูแลอยู่ในข่ายเฝ้าระวัง: {$studentName}",
                "{$studentName} ({$studentCode}) อยู่ในกลุ่มเฝ้าระวังระดับ: {$riskLabelTh} แนะนำให้นัดหมายเพื่อการดูแลช่วยเหลือ",
                '/teacher/students'
            );
        }
    }

    // ========================================
    // Utility
    // ========================================

    /**
     * Get unread notification count
     */
    public static function getUnreadCount(int $userId): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int) ($result['cnt'] ?? 0);
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId, int $userId): void
    {
        Database::execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }

    /**
     * Mark all as read
     */
    public static function markAllAsRead(int $userId): void
    {
        Database::execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    /**
     * Log audit action
     */
    private static function logAudit(int $userId, string $action, string $entityType, int $entityId): void
    {
        Database::insert(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $entityType,
                $entityId,
                $_SERVER['REMOTE_ADDR'] ?? 'cli',
                $_SERVER['HTTP_USER_AGENT'] ?? 'cli',
            ]
        );
    }
}
