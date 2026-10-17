<?php $pageTitle = 'ผลงานทั้งหมด'; ?>

<!-- Magazine Header -->
<div class="animate-fade-in mb-5" style="border-bottom: 2px solid var(--gray-900); padding-bottom: 24px;">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-4">
        <div>
            <h1
                style="font-size: 3.5rem; font-weight: 900; line-height: 0.9; text-transform: uppercase; letter-spacing: -2px; margin-bottom: 15px;">
                The <span style="color: var(--primary-600);">Portfolio</span><br>Selection.
            </h1>
            <p style="font-size: 1.1rem; color: var(--gray-600); max-width: 500px; font-weight: 500;">
                <span style="font-weight: 800; color: var(--gray-900);">PCSHS KSN Edition</span> —
                การรวบรวมสุดยอดผลงานวิชาการและกิจกรรมของนักเรียนผู้มีความสามารถพิเศษ รวมทั้งหมด <?= $total ?> ผลงาน
            </p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="<?= url('portfolio/export-pdf') ?>" class="btn btn-lg"
                style="border-radius: var(--radius-full); padding: 15px 32px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 2px solid var(--primary-600); color: var(--primary-600); background: transparent; text-decoration: none; display: inline-block; transition: all 0.3s;"
                onmouseover="this.style.background='var(--primary-600)';this.style.color='white'"
                onmouseout="this.style.background='transparent';this.style.color='var(--primary-600)'"
                title="ดาวน์โหลดแฟ้มสะสมงานรวมเล่ม PDF (Sarabun Font)">
                📄 พิมพ์ PDF รวมเล่ม
            </a>
            <a href="<?= url('portfolio/create') ?>" class="btn btn-primary btn-lg animate-pulse"
                style="border-radius: var(--radius-full); padding: 15px 40px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                ➕ Create New Piece
            </a>
        </div>
    </div>
</div>

<!-- Filter Bar (Modern Nav) -->
<div class="animate-fade-in" style="animation-delay: 0.1s; margin-bottom: 40px;">
    <form method="GET" action="<?= url('portfolio') ?>" class="glass-card p-3"
        style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; border-radius: var(--radius-full); padding: 10px 30px !important;">
        <div style="flex: 1; min-width: 250px; position: relative;">
            <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.5;">🔍</span>
            <input type="text" name="search" class="form-control" placeholder="Search our collection..."
                value="<?= e($search) ?>"
                style="border-radius: var(--radius-full); padding-left: 45px; border: none; background: rgba(0,0,0,0.03);">
        </div>
        <div style="min-width: 220px;">
            <select name="category" class="form-control" onchange="this.form.submit()"
                style="border-radius: var(--radius-full); border: none; background: rgba(0,0,0,0.03); font-weight: 600;">
                <option value="">📂 Explore All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"
            style="border-radius: var(--radius-full); padding: 10px 30px;">Filter</button>
        <?php if ($search || $category): ?>
            <a href="<?= url('portfolio') ?>" class="btn btn-secondary" style="border-radius: var(--radius-full);">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Portfolio Grid -->
