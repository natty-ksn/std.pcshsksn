<?php
/**
 * AuditLog Model — Immutable Audit Logging System
 *
 * Records all critical actions for Data Integrity and PDPA Compliance.
 * This model is INSERT-ONLY. No UPDATE or DELETE operations are allowed.
 */
class AuditLog
{
    /**
     * Table name for the audit log.
     */
    private const TABLE = 'audit_logs';

    /**
     * Maximum length for IP address storage.
     */
    private const MAX_IP_LENGTH = 45;

    /**
     * Maximum length for User-Agent storage.
     */
    private const MAX_UA_LENGTH = 500;

    /**
     * Log a critical action to the immutable audit log.
     *
     * @param int|null $userId       The user who performed the action (null for system)
     * @param string   $action       Action code (e.g., 'portfolio.verified', 'grade.changed')
     * @param string|null $entityType Entity type (e.g., 'portfolio', 'grade', 'project')
     * @param int|null  $entityId    Primary key of the affected entity
     * @param array|null $before     State before the change (will be JSON-encoded)
     * @param array|null $after      State after the change (will be JSON-encoded)
     *
     * @return int The inserted audit log record ID
     */
    public static function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null
    ): int {
        $ipAddress = self::getIpAddress();
        $userAgent = self::getUserAgent();

        $beforeJson = $before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $afterJson  = $after  !== null ? json_encode($after,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        return Database::insert(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $entityType,
                $entityId,
                $beforeJson,
                $afterJson,
                $ipAddress,
                $userAgent,
            ]
        );
    }

    /**
     * Retrieve audit logs for a specific entity.
     * Useful for showing the history of a portfolio, grade, etc.
     *
     * @param string $entityType Entity type (e.g., 'portfolio')
     * @param int    $entityId   Entity primary key
     * @param int    $limit      Maximum records to return
     *
     * @return array
     */
    public static function forEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.username, u.full_name
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.entity_type = ? AND al.entity_id = ?
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$entityType, $entityId, $limit]
        );
    }

    /**
     * Retrieve recent audit logs for a specific user.
     *
     * @param int $userId User ID
     * @param int $limit  Maximum records to return
     *
     * @return array
     */
    public static function forUser(int $userId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.username
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.user_id = ?
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Retrieve recent audit logs filtered by action.
     *
     * @param string $action Action code
     * @param int    $limit  Maximum records to return
     *
     * @return array
     */
    public static function forAction(string $action, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.username
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.action = ?
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$action, $limit]
        );
    }

    /**
     * Securely extract the client IP address.
     *
     * Uses REMOTE_ADDR as the primary source (most reliable).
     * Falls back to common proxy headers only when REMOTE_ADDR
     * is a known local/private address (e.g., behind a reverse proxy).
     *
     * @return string
     */
    private static function getIpAddress(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Validate IP format (IPv4 or IPv6)
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }

        // If behind a trusted proxy, check forwarded headers
        // Only accept these when REMOTE_ADDR is a private/local address
        if (self::isPrivateIp($ip)) {
            $forwardedHeaders = [
                'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP',
                'HTTP_X_REAL_IP',
            ];

            foreach ($forwardedHeaders as $header) {
                if (!empty($_SERVER[$header])) {
                    // X-Forwarded-For can contain multiple IPs; take the first one
                    $forwardedIp = trim(explode(',', $_SERVER[$header])[0]);
                    if (filter_var($forwardedIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        $ip = $forwardedIp;
                        break;
                    }
                }
            }
        }

        // Truncate to max length and sanitize
        return mb_substr($ip, 0, self::MAX_IP_LENGTH, 'UTF-8');
    }

    /**
     * Check if an IP address is private/local (behind a proxy).
     *
     * @param string $ip
     * @return bool
     */
    private static function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Securely extract the User-Agent string.
     *
     * @return string
     */
    private static function getUserAgent(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Sanitize and truncate
        $ua = htmlspecialchars($ua, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return mb_substr($ua, 0, self::MAX_UA_LENGTH, 'UTF-8');
    }

    /**
     * ⚠️  PREVENT MUTATION: Audit logs are IMMUTABLE.
     *
     * This class intentionally does NOT provide update() or delete() methods.
     * Any attempt to modify audit logs violates PDPA compliance and data integrity.
     *
     * If you need to correct an audit log entry, INSERT a new record with the
     * correction note and reference the original record ID in after_values.
     */
}
