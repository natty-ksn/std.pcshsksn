<?php $pageTitle = 'ตรวจรับรองผลงานแบบกลุ่ม'; ?>

<div class="animate-fade-in" style="max-width: 1200px; margin: 0 auto;">
    <div class="mb-4">
        <h2 style="font-weight: 800; color: var(--gray-900);">👩‍🏫 ระบบตรวจรับรองผลงานแบบกลุ่ม</h2>
        <p class="text-muted">เลือกผลงานที่ต้องการตรวจรับรองหลายรายการพร้อมกัน เพื่อความรวดเร็วในการจัดการข้อมูล</p>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="grid-3 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="class_year" class="form-control" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?= $i ?>" <?= $classYear == $i ? 'selected' : '' ?>>ม.<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">หมวดหมู่</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0 d-flex align-items-end">
                    <a href="<?= url('teacher/pending-reviews') ?>" class="btn btn-secondary w-100">ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($pendingWorks)): ?>
        <div class="text-center py-5 glass-card">
            <div style="font-size: 4rem; opacity: 0.3;">🎉</div>
            <h3 class="mt-3">ไม่มีผลงานค้างตรวจ</h3>
            <p class="text-muted">ผลงานของนักเรียนในเงื่อนไขที่เลือก ได้รับการตรวจครบถ้วนแล้ว</p>
        </div>
    <?php else: ?>
        <form id="bulkForm" method="POST" action="<?= url('teacher/bulk-verify') ?>">
            <?= CSRF::field() ?>
            
            <div class="card mb-4" style="position: sticky; top: 80px; z-index: 100; border: 2px solid var(--primary-200); box-shadow: var(--shadow-lg);">
                <div class="card-body d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="selectAll" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold" for="selectAll">เลือกทั้งหมด</label>
                        </div>
                        <span id="selectedCount" class="badge" style="background: var(--primary-600); color: white; padding: 5px 12px; border-radius: 50px;">0 รายการ</span>
                    </div>
                    
                    <div class="d-flex gap-3 align-items-center">
                        <div class="form-group mb-0">
                            <input type="number" name="points" class="form-control form-control-sm" value="80" min="0" max="100" style="width: 100px;" title="คะแนนที่จะให้">
                        </div>
                        <select name="bulk_action" class="form-control form-control-sm" style="width: 160px;">
                            <option value="verify">✅ รับรองผลงาน</option>
                            <option value="reject">❌ ส่งกลับเพื่อแก้ไข</option>
                        </select>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('ยืนยันการบันทึกผลการตรวจรับรองผลงานตามรายการที่เลือก?')">
                            ✅ บันทึกผลการตรวจกลุ่ม
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid-1 gap-3">
                <?php foreach($pendingWorks as $work): ?>
                    <div class="glass-card p-3 work-item" style="transition: var(--transition);">
                        <div class="d-flex gap-3 align-items-start">
                            <div style="padding-top: 10px;">
                                <input type="checkbox" name="portfolio_ids[]" value="<?= $work['id'] ?>" class="work-checkbox" style="width: 20px; height: 20px;">
                            </div>
                            <div style="width: 80px; height: 80px; border-radius: 12px; overflow: hidden; background: var(--gray-100); flex-shrink: 0;">
                                <?php if($work['thumbnail']): ?>
                                    <img src="<?= uploadUrl(str_starts_with($work['thumbnail'], 'portfolio/') ? $work['thumbnail'] : 'portfolio/' . $work['thumbnail']) ?>" style="width:100%; height:100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="display:flex; align-items:center; justify-content:center; height:100%; font-size: 2rem;"><?= e($work['category_icon']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <div class="d-flex justify-content-between">
                                    <h4 style="margin: 0; font-weight: 700;"><?= e($work['title']) ?></h4>
                                    <span style="font-size: 0.75rem; color: var(--gray-400);"><?= timeAgo($work['created_at']) ?></span>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--gray-500); margin: 5px 0;">
                                    โดย <strong><?= e($work['full_name']) ?></strong> (ม.<?= $work['class_year'] ?>/<?= $work['class_room'] ?>)
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge" style="background: var(--gray-100); color: var(--gray-600);"><?= e($work['category_name']) ?></span>
                                    <a href="<?= url('portfolio/' . $work['id']) ?>" target="_blank" style="font-size: 0.8rem; font-weight: 700;">👁️ ดูรายละเอียด</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.work-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const workItems = document.querySelectorAll('.work-item');

    function updateCount() {
        const checked = document.querySelectorAll('.work-checkbox:checked').length;
        selectedCount.textContent = `${checked} รายการ`;
        
        checkboxes.forEach((cb, index) => {
            if(cb.checked) {
                workItems[index].style.borderColor = 'var(--primary-400)';
                workItems[index].style.background = 'var(--primary-50)';
            } else {
                workItems[index].style.borderColor = 'rgba(255,255,255,0.2)';
                workItems[index].style.background = 'rgba(255,255,255,0.2)';
            }
        });
    }

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateCount();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
</script>
