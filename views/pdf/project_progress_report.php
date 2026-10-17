<?php
/**
 * PDF Template — รายงานความก้าวหน้าโครงงาน (Project Progress Report)
 *
 * Variables ที่คาดหวัง:
 *   $project   array  ข้อมูลโครงงาน (รวม advisor_name, subject_name, project_code, ฯลฯ)
 *   $members   array  รายชื่อสมาชิกในทีม
 *   $logbooks  array  บันทึกกิจกรรม
 *   $documents array  เอกสารแนบ
 *   $stats     array  ['total_hours' => float, 'log_count' => int, 'doc_count' => int]
 *   $issuedAt  string วัน-เวลาที่ออกเอกสาร
 */

$statusMap = [
    'proposal_submitted' => ['ข้อเสนอรอพิจารณา', 'badge-info'],
    'approved'           => ['อนุมัติแล้ว',     'badge-success'],
    'in_progress'        => ['กำลังดำเนินการ',  'badge-warning'],
    'completed'          => ['ดำเนินการเสร็จสิ้น','badge-success'],
    'rejected'           => ['ไม่ผ่านการพิจารณา','badge-danger'],
];
$statusInfo = $statusMap[$project['status'] ?? ''] ?? [$project['status'] ?? '-', 'badge-info'];

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>

<div class="doc-title">รายงานความก้าวหน้าโครงงาน</div>
<div class="doc-subtitle">Project Progress Report</div>

<div class="doc-meta">
    <table class="clean">
        <tr>
            <td width="22%"><strong>รหัสโครงงาน</strong></td>
            <td width="38%"><?= $h($project['project_code'] ?? '-') ?></td>
            <td width="22%"><strong>สถานะปัจจุบัน</strong></td>
            <td><span class="badge <?= $statusInfo[1] ?>"><?= $h($statusInfo[0]) ?></span></td>
        </tr>
        <tr>
            <td><strong>ปีการศึกษา</strong></td>
            <td><?= $h(($project['academic_year'] ?? date('Y')) + 543) ?></td>
            <td><strong>ภาคเรียน</strong></td>
            <td><?= $h($project['semester'] ?? '-') ?></td>
        </tr>
        <tr>
            <td><strong>กลุ่มสาระ / รายวิชา</strong></td>
            <td colspan="3"><?= $h($project['subject_name'] ?? 'ไม่ระบุ') ?></td>
        </tr>
        <tr>
            <td><strong>ครูที่ปรึกษา</strong></td>
            <td colspan="3"><?= $h($project['advisor_name'] ?? '-') ?></td>
        </tr>
    </table>
</div>

<h2>๑. ชื่อโครงงาน</h2>
<p class="no-indent"><strong>ภาษาไทย:</strong> <?= $h($project['title_th'] ?? '-') ?></p>
<?php if (!empty($project['title_en'])): ?>
<p class="no-indent"><strong>English:</strong> <?= $h($project['title_en']) ?></p>
<?php endif; ?>

<h2>๒. คณะผู้จัดทำ</h2>
<table>
    <thead>
        <tr>
            <th width="8%" class="text-center">ลำดับ</th>
            <th>ชื่อ-สกุล</th>
            <th width="25%">บทบาท</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($members)): ?>
            <tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูลสมาชิก</td></tr>
        <?php else: foreach ($members as $i => $m): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= $h($m['full_name'] ?? $m['username'] ?? '-') ?></td>
                <td><?= ($m['role_in_project'] ?? '') === 'leader' ? 'หัวหน้าทีม' : 'สมาชิก' ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<h2>๓. บทคัดย่อ / ความเป็นมา</h2>
<p><?= nl2br($h($project['abstract_th'] ?? 'ยังไม่มีข้อมูลบทคัดย่อ')) ?></p>

<h2>๔. สรุปความก้าวหน้า</h2>
<table>
    <tr>
        <th width="40%">ตัวชี้วัด</th>
        <th>ค่า</th>
    </tr>
    <tr>
        <td>จำนวนบันทึกกิจกรรม (Logbook)</td>
        <td><?= (int) ($stats['log_count'] ?? count($logbooks ?? [])) ?> รายการ</td>
    </tr>
    <tr>
        <td>เวลารวมที่ใช้ในการดำเนินงาน</td>
        <td><?= number_format((float) ($stats['total_hours'] ?? 0), 1) ?> ชั่วโมง</td>
    </tr>
    <tr>
        <td>จำนวนเอกสารแนบ</td>
        <td><?= (int) ($stats['doc_count'] ?? count($documents ?? [])) ?> ไฟล์</td>
    </tr>
</table>

<h2>๕. บันทึกกิจกรรม (Logbook)</h2>
<?php if (empty($logbooks)): ?>
    <p class="text-muted no-indent">— ยังไม่มีบันทึกกิจกรรม —</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th width="14%">วันที่</th>
                <th>กิจกรรม</th>
                <th width="18%">ผู้บันทึก</th>
                <th width="10%" class="text-center">ชั่วโมง</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logbooks as $lb): ?>
                <tr>
                    <td class="small"><?= $h(PDFService::thaiDate($lb['created_at'] ?? '')) ?></td>
                    <td><?= nl2br($h($lb['activity_detail'] ?? '-')) ?></td>
                    <td class="small"><?= $h($lb['student_name'] ?? '-') ?></td>
                    <td class="text-center"><?= number_format((float) ($lb['hours_spent'] ?? 0), 1) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>๖. เอกสารและไฟล์แนบ</h2>
<?php if (empty($documents)): ?>
    <p class="text-muted no-indent">— ยังไม่มีเอกสารแนบ —</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">ลำดับ</th>
                <th>ชื่อเอกสาร / รายละเอียด</th>
                <th width="22%">ประเภท</th>
                <th width="18%">วันที่อัปโหลด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $i => $doc): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= $h($doc['title'] ?? $doc['file_name'] ?? '-') ?></td>
                    <td><?= $h($doc['document_type'] ?? $doc['type'] ?? '-') ?></td>
                    <td class="small"><?= $h(PDFService::thaiDate($doc['created_at'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>๗. การลงนามรับรองเอกสาร</h2>
<table class="clean signature-row">
    <tr>
        <td class="signature-cell">
            <div class="signature-line"></div>
            ( <?= $h($members[0]['full_name'] ?? $members[0]['username'] ?? '...........................') ?> )<br>
            <span class="text-muted small">หัวหน้าทีมโครงงาน</span><br>
            <span class="text-muted small">วันที่ ............ / ............ / ............</span>
        </td>
        <td class="signature-cell">
            <div class="signature-line"></div>
            ( <?= $h($project['advisor_name'] ?? '...........................') ?> )<br>
            <span class="text-muted small">ครูที่ปรึกษาโครงงาน</span><br>
            <span class="text-muted small">วันที่ ............ / ............ / ............</span>
        </td>
    </tr>
</table>

<p class="text-center text-muted small mt-4">
    เอกสารฉบับนี้ออกโดยระบบ PCSHS KSN e-Portfolio &middot; ออกเมื่อ <?= $h($issuedAt ?? PDFService::thaiDate()) ?>
</p>
