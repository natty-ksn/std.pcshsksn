<?php $pageTitle = 'แก้ไขผู้ใช้'; ?>

<div class="flex-between mb-3">
    <h2>✏️ แก้ไขข้อมูลผู้ใช้: <?= e($user['username']) ?></h2>
    <a href="<?= url('admin/users') ?>" class="btn btn-secondary">⬅️ กลับไปหน้าจัดการ</a>
</div>

<div class="card slide-up" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body" style="padding: 32px;">
        <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/update') ?>">
            <?= CSRF::field() ?>
            
            <div class="form-group">
                <label class="form-label">Username (รหัสนักเรียน/บุคลากร)</label>
                <input type="text" name="username" class="form-control" value="<?= e($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Role (สิทธิ์การใช้งาน)</label>
                    <select name="role" class="form-control" required>
                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>นักเรียน</option>
                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>ครู</option>
                        <option value="executive" <?= $user['role'] === 'executive' ? 'selected' : '' ?>>ผู้บริหาร</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="parent" <?= $user['role'] === 'parent' ? 'selected' : '' ?>>ผู้ปกครอง</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ตำแหน่ง (สำหรับผู้บริหาร)</label>
                    <input type="text" name="position" class="form-control" value="<?= e($user['position']) ?>" placeholder="เช่น ผู้อำนวยการโรงเรียน">
                </div>
            </div>

            <div class="form-group" style="background: var(--gray-50); padding: 15px; border-radius: 12px; border: 1px dashed var(--gray-200);">
                <label class="form-label" style="color: var(--danger-600); font-weight: 700;">🔐 เปลี่ยนรหัสผ่านใหม่</label>
                <input type="text" name="password" class="form-control" placeholder="ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน">
                <small style="color: var(--gray-500); display: block; margin-top: 5px;">* หากใส่ข้อมูลในช่องนี้ ระบบจะเปลี่ยนรหัสผ่านเป็นค่าใหม่ทันที</small>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 บันทึกการเปลี่ยนแปลง</button>
                <button type="button" onclick="history.back()" class="btn btn-outline" style="flex: 1;">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>
