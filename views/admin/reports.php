<?php $pageTitle = 'รายงานและสถิติการศึกษา (Executive KPI)'; ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;" class="no-print">
    <h2 style="margin: 0;">📊 Executive Dashboard & Reports</h2>
    <div style="display: flex; gap: 8px;">
        <a href="<?= url('analytics/dashboard/export') ?>" class="btn btn-primary" style="background: #e11d48; border-color: #e11d48; border-radius: 8px;">
            📄 ดาวน์โหลดรายงาน PDF (mPDF)
        </a>
        <button onclick="window.print()" class="btn btn-outline" style="border-radius: 8px;">
            🖨️ พิมพ์หน้านี้
        </button>
    </div>
</div>

<!-- Formal Report Header (Print Only) -->
<div class="print-only" style="text-align: center; margin-bottom: 40px;">
    <img src="<?= asset('images/logo.png') ?>"
        alt="PCSHS Kalasin Logo" style="width: 65px; height: auto; margin-bottom: 16px;">
    <h1 style="font-size: 24pt; margin: 0; font-weight: 700;">รายงานผลสัมฤทธิ์ทางการศึกษาและการดำเนินงาน</h1>
    <h2 style="font-size: 18pt; margin: 10px 0;">ศูนย์ข้อมูลสารสนเทศ โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
    </h2>
    <p style="font-size: 16pt;">ข้อมูล ณ วันที่: <?= date('d/m/Y') ?></p>
</div>

<!-- Executive KPIs -->
<div class="grid-3 no-print" style="gap: 20px; margin-bottom: 24px;">
    <?php
    // Calculate Total Students and At-Risk %
    $totalStudents = 0;
    $riskStudents = 0;
    foreach ($stats['at_risk'] as $ar) {
        $totalStudents += $ar['total_students'];
        $riskStudents += $ar['risk_count'];
    }
    $riskRatio = $totalStudents > 0 ? round(($riskStudents / $totalStudents) * 100, 1) : 0;

    // Calculate Average GPA Overall
    $avgGpaOverall = 0;
    $gpaClassCount = 0;
    foreach ($stats['gpa_by_class'] as $gp) {
        if ($gp['avg_gpa'] > 0) {
            $avgGpaOverall += $gp['avg_gpa'];
            $gpaClassCount++;
        }
    }
    $avgGpaOverall = $gpaClassCount > 0 ? round($avgGpaOverall / $gpaClassCount, 2) : 0.00;

    // Project Budget
    $bug_req = $stats['projects']['requested'] ?? 0;
    $bug_app = $stats['projects']['approved'] ?? 0;
    $bug_ratio = $bug_req > 0 ? round(($bug_app / $bug_req) * 100, 1) : 0;
    ?>
    <div class="card slide-up" style="border-left: 4px solid #10b981;">
        <div class="card-body">
            <div style="font-size: 0.85rem; color: var(--gray-500); font-weight: 600;">เกรดเฉลี่ย (Overall GPA)</div>
            <div style="font-size: 2rem; font-weight: 800; color: #022c22; margin-top: 8px;">
                <?= number_format((float) $avgGpaOverall, 2) ?>
            </div>
            <div style="font-size: 0.8rem; color: #10b981;">จากชั้นมัธยมศึกษาศึกษาทั้งหมด</div>
        </div>
    </div>

    <div class="card slide-up" style="border-left: 4px solid #ef4444;">
        <div class="card-body">
            <div style="font-size: 0.85rem; color: var(--gray-500); font-weight: 600;">ติดกลุ่มเสี่ยง (Academic Risk)
            </div>
            <div style="font-size: 2rem; font-weight: 800; color: #450a0a; margin-top: 8px;">
                <?= $riskStudents ?> <span style="font-size: 1rem; color: var(--gray-500);">/ <?= $totalStudents ?>
                    คน</span>
            </div>
            <div style="font-size: 0.8rem; color: #ef4444;">(คิดเป็น <?= $riskRatio ?>%) ต่ำกว่า 2.5 หรือไม่มีผลงาน
            </div>
        </div>
    </div>

    <div class="card slide-up" style="border-left: 4px solid #f59e0b;">
        <div class="card-body">
            <div style="font-size: 0.85rem; color: var(--gray-500); font-weight: 600;">อัตราอนุมัติงบฯ ทุนโครงงาน</div>
            <div style="font-size: 2rem; font-weight: 800; color: #451a03; margin-top: 8px;">
                <?= $bug_ratio ?>%
            </div>
            <div style="font-size: 0.8rem; color: #f59e0b;">เบิกจ่าย <?= number_format((float) ($bug_app ?? 0)) ?> /
                <?= number_format((float) ($bug_req ?? 0)) ?> บาท
            </div>
        </div>
    </div>
</div>

