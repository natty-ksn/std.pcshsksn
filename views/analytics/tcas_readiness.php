<?php
$pageTitle = 'ประเมินความพร้อม TCAS';
/**
 * TCAS Readiness Predictor View
 */
$levelLabels = [
    'excellent' => ['label' => 'ยอดเยี่ยม', 'icon' => '🟢', 'color' => 'success'],
    'good' => ['label' => 'ดี', 'icon' => '🟡', 'color' => 'primary'],
    'fair' => ['label' => 'พอใช้', 'icon' => '🟠', 'color' => 'warning'],
    'needs_improvement' => ['label' => 'ต้องปรับปรุง', 'icon' => '🔴', 'color' => 'danger'],
];
$level = $levelLabels[$readiness['level'] ?? 'fair'];
$components = $readiness['components'] ?? [];
?>

<div class="page-header">
    <h1>🎯 TCAS Readiness Predictor — วิเคราะห์ความพร้อมเข้ามหาวิทยาลัย</h1>
    <p class="text-muted">ประเมินความพร้อมและแนะนำแนวทางการเตรียมตัว</p>
</div>

<?php if (isset($readiness['error'])): ?>
<div class="alert alert-error"><?= e($readiness['error']) ?></div>
<?php else: ?>

<!-- Overall Score -->
<div class="card mb-4">
    <div class="card-body text-center py-5">
        <h2 class="mb-0"><?= $level['icon'] ?> คะแนนความพร้อมรวม</h2>
        <div class="display-1 fw-bold my-3">
            <span class="text-<?= $level['color'] ?>"><?= $readiness['overall_score'] ?? 0 ?></span>
            <span class="text-muted" style="font-size:.5em">/100</span>
        </div>
        <h3>ระดับ: <span class="badge bg-<?= $level['color'] ?>"><?= $level['label'] ?></span></h3>
        <div class="progress mt-3" style="height:30px;max-width:500px;margin:0 auto">
            <div class="progress-bar bg-<?= $level['color'] ?>" style="width:<?= $readiness['overall_score'] ?? 0 ?>%">
                <?= $readiness['overall_score'] ?? 0 ?>%
            </div>
        </div>
    </div>
</div>

<!-- Component Breakdown -->
<div class="row">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header"><strong>📊 คะแนนแต่ละองค์ประกอบ</strong></div>
            <div class="card-body">
                <div class="table-container" style="margin-bottom: 0;">
                <table class="table table-hover">
                    <thead>
                        <tr><th>องค์ประกอบ</th><th>คะแนน</th><th>สถานะ</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($components as $key => $comp): 
                        $statusIcon = $comp['status'] === 'pass' ? '✅' : ($comp['status'] === 'warning' ? '⚠️' : '❌');
                    ?>
                    <tr>
                        <td><strong><?= e($comp['label']) ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height:8px">
                                    <div class="progress-bar bg-<?= $comp['status'] === 'pass' ? 'success' : ($comp['status'] === 'warning' ? 'warning' : 'danger') ?>"
                                         style="width:<?= ($comp['score'] / $comp['max']) * 100 ?>%"></div>
                                </div>
                                <strong style="min-width:60px"><?= $comp['score'] ?>/<?= $comp['max'] ?></strong>
                            </div>
                        </td>
                        <td><?= $statusIcon ?> <?= $comp['value'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <!-- Gaps -->
        <?php if (!empty($readiness['gaps'])): ?>
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white"><strong>🔴 ช่องว่างที่ต้องเติม</strong></div>
            <div class="card-body">
                <ul class="mb-0">
                <?php foreach ($readiness['gaps'] as $gap): ?>
                <li><?= e($gap) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recommendations -->
        <?php if (!empty($readiness['recommendations'])): ?>
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white"><strong>🟢 คำแนะนำ</strong></div>
            <div class="card-body">
                <ol class="mb-0">
                <?php foreach ($readiness['recommendations'] as $rec): ?>
                <li><?= e($rec) ?></li>
                <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<style>
.display-1 { font-size: 4em; }
.progress { border-radius: 4px; }
</style>
