<?php
$pageTitle = 'วิเคราะห์ความพร้อมรายสาขาวิชา';
/**
 * Field Readiness Analyzer View — Phase 4 AI Insights
 */
$a = $analysis ?? [];
$topMatches = $a['top_matches'] ?? [];
$fieldReadiness = $a['field_readiness'] ?? [];
$single = $single ?? null;
$selectedTrack = $selectedTrack ?? '';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.8rem; font-weight:900; color:#0f172a; margin:0 0 4px;">🎯 วิเคราะห์ความพร้อมรายสาขาวิชา</h1>
        <p style="color:#64748b; font-size:0.95rem; margin:0;">Field-Specific Readiness Analyzer — AI-Powered Gap Analysis</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('ai/portfolio-audit') ?>" class="btn btn-sm" style="border-radius:10px; background:#f1f5f9; color:#334155; font-weight:600; text-decoration:none; padding:8px 16px;">🔍 ตรวจคุณภาพพอร์ต</a>
        <a href="<?= url('ai-guide') ?>" class="btn btn-sm btn-primary" style="border-radius:10px; padding:8px 16px;">🤖 AI Guide</a>
    </div>
</div>

<!-- Student Profile Summary -->
<div class="glass-card p-4 mb-5" style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 20px;">
    <div class="d-flex align-items-center gap-3 mb-2">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: #1e3a8a; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 800;">
            <?= e(mb_substr($a['student_profile']['full_name'] ?? 'S', 0, 1, 'UTF-8')) ?>
        </div>
        <div>
            <h3 style="margin:0; font-size:1.3rem; font-weight:800; color:#0f172a;"><?= $a['student_profile']['full_name'] ?? 'นักเรียน' ?></h3>
            <p style="margin:0; color:#64748b; font-size:0.85rem;">
                ม.<?= $a['student_profile']['class_year'] ?? '-' ?>/<?= $a['student_profile']['class_room'] ?? '-' ?>
                &middot; GPA <strong style="color:#1e3a8a;"><?= $a['gpa'] ?? 'N/A' ?></strong>
                &middot; <?= $a['portfolio_profile']['total_count'] ?? 0 ?> ผลงาน
                &middot; <?= $a['logbook_hours'] ?? 0 ?> ชม. Logbook
            </p>
        </div>
    </div>
    <?php if (!empty($a['current_goal'])): ?>
        <div style="background: #dbeafe; border-left: 3px solid #1e40af; padding: 8px 14px; border-radius: 8px; margin-top: 10px; font-size: 0.85rem;">
            🎯 เป้าหมายปัจจุบัน: <strong><?= $a['current_goal']['goal']['target'] ?? '-' ?></strong> → สาขา <strong><?= $a['current_goal']['track_name'] ?? '-' ?></strong>
        </div>
    <?php endif; ?>
</div>

