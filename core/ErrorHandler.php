<?php
/**
 * Error Handler — Centralized Exception & Error Handling
 * 
 * จัดการ error ทั้งหมดในระบบ:
 * - 404 Not Found
 * - 403 Forbidden
 * - 500 Internal Server Error
 * - Maintenance Mode
 * - Uncaught Exceptions
 * - PHP Errors
 */
class ErrorHandler
{
    private static bool $registered = false;

    /**
     * Register global error handlers
     * เรียกใช้ครั้งเดียวใน public/index.php
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set custom error handler
        set_error_handler([self::class, 'handleError']);

        // Set shutdown handler for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $exception): void
    {
        // Log the error
        self::logError($exception);

        // Determine HTTP status code (PDO exceptions return string codes like '42S22')
        $code = $exception->getCode();
        if (!is_int($code) || $code < 400 || $code >= 600) {
            $code = 500;
        }

        http_response_code($code);

        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => App::isDebug() ? $exception->getMessage() : 'เกิดข้อผิดพลาดภายใน',
                'code' => $code,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Render error page
        $data = [
            'code' => $code,
            'message' => self::getErrorMessage($code, $exception),
            'exception' => $exception,
        ];

        self::renderErrorPage($data);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Respect error_reporting setting
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Log the error
        $logMessage = "PHP Error [{$errno}] {$errstr} in {$errfile} on line {$errline}";
        App::log($logMessage, 'ERROR');

        // Convert to exception for handling
        if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        // For warnings/notices, log but don't show to user in production
        if (App::isDebug()) {
            echo "<pre style='background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:10px;'>";
            echo "<strong>⚠️ {$errstr}</strong><br>";
            echo "File: {$errfile}<br>";
            echo "Line: {$errline}";
            echo "</pre>";
        }

        return true;
    }

    /**
     * Handle fatal errors (shutdown)
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            // Clear any output so far
            if (ob_get_level()) {
                ob_clean();
            }

            self::handleException($exception);
        }
    }

    /**
     * Render 404 page
     */
    public static function notFound(string $message = 'ไม่พบหน้าที่ต้องการ'): void
    {
        http_response_code(404);

        $data = [
            'code' => 404,
            'message' => $message,
            'exception' => null,
        ];

        self::renderErrorPage($data);
        exit;
    }

    /**
     * Render 403 page
     */
    public static function forbidden(string $message = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'): void
    {
        http_response_code(403);

        $data = [
            'code' => 403,
            'message' => $message,
            'exception' => null,
        ];

        self::renderErrorPage($data);
        exit;
    }

    /**
     * Render 500 page
     */
    public static function serverError(string $message = 'เกิดข้อผิดพลาดภายในระบบ'): void
    {
        http_response_code(500);

        $data = [
            'code' => 500,
            'message' => $message,
            'exception' => null,
        ];

        self::renderErrorPage($data);
        exit;
    }

    /**
     * Render maintenance page
     */
    public static function maintenance(string $message = 'ระบบอยู่ระหว่างปรับปรุง'): void
    {
        http_response_code(503);

        $viewPath = VIEW_PATH . '/errors/maintenance.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<!DOCTYPE html><html lang='th'><head><meta charset='UTF-8'><title>ระบบปรับปรุง</title>";
            echo "<style>body{font-family:'Noto Sans Thai',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f0f4f8;}";
            echo ".container{text-align:center;background:#fff;padding:60px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1);}";
            echo "h1{color:#6366f1;font-size:2em;margin:0 0 10px;}p{color:#64748b;font-size:1.1em;}</style></head>";
            echo "<body><div class='container'><h1>🔧 ระบบอยู่ระหว่างปรับปรุง</h1><p>{$message}</p>";
            echo "<p style='margin-top:20px;color:#94a3b8;'>กรุณาลองใหม่อีกครั้งในภายหลัง</p></div></body></html>";
        }
        exit;
    }

    /**
     * Render error page
     */
    private static function renderErrorPage(array $data): void
    {
        $viewPath = VIEW_PATH . "/errors/{$data['code']}.php";

        if (file_exists($viewPath)) {
            extract($data);
            require $viewPath;
        } else {
            self::renderDefaultError($data);
        }
    }

    /**
     * Render default error page (fallback)
     */
    private static function renderDefaultError(array $data): void
    {
        $code = $data['code'];
        $message = $data['message'];
        $exception = $data['exception'];

        $debugHtml = '';
        if ($exception) {
            $debugHtml = '<div class="debug"><strong>Debug Info:</strong><br>';
            $debugHtml .= '<code>' . htmlspecialchars($exception->getMessage()) . '</code><br><br>';
            $debugHtml .= 'File: ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . '<br><br>';
            $debugHtml .= 'Stack Trace:<br>';
            $debugHtml .= '<code>' . nl2br(htmlspecialchars($exception->getTraceAsString())) . '</code></div>';
        }

        $html = '<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error ' . $code . ' - PCSHSKSN e-Portfolio</title>
    <style>
        body { font-family: \'Noto Sans Thai\', \'Sarabun\', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f0f4f8; }
        .container { text-align: center; background: #fff; padding: 60px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.1); max-width: 600px; }
        h1 { color: #ef4444; font-size: 3em; margin: 0; }
        h2 { color: #1e293b; font-size: 1.3em; margin: 10px 0; }
        p { color: #64748b; font-size: 1.1em; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 8px; transition: background .2s; }
        .back-btn:hover { background: #4f46e5; }
        .debug { text-align: left; background: #1e293b; color: #e2e8f0; padding: 20px; border-radius: 8px; margin-top: 20px; font-size: 0.9em; overflow-x: auto; }
        .debug code { color: #38bdf8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>' . $code . '</h1>
        <h2>' . htmlspecialchars($message) . '</h2>
        <p>PCSHSKSN e-Portfolio — ระบบแฟ้มสะสมผลงานอัจฉริยะ</p>
        <a href="/" class="back-btn">&larr; กลับหน้าหลัก</a>
        ' . $debugHtml . '
    </div>
</body>
</html>';

        echo $html;
    }

    /**
     * Get user-friendly error message
     */
    private static function getErrorMessage(int $code, Throwable $exception): string
    {
        switch ($code) {
            case 404:
                return 'ไม่พบหน้าที่ต้องการ — URL นี้ไม่มีอยู่ในระบบ';
            case 403:
                return 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ — กรุณาตรวจสอบสิทธิ์';
            case 500:
                return 'เกิดข้อผิดพลาดภายในระบบ — กรุณาลองใหม่อีกครั้ง';
            case 405:
                return 'ไม่รองรับ method นี้';
            case 429:
                return 'เรียกใช้บ่อยเกินไป — กรุณารอสักครู่';
            case 503:
                return 'ระบบไม่พร้อมใช้งาน — กรุณาลองใหม่อีกครั้ง';
            default:
                return App::isDebug() ? $exception->getMessage() : 'เกิดข้อผิดพลาด — กรุณาติดต่อผู้ดูแลระบบ';
        }
    }

    /**
     * Log error to file
     */
    private static function logError(Throwable $exception): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $logDir = defined('LOG_PATH') ? LOG_PATH : (defined('ROOT_PATH') ? ROOT_PATH . '/logs' : dirname(__DIR__) . '/logs');
        $logFile = $logDir . '/error.log';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
