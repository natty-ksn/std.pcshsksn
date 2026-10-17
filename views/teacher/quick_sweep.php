<?php $pageTitle = 'ตรวจสอบและคัดกรองผลงานด่วน (Quick Audit)'; ?>

<div class="animate-fade-in" style="max-width: 1280px; margin: 0 auto;">

    <!-- Premium Header -->
    <div class="glass-card mb-4" style="padding: 36px; border-radius: 24px; background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; border: none; box-shadow: var(--shadow-2xl);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; color: #94a3b8;">ระบบสนับสนุนการประเมินผลงานวิชาการ</div>
                <h2 style="color: #fff; font-weight: 900; margin: 8px 0; letter-spacing: -0.5px;">⚡ ตรวจสอบและคัดกรองผลงานด่วน (Quick Audit)</h2>
                <p style="color: #cbd5e1; margin: 0;">ระบบตรวจสอบความสมบูรณ์ของข้อมูลผลงานเบื้องต้น ก่อนดำเนินการตรวจรับรองทางวิชาการ</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px;">ผลงานที่ตรวจสอบ</div>
                <div style="font-size: 2.4rem; font-weight: 900; color: #fff;"><?= (int) $scannedCount ?></div>
                <div style="font-size: 0.85rem; color: #fca5a5;">พบรายการที่ต้องปรับปรุง <strong><?= count($report) ?></strong> รายการ</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
                <div class="form-group mb-0">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="class_year" class="form-control">
                        <option value="">ทั้งหมด</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= $classYear == $i ? 'selected' : '' ?>>ม.<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">เกณฑ์ขั้นต่ำของบทคัดย่อ (ตัวอักษร)</label>
                    <input type="number" name="min_desc" value="<?= (int) $minDescLen ?>" min="20" max="2000" class="form-control" style="width: 180px;">
                </div>
                <button type="submit" class="btn btn-primary">🔄 ตรวจสอบใหม่</button>
                <a href="<?= url('teacher/quick-sweep?format=json' . ($classYear ? '&class_year=' . $classYear : '')) ?>" target="_blank" class="btn btn-secondary">📦 ดูข้อมูล JSON</a>
            </form>
        </div>
    </div>

    <?php if (empty($report)): ?>
        <div class="text-center py-5 glass-card" style="padding: 60px;">
            <div style="font-size: 4rem;">✨</div>
            <h3 class="mt-3">ข้อมูลผลงานมีความสมบูรณ์ครบถ้วน</h3>
            <p class="text-muted">ไม่พบรายการที่ต้องปรับปรุงในผลงานที่รอตรวจ พร้อมเข้าสู่กระบวนการรับรองผลงาน</p>
        </div>
    <?php else: ?>

        <?php
        $sevColors = [
            'critical' => ['#7f1d1d', '#fee2e2', '🚨', 'ต้องแก้ไขเร่งด่วน'],
            'issue' => ['#9a3412', '#ffedd5', '⚠️', 'ควรปรับปรุง'],
            'suggestion' => ['#1e40af', '#dbeafe', '💡', 'ข้อเสนอแนะ'],
            'info' => ['#1f2937', '#f3f4f6', 'ℹ️', 'ข้อมูลทั่วไป'],
        ];
        ?>

        <div class="grid-1 gap-3">
            <?php foreach ($report as $row): ?>
                <?php $sev = $sevColors[$row['overall_severity']] ?? $sevColors['info']; ?>
                <div class="glass-card" style="border-left: 5px solid <?= $sev[0] ?>; padding: 22px; border-radius: 16px;">
                    <div class="d-flex gap-3 align-items-start">
                        <div style="width: 80px; height: 80px; border-radius: 12px; overflow: hidden; background: var(--gray-100); flex-shrink: 0;">
                            <?php if (!empty($row['thumbnail'])): ?>
                                <img src="<?= uploadUrl('portfolio/' . $row['thumbnail']) ?>" style="width:100%; height:100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="display:flex; align-items:center; justify-content:center; height:100%; font-size: 2.2rem;"><?= e($row['category_icon'] ?? '📄') ?></div>
                            <?php endif; ?>
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <div>
                                    <h4 style="margin: 0; font-weight: 800; color: var(--gray-900);"><?= e($row['title']) ?></h4>
                                    <div style="font-size: 0.85rem; color: var(--gray-500); margin-top: 4px;">
                                        โดย <strong><?= e($row['full_name']) ?></strong> · ม.<?= (int) $row['class_year'] ?>/<?= e($row['class_room']) ?>
                                        <?php if (!empty($row['category_name'])): ?>
                                            · <span><?= e($row['category_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span style="background: <?= $sev[1] ?>; color: <?= $sev[0] ?>; font-weight: 800; font-size: 0.75rem; padding: 6px 14px; border-radius: 50px; text-transform: uppercase; letter-spacing: 1px;">
                                    <?= $sev[2] ?> <?= $sev[3] ?> · <?= (int) $row['issue_count'] ?> จุด
                                </span>
                            </div>

                            <div style="margin-top: 14px; display: grid; gap: 8px;">
                                <?php foreach ($row['issues'] as $issue): ?>
                                    <?php $isev = $sevColors[$issue['severity']] ?? $sevColors['info']; ?>
                                    <div style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: <?= $isev[1] ?>; border-radius: 10px;">
                                        <div style="font-size: 1rem;"><?= $isev[2] ?></div>
                                        <div style="flex: 1;">
                                            <div style="font-size: 0.7rem; font-weight: 800; color: <?= $isev[0] ?>; text-transform: uppercase; letter-spacing: 1px;"><?= e($issue['code']) ?></div>
                                            <div style="font-size: 0.88rem; color: var(--gray-800);"><?= e($issue['message']) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div style="margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="<?= url('portfolio/' . $row['id']) ?>" class="btn btn-sm btn-primary" style="border-radius: 50px;">🔍 เปิดผลงานเพื่อตรวจ</a>
                                <a href="<?= url('portfolio/' . $row['id']) ?>#annotation-form" class="btn btn-sm btn-secondary" style="border-radius: 50px;">✏️ เขียนข้อเสนอแนะเฉพาะจุด</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
