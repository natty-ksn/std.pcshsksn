<?php $pageTitle = 'รายชื่อนักเรียน'; ?>
<div class="flex-between mb-3" style="flex-wrap:wrap;gap:12px;">
    <h2>👥 รายชื่อนักเรียน (
        <?= $total ?> คน)
    </h2>
</div>
<div class="card mb-3">
    <div class="card-body" style="padding:14px;">
        <form method="GET" action="<?= url('teacher/students') ?>"
            style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" class="form-control" style="flex:1;min-width:200px;"
                placeholder="🔍 ค้นหาชื่อ/รหัส..." value="<?= e($search) ?>">
            <select name="class_year" class="form-control" style="width:120px;" onchange="this.form.submit()">
                <option value="">ทุกชั้น</option>
                <?php for ($y = 1; $y <= 6; $y++): ?>
                    <option value="<?= $y ?>" <?= $classYear == $y ? 'selected' : '' ?>>ม.
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-secondary">ค้นหา</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>นักเรียน</th>
                        <th>ชั้น/ห้อง</th>
                        <th>ผลงาน</th>
                        <th>GPA</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px 20px; color: #64748b;">
                                <div style="font-size: 2.5rem; margin-bottom: 10px;">👥</div>
                                <h4 style="margin: 0 0 5px 0; color: #1e293b;">ไม่พบข้อมูลนักเรียน</h4>
                                <p style="margin: 0; font-size: 0.85rem;">ไม่พบนักเรียนตามเงื่อนไขชั้นปีที่เลือก หรือยังไม่มีการเพิ่มข้อมูลนักเรียนในระบบ</p>
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="<?= $s['avatar_path'] ? uploadUrl($s['avatar_path']) : defaultAvatar() ?>"
                                        style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                    <div><strong>
                                            <?= e($s['full_name']) ?>
                                        </strong>
                                        <div style="font-size:0.75rem;color:var(--gray-400);">
                                            <?= e($s['student_code']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= classYearLabel($s['class_year']) ?>/
                                <?= e($s['class_room']) ?>
                            </td>
                            <td><span style="font-weight:700;color:var(--primary-600);">
                                    <?= $s['work_count'] ?>
                                </span></td>
                            <td><span
                                    style="font-weight:700;color:<?= ($s['gpa'] ?? 0) >= 3 ? 'var(--accent-600)' : 'var(--gray-600)' ?>;">
                                    <?= $s['gpa'] ? number_format($s['gpa'], 2) : '-' ?>
                                </span></td>
                            <td><a href="<?= url('teacher/student/' . $s['id']) ?>"
                                    class="btn btn-sm btn-outline">ดูรายละเอียด</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (($totalPages ?? 1) > 1): ?>
            <div style="display: flex; justify-content: center; gap: 6px; padding: 16px; border-top: 1px solid var(--gray-100); flex-wrap: wrap;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="<?= url('teacher/students?page=' . $p . ($search ? '&search=' . urlencode($search) : '') . ($classYear ? '&class_year=' . urlencode($classYear) : '')) ?>"
                       class="btn btn-sm <?= ($page ?? 1) == $p ? 'btn-primary' : 'btn-outline' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>