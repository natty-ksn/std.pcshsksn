<?php $pageTitle = 'แผงหน้าหลัก'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb" aria-label="breadcrumb" style="margin-bottom: 8px;">
    <span class="breadcrumb-current">🏠 แผงหน้าหลัก</span>
</nav>

<!-- Greeting Banner -->
<div class="card mb-4 glass-card animate-fade-in"
    style="border: none; background: linear-gradient(135deg, rgba(238, 242, 255, 0.9) 0%, rgba(224, 231, 255, 0.9) 100%);">
    <div class="card-body p-4" style="position: relative; overflow: hidden;">
        <div
            style="position: absolute; right: -30px; top: -30px; font-size: 10rem; opacity: 0.05; transform: rotate(15deg);">
            🧭</div>
        <div style="position: relative; z-index: 1;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <?php
                    $hour = (int) date('H');
                    $greeting = $hour < 12 ? 'สวัสดีตอนเช้า' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : 'สวัสดีตอนเย็น');
                    ?>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">
                        <?= $greeting ?>, <?= e($profile['full_name']) ?> 👋
                    </h1>
                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
                        มัธยมศึกษาปีที่ <?= e($profile['class_year']) ?>/<?= e($profile['class_room']) ?> —
                        เลขอัตลักษณ์: <?= e($profile['student_code']) ?>
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?= url('portfolio/create') ?>" class="btn btn-primary"
                        style="border-radius: 12px; font-weight: 700;">➕ เพิ่มผลงาน</a>
                    <a href="<?= url('profile/360') ?>" class="btn btn-outline"
                        style="border-radius: 12px; font-weight: 600;">🎓 Student 360°</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TCAS Readiness Gauge — Main Hero Card -->
<div class="card mb-4 glass-card animate-fade-in" style="border: none; animation-delay: 0.1s;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
            <h3 style="font-weight: 800; color: #1e293b; margin: 0; font-size: 1.25rem;">🎯 ความพร้อม TCAS ของคุณ</h3>
            <span class="badge ms-2"
                style="background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem;">
                เป้าหมาย: <?= e($activeGoals[0]['target'] ?? 'ยังไม่ได้ตั้ง') ?>
            </span>
        </div>
        <div class="row align-items-center">
            <div class="col-md-5 text-center">
                <!-- Circular Gauge -->
                <div style="position: relative; width: 180px; height: 180px; margin: 0 auto;">
                    <canvas id="gaugeChart" width="180" height="180"></canvas>
                    <div
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 2.5rem; font-weight: 900; color: #1e293b; line-height: 1;">
                            <?= $readiness['overall_pct'] ?><span style="font-size: 1rem; color: #64748b;">%</span>
                        </div>
                        <div
                            style="font-size: 0.7rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 1px;">
                            ความพร้อม</div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem; margin-bottom: 4px;">
                            <span style="font-weight: 600; color: #475569;">📊 Academic (เกรด)</span>
                            <span style="font-weight: 700; color: #1e293b;"><?= $readiness['academic_score'] ?>%</span>
                        </div>
                        <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div
                                style="width: <?= $readiness['academic_score'] ?>%; height: 100%; background: #3b82f6; border-radius: 4px; transition: width 1s;">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem; margin-bottom: 4px;">
                            <span style="font-weight: 600; color: #475569;">📁 Portfolio (ผลงาน)</span>
                            <span style="font-weight: 700; color: #1e293b;"><?= $readiness['portfolio_score'] ?>%</span>
                        </div>
                        <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div
                                style="width: <?= $readiness['portfolio_score'] ?>%; height: 100%; background: #8b5cf6; border-radius: 4px; transition: width 1s;">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem; margin-bottom: 4px;">
                            <span style="font-weight: 600; color: #475569;">🧠 Skills (ความถนัด)</span>
                            <span style="font-weight: 700; color: #1e293b;"><?= $readiness['skill_score'] ?>%</span>
                        </div>
                        <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div
                                style="width: <?= $readiness['skill_score'] ?>%; height: 100%; background: #f59e0b; border-radius: 4px; transition: width 1s;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <a href="<?= url('tcas-readiness') ?>" class="btn btn-sm"
                        style="background: #6366f1; color: white; border-radius: 8px; font-weight: 600;">🎯 ดู TCAS
                        Readiness</a>
                    <a href="<?= url('ai/field-readiness') ?>" class="btn btn-sm btn-outline"
                        style="border-radius: 8px; background: #f1f5f9; color: #334155; font-weight: 600; text-decoration: none;">🎯
                        วิเคราะห์สาขาวิชา</a>
                    <a href="<?= url('ai/portfolio-audit') ?>" class="btn btn-sm btn-outline"
                        style="border-radius: 8px; background: #f1f5f9; color: #334155; font-weight: 600; text-decoration: none;">🔍
                        ตรวจคุณภาพพอร์ต</a>
                    <a href="<?= url('rewards/badges') ?>" class="btn btn-sm btn-outline"
                        style="border-radius: 8px; background: #fef3c7; color: #92400e; font-weight: 600; text-decoration: none;">🏅
                        เหรียญตรา</a>
                    <a href="<?= url('ai-guide') ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">🤖 AI
                        วิเคราะห์</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Storage Quota -->
