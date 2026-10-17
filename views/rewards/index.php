<?php $pageTitle = 'ศูนย์แลกของรางวัล - PCSHS KSN Rewards'; ?>

<!-- Header -->
<div class="animate-fade-in mb-5" style="border-bottom: 2px solid var(--gray-900); padding-bottom: 30px;">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-4">
        <div>
            <h1
                style="font-size: 3rem; font-weight: 900; line-height: 1; margin-bottom: 15px; color: var(--gray-900);">
                🎁 ศูนย์แลกของรางวัล <span style="color: var(--primary-600); font-size: 2rem;">(Rewards)</span>
            </h1>
            <p style="font-size: 1.05rem; color: var(--gray-600); max-width: 600px; font-weight: 500; line-height: 1.6;">
                นำคะแนนสะสมที่ได้จากการบันทึกผลงานและกิจกรรมทางวิชาการ มาแลกรับของรางวัลและสิทธิประโยชน์ต่างๆ
            </p>
        </div>

        <div class="glass-card"
            style="padding: 25px 40px; border-radius: 24px; background: var(--gray-900); color: white; border: none; text-align: center; min-width: 250px; box-shadow: var(--shadow-xl);">
            <div
                style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.7; margin-bottom: 5px;">
                คะแนนสะสมของคุณ</div>
            <div style="font-size: 2.8rem; font-weight: 900; color: var(--rank-gold); line-height: 1;">
                <?= number_format($userPoints) ?>
                <span style="font-size: 1rem; color: white;"> คะแนน</span>
            </div>
            <div style="font-size: 0.75rem; margin-top: 10px; color: var(--primary-300);">⭐
                สะสมเพิ่มได้จากการบันทึกผลงาน</div>
        </div>
        <a href="<?= url('rewards/badges') ?>" class="btn btn-lg"
            style="border-radius: 24px; padding: 25px 40px; font-weight: 800; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; border: 2px solid #f59e0b; text-decoration: none; display: block; text-align: center; box-shadow: var(--shadow-xl);">
            🏅 ทำเนียบเหรียญรางวัล<br><span style="font-size: 0.8rem; font-weight: 600;">(Badge Collection)</span>
        </a>
    </div>
</div>

