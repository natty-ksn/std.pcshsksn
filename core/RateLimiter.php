<?php
/**
 * RateLimiter — ระบบป้องกัน API/Web Spam แบบไม่เปลือง MySQL Connection
 *
 * ใช้ File-based Storage แยกตาม IP + Session/User เพื่อ:
 * 1. ไม่ต้องเปิด MySQL Connection หรือรัน CREATE TABLE / UPDATE ทุกๆ Request (ป้องกัน max_user_connections เต็ม)
 * 2. รองรับกรณีนักเรียนทั้งโรงเรียนต่อ Wi-Fi วงเดียวกัน (Public IP เดียวกัน) โดยไม่บล็อกกันเอง
 */
class RateLimiter
{
    private const STORAGE_DIR = ROOT_PATH . '/tmp/ratelimit';

    /**
     * Check if the current request exceeds the rate limit.
     *
     * @param int $maxRequests Maximum requests allowed per client within window
     * @param int $windowSeconds Time window in seconds
     */
    public static function check(int $maxRequests = 500, int $windowSeconds = 60): void
    {
        try {
            // Skip rate-limit overhead for lightweight notification poll endpoints
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (str_contains($uri, 'api/stream/notifications') || str_contains($uri, 'api/notifications/count')) {
                return;
            }

            if (!is_dir(self::STORAGE_DIR)) {
                @mkdir(self::STORAGE_DIR, 0755, true);
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            // Distinguish users behind the same school NAT IP via session cookie if present
            $sessionName = session_name() ?: 'PHPSESSID';
            $sessionKey = $_COOKIE[$sessionName] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
            $clientKey = md5($ip . '|' . substr((string) $sessionKey, 0, 32));
            $filePath = self::STORAGE_DIR . '/rl_' . $clientKey . '.json';

            $now = time();
            $data = ['start' => $now, 'count' => 0];

            if (is_file($filePath)) {
                $raw = @file_get_contents($filePath);
                if ($raw !== false) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded) && isset($decoded['start'], $decoded['count'])) {
                        if (($now - (int) $decoded['start']) < $windowSeconds) {
                            $data = $decoded;
                        }
                    }
                }
            }

            $data['count'] = (int) $data['count'] + 1;

            if ($data['count'] > $maxRequests) {
                http_response_code(429);
                header('Retry-After: ' . $windowSeconds);
                header('Content-Type: text/plain; charset=utf-8');
                echo "429 Too Many Requests - ระบบจำกัดการเข้าถึงชั่วคราวเนื่องจากมีการเรียกใช้งานมากเกินไป กรุณารอสักครู่";
                exit;
            }

            @file_put_contents($filePath, json_encode($data), LOCK_EX);

            // 1% chance to clean up expired rate limit files (> 5 minutes old)
            if (random_int(1, 100) === 1) {
                foreach (glob(self::STORAGE_DIR . '/rl_*.json') ?: [] as $oldFile) {
                    if (($now - (int) @filemtime($oldFile)) > 300) {
                        @unlink($oldFile);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Never let rate limiter crash the application
            error_log("RateLimiter error (non-fatal): " . $e->getMessage());
        }
    }
}

