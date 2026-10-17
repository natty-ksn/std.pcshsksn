<?php $pageTitle = 'PDPA Consent'; ?>
<div class="auth-page">
    <div class="auth-card" style="max-width: 560px;">
        <div class="auth-logo">
            <div class="auth-logo-icon">🔒</div>
            <h1>ความยินยอมข้อมูลส่วนบุคคล</h1>
            <p>ตาม พ.ร.บ.คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)</p>
        </div>

        <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-error">❌
                <?= e(Session::getFlash('error')) ?>
            </div>
        <?php endif; ?>

        <div
            style="background: var(--gray-50); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px; max-height: 300px; overflow-y: auto; font-size: 0.85rem; line-height: 1.8; color: var(--gray-600);">
            <h3 style="margin-bottom: 12px; color: var(--gray-800);">📋 นโยบายความเป็นส่วนตัว</h3>

            <p><strong>โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร</strong> ขอแจ้งให้ทราบว่าระบบ e-Portfolio
                จะมีการเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลของท่าน ดังนี้:</p>

            <p><strong>1. ข้อมูลที่เก็บรวบรวม:</strong></p>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li>ข้อมูลส่วนตัว: ชื่อ-นามสกุล, รหัสนักเรียน, ชั้นเรียน</li>
                <li>ข้อมูลการศึกษา: ผลการเรียน, ผลงาน, เกียรติบัตร</li>
                <li>ข้อมูลความสนใจ: ความถนัด, เป้าหมายการศึกษาต่อ</li>
                <li>ไฟล์เอกสาร: รูปภาพ, เอกสาร PDF, ไฟล์ผลงาน</li>
            </ul>

            <p><strong>2. วัตถุประสงค์:</strong></p>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li>จัดทำแฟ้มสะสมผลงาน (Portfolio) สำหรับการศึกษาต่อ</li>
                <li>วิเคราะห์และแนะแนวทางการเรียนด้วย AI</li>
                <li>รายงานต่อผู้ปกครองและครูที่ปรึกษา</li>
            </ul>

            <p><strong>3. สิทธิของเจ้าของข้อมูล:</strong></p>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li>สิทธิในการเข้าถึง แก้ไข และลบข้อมูลของตน</li>
                <li>สิทธิในการถอนความยินยอมได้ทุกเมื่อ</li>
                <li>สิทธิในการร้องเรียนต่อคณะกรรมการคุ้มครองข้อมูลส่วนบุคคล</li>
            </ul>

            <p><strong>4. ระยะเวลาจัดเก็บ:</strong> เก็บข้อมูลตลอดระยะเวลาที่ศึกษาอยู่ และหลังจบการศึกษาอีก 5 ปี</p>

            <p><strong>5. การรักษาความปลอดภัย:</strong> ข้อมูลถูกเข้ารหัสและจัดเก็บอย่างปลอดภัยบนเซิร์ฟเวอร์ของโรงเรียน
            </p>
        </div>

        <form method="POST" action="<?= url('consent') ?>">
            <?= CSRF::field() ?>

            <label
                style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 16px; border: 2px solid var(--gray-200); border-radius: var(--radius-md); transition: var(--transition);"
                onmouseover="this.style.borderColor='var(--primary-300)'"
                onmouseout="this.style.borderColor='var(--gray-200)'">
                <input type="checkbox" name="consent_agree" value="1" id="consentCheck" required
                    style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary-600);">
                <span style="font-size: 0.88rem; color: var(--gray-700); line-height: 1.6;">
                    ข้าพเจ้าได้อ่านและเข้าใจนโยบายความเป็นส่วนตัวแล้ว ยินยอมให้โรงเรียนเก็บรวบรวม ใช้
                    และเปิดเผยข้อมูลส่วนบุคคลตามวัตถุประสงค์ที่ระบุข้างต้น
                </span>
            </label>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <a href="<?= url('logout') ?>" class="btn btn-secondary" style="flex: 1;">
                    ❌ ไม่ยินยอม (ออกจากระบบ)
                </a>
                <button type="submit" class="btn btn-primary" style="flex: 1;" id="consentBtn" disabled>
                    ✅ ยินยอมและเข้าใช้งาน
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('consentCheck').addEventListener('change', function () {
        document.getElementById('consentBtn').disabled = !this.checked;
    });
</script>