<?php
$pageTitle = 'ข้อมูลโปรไฟล์รอบด้าน 360°';
/**
 * Student 360° Profile View
 */
$completeness = $profile['profile_completeness']['total'] ?? 0;
$academic = $profile['academic_records'];
$portfolio = $profile['portfolio_items'];
$goals = $profile['goals'];
$projects = $profile['projects'];
$badges = $profile['badges'];
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>🎓 ข้อมูลโปรไฟล์รอบด้าน (360° Profile)</h1>
            <p class="text-muted"><?= e($profile['full_name']) ?> — <?= e($profile['student_code']) ?> | ชั้น
                ม.<?= $profile['class_year'] ?>/<?= e($profile['class_room']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('profile/export-data') ?>" class="btn btn-outline-primary"
                title="ดาวน์โหลดข้อมูลส่วนตัวของคุณ (PDPA/GDPR)">
                📥 ดาวน์โหลดข้อมูลของฉัน (PDPA)
            </a>
            <a href="<?= url('profile') ?>" class="btn btn-secondary">← ข้อมูลส่วนตัว</a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon" style="font-size:2em">📚</div>
                <div class="stat-value"><?= $academic['overall_gpa'] ?? '—' ?></div>
                <div class="stat-label">GPA รวม</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon" style="font-size:2em">📁</div>
                <div class="stat-value"><?= $portfolio['count'] ?? 0 ?></div>
                <div class="stat-label">ผลงานทั้งหมด</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon" style="font-size:2em">🏆</div>
                <div class="stat-value"><?= count($badges) ?></div>
                <div class="stat-label">เหรียญรางวัลที่ได้รับ</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon" style="font-size:2em">🔬</div>
                <div class="stat-value"><?= count($projects) ?></div>
                <div class="stat-label">โครงงาน</div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Completeness -->
<div class="card mb-4">
    <div class="card-header"><strong>📊 ความสมบูรณ์ของโปรไฟล์</strong></div>
    <div class="card-body">
        <div class="progress" style="height:24px">
            <div class="progress-bar bg-success" style="width:<?= $completeness ?>%"><?= $completeness ?>%</div>
        </div>
        <div class="row mt-3">
            <?php if (!empty($completenessData = $profile['profile_completeness']['details'] ?? [])): ?>
                <?php foreach ($completenessData as $key => $detail): ?>
                    <div class="col-md-4 mb-2">
                        <small><?= e(ucwords(str_replace('_', ' ', $key))) ?>:
                            <strong><?= $detail['score'] ?>/<?= $detail['max'] ?></strong>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
        <!-- Academic Records -->
        <div class="card mb-4">
            <div class="card-header"><strong>📚 ผลการเรียน</strong></div>
            <div class="card-body">
                <?php if (!empty($academic['records'])): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>วิชา</th>
                                <th>ปี</th>
                                <th>ภาค</th>
                                <th>เกรด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($academic['records'], 0, 10) as $rec): ?>
                                <tr>
                                    <td><?= e($rec['subject']) ?></td>
                                    <td><?= e($rec['academic_year']) ?></td>
                                    <td><?= $rec['semester'] ?></td>
                                    <td><strong><?= $rec['grade'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($academic['records']) > 10): ?>
                        <p class="text-muted text-center">... และอีก <?= count($academic['records']) - 10 ?> วิชา</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีข้อมูลการเรียน</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Goals -->
        <div class="card mb-4">
            <div class="card-header"><strong>🎯 เป้าหมาย</strong></div>
            <div class="card-body">
                <?php if (!empty($goals)): ?>
                    <?php foreach ($goals as $goal): ?>
                        <div class="mb-2">
                            <strong><?= e($goal['target']) ?></strong>
                            <span
                                class="badge bg-<?= $goal['status'] === 'achieved' ? 'success' : ($goal['status'] === 'inprogress' ? 'primary' : 'secondary') ?>">
                                <?= e($goal['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีเป้าหมาย</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-6">
        <!-- Portfolio by Category -->
        <div class="card mb-4">
            <div class="card-header"><strong>📁 ผลงานตามหมวดหมู่</strong></div>
            <div class="card-body">
                <?php if (!empty($portfolio['by_category'])): ?>
                    <?php foreach ($portfolio['by_category'] as $cat => $count): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= e($cat) ?></span>
                            <strong><?= $count ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีผลงาน</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Projects -->
        <div class="card mb-4">
            <div class="card-header"><strong>🔬 โครงงาน</strong></div>
            <div class="card-body">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $proj): ?>
                        <div class="mb-2">
                            <a href="<?= url("projects/{$proj['id']}") ?>"><?= e($proj['title_th']) ?></a>
                            <span
                                class="badge bg-<?= in_array($proj['status'], ['completed', 'proposal_approved']) ? 'success' : 'warning' ?>">
                                <?= e($proj['status_label']) ?>
                            </span>
                            <small class="text-muted">— <?= e($proj['role_in_project']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีโครงงาน</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Badges -->
        <div class="card mb-4">
            <div class="card-header"><strong>🏆 ตราที่ได้รับ</strong></div>
            <div class="card-body">
                <?php if (!empty($badges)): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($badges as $badge): ?>
                            <div class="badge-item text-center" style="width:80px">
                                <div style="font-size:2em"><?= e($badge['icon']) ?></div>
                                <small><?= e($badge['name']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">ยังไม่มีตรา</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        border-left: 4px solid var(--primary-color, #6366f1);
    }

    .stat-value {
        font-size: 1.8em;
        font-weight: 700;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.9em;
    }

    .badge-item {
        padding: 8px;
        border-radius: 8px;
        background: #f8fafc;
    }
</style>