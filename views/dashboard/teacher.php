<?php $pageTitle = 'Dashboard ครู'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb" style="margin-bottom: 8px;">
    <span class="breadcrumb-current">🏠 แผงหน้าหลัก</span>
</nav>

<!-- Greeting -->
<div class="card mb-4"
    style="border: none; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 20px;">
    <div class="card-body p-4" style="position: relative; overflow: hidden;">
        <div
            style="position: absolute; right: -20px; top: -20px; font-size: 8rem; opacity: 0.05; transform: rotate(10deg);">
            👩‍🏫</div>
        <div style="position: relative; z-index: 1;">
            <?php
            $hour = (int) date('H');
            $greeting = $hour < 12 ? 'สวัสดีตอนเช้า' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
            ?>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #065f46; margin: 0 0 4px;"><?= $greeting ?>,
                <?= e(Auth::name()) ?> 🌿</h1>
            <p style="color: #047857; font-size: 0.9rem; margin: 0;">ภาพรวมวันนี้ —
                <?= thaiDate(date('Y-m-d'), 'long') ?></p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card green">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?= $stats['total_students'] ?></div>
        <div class="stat-card-label">นักเรียนทั้งหมด</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-card-icon">📁</div>
        <div class="stat-card-value"><?= $stats['total_works'] ?></div>
        <div class="stat-card-label">ผลงานทั้งหมด</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-card-icon">📤</div>
        <div class="stat-card-value"><?= $stats['recent_uploads'] ?></div>
        <div class="stat-card-label">อัพโหลดสัปดาห์นี้</div>
    </div>
</div>

<!-- Action Required Queue -->
<div class="card mb-4" style="border: none; border-left: 5px solid #f59e0b; border-radius: 16px; overflow: hidden;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 style="margin: 0; color: #b45309; font-size: 1.1rem; font-weight: 700;">⚡ งานที่ต้องดำเนินการ
                (<?= count($pendingPortfolios) + count($pendingDocuments) + count($pendingMilestones) ?> รายการ)</h3>
            <?php if (!empty($pendingPortfolios)): ?>
                <a href="<?= url('teacher/pending-reviews') ?>" class="btn btn-sm" 
                   style="background: #f59e0b; color: white; border-radius: 8px; font-weight: 700; border: none; padding: 6px 15px;">
                   ✅ ตรวจแบบกลุ่ม (Bulk)
                </a>
            <?php endif; ?>
        </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($pendingPortfolios) && empty($pendingDocuments) && empty($pendingMilestones)): ?>
            <div style="padding: 40px 24px; text-align: center; color: #94a3b8;">
                <div style="font-size: 3rem; margin-bottom: 8px;">🎉</div>
                <p style="font-weight: 600;">ยอดเยี่ยม! ไม่มีงานค้างพิจารณา</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingPortfolios as $item): ?>
                <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            📁</div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">แฟ้มสะสมผลงาน:
                                <?= e(truncate($item['title'], 40)) ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">โดย <?= e($item['full_name']) ?> •
                                <?= timeAgo($item['created_at']) ?></div>
                        </div>
                    </div>
                    <a href="<?= url('portfolio/' . $item['id']) ?>" class="btn btn-sm"
                        style="background: #eef2ff; color: #4f46e5; border-radius: 8px; font-weight: 600;">📄 ตรวจ</a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($pendingDocuments as $item): ?>
                <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: #fef2f2; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            📝</div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">เอกสาร:
                                <?= e(truncate($item['title'], 40)) ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">โครงงาน:
                                <?= e(truncate($item['project_title'], 30)) ?> • โดย <?= e($item['submitter']) ?></div>
                        </div>
                    </div>
                    <a href="<?= url('advisor/projects/' . $item['project_id']) ?>" class="btn btn-sm"
                        style="background: #fef2f2; color: #ef4444; border-radius: 8px; font-weight: 600;">✍️ ตรวจเอกสาร</a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($pendingMilestones as $item): ?>
                <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="d-flex align-items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            🎯</div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">ความคืบหน้า:
                                <?= e($item['phase_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">โครงงาน:
                                <?= e(truncate($item['project_title'], 30)) ?> • โดย <?= e($item['submitter']) ?></div>
                        </div>
                    </div>
                    <a href="<?= url('advisor/projects/' . $item['project_id']) ?>" class="btn btn-sm"
                        style="background: #f0fdf4; color: #16a34a; border-radius: 8px; font-weight: 600;">✅ อนุมัติ</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 2-Column: Recent Uploads + Early Warning -->
<div class="grid-2">
    <!-- Recent Uploads -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">📥 ผลงานอัพโหลดล่าสุด</h3>
            <a href="<?= url('teacher/students') ?>" class="btn btn-sm btn-outline"
                style="border-radius: 8px;">ดูนักเรียนทั้งหมด</a>
        </div>
        <div class="card-body" style="padding: 12px;">
            <?php foreach ($recentStudents as $s): ?>
                <div class="d-flex align-items-center gap-3 p-3 mb-2" style="background: #f8fafc; border-radius: 12px;">
                    <img src="<?= $s['avatar_path'] ? uploadUrl($s['avatar_path']) : defaultAvatar() ?>" alt=""
                        style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= e($s['full_name']) ?></div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">
                            ม.<?= $s['class_year'] ?>/<?= e($s['class_room']) ?> • <?= e(truncate($s['last_work'], 30)) ?>
                        </div>
                    </div>
                    <div style="font-size: 0.75rem; color: #94a3b8;"><?= timeAgo($s['work_date']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Early Warning Quick View -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🚨 นักเรียนที่ต้องดูแล</h3>
        </div>
        <div class="card-body" style="padding: 12px;">
            <?php
            $riskStudents = $riskStudents ?? [];
            if (empty($riskStudents)):
                ?>
                <div style="text-align: center; padding: 30px; color: #94a3b8;">
                    <div style="font-size: 3rem;">✅</div>
                    <p>นักเรียนทุกคนมีความพร้อม</p>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($riskStudents, 0, 5) as $rs): ?>
                    <div class="d-flex align-items-center gap-3 p-3 mb-2 <?= $rs['risk_level'] === 'critical' ? '' : '' ?>"
                        style="background: <?= $rs['risk_level'] === 'critical' ? '#fef2f2' : '#fffbeb' ?>; border-radius: 12px;">
                        <div style="font-size: 1.5rem;"><?= $rs['risk_level'] === 'critical' ? '🔴' : '🟡' ?></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= e($rs['full_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                ม.<?= $rs['class_year'] ?>/<?= e($rs['class_room']) ?> • คะแนน <?= $rs['total_score'] ?>/100
                            </div>
                        </div>
                        <a href="<?= url('teacher/student/' . ($rs['student_id'] ?? $rs['user_id'])) ?>" class="btn btn-sm"
                            style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem;">👁️
                            ดู</a>
                    </div>
                <?php endforeach; ?>
                <div class="text-center mt-2">
                    <a href="<?= url('analytics/early-warning') ?>" class="btn btn-sm btn-outline"
                        style="border-radius: 8px;">🚨 ดู Early Warning ทั้งหมด →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .gap-3 {
        gap: 16px;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-4 {
        margin-bottom: 24px;
    }

    .mt-2 {
        margin-top: 8px;
    }

    .p-3 {
        padding: 16px;
    }

    .text-center {
        text-align: center;
    }
</style>