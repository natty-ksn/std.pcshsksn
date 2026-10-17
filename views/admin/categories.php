<?php $pageTitle = 'จัดการหมวดหมู่ผลงาน'; ?>

<div class="admin-categories">
    <div class="page-header">
        <div>
            <h1 class="page-title">จัดการหมวดหมู่ผลงาน</h1>
            <p class="page-subtitle">จัดการหมวดหมู่เพื่อให้ข้อมูลนักเรียนเป็นระเบียบ</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()">
            <span>➕</span> เพิ่มหมวดหมู่ใหม่
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th width="80">ลำดับ</th>
                        <th>หมวดหมู่</th>
                        <th>สีประจำหมวด</th>
                        <th>จำนวนผลงาน</th>
                        <th>สถานะ</th>
                        <th class="text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <span class="badge" style="background: var(--gray-100); color: var(--gray-600);">
                                    <?= $cat['sort_order'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 1.5rem;"><?= e($cat['icon']) ?></span>
                                    <strong><?= e($cat['name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 20px; height: 20px; border-radius: 4px; background: <?= $cat['color'] ?>"></div>
                                    <code style="font-size: 0.75rem;"><?= $cat['color'] ?></code>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                                    <?= number_format($cat['item_count']) ?> ผลงาน
                                </span>
                            </td>
                            <td>
                                <?php if ($cat['is_active']): ?>
                                    <span class="badge badge-success">เปิดใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge badge-error">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                <button type="button" class="btn btn-sm btn-ghost" title="แก้ไข"
                                    onclick='openEditModal(<?= json_encode([
                                        "id" => $cat["id"],
                                        "name" => $cat["name"],
                                        "icon" => $cat["icon"],
                                        "color" => $cat["color"],
                                        "sort_order" => $cat["sort_order"],
                                        "is_active" => $cat["is_active"]
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
                                <form action="<?= url('admin/categories/' . $cat['id'] . '/delete') ?>" method="POST" style="display: inline;"
                                    onsubmit="return confirm('ยืนยันการลบหมวดหมู่ <?= e(addslashes($cat['name'])) ?>?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-ghost text-red-500" title="<?= $cat['item_count'] > 0 ? 'ไม่สามารถลบได้เนื่องจากมีผลงานใช้งานอยู่' : 'ลบหมวดหมู่' ?>"
                                        <?= $cat['item_count'] > 0 ? 'disabled' : '' ?>>🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for adding/editing category -->
<div id="categoryModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="categoryModalTitle">เพิ่มหมวดหมู่ใหม่</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form id="categoryForm" action="<?= url('admin/categories/store') ?>" method="POST">
            <?= CSRF::field() ?>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">ชื่อหมวดหมู่ <span class="required">*</span></label>
                    <input type="text" id="catName" name="name" class="form-control" placeholder="เช่น วิทยาศาสตร์, ศิลปะ" required>
                </div>
                <div class="grid grid-2" style="gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji)</label>
                        <input type="text" id="catIcon" name="icon" class="form-control" placeholder="เช่น 🔬, 🎨" value="📁">
                    </div>
                    <div class="form-group">
                        <label class="form-label">สีประจำหมวด</label>
                        <input type="color" id="catColor" name="color" class="form-control" style="height: 42px; padding: 4px;" value="#6366f1">
                    </div>
                </div>
                <div class="grid grid-2" style="gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input type="number" id="catSortOrder" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group" id="catActiveGroup" style="display: none;">
                        <label class="form-label">สถานะ</label>
                        <select id="catIsActive" name="is_active" class="form-control">
                            <option value="1">เปิดใช้งาน</option>
                            <option value="0">ปิดใช้งาน</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกหมวดหมู่</button>
            </div>
        </form>
    </div>
</div>

<script>
    const storeUrl = '<?= url('admin/categories/store') ?>';
    const updateBaseUrl = '<?= url('admin/categories') ?>';

    function openModal() {
        document.getElementById('categoryModalTitle').textContent = 'เพิ่มหมวดหมู่ใหม่';
        document.getElementById('categoryForm').action = storeUrl;
        document.getElementById('catName').value = '';
        document.getElementById('catIcon').value = '📁';
        document.getElementById('catColor').value = '#6366f1';
        document.getElementById('catSortOrder').value = '0';
        document.getElementById('catActiveGroup').style.display = 'none';
        document.getElementById('categoryModal').classList.add('active');
    }

    function openEditModal(cat) {
        document.getElementById('categoryModalTitle').textContent = 'แก้ไขหมวดหมู่: ' + cat.name;
        document.getElementById('categoryForm').action = updateBaseUrl + '/' + cat.id + '/update';
        document.getElementById('catName').value = cat.name || '';
        document.getElementById('catIcon').value = cat.icon || '📁';
        document.getElementById('catColor').value = cat.color || '#6366f1';
        document.getElementById('catSortOrder').value = cat.sort_order || 0;
        document.getElementById('catIsActive').value = cat.is_active ? '1' : '0';
        document.getElementById('catActiveGroup').style.display = 'block';
        document.getElementById('categoryModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.remove('active');
    }
</script>