<div class="grid-2" style="grid-template-columns: 1fr 350px; gap: 50px;">
    <!-- Left: Showcase -->
    <div>
        <!-- Filter Tabs -->
        <div class="d-flex gap-3 mb-5 overflow-auto pb-2" style="white-space: nowrap;">
            <a href="<?= url('rewards') ?>" class="btn <?= !$selectedCategory ? 'btn-primary' : 'btn-outline' ?>"
                style="border-radius: var(--radius-full); padding: 10px 25px; font-weight: 700;">ทั้งหมด</a>
            <a href="<?= url('rewards?category=digital') ?>"
                class="btn <?= $selectedCategory == 'digital' ? 'btn-primary' : 'btn-outline' ?>"
                style="border-radius: var(--radius-full); padding: 10px 25px; font-weight: 700;">ไอเทมดิจิทัล</a>
            <a href="<?= url('rewards?category=academic') ?>"
                class="btn <?= $selectedCategory == 'academic' ? 'btn-primary' : 'btn-outline' ?>"
                style="border-radius: var(--radius-full); padding: 10px 25px; font-weight: 700;">สิทธิประโยชน์ทางวิชาการ</a>
            <a href="<?= url('rewards?category=privilege') ?>"
                class="btn <?= $selectedCategory == 'privilege' ? 'btn-primary' : 'btn-outline' ?>"
                style="border-radius: var(--radius-full); padding: 10px 25px; font-weight: 700;">สิทธิพิเศษ</a>
        </div>

        <div class="portfolio-grid animate-fade-in"
            style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
            <?php foreach ($rewards as $reward): ?>
                <?php $canRedeem = $userPoints >= $reward['points_required']; ?>
                <div class="portfolio-card glass-card <?= !$canRedeem ? 'locked' : '' ?>"
                    style="border: none; overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s ease; <?= !$canRedeem ? 'opacity: 0.8;' : '' ?>">
                    <div
                        style="height: 180px; background: linear-gradient(135deg, var(--gray-100), var(--gray-50)); display: flex; align-items: center; justify-content: center; position: relative;">
                        <span style="font-size: 5rem; z-index: 1;">🎁</span>
                        <div
                            style="position: absolute; top: 15px; right: 15px; background: <?= $canRedeem ? 'var(--primary-600)' : 'var(--gray-400)' ?>; color: white; padding: 4px 15px; border-radius: var(--radius-full); font-size: 0.9rem; font-weight: 900;">
                            <?= number_format($reward['points_required']) ?> คะแนน
                        </div>
                    </div>

                    <div class="p-4" style="flex-grow: 1; display: flex; flex-direction: column;">
                        <span
                            style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-600); margin-bottom: 5px;">
                            <?= strtoupper($reward['category']) ?>
                        </span>
                        <h3
                            style="font-size: 1.2rem; font-weight: 800; line-height: 1.3; margin-bottom: 10px; color: var(--gray-900);">
                            <?= e($reward['title']) ?>
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.5; margin-bottom: 20px;">
                            <?= e($reward['description']) ?>
                        </p>

                        <div style="margin-top: auto;">
                            <?php if ($canRedeem): ?>
                                <form method="POST" action="<?= url('rewards/redeem/' . $reward['id']) ?>"
                                    onsubmit="if(!confirm('ยืนยันการแลกรับรางวัลนี้?')) return false; var b=this.querySelector('button[type=submit]'); if(b){b.disabled=true; b.innerText='⏳ กำลังดำเนินการ...';}">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-primary w-100"
                                        style="border-radius: var(--radius-md); font-weight: 800; letter-spacing: 0.5px; padding: 12px;">แลกรับรางวัล</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 disabled"
                                    style="border-radius: var(--radius-md); font-weight: 800; letter-spacing: 0.5px; padding: 12px; cursor: not-allowed; background: var(--gray-200); color: var(--gray-400); border:none;">คะแนนสะสมไม่เพียงพอ</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: History -->
    <div class="animate-fade-in" style="animation-delay: 0.3s;">
        <div class="card glass-card p-4" style="padding: 35px !important; border-radius: 30px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 25px;">ประวัติการแลกของรางวัล</h2>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php if (empty($myRedemptions)): ?>
                    <div style="text-align: center; padding: 40px 0; color: var(--gray-400);">
                        <div style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.3;">📦</div>
                        <p style="font-size: 0.9rem;">ยังไม่มีประวัติการแลกของรางวัล<br>สะสมคะแนนจากผลงานเพื่อนำมาแลกได้เลย</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($myRedemptions as $red): ?>
                        <div
                            style="display: flex; gap: 15px; align-items: center; padding-bottom: 15px; border-bottom: 1px solid var(--gray-100);">
                            <div
                                style="width: 50px; height: 50px; background: var(--primary-50); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                📦
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.9rem;"><?= e($red['title']) ?></div>
                                <div style="font-size: 0.7rem; color: var(--gray-400);"><?= timeAgo($red['redeemed_at']) ?>
                                </div>
                            </div>
                            <div>
                                <span class="badge"
                                    style="background: <?= $red['status'] == 'completed' ? '#dcfce7' : ($red['status'] == 'pending' ? '#fef3c7' : '#fee2e2') ?>; color: <?= $red['status'] == 'completed' ? '#166534' : ($red['status'] == 'pending' ? '#92400e' : '#991b1b') ?>; font-size: 0.65rem; border-radius: 50px;">
                                    <?= $red['status'] == 'completed' ? 'รับแล้ว' : ($red['status'] == 'pending' ? 'รอดำเนินการ' : 'ยกเลิก') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div
                style="margin-top: 30px; padding: 20px; background: var(--primary-50); border-radius: 15px; font-size: 0.8rem; color: var(--primary-700); line-height: 1.5;">
                <strong>💡 ข้อแนะนำ:</strong> ผลงานที่ผ่านการตรวจรับรองและมีคะแนนประเมินสูง จะได้รับคะแนนสะสมพิเศษเพิ่มเติม
            </div>
        </div>
    </div>
</div>

<style>
    .locked {
        filter: grayscale(0.5);
    }

    .portfolio-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-2xl) !important;
    }
</style>