<?php
$quotaMB = 50;
$usedMB = round(($stats['used_space'] ?? 0) / (1024 * 1024), 2);
$percent = min(100, ($usedMB / $quotaMB) * 100);
$quotaColor = $percent > 90 ? '#ef4444' : ($percent > 75 ? '#f59e0b' : '#10b981');
?>
<div class="card mb-4 glass-card animate-fade-in"
    style="border-left: 5px solid <?= $quotaColor ?>; animation-delay: 0.2s;">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-weight: 700; font-size: 0.95rem;">☁️ พื้นที่จัดเก็บ</span>
            <span style="font-size: 0.85rem; color: #64748b;"><?= $usedMB ?> / <?= $quotaMB ?> MB</span>
        </div>
        <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
            <div
                style="width: <?= $percent ?>%; height: 100%; background: <?= $quotaColor ?>; border-radius: 4px; transition: width 0.8s;">
            </div>
        </div>
        <?php if ($percent > 90): ?>
            <p style="color: #ef4444; font-size: 0.8rem; margin: 8px 0 0;">⚠️ พื้นที่ใกล้เต็มแล้ว
                แนะนำให้ลบผลงานเก่าหรือใช้ลิงก์ภายนอก</p>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4 animate-fade-in" style="animation-delay: 0.3s;">
    <div class="stat-card purple glass-card">
        <div class="stat-card-icon">📁</div>
        <div class="stat-card-value"><?= $stats['total_works'] ?></div>
        <div class="stat-card-label">ผลงานทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon">⭐</div>
        <div class="stat-card-value"><?= $stats['featured_works'] ?></div>
        <div class="stat-card-label">ผลงานดีเด่น</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-card-icon">📈</div>
        <div class="stat-card-value"><?= number_format($stats['gpa'], 2) ?></div>
        <div class="stat-card-label">GPA</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-card-icon">🎯</div>
        <div class="stat-card-value"><?= $stats['achieved_goals'] ?>/<?= $stats['total_goals'] ?></div>
        <div class="stat-card-label">เป้าหมายที่บรรลุ</div>
    </div>
</div>

