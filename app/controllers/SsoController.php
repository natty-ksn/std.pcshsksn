<?php
/**
 * Single Sign-On Controller (Hybrid Auth with Google Workspace)
 * Optimized for Schema Resilience & Multi-Version Compatibility
 */

class SsoController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        $clientId = Env::get('GOOGLE_CLIENT_ID');
        $redirectUri = Env::get('GOOGLE_REDIRECT_URI');

        if (empty($clientId) || empty($redirectUri)) {
            App::log('Google SSO requested but GOOGLE_CLIENT_ID / GOOGLE_REDIRECT_URI is not configured', 'WARNING');
            Session::flash('error', 'ระบบเข้าสู่ระบบด้วย Google ยังไม่เปิดให้บริการ กรุณาเข้าสู่ระบบด้วยชื่อผู้ใช้และรหัสผ่าน');
            App::redirect('login');
            return;
        }

        $state = bin2hex(random_bytes(16));
        Session::set('oauth2state', $state);

        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth"
            . "?client_id=" . urlencode($clientId)
            . "&redirect_uri=" . urlencode($redirectUri)
            . "&response_type=code"
            . "&scope=email%20profile"
            . "&access_type=online"
            . "&state=" . $state
            . "&hd=pcshsksn.ac.th";

        header("Location: $authUrl");
        exit;
    }

    /**
     * OAuth Callback
     */
    public function callback()
    {
        if (isset($_GET['error'])) {
            Session::flash('error', 'การล็อกอินถูกยกเลิก: ' . htmlspecialchars($_GET['error']));
            App::redirect('login');
        }

        $state = $_GET['state'] ?? '';
        if (empty($state) || $state !== Session::get('oauth2state')) {
            Session::flash('error', 'เซสชั่นตรวจสอบสิทธิ์หมดอายุ กรุณาลองใหม่');
            App::redirect('login');
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            Session::flash('error', 'ไม่ได้รับข้อมูลยืนยันจาก Google');
            App::redirect('login');
        }

        $tokenData = $this->getAccessToken($code);
        if (!$tokenData || empty($tokenData['access_token'])) {
            Session::flash('error', 'ไม่สามารถเชื่อมต่อระบบยืนยันตัวตนได้');
            App::redirect('login');
        }

        $userInfo = $this->getUserProfile($tokenData['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            Session::flash('error', 'ไม่สามารถดึงข้อมูลอีเมลได้');
            App::redirect('login');
        }

        if (!str_ends_with(strtolower($userInfo['email']), '@pcshsksn.ac.th')) {
            Session::flash('error', 'กรุณาใช้อีเมลโรงเรียน (@pcshsksn.ac.th) เท่านั้น');
            App::redirect('login');
        }

        $this->processLogin($userInfo);
    }

    /**
     * Handle Login & Data Sync (Robust Schema-Agnostic Version)
     */
    private function processLogin(array $googleUser)
    {
        $email = $googleUser['email'];
        $name = $googleUser['name'] ?? 'ผู้ใช้ Google SSO';
        $picture = $googleUser['picture'] ?? '';

        try {
            // 1. หาผู้ใช้เดิม
            $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

            if ($user) {
                // ผู้ใช้เดิม: พยายาม Sync ชื่อและรูปจาก Google (ห้ามพังเด็ดขาดแม้ไม่มีคอลัมน์)
                try {
                    Database::execute(
                        "UPDATE users SET full_name = COALESCE(full_name, ?), avatar_path = COALESCE(avatar_path, ?) WHERE id = ?",
                        [$name, $picture, $user['id']]
                    );
                } catch (\Exception $e) {
                    App::log("Silent Fail: User table sync skipped - " . $e->getMessage(), 'WARNING');
                }
                
                $this->createSession($user);
                Session::flash('success', 'เข้าสู่ระบบสำเร็จผ่านบัญชี Google');
                App::redirect('dashboard');
                return;
            }

            // 2. ผู้ใช้ใหม่: สถาปนาบัญชี (Failsafe for Schema)
            $username = explode('@', $email)[0];
            $hashedPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
            
            Database::beginTransaction();
            try {
                // Try Full Insert (New Schema)
                $newUserId = Database::insert(
                    "INSERT INTO users (username, email, password, role, full_name, avatar_path) VALUES (?, ?, ?, 'student', ?, ?)",
                    [$username, $email, $hashedPassword, $name, $picture]
                );
            } catch (\Exception $e) {
                // Fallback to Basic Insert (Old Schema)
                $newUserId = Database::insert(
                    "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')",
                    [$username, $email, $hashedPassword]
                );
            }

            // 3. Create Basic Student Profile
            Database::execute(
                "INSERT INTO student_profiles (user_id, student_code, full_name, class_year) VALUES (?, ?, ?, 1)",
                [$newUserId, $username, $name]
            );

            Database::commit();

            $newUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$newUserId]);
            $this->createSession($newUser);
            Session::flash('success', 'ยินดีต้อนรับสู่ e-Portfolio!');
            App::redirect('dashboard');

        } catch (\Exception $e) {
            if (Database::inTransaction()) Database::rollback();
            App::log("SSO Critical Failure: " . $e->getMessage(), 'ERROR');
            Session::flash('error', 'ระบบล็อกอินขัดข้อง: ' . $e->getMessage());
            App::redirect('login');
        }
    }

    private function createSession($user)
    {
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_username', $user['username']);
        Session::set('user_email', $user['email']);
        
        Auth::sync();
        session_regenerate_id(true);

        Database::execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        try {
            Database::insert(
                "INSERT INTO audit_logs (user_id, action, entity_type, ip_address, created_at) VALUES (?, 'login_sso', 'user', ?, NOW())",
                [$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']
            );
        } catch (\Exception $e) {}
    }

    private function getAccessToken($code)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => Env::get('GOOGLE_CLIENT_ID'),
            'client_secret' => Env::get('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => Env::get('GOOGLE_REDIRECT_URI'),
            'grant_type' => 'authorization_code',
            'code' => $code
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function getUserProfile($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v2/userinfo");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
