<?php
/**
 * Session Manager
 * จัดการ Session อย่างปลอดภัย
 */
class Session
{
    /**
     * Start session with security settings
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = (int) Env::get('SESSION_LIFETIME', '7200');
            $isSecure = (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) === 'on')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.gc_maxlifetime', (string) $lifetime);

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => $isSecure
            ]);

            session_start();

            // Regenerate session ID periodically (ป้องกัน Session Fixation)
            if (!isset($_SESSION['_last_regenerate'])) {
                $_SESSION['_last_regenerate'] = time();
            } elseif (time() - $_SESSION['_last_regenerate'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_last_regenerate'] = time();
            }
        }
    }

    /**
     * Set session value
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key  
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash message — ข้อความแสดงครั้งเดียว (Universal Getter/Setter)
     */
    public static function flash(string $type, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$type] = $message;
            return null;
        }

        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }

    /**
     * Get and remove flash message (Legacy support)
     */
    public static function getFlash(string $type): ?string
    {
        return self::flash($type);
    }

    /**
     * Check if flash exists
     */
    public static function hasFlash(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }

    /**
     * Flash input data for next request
     */
    public static function flashInput(array $data): void
    {
        $_SESSION['_old_input'] = $data;
    }

    /**
     * Get old input value
     */
    public static function old(string $key, $default = '')
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    /**
     * Clear flashed input
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }

    /**
     * Destroy session (logout)
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