<div class="print-layout">
    <!-- SECTION 1: Academic Performance -->
    <div class="print-section">
        <h3 class="print-heading">1. ผลสัมฤทธิ์ทางการศึกษาเชิงประจักษ์ (KPI)</h3>
        <p class="print-text" style="text-indent: 1.5in;">
            ภาพรวมผลการดำเนินงานด้านวิชาการ พบว่านักเรียนมีระดับคะแนนเฉลี่ยอยูในเกณฑ์มาตรฐาน
            โดยมีค่าเกลี่ยเฉลี่ยรวม (Overall GPA) เท่ากับ <strong><?= number_format($avgGpaOverall, 2) ?></strong>
            พบว่ามีนักเรียนที่จัดอยู่ในกลุ่มควรติดตามใกล้ชิด (เกรดเฉลี่ยต่ำกว่า 2.5
            หรือยังไม่เริ่มประมวลผลงานเข้าสู่ระบบ)
            จำนวน <strong><?= $riskStudents ?></strong> คน จากทั้งหมด <strong><?= $totalStudents ?></strong> คน (คิดเป็น
            <?= $riskRatio ?>%)
        </p>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 20%; text-align: center;">ระดับชั้น</th>
                    <th style="width: 25%; text-align: center;">จำนวนนักเรียน (คน)</th>
                    <th style="width: 25%; text-align: center;">เกรดเฉลี่ย (GPA)</th>
                    <th style="width: 30%; text-align: center;">นักเรียนกลุ่มเสี่ยง (คน)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['at_risk'] as $i => $ar): ?>
                    <?php
                    $classGpa = 0.00;
                    foreach ($stats['gpa_by_class'] as $gp) {
                        if ($gp['class_year'] == $ar['class_year']) {
                            $classGpa = $gp['avg_gpa'] ?? 0;
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td style="text-align: center;">ชั้นมัธยมศึกษาปีที่ <?= e($ar['class_year']) ?></td>
                        <td style="text-align: center;"><?= e($ar['total_students'] ?? 0) ?></td>
                        <td style="text-align: center; font-weight: bold;"><?= number_format((float) $classGpa, 2) ?></td>
                        <td style="text-align: center; color: #ef4444;">
                            <?= ($ar['risk_count'] ?? 0) > 0 ? $ar['risk_count'] : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- SECTION 2: Project Management -->
    <div class="print-section">
        <h3 class="print-heading">2. การบริหารจัดการโครงงานนักเรียนและงบประมาณ</h3>
        <p class="print-text" style="text-indent: 1.5in;">
            ในภาคเรียนปัจจุบัน มีโครงงานนักเรียนในระบบจำนวน
            <strong><?= number_format((int) ($stats['projects']['total_projects'] ?? 0)) ?></strong> โครงงาน
            ผ่านเกณฑ์พิจารณาเสร็จสิ้นแล้ว
            <strong><?= number_format((int) ($stats['projects']['completed'] ?? 0)) ?></strong> โครงงาน
            มีการร้องของบประมาณสนับสนุนรวมทั้งสิ้น <strong><?= number_format((float) ($bug_req ?? 0)) ?></strong> บาท
            และได้รับการอนุมัติเบิกจ่ายตามระเบียบพัสดุจำนวน
            <strong><?= number_format((float) ($bug_app ?? 0)) ?></strong> บาท
            (อัตราอนุมัติ <?= (int) ($bug_ratio ?? 0) ?>%)
        </p>
    </div>
</div>
<div class="grid-2 no-print">
    <div class="card slide-up">
        <div class="card-header">
            <h3>👥 จำนวนนักเรียนแยกตามชั้น</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-container" style="margin-bottom:0;">
            <table>
                <thead>
                    <tr>
                        <th>ชั้น/ห้อง</th>
                        <th>จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['by_class'] as $r): ?>
                        <tr>
                            <td>ม.
                                <?= $r['class_year'] ?>/
                                <?= $r['class_room'] ?>
                            </td>
                            <td><strong>
                                    <?= $r['count'] ?>
                                </strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="card slide-up">
        <div class="card-header">
            <h3>📁 ผลงานแยกตามหมวดหมู่</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-container" style="margin-bottom:0;">
            <table>
                <thead>
                    <tr>
                        <th>หมวดหมู่</th>
                        <th>จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['by_category'] as $r): ?>
                        <tr>
                            <td>
                                <?= e($r['icon'] . ' ' . $r['name']) ?>
                            </td>
                            <td><strong>
                                    <?= $r['count'] ?>
                                </strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3 slide-up no-print">
    <div class="card-header">
        <h3>📈 ผลงานรายเดือน</h3>
    </div>
    <div class="card-body"><canvas id="monthlyChart" height="80"></canvas></div>
</div>
<style>
    /* Print Utilities */
    .print-only {
        display: none;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        margin-bottom: 25px;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #000;
        padding: 8px;
        font-size: 16pt;
    }

    @media print {
        @page {
            size: A4;
            margin: 2.54cm 2.54cm 2.54cm 3cm;
        }

        /* Thai Standard (Top 2.54, Right 2.54, Bottom 2.54, Left 3.0) */
        body {
            background: white !important;
            font-family: 'Sarabun', serif !important;
            color: black !important;
        }

        .no-print,
        .sidebar,
        .top-bar,
        .mobile-menu-btn {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }

        .print-only {
            display: block !important;
        }

        /* Typography for Print */
        .print-heading {
            font-size: 18pt !important;
            font-weight: bold !important;
            margin-bottom: 10px !important;
        }

        .print-text {
            font-size: 16pt !important;
            line-height: 1.5 !important;
            text-align: justify !important;
        }

        .report-table th {
            background: #f0f0f0 !important;
        }
    }
</style>

<script>
    const monthlyData = <?= json_encode($stats['monthly']) ?>;
    if (monthlyData.length > 0 && document.getElementById('monthlyChart')) {
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar', data: {
                labels: monthlyData.map(m => m.month),
                datasets: [{ label: 'ผลงาน', data: monthlyData.map(m => m.count), backgroundColor: '#6366f1', borderRadius: 6 }]
            }, options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }
</script>