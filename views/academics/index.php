<?php $pageTitle = 'ผลการเรียน'; ?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="flex-between mb-3" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <h2>📝 ผลการเรียน</h2>
            <p class="text-muted text-sm">GPA สะสม: <strong style="color: var(--primary-600); font-size: 1.1rem;">
                    <?= number_format($gpa, 2) ?>
                </strong> (
                <?= $totalCredits ?> หน่วยกิต)
            </p>
        </div>
    </div>

    <!-- GPA Chart -->
    <?php if (!empty($gpaByTerm)): ?>
        <div class="card mb-3 slide-up">
            <div class="card-header">
                <h3>📈 แนวโน้ม GPA</h3>
            </div>
            <div class="card-body">
                <canvas id="gpaChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- Add Grade Form -->
    <?php if (Auth::hasRole('student') || Auth::hasRole('teacher')): ?>
        <div class="card mb-3 slide-up">
            <div class="card-header">
                <h3>➕ เพิ่มผลการเรียน</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('academics/store') ?>">
                    <?= CSRF::field() ?>
                    <style>
                        .academics-input-grid {
                            display: grid;
                            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr auto;
                            gap: 10px;
                            align-items: end;
                        }
                        @media (max-width: 900px) {
                            .academics-input-grid {
                                grid-template-columns: 1fr 1fr;
                            }
                        }
                        @media (max-width: 500px) {
                            .academics-input-grid {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>
                    <div class="academics-input-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">วิชา *</label>
                            <input type="text" name="subject" class="form-control" placeholder="ชื่อวิชา" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">รหัสวิชา</label>
                            <input type="text" name="subject_code" class="form-control" placeholder="เช่น ว30101">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">เทอม *</label>
                            <select name="semester" class="form-control" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">ปีการศึกษา *</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="2568" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">เกรด</label>
                            <input type="number" name="grade" class="form-control" step="0.5" min="0" max="4"
                                placeholder="0-4">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">หน่วยกิต</label>
                            <input type="number" name="credit" class="form-control" value="1" min="0" max="6">
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">➕</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- CSV Import (Teacher) -->
    <?php if (Auth::hasRole('teacher') || Auth::hasRole('admin')): ?>
        <div class="card mb-3 slide-up">
            <div class="card-header">
                <h3>📥 นำเข้าจากไฟล์ CSV</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('academics/import') ?>" enctype="multipart/form-data"
                    style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <?= CSRF::field() ?>
                    <input type="file" name="import_file" accept=".csv" required style="font-size: 0.85rem;">
                    <button type="submit" class="btn btn-sm btn-success">📥 นำเข้า</button>
                </form>
                <p style="font-size: 0.75rem; color: var(--gray-400); margin-top: 8px;">
                    Format CSV: รหัสนักเรียน, วิชา, รหัสวิชา, เทอม, ปีการศึกษา, เกรด, หน่วยกิต
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grade Records -->
    <?php if (empty($grouped)): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3>ยังไม่มีข้อมูลผลการเรียน</h3>
                <p>เพิ่มผลการเรียนได้ที่ฟอร์มด้านบน</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $key => $records): ?>
            <?php
            $parts = explode('_S', $key);
            $year = $parts[0];
            $sem = $parts[1];
            $termGpa = 0;
            $termCredits = 0;
            foreach ($records as $r) {
                if ($r['grade'] !== null) {
                    $termGpa += $r['grade'] * $r['credit'];
                    $termCredits += $r['credit'];
                }
            }
            $termGpa = $termCredits > 0 ? round($termGpa / $termCredits, 2) : 0;
            ?>
            <div class="card mb-2 slide-up">
                <div class="card-header">
                    <h3>📚 ปีการศึกษา
                        <?= e($year) ?> เทอม
                        <?= e($sem) ?>
                    </h3>
                    <span style="font-size: 0.88rem; font-weight: 700; color: var(--primary-600);">GPA:
                        <?= number_format($termGpa, 2) ?>
                    </span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>รหัสวิชา</th>
                                    <th>วิชา</th>
                                    <th style="text-align:center;">เกรด</th>
                                    <th style="text-align:center;">หน่วยกิต</th>
                                    <?php if (Auth::hasRole('student')): ?>
                                    <th style="text-align:center; width:120px;">จัดการ</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $r): ?>
                                    <tr id="row-<?= $r['id'] ?>">
                                        <td style="font-size:0.82rem; color:var(--gray-500);">
                                            <?= e($r['subject_code'] ?: '-') ?>
                                        </td>
                                        <td><strong><?= e($r['subject']) ?></strong></td>
                                        <td style="text-align:center;">
                                            <?php if ($r['grade'] !== null): ?>
                                                <span style="display:inline-block; padding:3px 12px; border-radius:20px; font-weight:800; font-size:0.9rem;
                                                    background:<?= $r['grade']>=3 ? '#dcfce7' : ($r['grade']>=2 ? '#fef9c3' : '#fee2e2') ?>;
                                                    color:<?= $r['grade']>=3 ? '#166534' : ($r['grade']>=2 ? '#854d0e' : '#991b1b') ?>;">
                                                    <?= number_format($r['grade'], 1) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color:var(--gray-300); font-size:0.85rem;">รอเกรด</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:center; font-weight:700;"><?= $r['credit'] ?> นก.</td>
                                        <?php if (Auth::hasRole('student')): ?>
                                        <td style="text-align:center;">
                                            <div style="display:flex; gap:6px; justify-content:center;">
                                                <a href="<?= url('academics/' . $r['id'] . '/edit') ?>"
                                                   class="btn btn-sm"
                                                   style="padding:4px 10px; font-size:0.75rem; border-radius:8px; background:var(--primary-50); color:var(--primary-700); border:1px solid var(--primary-200);">
                                                    ✏️
                                                </a>
                                                <button type="button"
                                                    onclick="confirmDeleteGrade(<?= $r['id'] ?>, '<?= e(addslashes($r['subject'])) ?>')"
                                                    class="btn btn-sm"
                                                    style="padding:4px 10px; font-size:0.75rem; border-radius:8px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca;">
                                                    🗑️
                                                </button>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($gpaByTerm)): ?>
    <script>
        new Chart(document.getElementById('gpaChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(fn($t) => "ปี {$t['academic_year']} เทอม {$t['semester']}", $gpaByTerm)) ?>,
                datasets: [{
                    label: 'GPA',
                    data: <?= json_encode(array_map(fn($t) => (float) $t['term_gpa'], $gpaByTerm)) ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#6366f1',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { min: 0, max: 4, ticks: { stepSize: 0.5 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
<?php endif; ?>

<!-- Hidden Delete Form -->
<form id="delete-grade-form" method="POST" action="" style="display: none;">
    <?= CSRF::field() ?>
</form>

<script>
function confirmDeleteGrade(id, name) {
    if (confirm('คุณต้องการลบผลการเรียนวิชา "' + name + '" หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้')) {
        const form = document.getElementById('delete-grade-form');
        form.action = '<?= url("academics") ?>/' + id + '/delete';
        form.submit();
    }
}
</script>