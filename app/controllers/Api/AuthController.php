<?php
/**
 * Api\AuthController — API Authentication & Token Management
 *
 * Handles login via username/password (issuing API tokens)
 * and token lifecycle operations (refresh, revoke, list).
 *
 * All responses are strict JSON via BaseApiController.
 *
 * @package  PCSHSKSN e-Portfolio
 * @category API Controllers
 */
class ApiAuthController extends BaseApiController
{
    /**
     * API token TTL — 90 days from creation.
     */
    private const TOKEN_TTL_DAYS = 90;

    /**
     * Raw token length (random bytes hex-encoded = 64 chars).
     */
    private const TOKEN_LENGTH = 32; // bytes → 64 hex chars

    // ============================================================
    // LOGIN — Issue API Token
    // ============================================================

    /**
     * POST /api/v1/auth/login
     *
     * Accepts JSON body: { "username": "...", "password": "..." }
     * Optionally: { "device_name": "iPhone 15", "abilities": ["read","write"] }
     *
     * Returns: { "token": "...", "user": {...}, "expires_at": "..." }
     */
    public function login(): void
    {
        // Parse JSON body
        $body = $this->jsonInput();

        $username = trim($body['username'] ?? '');
        $password = $body['password'] ?? '';
        $deviceName = trim($body['device_name'] ?? 'API Client');
        $abilities = $body['abilities'] ?? ['read', 'write'];

        // Validate required fields
        if (empty($username) || empty($password)) {
            $this->jsonError('Validation Failed', 422, [
                'username' => empty($username) ? 'Username or email is required' : null,
                'password' => empty($password) ? 'Password is required' : null,
            ]);
        }

        // Look up user (same logic as Auth::attempt)
        $user = Database::fetch(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );

        if (!$user) {
            $this->jsonError('Invalid credentials', 401);
        }

        // Verify password (bcrypt)
        if (!password_verify($password, $user['password'])) {
            $this->jsonError('Invalid credentials', 401);
        }

        // ---- Generate API Token ----
        $rawToken = $this->generateTokenString();
        $tokenHash = hash('sha256', $rawToken);
        $tokenPrefix = substr($rawToken, 0, 8);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_TTL_DAYS . ' days'));

        // Store the hashed token
        $tokenId = Database::insert(
            "INSERT INTO api_tokens (user_id, token_hash, token_prefix, name, abilities, expires_at, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int) $user['id'],
                $tokenHash,
                $tokenPrefix,
                $deviceName,
                json_encode($abilities, JSON_UNESCAPED_UNICODE),
                $expiresAt,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]
        );

        // Update last_login
        Database::execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [(int) $user['id']]
        );

        // Audit log
        try {
            AuditLog::log(
                (int) $user['id'],
                'api.login',
                'api_token',
                $tokenId,
                null,
                [
                    'device_name' => $deviceName,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'token_prefix' => $tokenPrefix,
                ]
            );
        } catch (\Throwable $e) {
            // Don't fail login if audit log fails
        }

        // Return token + user info
        $this->jsonResponse([
            'token' => $rawToken,       // Only sent once — never stored server-side
            'token_prefix' => $tokenPrefix,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
            ],
        ], 'Login successful');
    }

    // ============================================================
    // TOKEN LIFECYCLE — Require Auth
    // ============================================================

    /**
     * GET /api/v1/auth/me
     *
     * Returns the authenticated user's profile.
     * Requires: Bearer Token
     */
    public function me(): void
    {
        $this->validateBearerToken(['read']);

        // Fetch full user data
        $user = Database::fetch(
            "SELECT id, username, email, full_name, position, role, is_active, last_login, created_at
             FROM users WHERE id = ?",
            [$this->authenticatedUserId]
        );

        if (!$user) {
            $this->jsonError('User not found', 404);
        }

        // Fetch student profile if applicable
        $studentProfile = Database::fetch(
            "SELECT id, student_code, full_name, nickname, class_year, class_room, avatar_path, bio
             FROM student_profiles WHERE user_id = ?",
            [$this->authenticatedUserId]
        );

        $this->jsonResponse([
            'user' => $user,
            'student_profile' => $studentProfile,
        ]);
    }

    /**
     * POST /api/v1/auth/refresh
     *
     * Revokes current token and issues a new one.
     * Requires: Bearer Token
     */
    public function refresh(): void
    {
        $this->validateBearerToken(['read', 'write']);

        // Revoke the current token (we need the token record from validation)
        $tokenRecord = $this->getCurrentTokenRecord();
        if ($tokenRecord) {
            Database::execute(
                "UPDATE api_tokens SET revoked_at = NOW() WHERE id = ?",
                [(int) $tokenRecord['id']]
            );
        }

        // Issue a new token
        $rawToken = $this->generateTokenString();
        $tokenHash = hash('sha256', $rawToken);
        $tokenPrefix = substr($rawToken, 0, 8);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_TTL_DAYS . ' days'));

        Database::insert(
            "INSERT INTO api_tokens (user_id, token_hash, token_prefix, name, abilities, expires_at, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->authenticatedUserId,
                $tokenHash,
                $tokenPrefix,
                'Refreshed token',
                json_encode(['read', 'write'], JSON_UNESCAPED_UNICODE),
                $expiresAt,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]
        );

        $this->jsonResponse([
            'token' => $rawToken,
            'token_prefix' => $tokenPrefix,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ], 'Token refreshed successfully');
    }

    /**
     * POST /api/v1/auth/logout
     *
     * Revokes the current API token.
     * Requires: Bearer Token
     */
    public function logout(): void
    {
        $this->validateBearerToken();

        $tokenRecord = $this->getCurrentTokenRecord();
        if ($tokenRecord) {
            Database::execute(
                "UPDATE api_tokens SET revoked_at = NOW() WHERE id = ?",
                [(int) $tokenRecord['id']]
            );

            AuditLog::log(
                $this->authenticatedUserId,
                'api.logout',
                'api_token',
                (int) $tokenRecord['id'],
                ['token_prefix' => $tokenRecord['token_prefix']],
                null
            );
        }

        $this->jsonResponse([], 'Logged out — token revoked');
    }

    /**
     * GET /api/v1/auth/tokens
     *
     * List all active API tokens for the authenticated user.
     * Requires: Bearer Token
     */
    public function listTokens(): void
    {
        $this->validateBearerToken(['read']);

        $tokens = Database::fetchAll(
            "SELECT id, name, token_prefix, abilities, last_used_at, expires_at, created_at
             FROM api_tokens
             WHERE user_id = ? AND revoked_at IS NULL
             ORDER BY created_at DESC",
            [$this->authenticatedUserId]
        );

        $this->jsonResponse([
            'tokens' => array_map(function ($t) {
                $t['abilities'] = !empty($t['abilities']) ? json_decode($t['abilities'], true) : [];
                return $t;
            }, $tokens),
        ]);
    }

    /**
     * POST /api/v1/auth/tokens/{id}/revoke
     *
     * Revoke a specific API token by ID.
     * Requires: Bearer Token
     */
    public function revokeToken(string $id): void
    {
        $this->validateBearerToken(['write']);

        $affected = Database::execute(
            "UPDATE api_tokens SET revoked_at = NOW()
             WHERE id = ? AND user_id = ? AND revoked_at IS NULL",
            [(int) $id, $this->authenticatedUserId]
        );

        if ($affected === 0) {
            $this->jsonError('Token not found or already revoked', 404);
        }

        $this->jsonResponse([], 'Token revoked');
    }

    /**
     * POST /api/v1/auth/tokens/revoke-all
     *
     * Revoke all API tokens for the authenticated user.
     * Requires: Bearer Token
     */
    public function revokeAllTokens(): void
    {
        $this->validateBearerToken(['write']);

        Database::execute(
            "UPDATE api_tokens SET revoked_at = NOW()
             WHERE user_id = ? AND revoked_at IS NULL",
            [$this->authenticatedUserId]
        );

        AuditLog::log(
            $this->authenticatedUserId,
            'api.revoke_all_tokens',
            'api_token',
            null,
            null,
            ['action' => 'all_tokens_revoked']
        );

        $this->jsonResponse([], 'All tokens revoked');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Generate a cryptographically secure random token string.
     */
    private function generateTokenString(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    /**
     * Retrieve the current token record from the DB.
     * Uses the token_hash derived from the Authorization header.
     */
    private function getCurrentTokenRecord(): ?array
    {
        $token = $this->extractBearerToken();
        if (!$token) {
            return null;
        }

        $tokenHash = hash('sha256', $token);

        return Database::fetch(
            "SELECT * FROM api_tokens WHERE token_hash = ? AND user_id = ?",
            [$tokenHash, $this->authenticatedUserId]
        );
    }
}
