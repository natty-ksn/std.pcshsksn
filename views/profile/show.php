<?php $pageTitle = 'โปรไฟล์'; ?>

<div style="max-width: 900px; margin: 0 auto;">
    <!-- Profile Header Card -->
    <div class="card mb-3" style="overflow: visible;">
        <div
            style="height: 160px; background: linear-gradient(135deg, #4338ca, #6366f1, #818cf8); border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
        </div>
        <div class="card-body" style="position: relative; padding-top: 60px;">
            <!-- Avatar -->
            <div style="position: absolute; top: -50px; left: 32px;">
                <img src="<?= $profile['avatar_path'] ? uploadUrl($profile['avatar_path']) : defaultAvatar($profile['email'] ?? '', $profile['full_name'] ?? '') ?>"
                    alt="avatar"
                    style="width: 100px; height: 100px; border-radius: var(--radius-full); border: 4px solid white; object-fit: cover; box-shadow: var(--shadow-lg);">
            </div>
            <div style="position: absolute; top: -40px; right: 32px; display: flex; gap: 8px; flex-wrap: wrap;">
                <?php if ($profile['role'] === 'student' && !empty($profile['student_code'])): ?>
                    <a href="<?= url('p/' . $profile['student_code']) ?>" target="_blank" class="btn btn-sm"
                        style="background: rgba(255,255,255,0.95); color: var(--primary-700); font-weight: 600;">🔗 พอร์ตสาธารณะ (Public Showcase)</a>
                <?php endif; ?>
                <a href="<?= url('profile/edit') ?>" class="btn btn-sm"
                    style="background: rgba(255,255,255,0.9); color: var(--primary-700);">✏️ แก้ไข</a>
            </div>

            <div class="flex-between" style="flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="margin-bottom: 4px;">
                        <?= e($profile['full_name']) ?>
                    </h2>
                    <p class="text-muted">
                        <?php if ($profile['role'] === 'student'): ?>
                            <?= classYearLabel($profile['class_year']) ?>/<?= e($profile['class_room']) ?> — รหัส <?= e($profile['student_code']) ?>
                        <?php else: ?>
                            <span class="badge" style="background: var(--primary-100); color: var(--primary-700); font-weight: 600;">
                                <?= e($profile['position'] ?: roleLabel($profile['role'])) ?>
                            </span>
                        <?php endif; ?>
                    </p>
                    <?php if ($profile['bio']): ?>
                        <p style="margin-top: 12px; color: var(--gray-600); font-size: 0.9rem;">
                            <?= nl2br(e($profile['bio'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($profile['role'] === 'student'): ?>
            <!-- Profile Completeness -->
            <div style="margin-top: 20px; padding: 16px; background: var(--gray-50); border-radius: var(--radius-md);">
                <div class="flex-between mb-1">
                    <span style="font-size: 0.85rem; font-weight: 600;">โปรไฟล์สมบูรณ์
                        <?= $completeness ?>%
                    </span>
                    <?php if ($completeness === 100): ?>
                        <span class="badge-item"><span class="badge-item-icon">✅</span> สมบูรณ์แล้ว!</span>
                    <?php endif; ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?= $completeness ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="<?= ($profile['role'] === 'student') ? 'grid-2' : 'grid-1' ?>">
        <?php if ($profile['role'] === 'student'): ?>
        <!-- Quick Stats -->
        <div class="card slide-up">
            <div class="card-header">
                <h3>📊 สถิติ</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div
                        style="text-align: center; padding: 16px; background: var(--primary-50); border-radius: var(--radius-md);">
                        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary-600);">
                            <?= $portfolioCount ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--gray-500);">ผลงานในระบบ</div>
                    </div>
                    <div
                        style="text-align: center; padding: 16px; background: #ecfdf5; border-radius: var(--radius-md);">
                        <div style="font-size: 1.8rem; font-weight: 800; color: var(--accent-600);">
                            <?= number_format($gpa, 2) ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--gray-500);">GPA</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Personal Info -->
        <div class="card slide-up">
            <div class="card-header">
                <h3>👤 ข้อมูลส่วนตัว</h3>
            </div>
            <div class="card-body">
                <table>
                    <tr>
                        <td style="font-weight: 600; width: 120px;">ชื่อเล่น</td>
                        <td>
                            <?= e($profile['nickname'] ?: '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">วันเกิด</td>
                        <td>
                            <?= $profile['date_of_birth'] ? thaiDate($profile['date_of_birth'], 'long') : '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">โทรศัพท์</td>
                        <td>
                            <?= e($profile['phone'] ?: '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Email</td>
                        <td>
                            <?= e($profile['email'] ?: '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">สมัครเมื่อ</td>
                        <td>
                            <?= thaiDate($profile['joined_at'], 'long') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Interests (Student Only) -->
    <?php if ($profile['role'] === 'student'): ?>
        <div class="card mt-3 slide-up">
            <div class="card-header">
                <h3>💡 ความสนใจและความถนัด</h3>
                <a href="<?= url('interests') ?>" class="btn btn-sm btn-outline">จัดการ</a>
            </div>
            <div class="card-body">
                <?php if (!empty($interests)): ?>
                    <div class="tags-container">
                        <?php foreach ($interests as $item): ?>
                            <span class="tag" style="padding: 6px 14px; font-size: 0.85rem;">
                                <?= $item['type'] === 'talent' ? '⭐' : ($item['type'] === 'hobby' ? '🎮' : '💡') ?>
                                <?= e($item['item']) ?>
                                <span style="opacity: 0.5; margin-left: 4px;">(
                                    <?= $item['proficiency'] ?>/5)
                                </span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="margin: 0;">ยังไม่ได้ระบุความสนใจและความถนัด <a href="<?= url('interests') ?>">คลิกเพื่อเพิ่มข้อมูล</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Academic Goals -->
        <div class="card mt-3 slide-up">
            <div class="card-header">
                <h3>🎯 เป้าหมายการศึกษาต่อ (TCAS Goals)</h3>
                <a href="<?= url('goals') ?>" class="btn btn-sm btn-outline">จัดการเป้าหมาย</a>
            </div>
            <div class="card-body">
                <?php if (!empty($goals)): ?>
                    <div style="display: grid; gap: 10px;">
                        <?php foreach ($goals as $goal): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--gray-50); border-radius: var(--radius-md);">
                                <div>
                                    <strong style="color: var(--gray-800);">อันดับ <?= (int)($goal['priority'] ?? 1) ?>: <?= e($goal['faculty'] ?? '') ?> <?= !empty($goal['major']) ? '— ' . e($goal['major']) : '' ?></strong>
                                    <div style="font-size: 0.85rem; color: var(--gray-500);"><?= e($goal['university'] ?? '') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="margin: 0;">ยังไม่ได้ตั้งเป้าหมายคณะ/มหาวิทยาลัย <a href="<?= url('goals') ?>">คลิกเพื่อตั้งเป้าหมาย</a></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Badges -->
    <?php if ($profile['role'] === 'student' && !empty($badges)): ?>
        <div class="card mt-3 slide-up">
            <div class="card-header">
                <h3>🏅 เหรียญตรา (Badges)</h3>
                <a href="<?= url('rewards/badges') ?>" class="btn btn-sm btn-outline">ดูทั้งหมด</a>
            </div>
            <div class="card-body">
                <div class="badges-grid">
                    <?php foreach ($badges as $badge): ?>
                        <div class="badge-item">
                            <span class="badge-item-icon">
                                <?= e($badge['icon']) ?>
                            </span>
                            <span>
                                <?= e($badge['name']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>