<?php
/**
 * Authentication Manager
 * จัดการ Login/Logout, Role-Based Access Control (RBAC)
 */
class Auth
{
    /**
     * Attempt login (Robust Version)
     */
    public static function attempt(string $username, string $password): bool
    {
        // 1. ดึงข้อมูลหลักจากตาราง users (ต้องทำงานได้เสมอ)
        $user = Database::fetch(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            App::log("Failed login attempt for: {$username}", 'WARNING');
            return false;
        }

        // 2. พยายามดึงข้อมูลโปรไฟล์ (ถ้าล้มเหลวให้ใช้ค่าว่าง — ป้องกัน 500 Error)
        $studentProfile = [];
        try {
            $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [$user['id']]);
            if ($profile) {
                $studentProfile = $profile;
            }
        } catch (Exception $e) {
            App::log("Profile fetch failed (Schema mismatch?): " . $e->getMessage(), 'WARNING');
        }

        // 3. บันทึกข้อมูลใน Session แบบปลอดภัย
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_username', $user['username']);
        Session::set('user_email', $user['email']);
        Session::set('user_position', $user['position'] ?? '');
        Session::set('user_exp', $user['exp'] ?? 0);
        Session::set('user_level', $user['level'] ?? 1);
        Session::set('user_points', $user['points'] ?? 0);
        
        // Priority: Profile Name > User Full Name > Username
        $displayName = ($studentProfile['full_name'] ?? '') ?: ($user['full_name'] ?: $user['username']);
        Session::set('user_name', $displayName);
        
        // Avatar Fallback
        $avatarPath = ($studentProfile['avatar_path'] ?? '') ?: ($user['avatar_path'] ?? '');
        Session::set('user_avatar', $avatarPath);

        // Optional Data
        Session::set('student_code', $studentProfile['student_code'] ?? '');
        Session::set('class_year', $studentProfile['class_year'] ?? '');
        Session::set('class_room', $studentProfile['class_room'] ?? '');
        Session::set('_last_auth_sync', time());

        session_regenerate_id(true);
        self::logAction('login', 'user', $user['id']);

        Database::execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

        App::log("User logged in: {$user['username']} (Role: {$user['role']})", 'INFO');
        return true;
    }

    /**
     * Logout
     */
    public static function logout(): void
    {
        $userId = self::id();
        if ($userId) {
            self::logAction('logout', 'user', $userId);
        }
        Session::destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Get current user role
     */
    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    /**
     * Get current user name
     */
    public static function name(): string
    {
        return Session::get('user_name', 'ผู้ใช้');
    }

    /**
     * Check if user has specific role (accepts string or array of roles)
     */
    public static function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return self::hasAnyRole($role);
        }
        return Session::get('user_role') === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        return in_array(Session::get('user_role'), $roles);
    }

    /**
     * Force specific role or roles, or redirect
     */
    public static function requireRole(...$roles): void
    {
        if (!self::check() || !self::hasAnyRole($roles)) {
            Session::flash('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            App::redirect('dashboard');
        }
    }

    /**
     * Get full user data from session
     */
    public static function user(): array
    {
        return [
            'id' => Session::get('user_id'),
            'role' => Session::get('user_role'),
            'username' => Session::get('user_username'),
            'name' => Session::get('user_name'),
            'email' => Session::get('user_email'),
            'position' => Session::get('user_position', ''),
            'avatar' => Session::get('user_avatar'),
            'exp' => Session::get('user_exp', 0),
            'level' => Session::get('user_level', 1),
            'points' => Session::get('user_points', 0),
            'student_code' => Session::get('student_code'),
            'class_year' => Session::get('class_year'),
            'class_room' => Session::get('class_room'),
        ];
    }

    /**
     * Hash password (cost 10 balances strong security with high-concurrency login speed)
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * รีเฟรชข้อมูลใน Session จากฐานข้อมูล (Sync - Robust & Throttled Version)
     *
     * @param bool $force บังคับดึงข้อมูลจาก DB ทันที (เช่น หลังแก้ไขโปรไฟล์หรือได้ EXP ใหม่)
     */
    public static function sync(bool $force = false): void
    {
        $userId = self::id();
        if (!$userId) return;

        // Throttle DB sync to once every 60 seconds per user session unless forced
        $lastSync = (int) Session::get('_last_auth_sync', 0);
        if (!$force && $lastSync > 0 && (time() - $lastSync) < 60 && Session::has('user_name')) {
            return;
        }

        // 1. ดึงข้อมูลจาก users (ต้องทำงานได้เสมอ)
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) return;

        // 2. พยายามดึงข้อมูลโปรไฟล์ (เฉพาะนักเรียน หรือเมื่อจำเป็น)
        $studentProfile = [];
        if (($user['role'] ?? '') === 'student') {
            try {
                $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [$userId]);
                if ($profile) {
                    $studentProfile = $profile;
                }
            } catch (Exception $e) {
                App::log("Profile sync failed: " . $e->getMessage(), 'WARNING');
            }
        }

        // 3. ปรับปรุงข้อมูลใน Session
        $displayName = ($studentProfile['full_name'] ?? '') ?: ($user['full_name'] ?: $user['username']);
        $avatarPath = ($studentProfile['avatar_path'] ?? '') ?: ($user['avatar_path'] ?? '');
        
        Session::set('user_name', $displayName);
        Session::set('user_username', $user['username']);
        Session::set('user_position', $user['position'] ?? '');
        Session::set('user_avatar', $avatarPath);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_exp', $user['exp'] ?? 0);
        Session::set('user_level', $user['level'] ?? 1);
        Session::set('user_points', $user['points'] ?? 0);
        Session::set('_last_auth_sync', time());
        
        // Optional bits
        if (isset($studentProfile['student_code'])) Session::set('student_code', $studentProfile['student_code']);
        if (isset($studentProfile['class_year'])) Session::set('class_year', $studentProfile['class_year']);
        if (isset($studentProfile['class_room'])) Session::set('class_room', $studentProfile['class_room']);
    }

    /**
     * บันทึก Audit Log
     */
    public static function logAction(string $action, string $entityType = '', ?int $entityId = null, ?array $oldValue = null, ?array $newValue = null): void
    {
        try {
            Database::insert(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    self::id(),
                    $action,
                    $entityType,
                    $entityId,
                    $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
                    $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null,
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]
            );
        } catch (\Exception $e) {
            App::log("Audit log failed: " . $e->getMessage(), 'ERROR');
        }
    }
}
