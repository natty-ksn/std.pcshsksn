<?php
/**
 * AuthController — จัดการ Login/Logout/PDPA Consent
 */
class AuthController extends Controller
{
    /**
     * แสดงฟอร์ม Login
     */
    public function loginForm(): void
    {
        $this->view('auth.login', [], 'auth');
    }

    /**
     * ดำเนินการ Login
     */
    public function login(): void
    {
        CSRF::check();

        $username = $this->input('username');
        $password = $_POST['password'] ?? '';

        App::log("Login attempt for user: {$username}");
        if (empty($username) || empty($password)) {
            Session::flash('error', 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
            App::redirect('login');
            return;
        }

        // Attempt login
        if (Auth::attempt($username, $password)) {
            // ตรวจสอบ PDPA Consent
            $consent = Database::fetch(
                "SELECT * FROM pdpa_consents WHERE user_id = ? AND consent_type = 'data_collection' AND is_consented = 1",
                [Auth::id()]
            );

            if (!$consent) {
                App::redirect('consent');
                return;
            }

            Session::flash('success', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . Auth::name());
            App::redirect('dashboard');
        } else {
            Session::flashInput(['username' => $username]);
            Session::flash('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
            App::redirect('login');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'ออกจากระบบแล้ว');
        App::redirect('login');
    }

    /**
     * แสดงฟอร์ม PDPA Consent
     */
    public function consentForm(): void
    {
        $this->view('auth.consent', [], 'auth');
    }

    /**
     * บันทึก PDPA Consent
     */
    public function saveConsent(): void
    {
        CSRF::check();

        $consented = $this->input('consent_agree');

        if ($consented !== '1') {
            Session::flash('error', 'กรุณายินยอมก่อนเข้าใช้งานระบบ');
            App::redirect('consent');
            return;
        }

        $consentTypes = ['data_collection', 'data_sharing', 'ai_analysis'];

        foreach ($consentTypes as $type) {
            Database::query(
                "INSERT INTO pdpa_consents (user_id, consent_type, is_consented, consented_at, ip_address)
                 VALUES (?, ?, 1, NOW(), ?)
                 ON DUPLICATE KEY UPDATE is_consented = 1, consented_at = NOW(), ip_address = ?",
                [Auth::id(), $type, $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR']]
            );
        }

        Auth::logAction('pdpa_consent', 'user', Auth::id());
        Session::flash('success', 'บันทึกความยินยอมเรียบร้อย');
        App::redirect('dashboard');
    }
}
