<?php
/**
 * CSRF Protection
 * ป้องกัน Cross-Site Request Forgery
 */
class CSRF
{
    /**
     * Initialize CSRF token
     */
    public static function init(): void
    {
        if (!Session::has('_csrf_token')) {
            self::regenerate();
        }
    }

    /**
     * Generate new CSRF token
     */
    public static function regenerate(): void
    {
        Session::set('_csrf_token', bin2hex(random_bytes(32)));
        Session::set('_csrf_time', time());
    }

    /**
     * Get current token
     */
    public static function token(): string
    {
        return Session::get('_csrf_token', '');
    }

    /**
     * Generate hidden input field for forms
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    /**
     * Verify CSRF token from POST request
     */
    public static function verify(): bool
    {
        $token = $_POST['_csrf_token'] ?? '';
        $sessionToken = Session::get('_csrf_token', '');
        $tokenTime = Session::get('_csrf_time', 0);

        // Token ต้องตรงกัน
        if (!hash_equals($sessionToken, $token)) {
            return false;
        }

        // Token หมดอายุ (default 1 ชั่วโมง)
        $lifetime = (int) Env::get('CSRF_TOKEN_LIFETIME', '3600');
        if (time() - $tokenTime > $lifetime) {
            self::regenerate();
            return false;
        }

        return true;
    }

    /**
     * Verify CSRF and abort if invalid
     */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::verify()) {
            App::log("CSRF Failure from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 'WARNING');
            http_response_code(403);
            Session::flash('error', 'Session หมดอายุ กรุณาลองใหม่อีกครั้ง');
            App::back();
        }
    }
}
