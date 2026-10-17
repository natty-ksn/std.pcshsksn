<?php $pageTitle = 'Mentorship Intelligence — ติดตามนักเรียน'; ?>

<div class="animate-fade-in" style="max-width: 1200px; margin: 0 auto;">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 style="font-weight: 800; color: var(--gray-900);">🕵️‍♂️ Mentorship Intelligence</h2>
            <p class="text-muted">ระบบตรวจจับนักเรียนที่ขาดความเคลื่อนไหว (Zero-Entry Alert) เพื่อการติดตามอย่างทันท่วงที</p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--red-500); margin-bottom: 5px;">🔥 วิกฤต: ไม่มีการส่งงาน > <?= $inactiveDays ?> วัน</div>
            <div class="badge" style="background: var(--red-100); color: var(--red-700); padding: 8px 15px; border-radius: 50px;">พบนักเรียนกลุ่มเสี่ยง <?= count($inactiveStudents) ?> คน</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4" style="border: none; box-shadow: var(--shadow-sm); border-radius: 16px;">
        <div class="card-body">
            <form method="GET" class="grid-4 gap-3 align-items-end">
                <div class="form-group mb-0">
                    <label class="form-label">ตรวจสอบย้อนหลัง (วัน)</label>
                    <input type="number" name="days" class="form-control" value="<?= $inactiveDays ?>" min="7">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="class_year" class="form-control">
                        <option value="">ทั้งหมด</option>
                        <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $classYear == $i ? 'selected' : '' ?>>ม.<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">ห้อง</label>
                    <input type="text" name="room" class="form-control" value="<?= e($room) ?>" placeholder="เช่น 1">
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary w-100">📡 สแกนหากลุ่มเสี่ยง</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($inactiveStudents)): ?>
        <div class="text-center py-5 glass-card">
            <div style="font-size: 4rem;">🛡️</div>
            <h3 class="mt-3">นักเรียนทุกคนมีความเคลื่อนไหวปกติ</h3>
            <p class="text-muted">ไม่พบนักเรียนที่ไม่ส่งงานเกินที่กำหนดในส่วนที่คุณเลือก</p>
        </div>
    <?php else: ?>
        <div class="grid-1 gap-3">
            <?php foreach ($inactiveStudents as $student): ?>
                <div class="glass-card p-4 d-flex justify-content-between align-items-center" style="border-left: 5px solid var(--red-500);">
                    <div class="d-flex align-items-center gap-4">
                        <img src="<?= $student['avatar_path'] ? uploadUrl($student['avatar_path']) : defaultAvatar() ?>" 
                             style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: var(--shadow-sm);">
                        <div>
                            <h3 style="margin: 0; font-weight: 800; font-size: 1.1rem;"><?= e($student['full_name']) ?></h3>
                            <div style="font-size: 0.9rem; color: var(--gray-500); margin-bottom: 5px;">
                                ม.<?= $student['class_year'] ?>/<?= e($student['class_room']) ?> • รหัส <?= e($student['student_code']) ?>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge" style="background: var(--red-50); color: var(--red-600); font-weight: 700;">
                                    ⚠️ ขาดความเคลื่อนไหว: <?= $student['last_upload'] ? timeAgo($student['last_upload']) : 'ไม่เคยส่งงานเลย' ?>
                                </span>
                                <span class="badge" style="background: var(--blue-50); color: var(--blue-600);">
                                    📊 ผลงานสะสมทั้งหมด: <?= $student['total_works'] ?> รายการ
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('teacher/student/' . $student['id']) ?>" class="btn btn-secondary">
                            👁️ ดูข้อมูลเชิงลึก
                        </a>
                        <button type="button" class="btn btn-primary" onclick="sendMentorNudge(<?= (int)$student['id'] ?>, '<?= e(addslashes($student['full_name'])) ?>', this)">
                            💬 ส่งข้อความกระตุ้น
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function sendMentorNudge(studentId, studentName, btn) {
    if (!confirm('ส่งข้อความแจ้งเตือนกระตุ้นการส่งผลงานไปยัง ' + studentName + ' ใช่หรือไม่?')) return;
    btn.disabled = true;
    btn.textContent = '⏳ กำลังส่ง...';
    fetch('<?= url('analytics/early-warning/notify') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= CSRF::token() ?>'
        },
        body: JSON.stringify({
            student_id: studentId,
            risk_level: 'High',
            csrf_token: '<?= CSRF::token() ?>'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✅ ส่งแจ้งเตือนแล้ว';
            btn.className = 'btn btn-success';
        } else {
            alert(data.message || 'เกิดข้อผิดพลาดในการส่งข้อความ');
            btn.disabled = false;
            btn.textContent = '💬 ส่งข้อความกระตุ้น';
        }
    })
    .catch(() => {
        alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
        btn.disabled = false;
        btn.textContent = '💬 ส่งข้อความกระตุ้น';
    });
}
</script>

<style>
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); }
    @media (max-width: 768px) { .grid-4 { grid-template-columns: 1fr; } }
</style>
