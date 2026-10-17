<?php $pageTitle = 'Early Warning System (EWS)'; ?>

<div class="mb-4">
    <div class="flex-between">
        <div>
            <h1>⚠️ ระบบสัญญาณเตือนล่วงหน้า (Early Warning System)</h1>
            <p class="text-muted">รายการนักเรียนที่ไม่มีกิจกรรมในระบบเกิน 30 วัน หรือยังไม่มีผลงานปรากฏ</p>
        </div>
        <a href="<?= url('executive/summary') ?>" class="btn btn-outline">← กลับหน้ารวมผู้บริหาร</a>
    </div>
</div>

<div class="card">
    <div class="card-header flex-between">
        <h3>🚨 รายชื่อนักเรียนเฝ้าระวัง (At-Risk Students)</h3>
        <span class="badge badge-danger"><?= count($atRisk) ?> รายการ</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>นักเรียน</th>
                    <th>ห้อง</th>
                    <th>ผลงานรวม</th>
                    <th>กิจกรรมล่าสุด</th>
                    <th>ระดับความเสี่ยง</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($atRisk)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <span style="font-size: 3rem;">✅</span><br>
                            ไม่มีนักเรียนในกลุ่มเสี่ยงขณะนี้ ระบบทำงานได้ดีเยี่ยม!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($atRisk as $student): ?>
                        <tr>
                            <td>
                                <strong><?= e($student['full_name']) ?></strong><br>
                                <span class="text-xs text-muted">รหัส: <?= e($student['student_code']) ?></span>
                            </td>
                            <td>ม.<?= e($student['class_year']) ?>/<?= e($student['class_room']) ?></td>
                            <td><?= $student['total_works'] ?> ชิ้น</td>
                            <td>
                                <?php if ($student['last_activity']): ?>
                                    <span class="text-danger"><?= timeAgo($student['last_activity']) ?></span>
                                <?php else: ?>
                                    <span class="text-danger">ยังไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($student['total_works'] == 0): ?>
                                    <span class="badge badge-danger">ระดับสูง (วิกฤต)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">ระดับกลาง (เตือน)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('teacher/student/' . $student['id']) ?>" class="btn btn-sm btn-outline">👀 ติดตามผล</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 p-4" style="background: var(--amber-50); border: 2px dashed var(--amber-200); border-radius: 12px; display: flex; align-items: flex-start; gap: 16px;">
    <div style="font-size: 2rem;">💡</div>
    <div>
        <h4 style="color: var(--amber-800);">ข้อเสนอแนะเชิงบริหาร (Executive Insight)</h4>
        <p style="color: var(--amber-700); font-size: 0.9rem; margin-top: 4px; line-height: 1.5;">
            นักเรียนในรายการนี้มีความเสี่ยงที่จะ "ไม่มีผลงานสะสม" สำหรับยื่นพอร์ตมหาวิทยาลัยในอนาคต<br>
            <strong>แนะนํา:</strong> ผู้อำนวยการควรแจ้งหัวหน้าสายชั้นเพื่อประสานงานครูที่ปรึกษาให้ลงพื้นที่ (In-Person Check-in) หรือจัด Workshop "Quick Start Portfolio" พิเศษสำหรับกลุ่มนี้ครับ
        </p>
    </div>
</div>
