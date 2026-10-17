<?php $pageTitle = 'Opportunity Scout — ค้นหาโอกาสที่ใช่'; ?>

<div class="mb-3">
    <div class="flex-between">
        <div>
            <h2>🔍 Opportunity Scout</h2>
            <p style="color: var(--gray-500);">AI ช่วยค้นหาค่าย การแข่งขัน และโอกาสที่ตรงกับเป้าหมายของคุณ</p>
        </div>
        <a href="<?= url('dashboard') ?>" class="btn btn-secondary">← กลับแดชบอร์ด</a>
    </div>
</div>

<div class="card mb-3" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border: none; overflow: hidden; position: relative;">
    <div class="card-body" style="padding: 32px; z-index: 1; position: relative;">
        <h3 style="color: white; margin-bottom: 12px;">แมตช์ที่ใช่... เพื่ออนาคตที่คุณเลือก! 🚀</h3>
        <p style="opacity: 0.9; max-width: 600px;">
            เราคัดเลือกค่ายวิชาการและการแข่งขันที่สอดคล้องกับ <strong>เป้าหมายคณะ/อาชีพ</strong> ที่คุณตั้งไว้ 
            ลองดูสิว่ามีกิจกรรมไหนที่น่าสนใจบ้างเพื่อนำมาเสริม Portfolio ของคุณ
        </p>
    </div>
    <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.1; transform: rotate(-15deg);">🔭</div>
</div>

<div class="grid-3">
    <?php foreach($matches as $opp): ?>
        <div class="card slide-up hover-glow">
            <div class="card-body" style="padding: 24px;">
                <div class="flex-between mb-3">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary-600); background: var(--primary-50); padding: 4px 12px; border-radius: 50px;">
                        # <?= e($opp['tag']) ?>
                    </span>
                    <span style="font-size: 1.2rem;">
                        <?= $opp['type'] === 'camp' ? '⛺' : ($opp['type'] === 'contest' ? '🏆' : '🤝') ?>
                    </span>
                </div>
                <h4 style="margin-bottom: 8px; font-size: 1.1rem; line-height: 1.4; color: var(--gray-800);"><?= e($opp['title']) ?></h4>
                <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 16px;">
                    📍 <?= e($opp['provider']) ?>
                </p>
                <div style="border-top: 1px solid var(--gray-100); padding-top: 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.8rem; color: var(--success-600); font-weight: 600;">
                        ✨ AI แนะนำพิเศษ
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="alert('ระบบกำลังพาคุณไปยังหน้าสมัครกิจกรรม...')">ดูรายละเอียด</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mt-3" style="border: 1px dashed var(--gray-300); background: var(--gray-50);">
    <div class="card-body text-center" style="padding: 40px;">
        <div style="font-size: 2rem; margin-bottom: 15px;">🔍</div>
        <h4 style="color: var(--gray-700);">ไม่พบกิจกรรมที่ถูกใจ?</h4>
        <p style="color: var(--gray-500); margin-bottom: 20px;">ลองอัปเดต "เป้าหมาย" ของคุณในเมนูเป้าหมาย เพื่อให้ AI ค้นหากิจกรรมที่ตรงใจคุณมากขึ้น</p>
        <a href="<?= url('goals') ?>" class="btn btn-outline">ไปที่หน้าเป้าหมาย</a>
    </div>
</div>
