<?php
$pageTitle = 'ตรวจสอบคุณภาพพอร์ตโฟลิโอ';
/**
 * Portfolio Quality Audit View — Phase 4 AI Insights
 */
$audit = $audit ?? ['total_items' => 0, 'avg_score' => 0, 'avg_grade' => 'F', 'items' => [], 'items_with_issues' => []];
$smartRecs = $smartRecs ?? ['recommendations' => [], 'total_gaps' => 0, 'top_track' => null];
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.8rem; font-weight:900; color:#0f172a; margin:0 0 4px;">🔍 ตรวจสอบคุณภาพพอร์ตโฟลิโอ</h1>
        <p style="color:#64748b; font-size:0.95rem; margin:0;">Portfolio Quality Audit — AI Content Quality Analysis</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('ai/field-readiness') ?>" class="btn btn-sm" style="border-radius:10px; background:#f1f5f9; color:#334155; font-weight:600; text-decoration:none; padding:8px 16px;">🎯 วิเคราะห์สาขาวิชา</a>
        <a href="<?= url('ai-guide') ?>" class="btn btn-sm btn-primary" style="border-radius:10px; padding:8px 16px;">🤖 AI Guide</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="mb-5" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
    <div class="glass-card p-4 text-center" style="border-radius: 16px; background: linear-gradient(135deg, #eef2ff, #e0e7ff);">
        <div style="font-size: 2.5rem; font-weight: 900; color: #3730a3;"><?= $audit['total_items'] ?></div>
        <div style="font-size: 0.8rem; color: #6366f1; text-transform: uppercase; letter-spacing: 1px;">ผลงานทั้งหมด</div>
    </div>
    <div class="glass-card p-4 text-center" style="border-radius: 16px; background: linear-gradient(135deg, #f0fdf4, #dcfce7);">
        <div style="font-size: 2.5rem; font-weight: 900; color: #166534;"><?= $audit['avg_score'] ?></div>
        <div style="font-size: 0.8rem; color: #15803d; text-transform: uppercase; letter-spacing: 1px;">คะแนนเฉลี่ย/100</div>
    </div>
    <div class="glass-card p-4 text-center" style="border-radius: 16px; background: linear-gradient(135deg, #fefce8, #fef3c7);">
        <div style="font-size: 2.5rem; font-weight: 900; color: #92400e;"><?= $audit['avg_grade'] ?></div>
        <div style="font-size: 0.8rem; color: #ca8a04; text-transform: uppercase; letter-spacing: 1px;">เกรดเฉลี่ย</div>
    </div>
    <div class="glass-card p-4 text-center" style="border-radius: 16px; background: linear-gradient(135deg, #fef2f2, #fee2e2);">
        <div style="font-size: 2.5rem; font-weight: 900; color: #991b1b;"><?= $audit['issue_count'] ?? 0 ?></div>
        <div style="font-size: 0.8rem; color: #dc2626; text-transform: uppercase; letter-spacing: 1px;">ผลงานต้องแก้ไข</div>
    </div>
</div>

<?php if ($audit['total_items'] === 0): ?>
    <div class="glass-card p-5 text-center" style="border-radius: 20px; background: #f8fafc;">
        <div style="font-size: 4rem;">📭</div>
        <h3 style="color: #475569; margin: 12px 0;">ยังไม่มีผลงาน</h3>
        <p style="color: #94a3b8;">เริ่มเพิ่มผลงานเพื่อตรวจสอบคุณภาพ</p>
        <a href="<?= url('portfolio/create') ?>" class="btn btn-primary" style="border-radius: 12px; padding: 12px 32px; font-weight: 700; text-decoration: none;">➕ เพิ่มผลงานแรก</a>
    </div>
<?php else: ?>

<!-- Items Quality Table -->
<h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">📊 คุณภาพรายผลงาน</h2>
<div class="glass-card" style="border-radius: 16px; overflow: hidden; margin-bottom: 32px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #0f172a;">ผลงาน</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #0f172a;">หมวดหมู่</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">คะแนน</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">เกรด</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #0f172a;">ปัญหา</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($audit['items'] as $item):
                    $gradeColor = match($item['grade']) {
                        'A' => '#15803d',
                        'B+' => '#166534',
                        'B' => '#ca8a04',
                        'C+' => '#d97706',
                        'C' => '#ea580c',
                        'D' => '#dc2626',
                        default => '#991b1b',
                    };
                ?>
                    <tr style="border-top: 1px solid #e2e8f0; <?= !empty($item['issues']) ? 'background: #fef2f2;' : '' ?>">
                        <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                            <?= htmlspecialchars($item['title']) ?>
                            <?php if (!empty($item['issues'])): ?>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.7rem; margin-left: 6px;">ต้องแก้ไข</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 16px; color: #475569;"><?= htmlspecialchars($item['category'] ?? '-') ?></td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <div style="background: #e2e8f0; height: 6px; border-radius: 99px; width: 60px; overflow: hidden;">
                                    <div style="background: <?= $gradeColor ?>; height: 6px; width: <?= $item['score'] ?>%; border-radius: 99px;"></div>
                                </div>
                                <span style="font-weight: 700; color: #0f172a;"><?= $item['score'] ?></span>
                            </div>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="font-weight: 800; color: <?= $gradeColor ?>; font-size: 1.1rem;"><?= $item['grade'] ?></span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <?php if (!empty($item['issues'])): ?>
                                <?php foreach ($item['issues'] as $issue): ?>
                                    <div style="font-size: 0.75rem; color: #dc2626; margin-bottom: 2px;">⚠️ <?= htmlspecialchars($issue) ?></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #15803d; font-size: 0.8rem;">✅ ผ่าน</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <a href="<?= url('portfolio/' . ($item['id'] ?? '') . '/edit') ?>" class="btn btn-sm btn-outline" style="font-size: 0.75rem; padding: 4px 10px;">✏️ แก้ไข</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Smart Recommendations -->
