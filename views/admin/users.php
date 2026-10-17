<?php $pageTitle = 'จัดการผู้ใช้'; ?>
<div class="flex-between mb-3" style="flex-wrap:wrap;gap:12px;">
    <h2>👥 จัดการผู้ใช้</h2>
    <div style="display:flex;gap:10px;">
        <button onclick="document.getElementById('importUserModal').style.display='flex'" class="btn btn-secondary">📥
            นำเข้า CSV</button>
        <button onclick="document.getElementById('addUserModal').style.display='flex'" class="btn btn-primary">➕
            เพิ่มผู้ใช้</button>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body" style="padding:14px;">
        <form method="GET" action="<?= url('admin/users') ?>" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="search" class="form-control" style="flex:1;" placeholder="🔍 ค้นหา..."
                value="<?= e($search) ?>">
            <select name="role" class="form-control" style="width:150px;" onchange="this.form.submit()">
                <option value="">ทุก Role</option>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>นักเรียน</option>
                <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>ครู</option>
                <option value="executive" <?= $role === 'executive' ? 'selected' : '' ?>>ผู้บริหาร</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="parent" <?= $role === 'parent' ? 'selected' : '' ?>>ผู้ปกครอง</option>
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
                        <th style="width: 50px;"></th>
                        <th>Username</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>Role</th>
                        <th>สถานะ</th>
                        <th>เข้าใช้ล่าสุด</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <img src="<?= !empty($u['avatar_path']) ? uploadUrl($u['avatar_path']) : defaultAvatar($u['email'] ?? '', $u['full_name'] ?? '') ?>" 
                                     alt="avatar" 
                                     style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--gray-200);">
                            </td>
                            <td><strong>
                                    <?= e($u['username']) ?>
                                </strong></td>
                            <td>
                                <?= e($u['full_name']) ?>
                            </td>
                            <td><span
                                    class="badge <?= $u['role'] === 'admin' ? 'badge-national' : ($u['role'] === 'teacher' ? 'badge-province' : ($u['role'] === 'executive' ? 'badge-national' : 'badge-school')) ?>"
                                    style="<?= $u['role'] === 'executive' ? 'background: linear-gradient(135deg, #fbbf24, #d97706);' : '' ?>">
                                    <?= e($u['role']) ?>
                                </span></td>
                            <td>
                                <?= $u['is_active'] ? '<span style="color:var(--accent-600);">✅ Active</span>' : '<span style="color:var(--danger-500);">❌ Disabled</span>' ?>
                            </td>
                            <td style="font-size:0.8rem;color:var(--gray-400);">
                                <?= $u['last_login'] ? timeAgo($u['last_login']) : 'ยังไม่เคย' ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <a href="<?= url('admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline" title="แก้ไข">✏️</a>
                                    
                                    <?php if ($u['role'] === 'student'): ?>
                                    <form method="POST" action="<?= url('admin/users/' . $u['id'] . '/promote') ?>" style="display:inline;" onsubmit="return confirm('ยืนยันอัปเกรดบัญชี <?= e($u['full_name']) ?> ให้เป็นสิทธิ์ ครู/บุคลากร หรือไม่?')">
                                        <?= CSRF::field() ?><button type="submit" class="btn btn-sm" style="background:var(--primary-600);color:white;border:none;" title="อัปเกรดเป็นครู">👩‍🏫 เลื่อนขั้น</button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="<?= url('admin/users/' . $u['id'] . '/toggle') ?>"
                                        style="display:inline;">
                                        <?= CSRF::field() ?><button type="submit" class="btn btn-sm btn-secondary">
                                            <?= $u['is_active'] ? 'ปิด' : 'เปิด' ?>
                                        </button>
                                    </form>

                                    <?php if ($u['id'] != Auth::id()): ?>
                                    <form method="POST" action="<?= url('admin/users/' . $u['id'] . '/delete') ?>"
                                        style="display:inline;" onsubmit="return confirm('ยืนยันการลบผู้ใช้รายนี้? ข้อมูลทั้งหมดจะหายไป!')">
                                        <?= CSRF::field() ?><button type="submit" class="btn btn-sm btn-danger" title="ลบ">🗑️</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center;">
    <div class="card" style="width:450px;max-width:90%;">
        <div class="card-header">
            <h3>➕ เพิ่มผู้ใช้ใหม่</h3><button onclick="this.closest('#addUserModal').style.display='none'"
                style="background:none;border:none;font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('admin/users/create') ?>">
                <?= CSRF::field() ?>
                <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username"
                        class="form-control" required></div>
                <div class="form-group"><label class="form-label">ชื่อ-นามสกุล *</label><input type="text"
                        name="full_name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control"></div>
                <div class="form-group"><label class="form-label">Role *</label><select name="role" class="form-control"
                        required>
                        <option value="student">นักเรียน</option>
                        <option value="teacher">ครู</option>
                        <option value="executive">ผู้บริหาร</option>
                        <option value="admin">Admin</option>
                        <option value="parent">ผู้ปกครอง</option>
                    </select></div>
                <div class="form-group"><label class="form-label">Password</label><input type="text" name="password"
                        class="form-control" placeholder="ค่าเริ่มต้น: pccks1234"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">💾 สร้างบัญชี</button>
            </form>
        </div>
    </div>
</div>

<!-- Import User Modal -->
<div id="importUserModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center;">
    <div class="card" style="width:500px;max-width:90%;">
        <div class="card-header">
            <h3>📥 นำเข้าข้อมูลผู้ใช้จาก CSV</h3><button onclick="this.closest('#importUserModal').style.display='none'"
                style="background:none;border:none;font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('admin/users/import') ?>" enctype="multipart/form-data">
                <?= CSRF::field() ?>
                <p class="text-gray-500 mb-3" style="font-size: 0.9rem;">
                    กรุณาเลือกไฟล์ .csv ที่มีรูปแบบดังนี้:<br>
                    <code
                        style="background:var(--gray-50);padding:4px;display:block;margin-top:4px;">username, full_name, role, email, password</code>
                </p>
                <div class="form-group">
                    <label class="form-label">เลือกไฟล์ CSV</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <div style="background:rgba(245,158,11,0.1);padding:12px;border-radius:8px;margin-bottom:20px;">
                    <small style="color:var(--warning-600);">
                        ⚠️ หาก Username หรือ Email ซ้ำกับที่มีอยู่แล้ว ระบบจะข้ามรายการนั้นไป
                    </small>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">🚀 เริ่มนำเข้าข้อมูล</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const action = params.get('action');
    if (action === 'add') {
        const modal = document.getElementById('addUserModal');
        if (modal) modal.style.display = 'flex';
    } else if (action === 'import') {
        const modal = document.getElementById('importUserModal');
        if (modal) modal.style.display = 'flex';
    }
});
</script>