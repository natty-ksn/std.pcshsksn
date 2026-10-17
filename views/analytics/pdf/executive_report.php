<?php
/**
 * PDF Template — รายงานสรุปผลการดำเนินงานระดับองค์กร (Executive Analytics Report — Phase 3)
 *
 * Variables:
 *   $data        array  ข้อมูล Analytics จาก ReportService::getSchoolAnalytics()
 *   $report_date string วัน-เวลาที่ออกเอกสาร
 *
 * Premium Government Style — Sarabun Font, Header/Footer/Watermark จาก PDFService
 */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$projectStatusMap = [
    'draft' => ['ฉบับร่าง', 'badge-warning'],
    'proposal_submitted' => ['รออนุมัติหัวข้อ', 'badge-info'],
    'proposal_approved' => ['หัวข้อผ่านการอนุมัติ', 'badge-info'],
    'in_progress' => ['กำลังดำเนินการ', 'badge-warning'],
    'completed' => ['เสร็จสิ้น', 'badge-success'],
    'rejected' => ['ไม่ผ่านการพิจารณา', 'badge-danger'],
];

$levelMap = [
    'international' => ['ระดับนานาชาติ', '#92400e', '#fef3c7'],
    'national' => ['ระดับชาติ', '#991b1b', '#fee2e2'],
    'province' => ['ระดับจังหวัด', '#1e40af', '#dbeafe'],
    'district' => ['ระดับเขตพื้นที่', '#166534', '#dcfce7'],
    'school' => ['ระดับโรงเรียน', '#475569', '#f1f5f9'],
];
?>

<!-- ============================================================ -->
<!-- COVER PAGE                                                     -->
<!-- ============================================================ -->
<div style="text-align: center; padding-top: 50pt;">
    <div style="font-size: 11pt; letter-spacing: 5pt; color: #1e3a8a; font-weight: bold; margin-bottom: 12pt;">
        EXECUTIVE ANALYTICS REPORT
    </div>
    <div style="font-size: 30pt; font-weight: bold; color: #0f172a; line-height: 1.1; margin: 6pt 0 4pt;">
        รายงานสรุปผลการดำเนินงาน
    </div>
    <div style="font-size: 15pt; color: #475569; margin-bottom: 30pt;">
        ระดับองค์กร — e-Portfolio & Project Tracking System
    </div>
    <div style="width: 70pt; border-top: 2pt solid #1e3a8a; margin: 12pt auto;"></div>

    <div style="font-size: 14pt; color: #1e293b; font-weight: bold; margin-top: 50pt;">
        โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
    </div>
    <div style="font-size: 11pt; color: #64748b;">
        Princess Chulabhorn Science High School Kalasin
    </div>

    <div style="margin-top: 35pt; font-size: 11pt; color: #64748b;">
        เอกสารฉบับนี้ออกเมื่อ <?= $h($report_date) ?>
    </div>
    <div style="font-size: 10pt; color: #94a3b8; margin-top: 6pt;">
        จัดทำโดย งานศูนย์ข้อมูลสารสนเทศ | กลุ่มบริหารงานวิชาการ
    </div>
</div>

