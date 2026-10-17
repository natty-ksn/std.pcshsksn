<?php $pageTitle = 'แผงควบคุมระบบ'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb" style="margin-bottom: 8px;">
    <span class="breadcrumb-current">⚙️ แผงควบคุมระบบ</span>
</nav>

<!-- Admin Header -->
<div class="card mb-4"
    style="border: none; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 20px;">
    <div class="card-body p-4" style="position: relative; overflow: hidden;">
        <div
            style="position: absolute; right: -20px; top: -20px; font-size: 8rem; opacity: 0.05; transform: rotate(10deg);">
            ⚙️</div>
        <div style="position: relative; z-index: 1;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800; color: #991b1b; margin: 0 0 4px;">⚙️ แผงควบคุมระบบ
                    </h1>
                    <p style="color: #b91c1c; font-size: 0.9rem; margin: 0;">ระบบบริหารจัดการ PCSHSKSN e-Portfolio</p>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span style="background: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">📅
                            <?= thaiDate(date('Y-m-d'), 'long') ?></span>
                        <span
                            style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <span style="font-size: 0.75rem; color: #64748b;">ระบบปกติ</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= url('analytics/dashboard') ?>" class="btn"
                        style="background: #7c3aed; color: white; border-radius: 10px; font-weight: 700;">📊
                        Analytics</a>
                    <a href="<?= url('analytics/early-warning') ?>" class="btn"
                        style="background: #f59e0b; color: white; border-radius: 10px; font-weight: 600;">🚨 EWS</a>
                    <a href="<?= url('admin/reports') ?>" class="btn btn-outline" style="border-radius: 10px;">📋
                        รายงาน</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card purple">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?= number_format($stats['total_users'] ?? 0) ?></div>
        <div class="stat-card-label">ผู้ใช้ทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon">🟢</div>
        <div class="stat-card-value"><?= number_format($stats['active_users'] ?? 0) ?></div>
        <div class="stat-card-label">Active วันนี้</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-card-icon">📁</div>
        <div class="stat-card-value"><?= number_format($stats['total_works'] ?? 0) ?></div>
        <div class="stat-card-label">ผลงานทั้งหมด</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-card-icon">📜</div>
        <div class="stat-card-value"><?= number_format($stats['today_logs'] ?? 0) ?></div>
        <div class="stat-card-label">กิจกรรมวันนี้</div>
    </div>
</div>

<!-- Quick Actions Grid -->
<div class="grid-2 mb-4">
    <!-- User Management -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">👥 จัดการผู้ใช้</h3>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= url('admin/users') ?>" class="btn btn-sm"
                    style="background: #eef2ff; color: #4f46e5; border-radius: 10px; font-weight: 600;">👁️
                    ดูทั้งหมด</a>
                <a href="<?= url('admin/users?action=add') ?>" class="btn btn-sm btn-primary" style="border-radius: 10px;">➕
                    เพิ่มผู้ใช้</a>
                <a href="<?= url('admin/users?action=import') ?>" class="btn btn-sm btn-outline"
                    style="border-radius: 10px;">📥 Import CSV</a>
            </div>
        </div>
    </div>

    <!-- Early Warning -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🚨 Early Warning</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($ewsSummary)): ?>
                <div class="d-flex gap-2 mb-2">
                    <span class="badge"
                        style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 8px;">🟢
                        <?= $ewsSummary['low'] ?? 0 ?></span>
                    <span class="badge"
                        style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px;">🟡
                        <?= $ewsSummary['medium'] ?? 0 ?></span>
                    <span class="badge"
                        style="background: #ffedd5; color: #9a3412; padding: 4px 10px; border-radius: 8px;">🟠
                        <?= $ewsSummary['high'] ?? 0 ?></span>
                    <span class="badge"
                        style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 8px;">🔴
                        <?= $ewsSummary['critical'] ?? 0 ?></span>
                </div>
            <?php endif; ?>
            <a href="<?= url('analytics/early-warning') ?>" class="btn btn-sm"
                style="background: #fef2f2; color: #991b1b; border-radius: 10px; font-weight: 600;">👁️ ดูรายละเอียด
                →</a>
        </div>
    </div>

    <!-- Categories -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🏷️ หมวดหมู่ผลงาน</h3>
        </div>
        <div class="card-body">
            <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline" style="border-radius: 10px;">⚙️
                จัดการหมวดหมู่</a>
        </div>
    </div>

    <!-- Settings -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">⚙️ ตั้งค่าระบบ</h3>
        </div>
        <div class="card-body">
            <a href="<?= url('admin/settings') ?>" class="btn btn-sm btn-outline" style="border-radius: 10px;">🔧
                ตั้งค่าทั่วไป</a>
        </div>
    </div>
</div>

<!-- Recent Audit Logs -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">📜 กิจกรรมล่าสุด</h3>
        <a href="<?= url('admin/logs') ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">ดูทั้งหมด →</a>
    </div>
    <div class="card-body" style="padding: 12px;">
        <?php if (!empty($recentLogs)): ?>
            <?php foreach (array_slice($recentLogs, 0, 8) as $log): ?>
                <div class="d-flex align-items-center gap-3 p-3 mb-2" style="background: #f8fafc; border-radius: 12px;">
                    <div
                        style="width: 36px; height: 36px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <?= $log['action'] === 'login' ? '🔑' : ($log['action'] === 'create' ? '➕' : ($log['action'] === 'update' ? '✏️' : ($log['action'] === 'delete' ? '🗑️' : '📝'))) ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= e($log['action']) ?> —
                            <?= e($log['entity_type'] ?? '') ?> #<?= $log['entity_id'] ?? '' ?></div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">โดย User #<?= $log['user_id'] ?? '-' ?> •
                            <?= timeAgo($log['created_at']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 20px; color: #94a3b8;">ยังไม่มีกิจกรรม</div>
        <?php endif; ?>
    </div>
</div>

<!-- System Info -->
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🖥️ ข้อมูลระบบ</h3>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3" style="justify-content: space-around; text-align: center;">
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 4px;">Server</div>
                <div style="font-weight: 600; font-size: 0.9rem;"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache' ?></div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 4px;">PHP</div>
                <div style="font-weight: 600; font-size: 0.9rem;"><?= phpversion() ?></div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 4px;">Database</div>
                <div style="font-weight: 600; font-size: 0.9rem; color: #10b981;">Connected ✅</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 4px;">Security</div>
                <div style="font-weight: 600; font-size: 0.9rem; color: #6366f1;">🔒 Active</div>
            </div>
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

    .align-items-start {
        align-items: flex-start;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .gap-2 {
        gap: 8px;
    }

    .gap-3 {
        gap: 16px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-4 {
        margin-bottom: 24px;
    }

    .p-3 {
        padding: 16px;
    }

    .p-4 {
        padding: 24px;
    }

    .text-center {
        text-align: center;
    }
</style>