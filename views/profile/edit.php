<?php $pageTitle = 'แก้ไขโปรไฟล์'; ?>

<div style="max-width: 700px; margin: 0 auto;">
    <div class="flex-between mb-3">
        <h2>✏️ แก้ไขโปรไฟล์</h2>
        <a href="<?= url('profile') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <!-- Avatar Upload -->
    <div class="card mb-3">
        <div class="card-body text-center">
            <img src="<?= ($profile['avatar_path'] ?? '') ? uploadUrl($profile['avatar_path']) : defaultAvatar() ?>"
                alt="avatar"
                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-200); margin-bottom: 16px;">
            <form method="POST" action="<?= url('profile/avatar') ?>" enctype="multipart/form-data"
                style="display: inline-flex; align-items: center; gap: 8px;">
                <?= CSRF::field() ?>
                <input type="file" name="avatar" accept="image/*" required style="font-size: 0.85rem;">
                <button type="submit" class="btn btn-sm btn-primary">📷 อัพโหลด</button>
            </form>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('profile/update') ?>">
                <?= CSRF::field() ?>

                <div class="form-group">
                    <label class="form-label">ชื่อ-นามสกุล <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                        value="<?= e($profile['full_name'] ?? '') ?>" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">ชื่อเล่น</label>
                        <input type="text" name="nickname" class="form-control"
                            value="<?= e($profile['nickname'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>"
                            placeholder="08X-XXXXXXX">
                    </div>
                </div>

                <?php if ($profile['role'] === 'student'): ?>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">ชั้นปี <span class="required">*</span></label>
                        <select name="class_year" class="form-control" required>
                            <?php for ($y = 1; $y <= 6; $y++): ?>
                                <option value="<?= $y ?>" <?= ($profile['class_year'] ?? 1) == $y ? 'selected' : '' ?>>ม.
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ห้อง</label>
                        <input type="text" name="class_room" class="form-control"
                            value="<?= e($profile['class_room'] ?? '') ?>" placeholder="เช่น 1, 2, 3, 4">
                    </div>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label class="form-label">ตำแหน่ง / ฝ่ายงาน <span class="required">*</span></label>
                    <input type="text" name="position" class="form-control"
                        value="<?= e($profile['position'] ?? '') ?>" placeholder="เช่น หัวหน้ากลุ่มสาระฯ, รองผู้อำนวยการ" required>
                    <small class="text-muted">ข้อมูลนี้จะถูกแสดงที่หน้าโปรไฟล์ของคุณ</small>
                </div>
                <!-- Hidden inputs to prevent SQL errors in update -->
                <input type="hidden" name="class_year" value="0">
                <input type="hidden" name="class_room" value="">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">วันเกิด</label>
                    <input type="date" name="date_of_birth" class="form-control"
                        value="<?= e($profile['date_of_birth'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">แนะนำตัว (Bio)</label>
                    <textarea name="bio" class="form-control" rows="4"
                        placeholder="บอกเล่าเกี่ยวกับตัวคุณ ความสนใจ สิ่งที่ชอบ..."><?= e($profile['bio'] ?? '') ?></textarea>
                </div>

                <!-- Account & Security Section -->
                <div style="margin: 24px 0; padding-top: 24px; border-top: 2px dashed var(--gray-100);">
                    <h3 style="margin-bottom: 16px; color: var(--primary-700);">🔒 บัญชีและความปลอดภัย</h3>
                    
                    <div class="form-group">
                        <label class="form-label">ชื่อผู้ใช้ (Username) <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" 
                               value="<?= e($profile['username'] ?? '') ?>" required>
                        <small class="text-muted">ใช้สำหรับการเข้าสู่ระบบแทนรหัสนักเรียนได้</small>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">รหัสผ่านใหม่ (ปล่อยว่างถ้าไม่ต้องการเปลี่ยน)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <a href="<?= url('profile') ?>" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">💾 บันทึกโปรไฟล์</button>
                </div>
            </form>
        </div>
    </div>
</div>