<pagebreak>

    <!-- ============================================================ -->
    <!-- SECTION 1: EXECUTIVE SUMMARY                                   -->
    <!-- ============================================================ -->
    <h2>๑. สรุปภาพรวมการดำเนินงาน (Executive Summary)</h2>

    <!-- Stat Cards Row -->
    <table style="width: 100%; border: none; margin: 10pt 0 18pt;">
        <tr>
            <?php
            $statCards = [
                ['label' => 'นักเรียนทั้งหมด', 'value' => number_format((int) ($data['total_students'] ?? 0)), 'icon' => '🎓'],
                ['label' => 'ครู/บุคลากร', 'value' => number_format((int) ($data['total_teachers'] ?? 0)), 'icon' => '👨‍🏫'],
                ['label' => 'ผลงานรับรอง', 'value' => number_format((int) ($data['total_portfolios'] ?? 0)), 'icon' => '📁'],
                ['label' => 'โครงงานทั้งหมด', 'value' => number_format((int) (($data['active_projects'] ?? 0) + ($data['completed_projects'] ?? 0))), 'icon' => '🔬'],
            ];
            foreach ($statCards as $sc): ?>
                <td style="width: 25%; border: 1pt solid #e2e8f0; padding: 14pt; text-align: center; background: #f8fafc;">
                    <div style="font-size: 22pt;"><?= $sc['icon'] ?></div>
                    <div style="font-size: 22pt; font-weight: bold; color: #1e3a8a; line-height: 1;"><?= $sc['value'] ?>
                    </div>
                    <div
                        style="font-size: 9pt; color: #64748b; margin-top: 3pt; text-transform: uppercase; letter-spacing: 1pt;">
                        <?= $sc['label'] ?></div>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>

    <!-- KPI Table -->
    <table>
        <thead>
            <tr>
                <th width="50%">ตัวชี้วัดหลัก (KPI)</th>
                <th>ค่าที่วัดได้</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>เกรดเฉลี่ยรวมระดับองค์กร (Org GPA)</td>
                <td><strong><?= number_format((float) ($data['avg_gpa'] ?? 0), 2) ?></strong></td>
            </tr>
            <tr>
                <td>นักเรียนที่ใช้งาน ต่อเนื่อง (Active) (30 วันล่าสุด)</td>
                <td><?= number_format((int) ($data['active_students_30d'] ?? 0)) ?> คน</td>
            </tr>
            <tr>
                <td>โครงงานที่ดำเนินการอยู่</td>
                <td><?= number_format((int) ($data['active_projects'] ?? 0)) ?> โครงงาน</td>
            </tr>
            <tr>
                <td>โครงงานที่เสร็จสมบูรณ์</td>
                <td><?= number_format((int) ($data['completed_projects'] ?? 0)) ?> โครงงาน</td>
            </tr>
            <tr>
                <td>บันทึกกิจกรรม (Logbook) — 30 วันล่าสุด</td>
                <td><?= number_format((int) ($data['logbooks_30d'] ?? 0)) ?> รายการ</td>
            </tr>
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- SECTION 2: PROJECTS & INNOVATION                               -->
    <!-- ============================================================ -->
    <h2>๒. โครงงานและนวัตกรรม (Projects & Innovation)</h2>

    <table>
        <thead>
            <tr>
                <th>สถานะโครงงาน</th>
                <th width="25%" class="text-center">จำนวน (โครงงาน)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['project_status_dist'])): ?>
                <?php foreach ($data['project_status_dist'] as $ps):
                    $sInfo = $projectStatusMap[$ps['status']] ?? [$ps['status'], 'badge-info'];
                    ?>
                    <tr>
                        <td>
                            <span class="badge <?= $sInfo[1] ?>"><?= $h($sInfo[0]) ?></span>
                            <span class="text-muted small" style="margin-left: 6pt;"><?= $h($ps['status']) ?></span>
                        </td>
                        <td class="text-center"><strong><?= number_format((int) $ps['count']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php
                // Completion rate
                $completed = 0;
                $total = 0;
                foreach ($data['project_status_dist'] as $ps) {
                    $total += (int) $ps['count'];
                    if ($ps['status'] === 'completed')
                        $completed = (int) $ps['count'];
                }
                $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                ?>
                <tr style="background: #f1f5f9;">
                    <td><strong>อัตราความสำเร็จ (Completion Rate)</strong></td>
                    <td class="text-center"><strong style="font-size: 14pt; color: #166534;"><?= $rate ?>%</strong></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center text-muted">— ยังไม่มีข้อมูลโครงงาน —</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- SECTION 3: PORTFOLIO DISTRIBUTION                              -->
    <!-- ============================================================ -->
    <h2>๓. การกระจายผลงานตามหมวดหมู่ (Portfolio Distribution)</h2>

    <?php if (!empty($data['portfolio_by_category'])): ?>
        <table>
            <thead>
                <tr>
                    <th>หมวดหมู่</th>
                    <th width="20%" class="text-center">จำนวน (ชิ้น)</th>
                    <th width="30%">สัดส่วน</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalCat = array_sum(array_column($data['portfolio_by_category'], 'count'));
                foreach ($data['portfolio_by_category'] as $cat):
                    $pct = $totalCat > 0 ? round(((int) $cat['count'] / $totalCat) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= $h($cat['icon'] ?? '') ?>         <?= $h($cat['name']) ?></td>
                        <td class="text-center"><?= number_format((int) $cat['count']) ?></td>
                        <td>
                            <div style="background: #e2e8f0; height: 8pt; border-radius: 4pt; overflow: hidden;">
                                <div style="background: #1e3a8a; height: 8pt; width: <?= $pct ?>%; border-radius: 4pt;"></div>
                            </div>
                            <span class="small text-muted"><?= $pct ?>%</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background: #f1f5f9;">
                    <td><strong>รวมทั้งหมด</strong></td>
                    <td class="text-center"><strong><?= number_format($totalCat) ?></strong></td>
                    <td><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted text-center">— ยังไม่มีข้อมูลผลงาน —</p>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- SECTION 4: CLASS DISTRIBUTION                                  -->
    <!-- ============================================================ -->
    <h2>๔. การกระจายนักเรียนตามชั้นเรียน (Student Distribution by Class)</h2>

    <?php if (!empty($data['students_by_class'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ชั้นเรียน</th>
                    <th width="20%" class="text-center">จำนวนนักเรียน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['students_by_class'] as $cls): ?>
                    <tr>
                        <td>มัธยมศึกษาปีที่ <?= $h($cls['class_year']) ?>/<?= $h($cls['class_room']) ?></td>
                        <td class="text-center"><?= number_format((int) $cls['count']) ?> คน</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted text-center">— ไม่มีข้อมูลชั้นเรียน —</p>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- SECTION 5: RISK & EARLY WARNING SUMMARY                        -->
    <!-- ============================================================ -->
    <h2>๕. สรุปความเสี่ยงนักเรียน (Early Warning Summary)</h2>

    <table>
        <thead>
            <tr>
                <th>ระดับความเสี่ยง</th>
                <th width="20%" class="text-center">จำนวนนักเรียน</th>
                <th width="30%">สัดส่วน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $riskLabels = [
                'critical' => ['วิกฤต — ต้องดูแลเร่งด่วน', 'badge-danger'],
                'high' => ['สูง — ควรติดตามอย่างใกล้ชิด', 'badge-danger'],
                'medium' => ['ปานกลาง — ควรให้คำแนะนำ', 'badge-warning'],
                'low' => ['ต่ำ — ปกติ', 'badge-success'],
            ];
            $riskCounts = [
                'critical' => (int) ($data['risk_critical_count'] ?? 0),
                'high' => (int) ($data['risk_high_count'] ?? 0),
                'medium' => (int) ($data['risk_medium_count'] ?? 0),
                'low' => (int) ($data['risk_low_count'] ?? 0),
            ];
            $totalRisk = array_sum($riskCounts);
            foreach ($riskLabels as $level => $rInfo):
                $cnt = $riskCounts[$level];
                $pct = $totalRisk > 0 ? round(($cnt / $totalRisk) * 100, 1) : 0;
                ?>
                <tr>
                    <td><span class="badge <?= $rInfo[1] ?>"><?= $h($rInfo[0]) ?></span></td>
                    <td class="text-center"><?= number_format($cnt) ?> คน</td>
                    <td>
                        <div style="background: #e2e8f0; height: 8pt; border-radius: 4pt; overflow: hidden;">
                            <div
                                style="background: <?= $level === 'low' ? '#15803d' : ($level === 'medium' ? '#ca8a04' : '#dc2626') ?>; height: 8pt; width: <?= $pct ?>%; border-radius: 4pt;">
                            </div>
                        </div>
                        <span class="small text-muted"><?= $pct ?>%</span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr style="background: #f1f5f9;">
                <td><strong>รวมทั้งหมด</strong></td>
                <td class="text-center"><strong><?= number_format($totalRisk) ?> คน</strong></td>
                <td><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- SECTION 6: ENGAGEMENT TREND (Last 6 months)                   -->
    <!-- ============================================================ -->
    <h2>๖. แนวโน้มการมีส่วนร่วม (Engagement Trend — 6 เดือนล่าสุด)</h2>

    <?php if (!empty($data['engagement_trend'])): ?>
        <table>
            <thead>
                <tr>
                    <th>เดือน</th>
                    <th width="20%" class="text-center">นักเรียนที่ใช้งาน</th>
                    <th width="20%" class="text-center">ผลงานที่เพิ่มใหม่</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['engagement_trend'] as $et):
                    $monthNames = [
                        '',
                        'มกราคม',
                        'กุมภาพันธ์',
                        'มีนาคม',
                        'เมษายน',
                        'พฤษภาคม',
                        'มิถุนายน',
                        'กรกฎาคม',
                        'สิงหาคม',
                        'กันยายน',
                        'ตุลาคม',
                        'พฤศจิกายน',
                        'ธันวาคม'
                    ];
                    $ts = strtotime($et['month'] . '-01');
                    $monthLabel = date('n', $ts);
                    $yearBE = (int) date('Y', $ts) + 543;
                    ?>
                    <tr>
                        <td><?= $h($monthNames[(int) $monthLabel] . ' ' . $yearBE) ?></td>
                        <td class="text-center"><?= number_format((int) $et['active_students']) ?> คน</td>
                        <td class="text-center"><?= number_format((int) $et['portfolio_additions']) ?> ชิ้น</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted text-center">— ไม่มีข้อมูลแนวโน้ม —</p>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- SECTION 7: EXCELLENCE REGISTRY (Top Achievement Levels)        -->
    <!-- ============================================================ -->
    <h2>๗. ทำเนียบผลงานระดับสูง (Excellence Registry)</h2>
    <p style="text-align: justify;">
        สรุปจำนวนผลงานที่ประสบความสำเร็จในระดับชาติและนานาชาติ
        ซึ่งเป็นการยืนยันคุณภาพการจัดการเรียนการสอนตามมาตรฐานโรงเรียนวิทยาศาสตร์
    </p>

    <table>
        <thead>
            <tr>
                <th>ระดับความสำเร็จ</th>
                <th width="20%" class="text-center">จำนวนผลงาน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query excellence data directly
            $excellenceData = [];
            try {
                $excellenceData = Database::fetchAll(
                    "SELECT achievement_level, COUNT(*) as count
                 FROM portfolio_items
                 WHERE status = 'published' AND achievement_level IS NOT NULL
                 GROUP BY achievement_level
                 ORDER BY FIELD(achievement_level, 'international', 'national', 'province', 'district', 'school')"
                );
            } catch (\Throwable $e) {
                $excellenceData = [];
            }
            if (!empty($excellenceData)):
                foreach ($excellenceData as $ex):
                    $lvl = $levelMap[$ex['achievement_level']] ?? ['ไม่ระบุ', '#64748b', '#f1f5f9'];
                    ?>
                    <tr>
                        <td>
                            <span class="badge" style="background: <?= $lvl[2] ?>; color: <?= $lvl[1] ?>;">
                                <?= $h($lvl[0]) ?>
                            </span>
                        </td>
                        <td class="text-center"><strong
                                style="font-size: 15pt; color: #1e3a8a;"><?= number_format((int) $ex['count']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php
                $totalEx = array_sum(array_column($excellenceData, 'count'));
                ?>
                <tr style="background: #f1f5f9;">
                    <td><strong>รวมผลงานระดับสูง</strong></td>
                    <td class="text-center"><strong
                            style="font-size: 15pt; color: #1e3a8a;"><?= number_format($totalEx) ?></strong></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center text-muted">— ยังไม่มีผลงานระดับสูงในระบบ —</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- SECTION 8: RECOMMENDATIONS & SIGNATURE                         -->
    <!-- ============================================================ -->
    <pagebreak>

        <h2>๘. ข้อเสนอแนะเชิงนโยบาย (Policy Recommendations)</h2>

        <div style="background: #f8fafc; border-left: 4pt solid #1e3a8a; padding: 14pt 18pt; margin: 12pt 0;">
            <p class="no-indent" style="text-indent: 0;">
                <?php
                $recommendations = [];
                if (!empty($riskCounts['critical']) && $riskCounts['critical'] > 0) {
                    $recommendations[] = '🔴 <strong>เร่งด่วน:</strong> มีนักเรียนกลุ่มวิกฤตจำนวน <strong>' . $riskCounts['critical'] . ' คน</strong> ที่ต้องการการดูแลเร่งด่วน — ควรจัดตั้งคณะกรรมการดูแลเฉพาะราย';
                }
                if (!empty($data['active_students_30d']) && (int) $data['active_students_30d'] < (int) $data['total_students'] * 0.5) {
                    $recommendations[] = '📊 <strong>การมีส่วนร่วม:</strong> นักเรียนที่ใช้งานต่อเนื่อง (Active)ต่ำกว่า 50% ของทั้งหมด — ควรส่งเสริมการใช้ระบบอย่างต่อเนื่อง';
                }
                if ($totalEx > 0 && $totalCat > 0 && ($totalEx / max(1, $totalCat)) < 0.1) {
                    $recommendations[] = '🏆 <strong>ยกระดับผลงาน:</strong> สัดส่วนผลงานระดับสูงยังต่ำ — ควรสนับสนุนให้นักเรียนเข้าร่วมแข่งขันภายนอกมากขึ้น';
                }
                if (empty($recommendations)) {
                    echo '✅ <strong>ภาพรวมอยู่ในเกณฑ์ดี</strong> — ไม่มีข้อกังวลสำคัญที่จำเป็นต้องดำเนินการเร่งด่วนในขณะนี้';
                } else {
                    echo implode('<br><br>', $recommendations);
                }
                ?>
            </p>
        </div>

        <!-- Signature Block -->
        <table class="clean signature-row">
            <tr>
                <td class="signature-cell">
                    <div class="signature-line"></div>
                    ( ........................................... )<br>
                    <span class="text-muted small">ผู้อำนวยการโรงเรียน</span><br>
                    <span class="text-muted small">วันที่ ............ / ............ / ............</span>
                </td>
                <td class="signature-cell">
                    <div class="signature-line"></div>
                    ( ........................................... )<br>
                    <span class="text-muted small">รองผู้อำนวยการฝ่ายวิชาการ</span><br>
                    <span class="text-muted small">วันที่ ............ / ............ / ............</span>
                </td>
            </tr>
        </table>

        <p class="text-center text-muted small mt-4">
            เอกสารฉบับนี้ออกโดยระบบ PCSHS KSN e-Portfolio &middot; จัดทำโดย งานศูนย์ข้อมูลสารสนเทศ<br>
            กลุ่มบริหารงานวิชาการ โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
        </p>