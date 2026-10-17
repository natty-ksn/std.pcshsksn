<?php
$pageTitle = 'ระบบเฝ้าระวังความเสี่ยง (EWS)';
/**
 * Early Warning System View
 */
$riskLabels = [
    'low' => ['label' => '🟢 ต่ำ', 'class' => 'success'],
    'medium' => ['label' => '🟡 ปานกลาง', 'class' => 'warning'],
    'high' => ['label' => '🟠 สูง', 'class' => 'danger'],
    'critical' => ['label' => '🔴 วิกฤต', 'class' => 'danger'],
];
?>

<div class="page-header">
    <h1>🚨 Early Warning System — ระบบแจ้งเตือนนักเรียนที่มีความเสี่ยง</h1>
    <p class="text-muted">วิเคราะห์ความเสี่ยงอัตโนมัติจาก GPA, Portfolio, กิจกรรม, เป้าหมาย, และ Logbook</p>
</div>

<!-- Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-success">
            <div class="card-body text-center">
                <div style="font-size:2em">🟢</div>
                <div style="font-size:1.8em;font-weight:700"><?= $riskCounts['low'] ?></div>
                <div class="text-muted">ความเสี่ยงต่ำ</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-warning">
            <div class="card-body text-center">
                <div style="font-size:2em">🟡</div>
                <div style="font-size:1.8em;font-weight:700"><?= $riskCounts['medium'] ?></div>
                <div class="text-muted">ความเสี่ยงปานกลาง</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-danger">
            <div class="card-body text-center">
                <div style="font-size:2em">🟠</div>
                <div style="font-size:1.8em;font-weight:700"><?= $riskCounts['high'] ?></div>
                <div class="text-muted">ความเสี่ยงสูง</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-danger">
            <div class="card-body text-center">
                <div style="font-size:2em">🔴</div>
                <div style="font-size:1.8em;font-weight:700"><?= $riskCounts['critical'] ?></div>
                <div class="text-muted">ความเสี่ยงวิกฤต</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-2">
                <select name="class_year" class="form-control">
                    <option value="">ทุกระดับชั้น</option>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?= $i ?>" <?= ($filters['class_year'] ?? '') == $i ? 'selected' : '' ?>>ม.<?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="class_room" class="form-control">
                    <option value="">ทุกห้อง</option>
                    <?php foreach (range('1', '5') as $room): ?>
                        <option value="<?= $room ?>" <?= ($filters['class_room'] ?? '') == $room ? 'selected' : '' ?>>ห้อง
                            <?= $room ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="risk_level" class="form-control">
                    <option value="">ทุกระดับความเสี่ยง</option>
                    <option value="low" <?= ($filters['risk_level'] ?? '') === 'low' ? 'selected' : '' ?>>🟢 ต่ำ</option>
                    <option value="medium" <?= ($filters['risk_level'] ?? '') === 'medium' ? 'selected' : '' ?>>🟡 ปานกลาง</option>
                    <option value="high" <?= ($filters['risk_level'] ?? '') === 'high' ? 'selected' : '' ?>>🟠 สูง</option>
                    <option value="critical" <?= ($filters['risk_level'] ?? '') === 'critical' ? 'selected' : '' ?>>🔴 วิกฤต</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">🔍 กรอง</button>
            </div>
        </form>
    </div>
</div>

<!-- Student Table -->
<div class="card">
    <div class="card-header"><strong>📋 รายชื่อนักเรียน <?= $total ?> คน</strong></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>นักเรียน</th>
                        <th>ชั้น</th>
                        <th>GPA</th>
                        <th>Portfolio</th>
                        <th>คะแนนรวม</th>
                        <th>ระดับความเสี่ยง</th>
                        <th>ข้อเสนอแนะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px 20px; color: #64748b;">
                                <div style="font-size: 2.5rem; margin-bottom: 10px;">🎉</div>
                                <h4 style="margin: 0 0 5px 0; color: #1e293b;">ไม่พบนักเรียนกลุ่มเสี่ยงตามเงื่อนไขที่เลือก</h4>
                                <p style="margin: 0; font-size: 0.85rem;">นักเรียนทุกคนมีผลการเรียนและการส่งผลงานผ่านเกณฑ์ หรือกรุณาลองปรับเงื่อนไขตัวกรองใหม่</p>
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($students as $s):
                        $risk = $riskLabels[$s['risk_level']] ?? ['label' => $s['risk_level'], 'class' => 'secondary'];
                        $studentProfileId = $s['student_id'] ?? $s['id'] ?? $s['user_id'];
                        ?>
                        <tr class="<?= in_array($s['risk_level'], ['high', 'critical']) ? 'table-danger' : '' ?>">
                            <td>
                                <strong><?= e($s['full_name']) ?></strong><br>
                                <small class="text-muted"><?= e($s['student_code']) ?></small>
                            </td>
                            <td>ม.<?= $s['class_year'] ?>/<?= e($s['class_room']) ?></td>
                            <td><strong><?= round($s['gpa_score'] * 4 / 25, 2) ?></strong></td>
                            <td><?= round($s['portfolio_score'] / 4) ?> รายการ</td>
                            <td>
                                <div class="progress" style="height:8px;width:80px">
                                    <div class="progress-bar bg-<?= $s['total_score'] >= 70 ? 'success' : ($s['total_score'] >= 40 ? 'warning' : 'danger') ?>"
                                        style="width:<?= $s['total_score'] ?>%"></div>
                                </div>
                                <small><?= $s['total_score'] ?>/100</small>
                            </td>
                            <td><span class="badge bg-<?= $risk['class'] ?>"><?= $risk['label'] ?></span></td>
                            <td>
                                <?php if (!empty($s['recommendations'])): ?>
                                    <ul class="mb-0 ps-3" style="font-size:0.85em">
                                        <?php foreach (array_slice($s['recommendations'], 0, 2) as $rec): ?>
                                            <li><?= e($rec['message']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('teacher/student/' . $studentProfileId) ?>" class="btn btn-sm btn-info">👁️
                                    ดู</a>
                                <?php if (in_array($s['risk_level'], ['high', 'critical'])): ?>
                                    <button onclick="notifyRisk(<?= $s['user_id'] ?>, '<?= $s['risk_level'] ?>')"
                                        class="btn btn-sm btn-warning">📩 แจ้งเตือน</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function notifyRisk(userId, riskLevel) {
        if (!confirm('ต้องการส่งแจ้งเตือนนักเรียนคนนี้?')) return;
        fetch('<?= url('analytics/early-warning/notify') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `student_id=${userId}&risk_level=${riskLevel}&_csrf_token=<?= CSRF::token() ?>`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ส่งแจ้งเตือนเรียบร้อยแล้ว');
                    location.reload();
                } else {
                    alert('❌ เกิดข้อผิดพลาด: ' + data.error);
                }
            });
    }
</script>

<style>
    .stat-card {
        border-left: 4px solid;
    }

    .table-danger {
        background-color: #fef2f2 !important;
    }
</style>