<?php
/**
 * BaseApiController — Foundation for all API v1 Controllers
 *
 * Provides strict JSON response wrappers, Bearer token validation,
 * and CORS preflight handling for the RESTful API gateway.
 *
 * Convention:
 *   All /api/v1/ responses follow this schema:
 *   {
 *       "success": true|false,
 *       "data": { ... } | null,
 *       "message": "Human-readable status" | null,
 *       "error": "Error details" | null,
 *       "meta": { "page": 1, "per_page": 20, "total": 100 } | null
 *   }
 *
 * @package  PCSHSKSN e-Portfolio
 * @category API Controllers
 */
class BaseApiController
{
    /**
     * Application version prefix for all API responses.
     */
    private const API_VERSION = 'v1';

    /**
     * Default pagination limit.
     */
    private const DEFAULT_PER_PAGE = 20;

    /**
     * Maximum pagination limit (prevent resource exhaustion).
     */
    private const MAX_PER_PAGE = 100;

    // ============================================================
    // RESPONSE WRAPPERS
    // ============================================================

    /**
     * Send a standardized JSON success response.
     *
     * @param array  $data    Payload data (goes under "data" key)
     * @param string $message Human-readable status message
     * @param int    $status  HTTP status code
     * @param array|null $meta Pagination or extra metadata
     * @return never
     */
    protected function jsonResponse(array $data = [], string $message = 'OK', int $status = 200, ?array $meta = null): void
    {
        $this->sendJson([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'error' => null,
            'meta' => $meta,
            'api_version' => self::API_VERSION,
        ], $status);
    }

    /**
     * Send a standardized JSON error response.
     *
     * @param string $message Error message
     * @param int    $status  HTTP status code
     * @param mixed  $errors  Additional error details (array, string, or null)
     * @return never
     */
    protected function jsonError(string $message = 'Bad Request', int $status = 400, $errors = null): void
    {
        $this->sendJson([
            'success' => false,
            'data' => null,
            'message' => $message,
            'error' => $errors,
            'api_version' => self::API_VERSION,
        ], $status);
    }

    /**
     * Send a paginated JSON response.
     *
     * @param array $items     Array of result items
     * @param int   $total     Total count (for pagination meta)
     * @param int   $page      Current page number
     * @param int   $perPage   Items per page
     * @param string $message  Human-readable status
     * @return never
     */
    protected function jsonPaginated(array $items, int $total, int $page = 1, int $perPage = 20, string $message = 'OK'): void
    {
        $perPage = min($perPage, self::MAX_PER_PAGE);
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

        $this->jsonResponse($items, $message, 200, [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
        ]);
    }

    /**
     * Core JSON sender — sets headers, encodes, and exits.
     *
     * @param array $payload Full response payload
     * @param int   $status  HTTP status code
     * @return never
     */
    private function sendJson(array $payload, int $status = 200): void
    {
        // Clear any previous output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ============================================================
    // INPUT HELPERS
    // ============================================================

    /**
     * Get JSON-decoded POST body (for PUT/PATCH/POST JSON requests).
     *
     * @param string|null $key    Specific key to extract, or null for full body
     * @param mixed       $default Default value if key missing
     * @return mixed
     */
    protected function jsonInput(?string $key = null, $default = null)
    {
        static $jsonBody = null;
        if ($jsonBody === null) {
            $raw = file_get_contents('php://input');
            $jsonBody = json_decode($raw, true) ?: [];
        }

        if ($key === null) {
            return $jsonBody;
        }

        return $jsonBody[$key] ?? $default;
    }

    /**
     * Get query parameter from URL.
     */
    protected function query(string $key, string $default = ''): string
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get the authenticated user ID from Bearer token validation.
     * Set by validateBearerToken() before controller action runs.
     */
    protected ?int $authenticatedUserId = null;

    /**
     * Get the authenticated user data from Bearer token validation.
     */
    protected ?array $authenticatedUser = null;

    // ============================================================
    // TOKEN AUTHENTICATION
    // ============================================================

    /**
     * Validate the Bearer token from the Authorization header.
     *
     * Sets $this->authenticatedUserId and $this->authenticatedUser
     * on success, or sends a 401 JSON error and exits on failure.
     *
     * @param array|string|null $requiredAbilities Optional required permission(s)
     * @return void (exits on failure, sets properties on success)
     */
    protected function validateBearerToken($requiredAbilities = null): void
    {
        $token = $this->extractBearerToken();

        if (!$token) {
            $this->jsonError('Unauthorized', 401, 'Missing or invalid Authorization header. Expected: Bearer <token>');
        }

        // Hash the incoming token for comparison
        $tokenHash = hash('sha256', $token);

        // Look up the token in the database
        $tokenRecord = Database::fetch(
            "SELECT at.*, u.id as user_id, u.username, u.email, u.role, u.full_name, u.is_active
             FROM api_tokens at
             JOIN users u ON at.user_id = u.id
             WHERE at.token_hash = ?
               AND at.revoked_at IS NULL
               AND (at.expires_at IS NULL OR at.expires_at > NOW())
               AND u.is_active = 1",
            [$tokenHash]
        );

        if (!$tokenRecord) {
            $this->jsonError('Unauthorized', 401, 'Invalid, expired, or revoked API token');
        }

        // Update last_used_at
        Database::execute(
            "UPDATE api_tokens SET last_used_at = NOW() WHERE id = ?",
            [(int) $tokenRecord['id']]
        );

        // Check required abilities
        if ($requiredAbilities !== null) {
            $tokenAbilities = [];
            if (!empty($tokenRecord['abilities'])) {
                $tokenAbilities = json_decode($tokenRecord['abilities'], true) ?: [];
            }

            $required = is_array($requiredAbilities) ? $requiredAbilities : [$requiredAbilities];
            foreach ($required as $ability) {
                if (!in_array($ability, $tokenAbilities) && !in_array('*', $tokenAbilities)) {
                    $this->jsonError('Forbidden', 403, "Token lacks required ability: {$ability}");
                }
            }
        }

        // Store authenticated user info
        $this->authenticatedUserId = (int) $tokenRecord['user_id'];
        $this->authenticatedUser = [
            'id' => (int) $tokenRecord['user_id'],
            'username' => $tokenRecord['username'],
            'email' => $tokenRecord['email'],
            'role' => $tokenRecord['role'],
            'full_name' => $tokenRecord['full_name'],
        ];
    }

    /**
     * Extract Bearer token from Authorization header.
     * Also checks ?token= query param as fallback (for webhooks/testing).
     */
    protected function extractBearerToken(): ?string
    {
        // Standard Authorization: Bearer <token> header
        $header = $this->getAuthorizationHeader();
        if ($header && preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        // Fallback: ?token= query param (convenience only, not recommended for production)
        if (!empty($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    /**
     * Get the Authorization header in a cross-platform way.
     */
    protected function getAuthorizationHeader(): ?string
    {
        if (isset($_SERVER['Authorization'])) {
            return $_SERVER['Authorization'];
        }

        // Apache with mod_php
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // CGI/FastCGI (PHP-FPM passes it as REDIRECT_HTTP_AUTHORIZATION)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return null;
    }
}
