<?php
/**
 * PDF Template — Portfolio Digest (แฟ้มสะสมผลงาน) — Premium Edition v2
 *
 * Variables:
 *   $profile    array
 *   $works      array
 *   $skills     array ['name' => avg_proficiency(0-5)]
 *   $badges     array
 *   $stats      array
 *   $goals      array [{goal_type, target, target_details, deadline, status}]
 *   $interests  array [{type, item, proficiency}]
 *   $academics  array [{academic_year, semester, gpa, subject_count}]
 *   $avatarAbs  ?string  absolute filesystem path
 *   $issuedAt   string
 */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$levelMap = [
    'international' => ['ระดับนานาชาติ',   '#78350f', '#fef3c7', 'International'],
    'national'      => ['ระดับชาติ',        '#7f1d1d', '#fee2e2', 'National'],
    'province'      => ['ระดับจังหวัด',     '#1e3a8a', '#dbeafe', 'Provincial'],
    'district'      => ['ระดับเขตพื้นที่',   '#14532d', '#dcfce7', 'District'],
    'school'        => ['ระดับโรงเรียน',     '#334155', '#f1f5f9', 'School'],
];

$goalTypeLabel = [
    'university' => 'มหาวิทยาลัยเป้าหมาย',
    'major'      => 'สาขาวิชาที่สนใจ',
    'career'     => 'อาชีพในอนาคต',
    'shortterm'  => 'เป้าหมายระยะสั้น',
    'longterm'   => 'เป้าหมายระยะยาว',
];

$interestLabel = [
    'interest' => 'ความสนใจ',
    'talent'   => 'ความถนัด',
    'hobby'    => 'งานอดิเรก',
];

$statusColor = [
    'achieved'   => ['#15803d', '#dcfce7', 'สำเร็จแล้ว'],
    'inprogress' => ['#c2410c', '#ffedd5', 'กำลังดำเนินการ'],
    'planning'   => ['#1e40af', '#dbeafe', 'วางแผน'],
    'cancelled'  => ['#64748b', '#f1f5f9', 'ยกเลิก'],
];

$totalWorks  = (int) ($stats['total_works'] ?? 0);
$totalPoints = (int) ($stats['total_points'] ?? 0);
$topLevel    = $stats['top_level'] ?? 'school';
$badgeCount  = count($badges ?? []);
?>

