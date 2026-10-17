<?php $pageTitle = 'เข้าสู่ระบบ'; ?>
<div class="auth-page animate-fade-in" style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
    <div class="auth-card glass-card" style="width: 100%; max-width: 420px; padding: 40px; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">
        <div class="auth-logo" style="text-align: center; margin-bottom: 28px;">
            <img src="<?= asset('images/logo.png') ?>" alt="PCSHSKSN Logo"
                onerror="this.onerror=null; this.src='https://krunat.online/logo3.png';"
                style="height: 100px; width: auto; max-width: 90px; margin-bottom: 16px; object-fit: contain;">
            <h1 style="font-size: 1.75rem; color: var(--primary-800); margin-bottom: 6px;">PCSHSKSN e-Portfolio</h1>
            <p style="color: var(--gray-600); font-size: 0.95rem; line-height: 1.5; margin: 0;">
                ระบบแฟ้มสะสมผลงานอัจฉริยะ<br>
                <strong>โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</strong>
            </p>
            <span style="display: inline-block; margin-top: 8px; font-size: 0.8rem; background: #e0e7ff; color: #3730a3; padding: 3px 12px; border-radius: 9999px; font-weight: 600;">
                สำหรับนักเรียน • ครู • ฝ่ายบริหาร
            </span>
        </div>

        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success">✅
                <?= e(Session::getFlash('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-error">❌
                <?= e(Session::getFlash('error')) ?>
            </div>
        <?php endif; ?>

        <!-- Google SSO Button -->
        <a href="<?= url('login/google') ?>" class="btn btn-outline animate-pulse" style="width: 100%; margin-bottom: 24px; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 12px; border: 2px solid var(--primary-100); border-radius: var(--radius-md); text-decoration: none; color: var(--gray-700); font-weight: 600; background: white; transition: var(--transition);">
            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo" width="22">
            เข้าสู่ระบบด้วย @pcshsksn.ac.th
        </a>

        <div style="display: flex; align-items: center; text-align: center; margin-bottom: 24px;">
            <div style="flex: 1; border-bottom: 1px solid var(--gray-200);"></div>
            <span style="padding: 0 15px; color: var(--gray-400); font-size: 0.85rem;">หรือเข้าสู่ระบบด้วยรหัสผ่าน</span>
            <div style="flex: 1; border-bottom: 1px solid var(--gray-200);"></div>
        </div>

        <form method="POST" action="<?= url('login') ?>">
            <?= CSRF::field() ?>

            <div class="form-group">
                <label for="username" class="form-label">
                    ชื่อผู้ใช้ / รหัสนักเรียน <span class="required">*</span>
                </label>
                <input type="text" id="username" name="username" class="form-control"
                    value="<?= old('username') ?>"
                    placeholder="เช่น 64001 หรือ student01@pcshsksn.ac.th" required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    รหัสผ่าน <span class="required">*</span>
                </label>
                <input type="password" id="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน"
                    required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-lg"
                style="width: 100%; margin-top: 16px; border: none; font-weight: 700; height: 52px; border-radius: var(--radius-md); letter-spacing: 1px;">
                🚀 เข้าสู่ระบบ
            </button>
        </form>

        <div style="margin-top: 24px; text-align: center; padding-top: 20px; border-top: 1px solid var(--gray-100);">
            <a href="<?= url('/') ?>" style="color: var(--gray-400); font-size: 0.85rem;">← กลับไปหน้าหลัก</a>
        </div>
    </div>
</div>