<!-- 2-Column Grid -->
<div class="grid-2 animate-fade-in" style="animation-delay: 0.4s;">
    <!-- Left: Recent Works + Category Chart -->
    <div>
        <!-- Recent Works -->
        <div class="card mb-4 glass-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">📁 ผลงานล่าสุด</h3>
                <a href="<?= url('portfolio') ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">ดูทั้งหมด
                    →</a>
            </div>
            <div class="card-body" style="padding: 12px;">
                <?php if (empty($recentWorks)): ?>
                    <div style="text-align: center; padding: 30px; color: #94a3b8;">
                        <div style="font-size: 3rem; margin-bottom: 8px;">📭</div>
                        <p>ยังไม่มีผลงาน</p>
                        <a href="<?= url('portfolio/create') ?>" class="btn btn-primary btn-sm"
                            style="border-radius: 8px;">➕ เพิ่มผลงานแรก</a>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($recentWorks, 0, 5) as $work): ?>
                        <a href="<?= url('portfolio/' . $work['id']) ?>"
                            class="d-flex align-items-center gap-3 p-3 text-decoration-none"
                            style="border-radius: 12px; transition: background 0.2s; color: inherit;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <div
                                style="width: 44px; height: 44px; border-radius: 12px; background: var(--primary-50); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <?= e($work['category_icon'] ?? '📁') ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div
                                    style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($work['title']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><?= e($work['category_name'] ?? 'ไม่ระบุ') ?> •
                                    <?= timeAgo($work['created_at']) ?>
                                </div>
                            </div>
                            <?php if ($work['is_featured']): ?><span style="font-size: 1rem;">⭐</span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category Chart -->
        <div class="card glass-card">
            <div class="card-header">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">📊 ผลงานตามหมวดหมู่</h3>
            </div>
            <div class="card-body">
                <div style="max-width: 300px; margin: 0 auto;"><canvas id="categoryChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Right: Goals + Action Items + Badges -->
    <div>
        <!-- Active Goals -->
        <div class="card mb-4 glass-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🎯 เป้าหมายที่กำลังดำเนินการ</h3>
                <a href="<?= url('goals') ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">จัดการ</a>
            </div>
            <div class="card-body" style="padding: 12px;">
                <?php if (empty($activeGoals)): ?>
                    <div style="text-align: center; padding: 20px; color: #94a3b8;">
                        <p>ยังไม่ได้ตั้งเป้าหมาย</p>
                        <a href="<?= url('goals') ?>" class="btn btn-sm btn-primary" style="border-radius: 8px;">🎯
                            ตั้งเป้าหมาย</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeGoals as $goal): ?>
                        <div class="p-3 mb-2" style="background: #f8fafc; border-radius: 12px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size: 0.9rem;"><?= e($goal['target']) ?></strong>
                                <span class="badge"
                                    style="background: <?= $goal['status'] === 'planning' ? '#e2e8f0' : '#eef2ff' ?>; color: <?= $goal['status'] === 'planning' ? '#475569' : '#4338ca' ?>; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem;">
                                    <?= $goal['status'] === 'planning' ? '📋 วางแผน' : '🔥 กำลังทำ' ?>
                                </span>
                            </div>
                            <?php if ($goal['target_score'] && $goal['current_score']): ?>
                                <?php $progress = min(100, ($goal['current_score'] / $goal['target_score']) * 100); ?>
                                <div
                                    style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-bottom: 4px;">
                                    <div style="width: <?= $progress ?>%; height: 100%; background: #6366f1; border-radius: 3px;">
                                    </div>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><?= number_format($goal['current_score'], 1) ?> /
                                    <?= number_format($goal['target_score'], 1) ?> (<?= number_format($progress, 0) ?>%)
                                </div>
                            <?php endif; ?>
                            <?php if ($goal['deadline']): ?>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">⏰
                                    <?= thaiDate($goal['deadline'], 'short') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Badges -->
        <?php if (!empty($badges)): ?>
            <div class="card mb-4 glass-card">
                <div class="card-header">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">🏅 ตราที่ได้รับ</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($badges as $badge): ?>
                            <div class="text-center p-2" style="background: #f8fafc; border-radius: 12px; width: 80px;">
                                <div style="font-size: 2rem;"><?= e($badge['icon']) ?></div>
                                <div style="font-size: 0.7rem; font-weight: 600;"><?= e($badge['name']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Opportunity Scout -->
        <div class="card glass-card animate-pulse"
            style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(99, 102, 241, 0.9)); color: white; border: none; overflow: hidden; position: relative;">
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <h3 style="color: white; font-size: 1.1rem; font-weight: 700; margin-bottom: 6px;">🔭
                    โอกาสที่เหมาะกับคุณ</h3>
                <p style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 16px;">
                    พบกิจกรรมใหม่ที่สอดคล้องกับเป้าหมายของคุณ</p>
                <a href="<?= url('opportunities') ?>" class="btn"
                    style="background: white; color: #4f46e5; font-weight: 700; border-radius: 10px; padding: 8px 20px;">ส่องโอกาสเลย
                    →</a>
            </div>
            <div
                style="position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.15; transform: rotate(-10deg);">
                🔭</div>
        </div>
    </div>
</div>

<script>
    // Gauge Chart
    const gaugeValue = <?= (int) $readiness['overall_pct'] ?>;
    const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');
    const gaugeColor = gaugeValue >= 70 ? '#10b981' : (gaugeValue >= 40 ? '#f59e0b' : '#ef4444');
    new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [gaugeValue, 100 - gaugeValue],
                backgroundColor: [gaugeColor, '#e2e8f0'],
                borderWidth: 0,
                circumference: 180,
                rotation: 270
            }]
        },
        options: {
            responsive: false,
            cutout: '75%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    // Category Chart
    const categoryData = <?= json_encode($categoryStats) ?>;
    const labels = categoryData.map(c => c.icon + ' ' + c.name);
    const values = categoryData.map(c => parseInt(c.count));
    const colors = categoryData.map(c => c.color || '#6366f1');

    if (values.some(v => v > 0)) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { family: "'Noto Sans Thai', sans-serif", size: 11 } } }
                },
                cutout: '60%'
            }
        });
    } else {
        document.getElementById('categoryChart').parentElement.innerHTML = '<div class="text-center text-muted" style="padding: 40px;">ยังไม่มีข้อมูลผลงาน</div>';
    }
</script>

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

    .text-decoration-none {
        text-decoration: none;
    }

    .text-center {
        text-align: center;
    }

    .fw-bold {
        font-weight: 700;
    }

    .ms-2 {
        margin-left: 8px;
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

    .mt-3 {
        margin-top: 16px;
    }

    .py-3 {
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .p-3 {
        padding: 16px;
    }

    .p-2 {
        padding: 8px;
    }

    .p-4 {
        padding: 24px;
    }

    .text-muted {
        color: #94a3b8;
    }
</style>