<?php if (!empty($smartRecs['recommendations'])): ?>
    <h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">🤖 คำแนะนำอัจฉริยะ</h2>
    <?php if ($smartRecs['top_track']): ?>
        <div class="glass-card p-3 mb-4" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-radius: 14px; border: 1px solid #818cf8;">
            <div style="font-size: 0.85rem; color: #4f46e5;">
                🎯 สาขาที่เหมาะสมที่สุด: <strong><?= htmlspecialchars($smartRecs['top_track']['track_name']) ?></strong>
                (ความพร้อม <strong><?= $smartRecs['top_track']['readiness_pct'] ?>%</strong>)
            </div>
        </div>
    <?php endif; ?>

    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($smartRecs['recommendations'] as $rec):
            $priorityColors = [
                'high'   => ['bg' => '#fee2e2', 'border' => '#dc2626', 'text' => '#991b1b'],
                'medium' => ['bg' => '#fef3c7', 'border' => '#f59e0b', 'text' => '#92400e'],
                'low'    => ['bg' => '#dbeafe', 'border' => '#3b82f6', 'text' => '#1e40af'],
            ];
            $pc = $priorityColors[$rec['priority']] ?? $priorityColors['low'];
        ?>
            <div style="background: <?= $pc['bg'] ?>; border-left: 4px solid <?= $pc['border'] ?>; padding: 14px 18px; border-radius: 12px;">
                <div class="d-flex align-items-start gap-2">
                    <span style="font-size: 1.2rem;"><?= $rec['icon'] ?? '💡' ?></span>
                    <div>
                        <span class="badge" style="background: <?= $pc['border'] ?>22; color: <?= $pc['text'] ?>; font-size: 0.65rem; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.5px;"><?= $rec['priority'] ?></span>
                        <div style="margin-top: 4px; font-size: 0.85rem; color: <?= $pc['text'] ?>;"><?= htmlspecialchars($rec['text']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="glass-card p-4 text-center" style="background: #f0fdf4; border-radius: 16px; border: 2px solid #86efac;">
        <div style="font-size: 2rem;">✅</div>
        <h3 style="margin: 8px 0; font-size: 1.1rem; font-weight: 800; color: #166534;">พอร์ตโฟลิโอของคุณมีคุณภาพดี!</h3>
        <p style="color: #15803d; font-size: 0.85rem;">ไม่พบปัญหาสำคัญ — รักษามาตรฐานนี้ไว้</p>
    </div>
<?php endif; ?>

<?php endif; ?>

<!-- Scoring Guide -->
<div class="mt-5 mb-5">
    <h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">📖 เกณฑ์การให้คะแนน</h2>
    <div class="glass-card p-4" style="border-radius: 16px; background: #f8fafc;">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
            <div>
                <strong style="color: #0f172a;">📝 คำอธิบาย (25 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">≥200 ตัวอักษร = 25 | ≥100 = 15 | ≥50 = 8 | &lt;50 = ต่ำ</div>
            </div>
            <div>
                <strong style="color: #0f172a;">🔧 ทักษะ (20 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">ทักษะละ 5 คะแนน (สูงสุด 4 ทักษะ)</div>
            </div>
            <div>
                <strong style="color: #0f172a;">🏅 ระดับความสำเร็จ (20 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">School=4 | District=8 | Province=12 | National=16 | International=20</div>
            </div>
            <div>
                <strong style="color: #0f172a;">🏆 รางวัล (15 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">มีรางวัล = 15 | ไม่มี = 0</div>
            </div>
            <div>
                <strong style="color: #0f172a;">📎 ไฟล์แนบ (10 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">มีไฟล์ = 10 | มีเฉพาะรูป = 5 | ไม่มี = 0</div>
            </div>
            <div>
                <strong style="color: #0f172a;">📁 ความสอดคล้องหมวดหมู่ (10 คะแนน)</strong>
                <div style="color: #64748b; margin-top: 4px;">คะแนนเต็ม (ตรวจสอบอัตโนมัติ)</div>
            </div>
        </div>
    </div>
</div>
