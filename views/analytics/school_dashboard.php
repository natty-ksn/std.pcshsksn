<?php
$pageTitle = 'School Analytics Dashboard';
/**
 * School Analytics Dashboard View — สำหรับผู้บริหาร
 * Redesigned: Compact, Polished, Data-Rich
 */
$d = $data ?? [];
$totalStudents = $d['total_students'] ?? 0;
$totalPortfolios = $d['total_portfolios'] ?? 0;
$total = ($d['risk_low_count'] ?? 0) + ($d['risk_medium_count'] ?? 0) + ($d['risk_high_count'] ?? 0) + ($d['risk_critical_count'] ?? 0);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.5rem; font-weight:800; color:#1e293b; margin:0 0 4px;">📊 School Analytics Dashboard</h1>
        <p style="color:#64748b; font-size:0.9rem; margin:0;">ข้อมูลภาพรวมโรงเรียน — สำหรับผู้บริหาร</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span style="background:#f1f5f9; padding:6px 14px; border-radius:20px; font-size:0.8rem; color:#64748b;">📅
            <?= date('d/m/Y H:i') ?> น.</span>
        <a href="<?= url('analytics/dashboard/export') ?>" class="btn btn-sm btn-primary" style="border-radius:8px; background:#e11d48; color:white; border:none; text-decoration:none; padding:6px 12px; font-weight:600;">📄 ดาวน์โหลด PDF</a>
        <button onclick="window.print()" class="btn btn-sm btn-outline" style="border-radius:8px;">🖨️ พิมพ์</button>
    </div>
</div>

<!-- KPI Cards — Compact Row -->
<div class="stats-grid mb-4">
    <div class="stat-card purple">
        <div class="stat-card-icon">🎓</div>
        <div class="stat-card-value"><?= number_format($totalStudents) ?></div>
        <div class="stat-card-label">นักเรียนทั้งหมด</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-card-icon">📁</div>
        <div class="stat-card-value"><?= number_format($totalPortfolios) ?></div>
        <div class="stat-card-label">ผลงานทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon">🔬</div>
        <div class="stat-card-value"><?= $d['active_projects'] ?? 0 ?></div>
        <div class="stat-card-label">โครงการที่กำลังทำ</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-card-icon">📈</div>
        <div class="stat-card-value"><?= $d['avg_gpa'] ?? '—' ?></div>
        <div class="stat-card-label">GPA เฉลี่ย</div>
    </div>
</div>