<!-- Top Matches — Rank Cards -->
<h2 style="font-size:1.2rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">🏆 สาขาที่เหมาะสมที่สุด</h2>
<div class="mb-5" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
    <?php foreach ($topMatches as $i => $tm):
        $pct = max(0, min(100, (int) round($tm['readiness_pct'])));
        $barColor = $pct >= 80 ? '#15803d' : ($pct >= 60 ? '#ca8a04' : '#dc2626');
        $rankEmoji = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '🏅'));
    ?>
        <div class="glass-card" style="border-radius: 16px; padding: 20px; border: 2px solid <?= $barColor ?>33; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit;"
             onclick="window.location.href='<?= url('ai/field-readiness?track=' . $tm['track_code']) ?>'"
             onmouseover="this.style.borderColor='<?= $barColor ?>'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)'"
             onmouseout="this.style.borderColor='<?= $barColor ?>33'; this.style.transform='none'; this.style.boxShadow='none'">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span style="font-size: 1.2rem;"><?= $rankEmoji ?></span>
                    <h4 style="margin: 4px 0 0; font-size: 1rem; font-weight: 800; color: #0f172a;"><?= $tm['track_name'] ?></h4>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 900; color: <?= $barColor ?>;"><?= $pct ?>%</div>
                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">ความพร้อม</div>
                </div>
            </div>
            <div style="background: #e2e8f0; border-radius: 99px; height: 8px; overflow: hidden; margin-bottom: 8px;">
                <div style="background: <?= $barColor ?>; height: 8px; width: <?= $pct ?>%; border-radius: 99px; transition: width 0.8s ease;"></div>
            </div>
            <div style="font-size: 0.8rem; color: #64748b;">
                <?= $tm['gap_count'] ?? 0 ?> ช่องว่างที่ต้องเติม
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Single Field Detail View -->
<?php if ($single && !isset($single['error'])): ?>
    <div class="glass-card p-4 mb-5" style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border-radius: 20px; border: 2px solid #0ea5e9;">
        <h2 style="font-size:1.3rem; font-weight:800; color:#0f172a; margin:0 0 20px;">📊 รายละเอียด: <?= $single['track_name'] ?> (<?= $single['track_code'] ?>)</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Scores Breakdown -->
            <div>
                <h4 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 12px;">คะแนนรายองค์ประกอบ</h4>
                <?php
                $scoreLabels = [
                    'gpa' => '📚 GPA',
                    'categories' => '📁 หมวดหมู่ผลงาน',
                    'skills' => '🔧 ทักษะ',
                    'riasec' => '🧭 RIASEC',
                    'achievement' => '🏅 ระดับความสำเร็จ',
                    'logbook' => '📓 ชั่วโมง Logbook',
                    'awards' => '🏆 รางวัล',
                ];
                $weights = [
                    'gpa' => '20%', 'categories' => '25%', 'skills' => '15%',
                    'riasec' => '15%', 'achievement' => '10%', 'logbook' => '5%', 'awards' => '10%',
                ];
                foreach (($single['scores'] ?? []) as $key => $val):
                    $barColor = $val >= 80 ? '#15803d' : ($val >= 50 ? '#ca8a04' : '#dc2626');
                ?>
                    <div style="margin-bottom: 10px;">
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                            <span style="color: #334155;"><?= $scoreLabels[$key] ?? $key ?> <span style="color: #94a3b8; font-size: 0.75rem;">(น้ำหนัก <?= $weights[$key] ?? '' ?>)</span></span>
                            <span style="font-weight: 700; color: <?= $barColor ?>;"><?= $val ?>%</span>
                        </div>
                        <div style="background: #e2e8f0; height: 6px; border-radius: 99px; overflow: hidden;">
                            <div style="background: <?= $barColor ?>; height: 6px; width: <?= max(0, min(100, $val)) ?>%; border-radius: 99px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Gaps & Strengths -->
            <div>
                <h4 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 12px;">จุดแข็งและช่องว่าง</h4>
                <?php if (!empty($single['strengths'])): ?>
                    <div style="background: #dcfce7; border-radius: 12px; padding: 12px; margin-bottom: 12px;">
                        <strong style="color: #166534; font-size: 0.85rem;">✅ จุดแข็ง (<?= count($single['strengths']) ?>)</strong>
                        <?php foreach ($single['strengths'] as $s): ?>
                            <div style="font-size: 0.8rem; color: #166534; margin-top: 4px;">&bull; <?= htmlspecialchars($s) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($single['gaps'])): ?>
                    <div style="background: #fee2e2; border-radius: 12px; padding: 12px;">
                        <strong style="color: #991b1b; font-size: 0.85rem;">❌ ช่องว่าง (<?= count($single['gaps']) ?>)</strong>
                        <?php foreach ($single['gaps'] as $g): ?>
                            <div style="font-size: 0.8rem; color: #991b1b; margin-top: 4px;">&bull; <?= htmlspecialchars($g) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommendations -->
        <?php if (!empty($single['recommendations'])): ?>
            <div style="margin-top: 20px;">
                <h4 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 12px;">💡 คำแนะนำ</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach ($single['recommendations'] as $rec): ?>
                        <div style="background: #f8fafc; border-left: 3px solid #1e3a8a; padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; color: #334155;">
                            <?= htmlspecialchars($rec) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- All Fields Comparison Table -->