<?php if (empty($works)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>ยังไม่มีผลงาน</h3>
            <p>เริ่มสร้างผลงานชิ้นแรกของคุณเลย!</p>
            <a href="<?= url('portfolio/create') ?>" class="btn btn-primary">➕ เพิ่มผลงานใหม่</a>
        </div>
    </div>
<?php else: ?>
    <!-- Portfolio Grid (Magazine Style) -->
    <div class="portfolio-grid animate-fade-in"
        style="animation-delay: 0.2s; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px;">
        <?php foreach ($works as $i => $work): ?>
            <div class="portfolio-card glass-card"
                style="border: none; overflow: hidden; height: 100%; display: flex; flex-direction: column; transition: all 0.5s ease; animation-delay: <?= 0.1 * ($i % 5) ?>s;">

                <div class="portfolio-card-image" style="height: 240px; position: relative; overflow: hidden;">
                    <?php if ($work['is_featured']): ?>
                        <div
                            style="position: absolute; top: 15px; left: 15px; z-index: 10; background: var(--rank-gold); color: #92400e; padding: 4px 15px; border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                            EDITOR'S CHOICE
                        </div>
                    <?php endif; ?>

                    <?php if ($work['thumbnail']): ?>
                        <img src="<?= uploadUrl(str_starts_with($work['thumbnail'], 'portfolio/') ? $work['thumbnail'] : 'portfolio/' . $work['thumbnail']) ?>" alt="<?= e($work['title']) ?>"
                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease;"
                            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <?php elseif ($work['file_path'] && in_array(pathinfo($work['file_path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?= uploadUrl(str_starts_with($work['file_path'], 'portfolio/') ? $work['file_path'] : 'portfolio/' . $work['file_path']) ?>" alt="<?= e($work['title']) ?>"
                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease;"
                            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <?php else: ?>
                        <div
                            style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-100), var(--primary-50)); display: flex; align-items: center; justify-content: center;">
                            <span
                                style="font-size: 4rem; opacity: 0.3; transform: rotate(-10deg);"><?= e($work['category_icon'] ?? '📁') ?></span>
                        </div>
                    <?php endif; ?>

                    <div
                        style="position: absolute; bottom: 0; left: 0; right: 0; height: 50%; background: linear-gradient(transparent, rgba(0,0,0,0.6));">
                    </div>
                    <div
                        style="position: absolute; bottom: 15px; right: 15px; color: white; display: flex; align-items: center; gap: 8px; font-size: 0.8rem; font-weight: 600;">
                        <span>👁️ <?= $work['view_count'] ?></span>
                        <span style="opacity: 0.7;">|</span>
                        <span><?= $work['event_date'] ? date('Y', strtotime($work['event_date'])) : '' ?></span>
                    </div>
                </div>

                <div class="portfolio-card-body" style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column;">
                    <div style="margin-bottom: 20px;">
                        <span
                            style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: <?= e($work['category_color'] ?? 'var(--primary-600)') ?>;">
                            <?= e($work['category_name'] ?? 'General') ?>
                        </span>
                        <h3
                            style="font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin: 10px 0 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3.4rem;">
                            <a href="<?= url('portfolio/' . $work['id']) ?>"
                                style="color: var(--gray-900); text-decoration: none; transition: color 0.3s;">
                                <?= e($work['title']) ?>
                            </a>
                        </h3>

                        <?php if ($work['achievement_level']): ?>
                            <div style="margin-bottom: 12px; transform: scale(0.95); transform-origin: left;">
                                <?= achievementBadge($work['achievement_level']) ?>
                            </div>
                        <?php endif; ?>

                        <p
                            style="font-size: 0.9rem; color: var(--gray-600); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0;">
                            <?= e(truncate(strip_tags($work['description']), 120)) ?>
                        </p>
                    </div>

                    <div
                        style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--gray-100); display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="font-weight: 700; font-size: 0.8rem; color: var(--gray-900);">
                                <?= e($work['full_name'] ?? 'Artist') ?>
                                <small
                                    style="display: block; font-weight: 400; color: var(--gray-400);"><?= isset($work['class_year']) ? classYearLabel($work['class_year']) : 'N/A' ?>/<?= e($work['class_room'] ?? '-') ?></small>
                            </div>
                        </div>
                        <a href="<?= url('portfolio/' . $work['id']) ?>"
                            style="font-weight: 800; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-600); text-decoration: none;">Read
                            Story →</a>
                    </div>
                    <?php if ($profile && $profile['id'] == $work['student_id']): ?>
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--gray-200); display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= url('portfolio/' . $work['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" style="font-size: 0.75rem; padding: 4px 12px; border-radius: 8px; border: 1px solid var(--gray-300);">✏️ แก้ไข</a>
                        <button type="button" onclick="confirmDeletePortfolio(<?= $work['id'] ?>, '<?= e(addslashes($work['title'])) ?>')" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; padding: 4px 12px; border-radius: 8px; border: 1px solid #fecaca; background: #fee2e2; color: #991b1b;">🗑️ ลบ</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a
                    href="<?= url('portfolio?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($category ? '&category=' . $category : '')) ?>">←
                    ก่อนหน้า</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a
                        href="<?= url('portfolio?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($category ? '&category=' . $category : '')) ?>">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a
                    href="<?= url('portfolio?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($category ? '&category=' . $category : '')) ?>">ถัดไป
                    →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Hidden Delete Form -->
<form id="delete-portfolio-form" method="POST" action="" style="display: none;">
    <?= CSRF::field() ?>
</form>

<script>
function confirmDeletePortfolio(id, title) {
    if (confirm('คุณต้องการลบผลงาน "' + title + '" หรือไม่? การดำเนินการนี้จะย้ายผลงานไปยังกล่องเก็บถาวร')) {
        const form = document.getElementById('delete-portfolio-form');
        form.action = '<?= url("portfolio") ?>/' + id + '/delete';
        form.submit();
    }
}
</script>