<!-- 3-Column Main Grid -->
<div class="grid-3 mb-4">
    <!-- Left: Portfolio by Category -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:1rem; font-weight:700; margin:0;">📁 ผลงานตามหมวดหมู่</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($d['portfolio_by_category'])): ?>
                <?php foreach ($d['portfolio_by_category'] as $cat): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span><?= $cat['icon'] ?>         <?= e($cat['name']) ?></span>
                            <strong><?= $cat['count'] ?></strong>
                        </div>
                        <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                            <div
                                style="width:<?= $totalPortfolios > 0 ? ($cat['count'] / $totalPortfolios * 100) : 0 ?>%; height:100%; background:<?= $cat['color'] ?>; border-radius:3px;">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center" style="padding:20px;">ยังไม่มีข้อมูล</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Middle: Early Warning + Engagement -->
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">🚨 ความเสี่ยงนักเรียน</h3>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 mb-3" style="justify-content:space-around;">
                    <div class="text-center">
                        <div style="font-size:1.8rem;">🟢</div>
                        <div style="font-weight:800; font-size:1.2rem;"><?= $d['risk_low_count'] ?? 0 ?></div>
                        <div style="font-size:0.7rem; color:#94a3b8;">ต่ำ</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.8rem;">🟡</div>
                        <div style="font-weight:800; font-size:1.2rem;"><?= $d['risk_medium_count'] ?? 0 ?></div>
                        <div style="font-size:0.7rem; color:#94a3b8;">ปานกลาง</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.8rem;">🟠</div>
                        <div style="font-weight:800; font-size:1.2rem;"><?= $d['risk_high_count'] ?? 0 ?></div>
                        <div style="font-size:0.7rem; color:#94a3b8;">สูง</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.8rem;">🔴</div>
                        <div style="font-weight:800; font-size:1.2rem;"><?= $d['risk_critical_count'] ?? 0 ?></div>
                        <div style="font-size:0.7rem; color:#94a3b8;">วิกฤต</div>
                    </div>
                </div>
                <?php if ($total > 0): ?>
                    <div style="height:10px; background:#f1f5f9; border-radius:5px; overflow:hidden; display:flex;">
                        <div style="width:<?= ($d['risk_low_count'] ?? 0) / $total * 100 ?>%; background:#10b981;"></div>
                        <div style="width:<?= ($d['risk_medium_count'] ?? 0) / $total * 100 ?>%; background:#f59e0b;"></div>
                        <div
                            style="width:<?= (($d['risk_high_count'] ?? 0) + ($d['risk_critical_count'] ?? 0)) / $total * 100 ?>%; background:linear-gradient(90deg,#f97316,#ef4444);">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Engagement -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">⚡ Engagement 30 วัน</h3>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3" style="justify-content:space-around;">
                    <div class="text-center">
                        <div style="font-size:1.5rem; font-weight:800; color:#6366f1;">
                            <?= $d['active_students_30d'] ?? 0 ?></div>
                        <div style="font-size:0.75rem; color:#94a3b8;">นักเรียนใช้งาน</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.5rem; font-weight:800; color:#10b981;"><?= $d['logbooks_30d'] ?? 0 ?>
                        </div>
                        <div style="font-size:0.75rem; color:#94a3b8;">Logbook ใหม่</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Students by Class + Projects -->
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">🏫 นักเรียนตามชั้น</h3>
            </div>
            <div class="card-body" style="padding:12px;">
                <?php if (!empty($d['students_by_class'])): ?>
                    <?php foreach ($d['students_by_class'] as $cls): ?>
                        <div class="d-flex justify-content-between align-items-center p-2 mb-1"
                            style="background:#f8fafc; border-radius:10px;">
                            <span style="font-size:0.85rem;">ม.<?= $cls['class_year'] ?>/<?= e($cls['class_room']) ?></span>
                            <strong style="font-size:0.9rem;"><?= $cls['count'] ?> คน</strong>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center" style="padding:20px;">ยังไม่มีข้อมูล</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Status -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">📊 สถานะโครงงาน</h3>
            </div>
            <div class="card-body" style="padding:12px;">
                <?php
                $statusLabels = [
                    'draft' => '📝 ร่าง',
                    'proposal_submitted' => '📤 เสนอแล้ว',
                    'proposal_approved' => '✅ อนุมัติ',
                    'in_progress' => '🔄 กำลังทำ',
                    'completed' => '🏆 เสร็จแล้ว',
                    'archived' => '📦 เก็บถาวร',
                ];
                $totalProjects = !empty($d['project_status_dist']) ? array_sum(array_column($d['project_status_dist'], 'count')) : 0;
                ?>
                <?php if (!empty($d['project_status_dist'])): ?>
                    <?php foreach ($d['project_status_dist'] as $ps): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:0.85rem;">
                            <span><?= $statusLabels[$ps['status']] ?? $ps['status'] ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:60px; height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                    <div
                                        style="width:<?= $totalProjects > 0 ? ($ps['count'] / $totalProjects * 100) : 0 ?>%; height:100%; background:<?= in_array($ps['status'], ['completed', 'proposal_approved']) ? '#10b981' : '#6366f1' ?>; border-radius:3px;">
                                    </div>
                                </div>
                                <strong style="min-width:25px; text-align:right;"><?= $ps['count'] ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center" style="padding:20px;">ยังไม่มีข้อมูล</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 style="font-size:1rem; font-weight:700; margin:0;">🔗 ลิงก์ด่วน</h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= url('analytics/early-warning') ?>" class="btn btn-sm"
                style="background:#fffbeb; color:#92400e; border-radius:10px; font-weight:600;">🚨 Early Warning</a>
            <a href="<?= url('teacher/students') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">👥
                รายชื่อนักเรียน</a>
            <a href="<?= url('executive/excellence') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">🏆
                ทำเนียบความเป็นเลิศ</a>
            <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">📋
                รายงาน</a>
            <?php if (Auth::hasRole('admin')): ?>
                <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">👤
                    จัดการผู้ใช้</a>
                <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">🏷️
                    หมวดหมู่</a>
                <a href="<?= url('admin/settings') ?>" class="btn btn-sm btn-outline" style="border-radius:10px;">⚙️
                    ตั้งค่า</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* 3-Column Grid */
    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    @media (max-width: 1200px) {
        .grid-3 {
            grid-template-columns: 1fr;
        }
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .stat-card-icon {
        font-size: 1.8rem;
        margin-bottom: 8px;
    }

    .stat-card-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-card-label {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .stat-card.purple {
        border-top: 4px solid #6366f1;
    }

    .stat-card.blue {
        border-top: 4px solid #3b82f6;
    }

    .stat-card.green {
        border-top: 4px solid #10b981;
    }

    .stat-card.orange {
        border-top: 4px solid #f59e0b;
    }

    /* Utilities */
    .d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
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

    .mb-1 {
        margin-bottom: 4px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-3 {
        margin-bottom: 16px;
    }

    .mb-4 {
        margin-bottom: 24px;
    }

    .p-2 {
        padding: 8px;
    }

    .text-center {
        text-align: center;
    }

    .text-muted {
        color: #94a3b8;
    }
</style>