<h2 style="font-size:1.2rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">📋 เปรียบเทียบทุกสาขาวิชา</h2>
<div class="glass-card" style="border-radius: 16px; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #0f172a;">สาขาวิชา</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">ความพร้อม</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">GPA</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">หมวดผลงาน</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">ทักษะ</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">RIASEC</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;">ช่องว่าง</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #0f172a;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $allFields = $fieldReadiness;
                uasort($allFields, fn($a, $b) => $b['readiness_pct'] <=> $a['readiness_pct']);
                foreach ($allFields as $code => $fr):
                    $pct = max(0, min(100, (int) round($fr['readiness_pct'])));
                    $barColor = $pct >= 80 ? '#15803d' : ($pct >= 60 ? '#ca8a04' : '#dc2626');
                ?>
                    <tr style="border-top: 1px solid #e2e8f0; <?= $code === $selectedTrack ? 'background: #f0f9ff;' : '' ?>">
                        <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;"><?= $fr['track_name'] ?></td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="font-weight: 800; color: <?= $barColor ?>; font-size: 1.1rem;"><?= $pct ?>%</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center; color: #475569;"><?= $fr['scores']['gpa'] ?? '-' ?>%</td>
                        <td style="padding: 12px 16px; text-align: center; color: #475569;"><?= $fr['scores']['categories'] ?? '-' ?>%</td>
                        <td style="padding: 12px 16px; text-align: center; color: #475569;"><?= $fr['scores']['skills'] ?? '-' ?>%</td>
                        <td style="padding: 12px 16px; text-align: center; color: #475569;"><?= $fr['scores']['riasec'] ?? '-' ?>%</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span class="badge" style="background: <?= $fr['gap_count'] > 4 ? '#fee2e2' : ($fr['gap_count'] > 2 ? '#fef3c7' : '#dcfce7') ?>; color: <?= $fr['gap_count'] > 4 ? '#991b1b' : ($fr['gap_count'] > 2 ? '#92400e' : '#166534') ?>; padding: 3px 10px; border-radius: 99px; font-weight: 600; font-size: 0.75rem;">
                                <?= $fr['gap_count'] ?>
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <a href="<?= url('ai/field-readiness?track=' . $code) ?>" style="color: #1e3a8a; font-weight: 600; text-decoration: none; font-size: 0.8rem;">
                                ดูรายละเอียด →
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Smart Recommendations Panel -->
<?php if (!empty($a['recommendations'])): ?>
    <div class="mt-5">
        <h2 style="font-size:1.2rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">💡 คำแนะนำอัจฉริยะ (จาก Gap Analysis)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 12px;">
            <?php foreach (array_slice($a['recommendations'], 0, 12) as $rec): ?>
                <div style="background: #fff; border-radius: 12px; padding: 14px 18px; border-left: 4px solid #1e3a8a; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <span style="font-size: 0.85rem; color: #334155;">💡 <?= htmlspecialchars($rec) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- RIASEC Profile -->
<?php if ($a['riasec_profile']['assessed'] && !empty($a['riasec_profile']['scores'])): ?>
    <div class="mt-5 mb-5">
        <h2 style="font-size:1.2rem; font-weight:800; color:#0f172a; margin-bottom: 16px;">🧭 โปรไฟล์ RIASEC</h2>
        <div class="glass-card p-4" style="border-radius: 16px;">
            <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 16px;">
                ประเภทเด่น: <strong style="color: #0f172a;"><?= $a['riasec_profile']['dominant'] ?? 'N/A' ?></strong>
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <?php
                $riasecNames = ['R' => 'Realistic (ปฏิบัติ)', 'I' => 'Investigative (สอบสวน)', 'A' => 'Artistic (ศิลปะ)',
                                'S' => 'Social (สังคม)', 'E' => 'Enterprising (ประกอบการ)', 'C' => 'Conventional (ระเบียบ)'];
                foreach ($a['riasec_profile']['scores'] as $trait => $score):
                    $pct = min(100, (int) round(($score / 8) * 100));
                    $barColor = $score >= 6 ? '#15803d' : ($score >= 4 ? '#ca8a04' : '#dc2626');
                ?>
                    <div style="flex: 1; min-width: 120px; max-width: 180px;">
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;"><?= $trait ?> — <?= $riasecNames[$trait] ?? $trait ?></div>
                        <div style="background: #e2e8f0; height: 6px; border-radius: 99px; overflow: hidden;">
                            <div style="background: <?= $barColor ?>; height: 6px; width: <?= $pct ?>%; border-radius: 99px;"></div>
                        </div>
                        <div style="text-align: right; font-size: 0.75rem; font-weight: 700; color: #0f172a; margin-top: 2px;"><?= $score ?>/8</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="mt-5 mb-5">
        <div class="glass-card p-4 text-center" style="background: #fef3c7; border-radius: 16px; border: 2px solid #f59e0b;">
            <div style="font-size: 2rem;">🧭</div>
            <h3 style="margin: 8px 0; font-size: 1.1rem; font-weight: 800; color: #92400e;">ยังไม่ได้ทำแบบประเมิน RIASEC</h3>
            <p style="color: #a16207; font-size: 0.85rem; margin: 0 0 12px;">การทำแบบประเมิน RIASEC จะช่วยให้ระบบวิเคราะห์ความพร้อมรายสาขาวิชาได้แม่นยำขึ้น</p>
            <a href="<?= url('assessment') ?>" class="btn" style="background: #f59e0b; color: white; font-weight: 600; border-radius: 10px; padding: 10px 24px; text-decoration: none;">เริ่มทำแบบประเมิน</a>
        </div>
    </div>
<?php endif; ?>