<style>
/* ======================================================= */
/*   GLOBAL                                                */
/* ======================================================= */
body { color: #1e293b; }
h1, h2, h3, h4 { margin: 0; padding: 0; color: #0f172a; }
.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: #64748b; }
.small       { font-size: 10pt; }
.mt-1 { margin-top: 4pt; } .mt-2 { margin-top: 8pt; }
.mt-3 { margin-top: 14pt; } .mt-4 { margin-top: 22pt; }

/* ======================================================= */
/*   COVER PAGE                                            */
/* ======================================================= */
.cover-wrap {
    border: 2pt solid #0f172a;
    padding: 0;
    height: 760pt;
    position: relative;
}
.cover-ribbon {
    background-color: #0f172a;
    color: #f8fafc;
    padding: 14pt 24pt;
    font-size: 10pt;
    letter-spacing: 8pt;
    text-transform: uppercase;
    border-bottom: 3pt solid #b45309;
}
.cover-body {
    padding: 60pt 50pt 40pt;
    text-align: center;
}
.cover-monogram {
    width: 86pt;
    height: 86pt;
    border: 2pt solid #0f172a;
    border-radius: 50%;
    text-align: center;
    line-height: 80pt;
    font-size: 26pt;
    font-weight: bold;
    color: #0f172a;
    margin: 0 auto 18pt;
    background: #fff;
}
.cover-supertitle {
    font-size: 11pt;
    letter-spacing: 10pt;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 10pt;
}
.cover-title-en {
    font-size: 46pt;
    font-weight: bold;
    color: #0f172a;
    letter-spacing: 2pt;
    margin-bottom: 2pt;
}
.cover-divider {
    width: 70pt;
    border-top: 2pt solid #b45309;
    margin: 12pt auto 16pt;
}
.cover-title-th {
    font-size: 20pt;
    color: #334155;
    margin-bottom: 30pt;
}
.cover-photo-frame {
    width: 150pt;
    height: 180pt;
    border: 3pt double #0f172a;
    padding: 4pt;
    margin: 10pt auto 20pt;
    background: #f8fafc;
}
.cover-photo-frame img {
    width: 142pt;
    height: 172pt;
    object-fit: cover;
}
.cover-photo-ph {
    color: #cbd5e1;
    font-size: 10pt;
    letter-spacing: 3pt;
    text-transform: uppercase;
    padding-top: 75pt;
}
.cover-name {
    font-size: 28pt;
    font-weight: bold;
    color: #0f172a;
    margin-top: 10pt;
}
.cover-name-en {
    font-size: 13pt;
    color: #64748b;
    letter-spacing: 1pt;
    margin-top: 2pt;
}
.cover-chips {
    margin-top: 14pt;
    font-size: 12pt;
    color: #334155;
}
.cover-chip {
    display: inline-block;
    padding: 4pt 12pt;
    background: #f1f5f9;
    border-radius: 12pt;
    margin: 0 3pt;
    font-size: 11pt;
    color: #0f172a;
}
.cover-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    border-top: 1pt solid #cbd5e1;
    background: #f8fafc;
    padding: 14pt 30pt;
    font-size: 10pt;
}

/* ======================================================= */
/*   TOC                                                   */
/* ======================================================= */
.toc-wrap { padding: 0 10pt; }
.toc-title {
    font-size: 22pt;
    color: #0f172a;
    font-weight: bold;
    border-bottom: 3pt solid #b45309;
    padding-bottom: 6pt;
    margin-bottom: 20pt;
}
.toc-row td {
    border: none !important;
    padding: 10pt 4pt !important;
    border-bottom: 0.5pt dotted #cbd5e1 !important;
    font-size: 13pt;
}
.toc-num {
    color: #b45309;
    font-weight: bold;
    width: 45pt;
}
.toc-name { color: #0f172a; font-weight: bold; }
.toc-desc { color: #64748b; font-size: 11pt; }

/* ======================================================= */
/*   SECTION HEADERS                                       */
/* ======================================================= */
.sec {
    margin: 26pt 0 14pt;
    border-bottom: 2pt solid #0f172a;
    padding-bottom: 6pt;
}
.sec-num {
    display: inline-block;
    background: #0f172a;
    color: #fff;
    padding: 2pt 10pt;
    font-size: 10pt;
    letter-spacing: 3pt;
    font-weight: bold;
    vertical-align: middle;
}
.sec-title {
    display: inline-block;
    font-size: 17pt;
    font-weight: bold;
    color: #0f172a;
    margin-left: 8pt;
    vertical-align: middle;
}
.sec-sub {
    display: inline-block;
    font-size: 10pt;
    color: #64748b;
    margin-left: 6pt;
    letter-spacing: 1pt;
}

.subsec {
    font-size: 13pt;
    font-weight: bold;
    color: #334155;
    margin: 16pt 0 8pt;
    border-left: 3pt solid #b45309;
    padding-left: 8pt;
}

/* ======================================================= */
/*   PROFILE CARD                                          */
/* ======================================================= */
.pc-table { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }
.pc-table td { border: none !important; vertical-align: top; padding: 0 !important; }
.pc-photo {
    width: 130pt;
    height: 155pt;
    border: 1.5pt solid #0f172a;
    padding: 3pt;
    background: #f8fafc;
}
.pc-photo img { width: 122pt; height: 147pt; object-fit: cover; }
.pc-photo-ph {
    color: #cbd5e1;
    text-align: center;
    padding-top: 60pt;
    font-size: 9pt;
    letter-spacing: 2pt;
    text-transform: uppercase;
}
.pc-info { padding-left: 20pt !important; }
.pc-name-th { font-size: 18pt; font-weight: bold; color: #0f172a; }
.pc-name-en { font-size: 11pt; color: #64748b; letter-spacing: 1pt; margin: 2pt 0 8pt; }

.info-grid { width: 100%; margin-top: 4pt; border-collapse: collapse; }
.info-grid td { padding: 5pt 6pt !important; font-size: 11pt; border: none !important; }
.info-lbl { color: #64748b; width: 30%; }
.info-val { color: #0f172a; font-weight: bold; }

/* ======================================================= */
/*   VISION / BIO BOX                                      */
/* ======================================================= */
.vision-box {
    background: #fffbeb;
    border: 1pt solid #fcd34d;
    border-left: 4pt solid #b45309;
    padding: 14pt 18pt;
    margin-top: 6pt;
    font-size: 12pt;
    color: #334155;
    line-height: 1.65;
    font-style: italic;
}
.vision-quote {
    font-size: 28pt;
    color: #b45309;
    line-height: 0.6;
    margin-right: 4pt;
    vertical-align: -5pt;
}

/* ======================================================= */
/*   GOALS                                                 */
/* ======================================================= */
.goal-table { width: 100%; border-collapse: collapse; margin-top: 6pt; }
.goal-table td {
    border: 0.5pt solid #e2e8f0 !important;
    padding: 10pt 12pt !important;
    vertical-align: top;
    font-size: 11pt;
}
.goal-type {
    font-size: 9pt;
    color: #b45309;
    font-weight: bold;
    letter-spacing: 1pt;
    text-transform: uppercase;
}
.goal-target { font-size: 12pt; color: #0f172a; font-weight: bold; margin-top: 2pt; }
.goal-meta { font-size: 10pt; color: #64748b; margin-top: 4pt; }
.goal-pill {
    display: inline-block;
    padding: 2pt 8pt;
    border-radius: 2pt;
    font-size: 9pt;
    font-weight: bold;
}

/* ======================================================= */
/*   STATS CARDS                                           */
/* ======================================================= */
.stats-table { width: 100%; border-collapse: separate; border-spacing: 6pt; margin: 8pt 0; }
.stats-table td {
    background: #f8fafc;
    border: 1pt solid #e2e8f0 !important;
    border-top: 3pt solid #0f172a !important;
    text-align: center;
    padding: 14pt 8pt !important;
    width: 25%;
}
.stats-num {
    font-size: 28pt;
    font-weight: bold;
    color: #0f172a;
    line-height: 1;
}
.stats-num-sm { font-size: 13pt; line-height: 1.2; }
.stats-lbl {
    font-size: 9pt;
    color: #64748b;
    margin-top: 6pt;
    letter-spacing: 1pt;
    text-transform: uppercase;
}
.stats-c1 td { border-top-color: #0f172a !important; } /* set via inline below */

/* ======================================================= */
/*   SKILLS                                                */
/* ======================================================= */
.skill-row { margin-bottom: 8pt; }
.skill-head { font-size: 11pt; margin-bottom: 3pt; }
.skill-name { font-weight: bold; color: #0f172a; }
.skill-val { float: right; color: #b45309; font-weight: bold; font-size: 10pt; }
.skill-bar {
    background: #e2e8f0;
    height: 8pt;
    border-radius: 4pt;
    overflow: hidden;
}
.skill-fill {
    background: #0f172a;
    height: 8pt;
    border-radius: 4pt;
}

/* ======================================================= */
/*   ACADEMIC                                              */
/* ======================================================= */
.acad-table { width: 100%; border-collapse: collapse; margin-top: 6pt; }
.acad-table th {
    background: #0f172a;
    color: #fff;
    padding: 8pt 10pt !important;
    font-weight: bold;
    font-size: 11pt;
    text-align: left;
    border: none !important;
}
.acad-table td {
    padding: 8pt 10pt !important;
    border: 0.5pt solid #e2e8f0 !important;
    font-size: 11pt;
}
.acad-gpa {
    font-weight: bold;
    font-size: 13pt;
    color: #b45309;
}

/* ======================================================= */
/*   INTERESTS                                             */
/* ======================================================= */
.int-group { margin-bottom: 10pt; }
.int-label {
    font-size: 10pt;
    letter-spacing: 2pt;
    font-weight: bold;
    color: #64748b;
    text-transform: uppercase;
}
.int-chip {
    display: inline-block;
    padding: 4pt 10pt;
    background: #f1f5f9;
    border: 0.5pt solid #cbd5e1;
    border-radius: 3pt;
    margin: 3pt 2pt;
    font-size: 11pt;
    color: #0f172a;
}

/* ======================================================= */
/*   BADGES                                                */
/* ======================================================= */
.badge-grid { width: 100%; border-collapse: separate; border-spacing: 6pt; margin-top: 6pt; }
.badge-cell {
    background: #fffbeb;
    border: 1pt solid #fcd34d !important;
    border-radius: 6pt;
    text-align: center;
    padding: 12pt 6pt !important;
    width: 25%;
}
.badge-ic { font-size: 24pt; line-height: 1; }
.badge-nm { font-size: 10pt; font-weight: bold; color: #78350f; margin-top: 5pt; }
.badge-dt { font-size: 8pt; color: #a16207; margin-top: 2pt; }

/* ======================================================= */
/*   WORK CARDS                                            */
/* ======================================================= */
.work {
    border: 1pt solid #e2e8f0;
    border-left: 4pt solid #0f172a;
    margin-bottom: 14pt;
    page-break-inside: avoid;
}
.work-head {
    background: #f8fafc;
    padding: 12pt 16pt;
    border-bottom: 0.5pt solid #e2e8f0;
}
.work-idx {
    font-size: 9pt;
    color: #b45309;
    font-weight: bold;
    letter-spacing: 2pt;
    text-transform: uppercase;
}
.work-title {
    font-size: 15pt;
    font-weight: bold;
    color: #0f172a;
    margin: 2pt 0 4pt;
    line-height: 1.25;
}
.work-meta { font-size: 10pt; color: #64748b; }
.work-body { padding: 12pt 16pt; }
.work-desc { font-size: 11pt; color: #334155; line-height: 1.55; text-align: justify; }
.work-award {
    background: #fffbeb;
    border-left: 3pt solid #b45309;
    padding: 8pt 12pt;
    margin-top: 10pt;
    font-size: 11pt;
    color: #78350f;
}
.work-skills { padding: 8pt 16pt; background: #fff; border-top: 0.5pt solid #f1f5f9; }
.lvl-pill {
    display: inline-block;
    padding: 2pt 8pt;
    font-size: 9pt;
    font-weight: bold;
    border-radius: 2pt;
    letter-spacing: 0.5pt;
}
.verify-stamp {
    display: inline-block;
    border: 1.5pt solid #15803d;
    color: #15803d;
    padding: 3pt 10pt;
    font-size: 9pt;
    font-weight: bold;
    letter-spacing: 2pt;
    text-transform: uppercase;
    border-radius: 2pt;
}
.skill-tag {
    display: inline-block;
    padding: 2pt 8pt;
    background: #eff6ff;
    border: 0.5pt solid #bfdbfe;
    color: #1e3a8a;
    border-radius: 10pt;
    font-size: 9pt;
    margin: 2pt 2pt 0 0;
}

/* ======================================================= */
/*   SIGNATURE                                             */
/* ======================================================= */
.endorse-text {
    font-size: 12pt;
    color: #334155;
    line-height: 1.7;
    text-align: justify;
    text-indent: 1cm;
    margin-top: 12pt;
}
.sig-table { width: 100%; margin-top: 60pt; border-collapse: collapse; }
.sig-table td {
    border: none !important;
    text-align: center;
    padding: 0 30pt !important;
    width: 50%;
    font-size: 11pt;
}
.sig-line { border-top: 0.8pt solid #0f172a; margin: 0 10pt 6pt; height: 0; }
.sig-name { font-weight: bold; color: #0f172a; font-size: 12pt; }
.sig-role { color: #64748b; font-size: 10pt; margin-top: 2pt; }

.end-mark {
    text-align: center;
    margin-top: 40pt;
    color: #94a3b8;
    font-size: 9pt;
    letter-spacing: 3pt;
    text-transform: uppercase;
}
.end-mark hr {
    width: 40pt;
    border: none;
    border-top: 1pt solid #cbd5e1;
    margin: 6pt auto;
}
</style>

<!-- ====================================================== -->
<!-- COVER PAGE                                             -->
<!-- ====================================================== -->
<div class="cover-wrap">
    <div class="cover-ribbon">Princess Chulabhorn Science High School &middot; Kalasin</div>

    <div class="cover-body">
        <div class="cover-monogram">จภ</div>

        <div class="cover-supertitle">e-Portfolio Digest</div>
        <div class="cover-title-en">PORTFOLIO</div>
        <div class="cover-divider"></div>
        <div class="cover-title-th">แฟ้มสะสมผลงานและบันทึกพัฒนาการ</div>

        <div class="cover-photo-frame">
            <?php if (!empty($avatarAbs)): ?>
                <img src="<?= $h($avatarAbs) ?>">
            <?php else: ?>
                <div class="cover-photo-ph">Photo Placeholder</div>
            <?php endif; ?>
        </div>

        <div class="cover-name"><?= $h($profile['full_name'] ?? '-') ?></div>
        <?php if (!empty($profile['nickname'])): ?>
            <div class="cover-name-en">( <?= $h($profile['nickname']) ?> )</div>
        <?php endif; ?>

        <div class="cover-chips">
            <?php if (!empty($profile['student_code'])): ?>
                <span class="cover-chip">รหัส <?= $h($profile['student_code']) ?></span>
            <?php endif; ?>
            <span class="cover-chip">ม.<?= $h($profile['class_year'] ?? '-') ?>/<?= $h($profile['class_room'] ?? '-') ?></span>
        </div>
    </div>

    <div class="cover-footer">
        <table width="100%"><tr>
            <td style="border:none; color:#64748b;">ออกเอกสาร ณ วันที่ <?= $h($issuedAt ?? PDFService::thaiDate()) ?></td>
            <td style="border:none; text-align:right; color:#64748b;">Document ID: PF-<?= $h($profile['student_code'] ?? date('Ymd')) ?>-<?= $h(date('His')) ?></td>
        </tr></table>
    </div>
</div>

<pagebreak />

<!-- ====================================================== -->
<!-- TABLE OF CONTENTS                                      -->
<!-- ====================================================== -->
<div class="toc-wrap">
    <div class="toc-title">สารบัญ <span style="font-size: 13pt; color: #64748b; font-weight: normal;">— Table of Contents</span></div>

    <table width="100%">
        <?php
        $toc = [
            ['01', 'ข้อมูลส่วนตัว',        'Personal Profile'],
            ['02', 'วิสัยทัศน์และเป้าหมาย', 'Career Vision & Goals'],
            ['03', 'สรุปภาพรวมความสำเร็จ',  'Achievement Overview'],
            ['04', 'ผลการเรียน',            'Academic Performance'],
            ['05', 'ทักษะและความถนัด',     'Skills & Interests'],
            ['06', 'คลังเหรียญเกียรติยศ',   'Badge Collection'],
            ['07', 'ผลงานที่ผ่านการรับรอง', 'Verified Portfolio Records'],
            ['08', 'การรับรองและลงนาม',    'Endorsement & Signatures'],
        ];
        foreach ($toc as $t): ?>
        <tr class="toc-row">
            <td class="toc-num"><?= $t[0] ?></td>
            <td>
                <div class="toc-name"><?= $h($t[1]) ?></div>
                <div class="toc-desc"><?= $h($t[2]) ?></div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<pagebreak />

<!-- ====================================================== -->
<!-- 01. PERSONAL PROFILE                                   -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">01</span>
    <span class="sec-title">ข้อมูลส่วนตัว</span>
    <span class="sec-sub">— Personal Profile</span>
</div>

<table class="pc-table">
    <tr>
        <td width="135pt">
            <div class="pc-photo">
                <?php if (!empty($avatarAbs)): ?>
                    <img src="<?= $h($avatarAbs) ?>">
                <?php else: ?>
                    <div class="pc-photo-ph">Photo</div>
                <?php endif; ?>
            </div>
        </td>
        <td class="pc-info">
            <div class="pc-name-th"><?= $h($profile['full_name'] ?? '-') ?></div>
            <?php if (!empty($profile['nickname'])): ?>
                <div class="pc-name-en">ชื่อเล่น: <?= $h($profile['nickname']) ?></div>
            <?php endif; ?>

            <table class="info-grid">
                <tr>
                    <td class="info-lbl">รหัสประจำตัว</td>
                    <td class="info-val"><?= $h($profile['student_code'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="info-lbl">ระดับชั้น / ห้อง</td>
                    <td class="info-val">มัธยมศึกษาปีที่ <?= $h($profile['class_year'] ?? '-') ?> ห้อง <?= $h($profile['class_room'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($profile['date_of_birth'])): ?>
                <tr>
                    <td class="info-lbl">วัน-เดือน-ปีเกิด</td>
                    <td class="info-val"><?= $h(PDFService::thaiDate($profile['date_of_birth'])) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($profile['phone'])): ?>
                <tr>
                    <td class="info-lbl">โทรศัพท์</td>
                    <td class="info-val"><?= $h($profile['phone']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($profile['email'])): ?>
                <tr>
                    <td class="info-lbl">อีเมล</td>
                    <td class="info-val"><?= $h($profile['email']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="info-lbl">สถานศึกษา</td>
                    <td class="info-val">โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ====================================================== -->
<!-- 02. CAREER VISION & GOALS                              -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">02</span>
    <span class="sec-title">วิสัยทัศน์และเป้าหมาย</span>
    <span class="sec-sub">— Career Vision &amp; Goals</span>
</div>

<?php if (!empty($profile['bio'])): ?>
    <div class="subsec">แนะนำตัวและปณิธานส่วนบุคคล</div>
    <div class="vision-box">
        <span class="vision-quote">&ldquo;</span><?= nl2br($h($profile['bio'])) ?>
    </div>
<?php endif; ?>

<?php if (!empty($goals)): ?>
    <div class="subsec">เป้าหมายการศึกษาและอาชีพ</div>
    <table class="goal-table">
        <?php foreach (array_chunk($goals, 2) as $row): ?>
            <tr>
                <?php foreach ($row as $g):
                    $st = $statusColor[$g['status'] ?? 'planning'] ?? $statusColor['planning'];
                ?>
                    <td width="50%">
                        <div class="goal-type"><?= $h($goalTypeLabel[$g['goal_type']] ?? $g['goal_type']) ?></div>
                        <div class="goal-target"><?= $h($g['target']) ?></div>
                        <?php if (!empty($g['target_details'])): ?>
                            <div class="goal-meta"><?= $h($g['target_details']) ?></div>
                        <?php endif; ?>
                        <div class="goal-meta" style="margin-top:6pt;">
                            <span class="goal-pill" style="background:<?= $st[1] ?>; color:<?= $st[0] ?>;"><?= $h($st[2]) ?></span>
                            <?php if (!empty($g['deadline'])): ?>
                                &nbsp; ภายใน <?= $h(PDFService::thaiDate($g['deadline'])) ?>
                            <?php endif; ?>
                        </div>
                    </td>
                <?php endforeach; ?>
                <?php for ($i = count($row); $i < 2; $i++): ?><td width="50%" style="border:none !important;"></td><?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (empty($profile['bio']) && empty($goals)): ?>
    <div class="text-muted" style="padding: 10pt 0;">ยังไม่มีการบันทึกวิสัยทัศน์และเป้าหมาย</div>
<?php endif; ?>

<pagebreak />

<!-- ====================================================== -->
<!-- 03. ACHIEVEMENT OVERVIEW                               -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">03</span>
    <span class="sec-title">สรุปภาพรวมความสำเร็จ</span>
    <span class="sec-sub">— Achievement Overview</span>
</div>

<table class="stats-table">
    <tr>
        <td style="border-top-color:#0f172a !important;">
            <div class="stats-num"><?= number_format($totalWorks) ?></div>
            <div class="stats-lbl">Verified Works</div>
            <div class="small text-muted mt-1">ผลงานที่ผ่านการรับรอง</div>
        </td>
        <td style="border-top-color:#b45309 !important;">
            <div class="stats-num"><?= number_format($totalPoints) ?></div>
            <div class="stats-lbl">Total XP</div>
            <div class="small text-muted mt-1">คะแนนสะสมรวม</div>
        </td>
        <td style="border-top-color:#15803d !important;">
            <div class="stats-num"><?= number_format($badgeCount) ?></div>
            <div class="stats-lbl">Badges Earned</div>
            <div class="small text-muted mt-1">เหรียญตราที่ได้รับ</div>
        </td>
        <td style="border-top-color:#7f1d1d !important;">
            <div class="stats-num stats-num-sm"><?= $h($levelMap[$topLevel][0] ?? '-') ?></div>
            <div class="stats-lbl">Peak Level</div>
            <div class="small text-muted mt-1">ระดับสูงสุดที่ได้รับ</div>
        </td>
    </tr>
</table>

<?php if (!empty($stats['category_counts'])): ?>
    <div class="subsec">การกระจายผลงานตามหมวดหมู่</div>
    <table class="acad-table">
        <thead>
            <tr><th>หมวดหมู่</th><th width="25%" class="text-center">จำนวน</th><th width="30%">สัดส่วน</th></tr>
        </thead>
        <tbody>
            <?php
            $maxCat = max($stats['category_counts']) ?: 1;
            foreach ($stats['category_counts'] as $cat => $cnt):
                $pct = ($cnt / $maxCat) * 100;
            ?>
            <tr>
                <td><?= $h($cat) ?></td>
                <td class="text-center"><?= number_format($cnt) ?></td>
                <td>
                    <div class="skill-bar"><div class="skill-fill" style="width:<?= $pct ?>%; background:#b45309;"></div></div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- ====================================================== -->
<!-- 04. ACADEMIC PERFORMANCE                               -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">04</span>
    <span class="sec-title">ผลการเรียน</span>
    <span class="sec-sub">— Academic Performance</span>
</div>

<?php if (!empty($academics)): ?>
    <table class="acad-table">
        <thead>
            <tr>
                <th>ปีการศึกษา</th>
                <th class="text-center">ภาคเรียน</th>
                <th class="text-center">จำนวนรายวิชา</th>
                <th class="text-center">เกรดเฉลี่ย (GPA)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($academics as $a): ?>
            <tr>
                <td><?= $h($a['academic_year']) ?></td>
                <td class="text-center"><?= $h($a['semester']) ?></td>
                <td class="text-center"><?= number_format((int) $a['subject_count']) ?></td>
                <td class="text-center acad-gpa"><?= number_format((float) $a['gpa'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="text-muted" style="padding: 10pt 0;">ยังไม่มีการบันทึกผลการเรียน</div>
<?php endif; ?>

<pagebreak />

<!-- ====================================================== -->
<!-- 05. SKILLS & INTERESTS                                 -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">05</span>
    <span class="sec-title">ทักษะและความถนัด</span>
    <span class="sec-sub">— Skills &amp; Interests</span>
</div>

<?php if (!empty($skills)): ?>
    <div class="subsec">ทักษะเด่นจากผลงานที่ได้รับการรับรอง</div>
    <?php
    $skillList = array_slice($skills, 0, 10, true);
    foreach ($skillList as $name => $lv):
        $pct = max(0, min(100, ((float) $lv / 5) * 100));
    ?>
        <div class="skill-row">
            <div class="skill-head">
                <span class="skill-name"><?= $h($name) ?></span>
                <span class="skill-val"><?= number_format((float) $lv, 1) ?> / 5.0</span>
            </div>
            <div class="skill-bar"><div class="skill-fill" style="width: <?= $pct ?>%;"></div></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($interests)):
    $grouped = [];
    foreach ($interests as $it) { $grouped[$it['type']][] = $it; }
?>
    <div class="subsec">ความสนใจ ความถนัด และงานอดิเรก</div>
    <?php foreach ($grouped as $type => $items): ?>
        <div class="int-group">
            <div class="int-label"><?= $h($interestLabel[$type] ?? $type) ?></div>
            <div style="margin-top: 4pt;">
            <?php foreach ($items as $it): ?>
                <span class="int-chip"><?= $h($it['item']) ?></span>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (empty($skills) && empty($interests)): ?>
    <div class="text-muted" style="padding: 10pt 0;">ยังไม่มีข้อมูลทักษะและความสนใจ</div>
<?php endif; ?>

<!-- ====================================================== -->
<!-- 06. BADGES                                             -->
<!-- ====================================================== -->
<?php if (!empty($badges)): ?>
<div class="sec">
    <span class="sec-num">06</span>
    <span class="sec-title">คลังเหรียญเกียรติยศ</span>
    <span class="sec-sub">— Badge Collection</span>
</div>

<table class="badge-grid">
    <?php foreach (array_chunk($badges, 4) as $row): ?>
    <tr>
        <?php foreach ($row as $b): ?>
        <td class="badge-cell">
            <div class="badge-ic"><?= $h($b['icon'] ?? '🏅') ?></div>
            <div class="badge-nm"><?= $h($b['name'] ?? '-') ?></div>
            <?php if (!empty($b['earned_at'])): ?>
                <div class="badge-dt"><?= $h(PDFService::thaiDate($b['earned_at'])) ?></div>
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
        <?php for ($i = count($row); $i < 4; $i++): ?><td style="border:none !important; background:transparent !important;"></td><?php endfor; ?>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<pagebreak />

<!-- ====================================================== -->
<!-- 07. VERIFIED PORTFOLIO                                 -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">07</span>
    <span class="sec-title">ผลงานที่ผ่านการรับรอง</span>
    <span class="sec-sub">— Verified Portfolio Records</span>
</div>

<?php if (empty($works)): ?>
    <div style="text-align:center; padding:40pt; color:#94a3b8; font-size:12pt;">
        — ยังไม่มีผลงานที่ผ่านการรับรองในปัจจุบัน —
    </div>
<?php else: ?>
    <?php foreach ($works as $idx => $w):
        $lv = $w['achievement_level'] ?? 'school';
        $lvl = $levelMap[$lv] ?? $levelMap['school'];
    ?>
    <div class="work">
        <div class="work-head">
            <table width="100%" style="margin:0;"><tr>
                <td style="border:none !important; padding:0 !important;">
                    <div class="work-idx">ผลงานที่ <?= sprintf('%02d', $idx + 1) ?>
                        <?php if (!empty($w['category_name'])): ?>
                            &middot; <?= $h($w['category_name']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="work-title"><?= $h($w['title'] ?? '-') ?></div>
                    <div class="work-meta">
                        <?php if (!empty($w['event_date'])): ?>
                            <?= $h(PDFService::thaiDate($w['event_date'])) ?> &middot;
                        <?php endif; ?>
                        <span class="lvl-pill" style="background:<?= $lvl[2] ?>; color:<?= $lvl[1] ?>;"><?= $h($lvl[0]) ?></span>
                    </div>
                </td>
                <td width="90pt" style="border:none !important; padding:0 !important; text-align:right; vertical-align:top;">
                    <span class="verify-stamp">Verified</span>
                </td>
            </tr></table>
        </div>

        <?php if (!empty($w['description'])): ?>
        <div class="work-body">
            <div class="work-desc"><?= nl2br($h($w['description'])) ?></div>
            <?php if (!empty($w['award'])): ?>
                <div class="work-award">🏆 <strong>รางวัลที่ได้รับ:</strong> <?= $h($w['award']) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($w['skill_list'])): ?>
        <div class="work-skills">
            <span class="small text-muted">ทักษะที่เกี่ยวข้อง: </span>
            <?php foreach (explode(',', $w['skill_list']) as $s): $s = trim($s); if ($s === '') continue; ?>
                <span class="skill-tag"><?= $h($s) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<pagebreak />

<!-- ====================================================== -->
<!-- 08. ENDORSEMENT & SIGNATURES                           -->
<!-- ====================================================== -->
<div class="sec">
    <span class="sec-num">08</span>
    <span class="sec-title">การรับรองและลงนาม</span>
    <span class="sec-sub">— Endorsement &amp; Signatures</span>
</div>

<p class="endorse-text">
    ข้าพเจ้าขอรับรองว่าข้อมูลส่วนบุคคล ผลการเรียน ทักษะ ผลงาน รางวัล และเหรียญเกียรติยศที่ปรากฏในแฟ้มสะสมผลงาน (Portfolio Digest)
    ฉบับนี้ เป็นข้อมูลจริงที่ได้รับการบันทึกและตรวจสอบผ่านระบบ e-Portfolio ของโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย มุกดาหาร
    ซึ่งเป็นส่วนหนึ่งของการติดตามและประเมินพัฒนาการของนักเรียนอย่างต่อเนื่อง เพื่อใช้ประกอบการสมัครเรียนต่อ การรับทุน
    และการยื่นเอกสารต่อสถาบันการศึกษาในระดับอุดมศึกษาต่อไป
</p>

<table class="sig-table">
    <tr>
        <td>
            <div style="height:60pt;"></div>
            <div class="sig-line"></div>
            <div class="sig-name">( <?= $h($profile['full_name'] ?? '.....................................') ?> )</div>
            <div class="sig-role">เจ้าของแฟ้มสะสมผลงาน</div>
            <div class="sig-role">วันที่ ............../............../..............</div>
        </td>
        <td>
            <div style="height:60pt;"></div>
            <div class="sig-line"></div>
            <div class="sig-name">( ........................................................... )</div>
            <div class="sig-role">ครูที่ปรึกษา / ผู้รับรองระดับโรงเรียน</div>
            <div class="sig-role">วันที่ ............../............../..............</div>
        </td>
    </tr>
</table>

<div class="end-mark">
    <hr>
    สิ้นสุดเอกสาร &middot; End of Document
    <hr>
    Issued by PCSHS KSN &middot; e-Portfolio Intelligence System
</div>
