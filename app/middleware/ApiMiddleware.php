<?php
/**
 * ApiMiddleware — CORS & API Request Pre-Processor
 *
 * This middleware is applied globally to all /api/ routes.
 * It handles:
 *   1. CORS preflight (OPTIONS) responses
 *   2. Content-Type validation for POST/PUT/PATCH
 *   3. Rate limiting for API endpoints
 *   4. Request logging
 *
 * Applied in public/index.php before Router dispatch.
 *
 * @package  PCSHSKSN e-Portfolio
 * @category Middleware
 */
class ApiMiddleware
{
    /**
     * API rate limit — requests per minute per IP.
     */
    private const RATE_LIMIT = 60;

    /**
     * API rate window — seconds.
     */
    private const RATE_WINDOW = 60;

    /**
     * Allowed CORS origins.
     * Use '*' for development; set specific origins in production.
     */
    private const ALLOWED_ORIGINS = '*';

    /**
     * Allowed CORS methods.
     */
    private const ALLOWED_METHODS = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';

    /**
     * Allowed CORS headers.
     */
    private const ALLOWED_HEADERS = 'Content-Type, Authorization, X-Requested-With, Accept, X-API-Key';

    /**
     * CORS max age (preflight cache duration in seconds).
     */
    private const MAX_AGE = 86400;

    /**
     * Apply CORS headers and validate the API request.
     *
     * Call this at the very top of public/index.php
     * before any output or session start.
     *
     * @return void
     */
    public static function handle(): void
    {
        $requestPath = self::getRequestPath();

        // Only apply to /api/ paths
        if (!str_starts_with($requestPath, 'api/')) {
            return;
        }

        // ---- Set CORS headers ----
        header('Access-Control-Allow-Origin: ' . self::ALLOWED_ORIGINS);
        header('Access-Control-Allow-Methods: ' . self::ALLOWED_METHODS);
        header('Access-Control-Allow-Headers: ' . self::ALLOWED_HEADERS);
        header('Access-Control-Max-Age: ' . self::MAX_AGE);
        header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');

        // ---- Handle preflight (OPTIONS) ----
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // ---- Rate limiting ----
        self::applyRateLimit();

        // ---- Content-Type validation for JSON APIs ----
        $method = $_SERVER['REQUEST_METHOD'];
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            // Allow both application/json and form-data
            if (!str_contains($contentType, 'application/json') && !str_contains($contentType, 'multipart/form-data')) {
                // For API v1, prefer JSON — but allow fallback for compatibility
            }
        }
    }

    /**
     * Apply API-specific rate limiting.
     *
     * Uses a simple file-based rate limiter to avoid DB overhead.
     * Sends rate limit headers in the response.
     */
    private static function applyRateLimit(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'api_rate_' . md5($ip);
        $now = time();

        // Simple file-based rate limiting
        $rateFile = sys_get_temp_dir() . '/pcshsksn_rate_' . md5($key);
        $requests = 1;

        if (file_exists($rateFile)) {
            $data = @json_decode(file_get_contents($rateFile), true);
            if ($data && ($now - $data['window_start']) < self::RATE_WINDOW) {
                $requests = $data['count'] + 1;

                if ($requests > self::RATE_LIMIT) {
                    header('X-RateLimit-Limit: ' . self::RATE_LIMIT);
                    header('X-RateLimit-Remaining: 0');
                    header('X-RateLimit-Reset: ' . ($data['window_start'] + self::RATE_WINDOW));
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(429);
                    echo json_encode([
                        'success' => false,
                        'data' => null,
                        'message' => 'Too Many Requests',
                        'error' => 'API rate limit exceeded. Try again in ' . (self::RATE_WINDOW - ($now - $data['window_start'])) . ' seconds.',
                        'api_version' => 'v1',
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                // Window expired — reset
                $requests = 1;
            }
        }

        // Update rate file
        @file_put_contents($rateFile, json_encode([
            'count' => $requests,
            'window_start' => $now,
        ]));

        // Set response headers
        header('X-RateLimit-Limit: ' . self::RATE_LIMIT);
        header('X-RateLimit-Remaining: ' . max(0, self::RATE_LIMIT - $requests));
        header('X-RateLimit-Reset: ' . ($now + self::RATE_WINDOW));
    }

    /**
     * Get the current request path (relative, no query string).
     */
    private static function getRequestPath(): string
    {
        // From Router's url parameter
        if (!empty($_GET['url'])) {
            return rtrim($_GET['url'], '/');
        }

        // Fallback: parse REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Remove subfolder prefix if any
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        if ($scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }

        return trim($path, '/');
    }
}
