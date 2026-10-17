<?php $pageTitle = e($profile['full_name']); ?>
<div style="max-width:900px;margin:0 auto;">
    <div class="d-flex justify-content-between mb-3">
        <a href="<?= url('teacher/students') ?>" class="btn btn-secondary btn-sm">← กลับ</a>
        <a href="<?= url("teacher/student/{$profile['id']}/export-ai") ?>" class="btn btn-dark btn-sm" style="border-radius:10px; font-weight:600;" title="นำออกข้อมูลเพื่อใช้วิเคราะห์ผลใน AI (Prompt Data)">
            🤖 Export Markdown (AI)
        </a>
    </div>
    <div class="card mb-3">
        <div
            style="height:120px;background:linear-gradient(135deg,#4338ca,#818cf8);border-radius:var(--radius-lg) var(--radius-lg) 0 0;">
        </div>
        <div class="card-body" style="position:relative;padding-top:50px;">
            <img src="<?= $profile['avatar_path'] ? uploadUrl($profile['avatar_path']) : defaultAvatar() ?>"
                style="width:80px;height:80px;border-radius:50%;border:3px solid white;position:absolute;top:-40px;left:24px;object-fit:cover;">
            <h2>
                <?= e($profile['full_name']) ?>
            </h2>
            <p class="text-muted">
                <?= classYearLabel($profile['class_year']) ?>/
                <?= e($profile['class_room']) ?> — รหัส
                <?= e($profile['student_code']) ?> — GPA: <strong>
                    <?= number_format($gpa, 2) ?>
                </strong>
            </p>
            <?php if ($profile['bio']): ?>
                <p style="margin-top:10px;color:var(--gray-600);font-size:0.9rem;">
                    <?= nl2br(e($profile['bio'])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <h3 class="mb-2">📁 ผลงาน (
        <?= count($works) ?>)
    </h3>
    <?php if (empty($works)): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>ยังไม่มีผลงาน</p>
            </div>
        </div>
    <?php else: ?>
        <div class="portfolio-grid">
            <?php foreach ($works as $w): ?>
                <div class="portfolio-card fade-in">
                    <div class="portfolio-card-image"><span class="placeholder-icon">
                            <?= e($w['category_icon'] ?? '📁') ?>
                        </span></div>
                    <div class="portfolio-card-body">
                        <span class="portfolio-card-category">
                            <?= e($w['category_icon'] . ' ' . $w['category_name']) ?>
                        </span>
                        <h3 class="portfolio-card-title"><a href="<?= url('portfolio/' . $w['id']) ?>">
                                <?= e($w['title']) ?>
                            </a></h3>
                        <div class="portfolio-card-meta"><span>📅
                                <?= timeAgo($w['created_at']) ?>
                            </span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>