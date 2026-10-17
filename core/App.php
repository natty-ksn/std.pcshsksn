<?php
/**
 * Application Core
 * จัดการข้อมูลพื้นฐานของแอปพลิเคชัน
 */
class App
{
    private static ?App $instance = null;

    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function baseUrl(string $path = ''): string
    {
        $base = Env::get('APP_URL', '');

        if (empty($base)) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            // Detect if we are in public/index.php
            $baseDir = str_replace(['/public/index.php', '/index.php'], '', $script);
            $base = "{$protocol}://{$host}{$baseDir}";
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Get asset URL (CSS, JS, images)
     */
    public static function asset(string $path): string
    {
        return self::baseUrl('public/assets/' . ltrim($path, '/'));
    }

    /**
     * Redirect to URL
     */
    public static function redirect(string $path): void
    {
        $target = self::baseUrl($path);
        $source = $_SERVER['REQUEST_URI'] ?? 'unknown';
        self::log("REDIRECT: From [{$source}] to [{$target}]", 'DEBUG');

        header('Location: ' . $target);
        exit;
    }

    /**
     * Redirect back
     */
    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? self::baseUrl();
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Return JSON response
     */
    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get current URL path
     */
    public static function currentPath(): string
    {
        return $_GET['url'] ?? '';
    }

    /**
     * Check if debug mode
     */
    public static function isDebug(): bool
    {
        return Env::get('APP_DEBUG', 'false') === 'true';
    }

    /**
     * Log error
     */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        $logFile = LOG_PATH . '/app.log';

        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
