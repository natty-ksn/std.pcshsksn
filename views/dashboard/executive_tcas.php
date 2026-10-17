<?php $pageTitle = 'TCAS Prediction (จำลองความสำเร็จ)'; ?>

<div class="mb-4">
    <div class="flex-between">
        <div>
            <h1>📈 ระบบจำลองความสำเร็จ (TCAS Prediction)</h1>
            <p class="text-muted">วิเคราะห์แนวโน้มการเข้ามหาวิทยาลัยจากข้อมูล GPA และความแข็งแกร่งของ Portfolio</p>
        </div>
        <a href="<?= url('executive/summary') ?>" class="btn btn-outline">← กลับหน้ารวมผู้บริหาร</a>
    </div>
</div>

<div class="alert alert-info py-4" style="border-radius: 12px; border: 2px solid var(--blue-200); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -30px; top: -30px; font-size: 10rem; opacity: 0.1;">🛰️</div>
    <div style="display: flex; align-items: flex-start; gap: 20px;">
        <div style="font-size: 2.5rem;">⚙️</div>
        <div>
            <h3 style="color: var(--primary-800); margin-bottom: 8px;">เกณฑ์การประเมินเบื้องต้นโดย AI (Executive Analysis Logic)</h3>
            <ul style="font-size: 0.9rem; color: var(--primary-700); line-height: 1.6;">
                <li><strong>ดาวเด่น (Top Tier):</strong> GPA ≥ 3.50 + มีระดับผลงานชาติ/สากล ≥ 1 ชิ้น</li>
                <li><strong>โดดเด่น (High Potential):</strong> GPA ≥ 3.20 + มีผลงานปักหมุด ≥ 3 ชิ้น</li>
                <li><strong>พร้อมรุก (Ready to Compete):</strong> GPA ≥ 2.80 + มีเป้าหมายชัดเจน (Goal-set)</li>
            </ul>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header flex-between">
        <h3>🎯 รายชื่อนักเรียนที่มีแนวโน้มความสำเร็จสูง (Top Candidates)</h3>
        <span class="text-muted text-sm">เรียงตามความแข็งแกร่งของข้อมูล</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container" style="margin-bottom: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>นักเรียน</th>
                    <th>ห้อง</th>
                    <th>GPA รวม</th>
                    <th>National Awards</th>
                    <th>เป้าหมาย/คณะ</th>
                    <th>ทิศทางความแม่นยำ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prediction as $row): ?>
                    <tr>
                        <td>
                            <strong><?= e($row['full_name']) ?></strong><br>
                            <span class="text-xs text-muted">ม.<?= e($row['class_year']) ?></span>
                        </td>
                        <td>ม.<?= e($row['class_year']) ?>/<?= e($row['class_room']) ?></td>
                        <td>
                            <strong class="text-primary"><?= number_format($row['gpa'], 2) ?></strong>
                        </td>
                        <td>
                            <?php if ($row['national_count'] > 0): ?>
                                <span class="badge badge-success">🏆 <?= $row['national_count'] ?> รางวัล</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">0</span>
                            <?php endif; ?>
                        </td>
                        <td title="<?= e($row['targets']) ?>">
                            <span class="text-sm"><?= e(truncate($row['targets'] ?: 'ยังไม่ตั้งเป้า', 30)) ?></span>
                        </td>
                        <td>
                            <?php if ($row['gpa'] >= 3.5 && ($row['national_count'] > 0 || $row['featured_count'] >= 5)): ?>
                                <span class="badge" style="background: #ecfdf5; color: #059669; border: 1px solid #10b981;">🚀 Very Likely</span>
                            <?php elseif ($row['gpa'] >= 3.0 && $row['featured_count'] >= 3): ?>
                                <span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #3b82f6;">⭐ Likely</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">In Development</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="mt-4 p-4 text-center" style="background: white; border-radius: 12px; border: 1px solid var(--gray-200);">
    <p class="text-muted text-sm">หมายเหตุ: ข้อมูลนี้เป็นเพียงการจำลองเพื่อการวางแผนเชิงรุกเท่านั้น ผลลัพธ์จริงขึ้นอยู่กับระเบียบการของแต่ละปีและการสมัครของนักเรียน</p>
</div>
