<?php
$pageTitle = 'ทำเนียบเหรียญรางวัลความสำเร็จ';
/**
 * Badge Collection View — Gamification
 */
$summary = $summary ?? ['total_badges' => 0, 'earned' => 0, 'progress_pct' => 0, 'by_tier' => [], 'recent' => []];
$allDefs = $allDefs ?? [];
$userBadges = $userBadges ?? [];
$newBadges = $newBadges ?? ['new_badges' => [], 'total' => 0];
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.8rem; font-weight:900; color:#0f172a; margin:0 0 4px;">🏅 ทำเนียบเหรียญรางวัลความสำเร็จ</h1>
        <p style="color:#64748b; font-size:0.95rem; margin:0;">สะสมเหรียญรางวัลเพื่อสะท้อนความก้าวหน้าและทักษะรอบด้าน</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('rewards') ?>" class="btn btn-sm" style="border-radius:10px; background:#f1f5f9; color:#334155; font-weight:600; text-decoration:none; padding:8px 16px;">🎁 แลกรางวัล</a>
    </div>
</div>

<!-- New Badge Celebration -->
<?php if ($newBadges['total'] > 0): ?>
    <div class="glass-card p-4 mb-5 text-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 20px; border: 2px solid #f59e0b; animation: fadeIn 0.5s ease;">
        <div style="font-size: 3rem;">🎉</div>
        <h2 style="margin: 8px 0; font-size: 1.3rem; font-weight: 900; color: #92400e;">ยินดีด้วย! คุณได้รับเหรียญใหม่!</h2>
        <div class="d-flex justify-content-center gap-3 flex-wrap mt-3">
            <?php foreach ($newBadges['new_badges'] as $nb): ?>
                <div style="background: white; border-radius: 16px; padding: 16px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-align: center; min-width: 120px;">
                    <div style="font-size: 2.5rem;"><?= $nb['icon'] ?></div>
                    <?php
                        $isLightTier = in_array(strtolower($nb['tier'] ?? ''), ['gold', 'silver', 'platinum', 'bronze']);
                        $tierTextColor = $isLightTier ? '#0f172a' : '#ffffff';
                    ?>
                    <span class="badge" style="background: <?= $nb['color'] ?>; color: <?= $tierTextColor ?>; font-weight: 700; font-size: 0.65rem; padding: 2px 8px; border-radius: 99px; margin-top: 4px; display: inline-block; text-transform: uppercase;"><?= $nb['tier'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Overall Progress -->
<div class="glass-card p-5 mb-5" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-radius: 24px; border: 2px solid #818cf8;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
        <div>
            <h2 style="margin: 0 0 8px; font-size: 1.4rem; font-weight: 900; color: #312e81;">🎖️ สรุปภาพรวมการสะสม</h2>
            <p style="color: #4f46e5; margin: 0; font-size: 0.95rem;">เหรียญที่ได้รับ: <strong><?= $summary['earned'] ?></strong> / <?= $summary['total_badges'] ?> เหรียญ</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 3rem; font-weight: 900; color: #4338ca;"><?= $summary['progress_pct'] ?>%</div>
            <div style="font-size: 0.75rem; color: #6366f1; text-transform: uppercase; letter-spacing: 1px;">ความคืบหน้า</div>
        </div>
    </div>
    <div style="background: #c7d2fe; height: 12px; border-radius: 99px; overflow: hidden; margin-top: 16px;">
        <div style="background: linear-gradient(90deg, #6366f1, #8b5cf6); height: 12px; width: <?= $summary['progress_pct'] ?>%; border-radius: 99px; transition: width 1s ease;"></div>
    </div>
</div>

<!-- Tier Progress Cards -->
<div class="mb-5" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
    <?php
    $tierConfig = [
        'bronze'   => ['name' => 'บรอนซ์',  'icon' => '🥉', 'color' => '#cd7f32', 'bg' => '#fef3c7'],
        'silver'   => ['name' => 'เงิน',    'icon' => '🥈', 'color' => '#c0c0c0', 'bg' => '#f1f5f9'],
        'gold'     => ['name' => 'ทอง',     'icon' => '🥇', 'color' => '#ffd700', 'bg' => '#fefce8'],
        'platinum' => ['name' => 'แพลทินัม','icon' => '💎', 'color' => '#e5e4e2', 'bg' => '#f0f9ff'],
        'secret'   => ['name' => 'ลับ',     'icon' => '🔮', 'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
    ];
    foreach ($tierConfig as $tier => $tc):
        $tierData = $summary['by_tier'][$tier] ?? ['earned' => 0, 'total' => 0, 'badges' => []];
        $pct = $tierData['total'] > 0 ? round(($tierData['earned'] / $tierData['total']) * 100, 1) : 0;
    ?>
        <div class="glass-card" style="border-radius: 16px; padding: 20px; background: <?= $tc['bg'] ?>; border: 2px solid <?= $tc['color'] ?>44;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="font-size: 1.5rem;"><?= $tc['icon'] ?></span>
                <div>
                    <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= $tc['name'] ?></div>
                    <div style="font-size: 0.75rem; color: #64748b;"><?= $tierData['earned'] ?>/<?= $tierData['total'] ?></div>
                </div>
            </div>
            <div style="background: <?= $tc['color'] ?>33; height: 6px; border-radius: 99px; overflow: hidden;">
                <div style="background: <?= $tc['color'] ?>; height: 6px; width: <?= $pct ?>%; border-radius: 99px;"></div>
            </div>
            <!-- Recent badges in this tier -->
            <?php if (!empty($tierData['badges'])): ?>
                <div class="d-flex flex-wrap gap-1 mt-3">
                    <?php foreach (array_slice($tierData['badges'], 0, 5) as $ub): ?>
                        <div title="<?= htmlspecialchars($ub['name']) ?>" style="font-size: 1.5rem; cursor: default;" class="animate-pulse">
                            <?= $ub['icon'] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- All Badges Grid -->
<h2 style="font-size:1.2rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">📋 เหรียญทั้งหมด</h2>

<?php foreach ($tierConfig as $tier => $tc): ?>
    <?php
    $tierDefs = array_filter($allDefs, fn($d) => $d['tier'] === $tier);
    if (empty($tierDefs)) continue;
    $userBadgeCodes = array_column($userBadges, 'badge_code');
    ?>
    <div class="mb-5">
        <h3 style="font-size:1rem; font-weight:800; color: <?= $tc['color'] ?>; margin-bottom: 12px;">
            <?= $tc['icon'] ?> <?= $tc['name'] ?> (<?= count(array_filter($tierDefs, fn($d) => in_array($d['code'], $userBadgeCodes))) ?>/<?= count($tierDefs) ?>)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
            <?php foreach ($tierDefs as $code => $def):
                $earned = in_array($code, $userBadgeCodes);
                $earnedAt = null;
                foreach ($userBadges as $ub) {
                    if ($ub['badge_code'] === $code) { $earnedAt = $ub['earned_at']; break; }
                }
            ?>
                <div style="border-radius: 14px; padding: 16px; background: <?= $earned ? 'white' : '#f8fafc' ?>; border: 2px solid <?= $earned ? $tc['color'] : '#e2e8f0' ?>; transition: all 0.3s; position: relative; <?= !$earned ? 'opacity: 0.5;' : '' ?>">
                    <div class="d-flex align-items-start gap-3">
                        <div style="font-size: 2rem; filter: <?= $earned ? 'none' : 'grayscale(100%)' ?>; min-width: 40px;">
                            <?= $def['icon'] ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem;"><?= $def['name'] ?></div>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px; line-height: 1.4;"><?= $def['description'] ?></div>
                            <?php if ($earned && $earnedAt): ?>
                                <div style="font-size: 0.7rem; color: #15803d; margin-top: 6px;">
                                    ✅ ได้รับเมื่อ <?= date('d/m/Y', strtotime($earnedAt)) ?>
                                </div>
                            <?php elseif (!$earned): ?>
                                <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 6px;">🔒 ยังไม่ปลดล็อก</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Tips -->
<div class="glass-card p-4 mt-5" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 16px; border: 1px solid #86efac;">
    <h3 style="margin: 0 0 8px; font-size: 1rem; font-weight: 800; color: #166534;">💡 วิธีรับเหรียญ</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; font-size: 0.85rem; color: #15803d;">
        <div>🌟 <strong>เพิ่มผลงาน</strong> — สร้างผลงานหลากหลายหมวดหมู่</div>
        <div>📝 <strong>บันทึก Logbook</strong> — บันทึกกิจกรรมสม่ำเสมอ</div>
        <div>🤝 <strong>ร่วมโครงงาน</strong> — เข้าร่วมและนำทีม</div>
        <div>🏆 <strong>แข่งขัน</strong> — สะสมรางวัลระดับภายนอก</div>
        <div>🧭 <strong>ทำ RIASEC</strong> — รู้จักตนเอง</div>
        <div>🎯 <strong>ตั้งเป้าหมาย</strong> — วางแผนอนาคต</div>
    </div>
</div>
