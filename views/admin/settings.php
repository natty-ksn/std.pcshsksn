<?php $pageTitle = 'ตั้งค่าระบบ'; ?>

<div class="admin-settings">
    <div class="page-header">
        <div>
            <h1 class="page-title">ตั้งค่าระบบ (System Settings)</h1>
            <p class="page-subtitle">กำหนดค่าพื้นฐานและนโยบายการใช้งานของแอปพลิเคชัน</p>
        </div>
    </div>

    <div class="grid grid-3" style="gap: 24px; align-items: start;">
        <div class="card col-span-2">
            <form action="<?= url('admin/settings/update') ?>" method="POST">
                <?= CSRF::field() ?>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <?php foreach ($settings as $s): ?>
                            <div class="form-group">
                                <label class="form-label" style="display: flex; justify-content: space-between;">
                                    <span>
                                        <?= e($s['description']) ?>
                                    </span>
                                    <code
                                        style="font-size: 0.7rem; color: var(--gray-400);"><?= e($s['setting_key']) ?></code>
                                </label>

                                <?php if ($s['setting_key'] === 'allow_sharing'): ?>
                                    <select name="config[<?= e($s['setting_key']) ?>]" class="form-control">
                                        <option value="1" <?= $s['setting_value'] == '1' ? 'selected' : '' ?>>✅ เปิดใช้งาน (Public)
                                        </option>
                                        <option value="0" <?= $s['setting_value'] == '0' ? 'selected' : '' ?>>🔒 ปิดใช้งาน (Private
                                            Only)</option>
                                    </select>
                                <?php elseif (preg_match('/(key|secret|token|password)/i', $s['setting_key'])): ?>
                                    <div style="display: flex; gap: 8px;">
                                        <input type="password" id="setting_<?= e($s['setting_key']) ?>" name="config[<?= e($s['setting_key']) ?>]"
                                            value="<?= e($s['setting_value']) ?>" class="form-control" placeholder="ระบุ API Key หรือปล่อยว่างหากใช้ค่าจาก .env">
                                        <button type="button" class="btn btn-outline btn-sm" style="white-space: nowrap;"
                                            onclick="const el = document.getElementById('setting_<?= e($s['setting_key']) ?>'); el.type = el.type === 'password' ? 'text' : 'password';">
                                            👁️ แสดง/ซ่อน
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <input type="text" name="config[<?= e($s['setting_key']) ?>]"
                                        value="<?= e($s['setting_value']) ?>" class="form-control" required>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer"
                    style="padding: 20px; border-top: 1px solid var(--gray-100); text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        💾 บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ℹ️ ข้อมูลระบบ</h3>
            </div>
            <div class="card-body" style="font-size: 0.875rem; line-height: 1.6;">
                <p><strong>PHP Version:</strong>
                    <?= phpversion() ?>
                </p>
                <p><strong>Database:</strong> MariaDB / MySQL</p>
                <p><strong>App Version:</strong> 2.0.0 (Production Ready)</p>
                <p><strong>Timezone:</strong>
                    <?= date_default_timezone_get() ?>
                </p>
                <hr style="margin: 16px 0; border: none; border-top: 1px solid var(--gray-100);">
                <p class="text-gray-500">
                    ค่าที่ตั้งในหน้านี้จะส่งผลต่อการคำนวณปีการศึกษา และการเข้าถึงหน้า Portfolio สาธารณะของนักเรียนทุกคน
                </p>
                <hr style="margin: 16px 0; border: none; border-top: 1px solid var(--gray-100);">
                <div style="background: var(--primary-50, #eff6ff); padding: 12px; border-radius: 8px; font-size: 0.8rem;">
                    <strong>🤖 รองรับคีย์ AI ฟรีหลายค่ายอัตโนมัติ (วางในช่อง <code>gemini_api_key</code> ได้เลย):</strong>
                    <ul style="margin: 6px 0 0 16px; padding: 0;">
                        <li><strong>Groq (ฟรี 100% ไม่ต้องผูกบัตร):</strong> คีย์ขึ้นต้น <code>gsk_...</code> จาก <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com/keys</a></li>
                        <li><strong>OpenRouter (ฟรี ไม่ต้องผูกบัตร):</strong> คีย์ขึ้นต้น <code>sk-or-...</code> จาก <a href="https://openrouter.ai/settings/keys" target="_blank" rel="noopener">openrouter.ai/settings/keys</a></li>
                        <li><strong>Cerebras (ฟรี ไม่ต้องผูกบัตร):</strong> คีย์ขึ้นต้น <code>csk-...</code> จาก <a href="https://cloud.cerebras.ai/" target="_blank" rel="noopener">cloud.cerebras.ai</a></li>
                        <li><strong>Google AI Studio:</strong> คีย์ขึ้นต้น <code>AIza...</code> (ต้องใช้บัญชี <code>@gmail.com</code> ส่วนตัว)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-settings .form-label {
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--gray-700);
    }

    .col-span-2 {
        grid-column: span 2;
    }

    @media (max-width: 992px) {
        .col-span-2 {
            grid-column: span 3;
        }
    }
</style>