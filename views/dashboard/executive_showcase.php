<?php $pageTitle = 'Executive Showcase — หอเกียรติยศดิจิทัล'; ?>

<style>
    .showcase-hero {
        background: radial-gradient(ellipse at top, #1e293b 0%, #020617 70%);
        color: #fff;
        padding: 60px 50px;
        border-radius: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(2, 6, 23, 0.4);
        margin-bottom: 40px;
    }

    .showcase-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.25), transparent 40%),
            radial-gradient(circle at 80% 80%, rgba(234, 179, 8, 0.18), transparent 45%);
        pointer-events: none;
    }

    .showcase-eyebrow {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #fbbf24;
    }

    .showcase-title {
        font-size: 3.2rem;
        font-weight: 900;
        line-height: 1.05;
        letter-spacing: -1.5px;
        color: #fff;
        margin: 12px 0 16px;
    }

    .showcase-sub {
        color: #cbd5e1;
        font-size: 1.05rem;
        max-width: 720px;
        line-height: 1.7;
    }

    .stat-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 36px;
        position: relative;
        z-index: 2;
    }

    .stat-cell {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 22px;
        backdrop-filter: blur(10px);
    }

    .stat-cell .num {
        font-size: 2.4rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
    }

    .stat-cell .lbl {
        font-size: 0.7rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-top: 6px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 28px;
    }

    .gallery-card {
        background: #fff;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        transition: transform 0.35s cubic-bezier(0.2, 0.9, 0.3, 1.2), box-shadow 0.35s;
        border: 1px solid rgba(15, 23, 42, 0.04);
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }

    .gallery-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.18);
    }

    .gallery-cover {
        height: 220px;
        overflow: hidden;
        position: relative;
        background: linear-gradient(135deg, #1e293b, #475569);
    }

    .gallery-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s;
    }

    .gallery-card:hover .gallery-cover img {
        transform: scale(1.08);
    }

    .gallery-cover .glow {
        position: absolute;
        inset: 0;
        background: linear-gradient(transparent 40%, rgba(0, 0, 0, 0.85));
    }

    .gallery-cover .ribbon {
        position: absolute;
        top: 16px;
        left: 16px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 6px 12px;
        border-radius: 50px;
        color: #1e293b;
    }

    .gallery-cover .featured-mark {
        position: absolute;
        top: 16px;
        right: 16px;
        background: linear-gradient(135deg, #f59e0b, #b45309);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 50px;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
    }

    .gallery-meta {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .gallery-title {
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1.3;
        color: #0f172a;
        margin-bottom: 10px;
        letter-spacing: -0.3px;
    }

    .gallery-author {
        font-size: 0.85rem;
        color: #64748b;
    }

    .gallery-author strong {
        color: #1e293b;
    }

    .gallery-tags {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed rgba(15, 23, 42, 0.08);
    }

    .gallery-tag {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 50px;
    }

    .level-flag {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .level-international {
        background: #fef3c7;
        color: #92400e;
    }

    .level-national {
        background: #fee2e2;
        color: #991b1b;
    }

    .level-province {
        background: #dbeafe;
        color: #1e40af;
    }

    .level-district {
        background: #dcfce7;
        color: #166534;
    }

    .level-school {
        background: #f3f4f6;
        color: #475569;
    }

    .filter-bar {
        background: #fff;
        border-radius: 18px;
        padding: 22px 28px;
        margin-bottom: 28px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: end;
    }

    @media (max-width: 768px) {
        .stat-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .showcase-title {
            font-size: 2.2rem;
        }

        .showcase-hero {
            padding: 40px 28px;
        }
    }
</style>

<div class="animate-fade-in" style="max-width: 1320px; margin: 0 auto;">

    <!-- Premium Hero -->
    <div class="showcase-hero">
        <div style="position: relative; z-index: 2;">
            <div class="showcase-eyebrow">★ ทำเนียบผลงานนักเรียนดีเด่น</div>
            <h1 class="showcase-title">หอเกียรติยศผลงานนักเรียน<br>PCSHS KSN</h1>
            <p class="showcase-sub">
                รวบรวมผลงานและโครงงานของนักเรียนที่ผ่านการประเมินและรับรองคุณภาพ
                เพื่อการนำเสนอเชิงประจักษ์ต่อคณะผู้บริหารและผู้ทรงคุณวุฒิ
                สะท้อนศักยภาพของนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
                <a href="<?= url('admin/reports') ?>"
                    style="background: rgba(255,255,255,0.15); color: white; padding: 10px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.3); transition: all 0.3s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'"
                    title="รายงานและสถิติการศึกษา Executive KPI">
                    📊 รายงานสถิติการศึกษา (KPI)
                </a>
                <a href="<?= url('analytics/dashboard/export') ?>"
                    style="background: rgba(255,255,255,0.15); color: white; padding: 10px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.3); transition: all 0.3s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'" title="ดาวน์โหลดรายงานทางการ PDF">
                    📄 ดาวน์โหลดรายงาน PDF
                </a>
            </div>

            <div class="stat-row">
                <div class="stat-cell">
                    <div class="num"><?= number_format((int) $stats['verified_count']) ?></div>
                    <div class="lbl">ผลงานรับรองแล้ว</div>
                </div>
                <div class="stat-cell">
                    <div class="num"><?= number_format((int) $stats['featured_count']) ?></div>
                    <div class="lbl">ผลงานระดับดีเด่น</div>
                </div>
                <div class="stat-cell">
                    <div class="num"><?= number_format((int) $stats['student_count']) ?></div>
                    <div class="lbl">นักเรียนเจ้าของผลงาน</div>
                </div>
                <div class="stat-cell">
                    <div class="num"><?= e((string) $stats['avg_score']) ?></div>
                    <div class="lbl">คะแนนเฉลี่ย / 100</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="form-group mb-0" style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-weight: 700; color: #1e293b;">หมวดหมู่ผลงาน</label>
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">ทั้งหมด</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0" style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-weight: 700; color: #1e293b;">ระดับความสำเร็จ</label>
            <select name="level" class="form-control" onchange="this.form.submit()">
                <option value="">ทุกระดับ</option>
                <option value="international" <?= $level === 'international' ? 'selected' : '' ?>>นานาชาติ</option>
                <option value="national" <?= $level === 'national' ? 'selected' : '' ?>>ระดับชาติ</option>
                <option value="province" <?= $level === 'province' ? 'selected' : '' ?>>ระดับจังหวัด</option>
                <option value="district" <?= $level === 'district' ? 'selected' : '' ?>>ระดับเขตพื้นที่</option>
                <option value="school" <?= $level === 'school' ? 'selected' : '' ?>>ระดับโรงเรียน</option>
            </select>
        </div>
        <a href="<?= url('executive/showcase') ?>" class="btn btn-secondary">ล้างค่า</a>
    </form>

    <!-- Gallery -->
    <?php if (empty($works)): ?>
        <div class="text-center py-5" style="background: #fff; border-radius: 18px; padding: 80px;">
            <div style="font-size: 4rem;">🏛️</div>
            <h3 class="mt-3">ยังไม่มีรายการผลงานดีเด่นในระบบ</h3>
            <p class="text-muted">ผลงานจะปรากฏในส่วนนี้เมื่อผ่านการตรวจรับรองจากครูที่ปรึกษาเรียบร้อยแล้ว</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($works as $w): ?>
                <a href="<?= url('portfolio/' . $w['id']) ?>" target="_blank" class="gallery-card"
                    style="text-decoration: none; color: inherit;">
                    <div class="gallery-cover">
                        <?php if (!empty($w['thumbnail'])): ?>
                            <img src="<?= uploadUrl('portfolio/' . $w['thumbnail']) ?>" alt="<?= e($w['title']) ?>">
                        <?php elseif (!empty($w['file_path'])): ?>
                            <?php $ext = strtolower(pathinfo($w['file_path'], PATHINFO_EXTENSION)); ?>
                            <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                <img src="<?= uploadUrl('portfolio/' . $w['file_path']) ?>" alt="<?= e($w['title']) ?>">
                            <?php else: ?>
                                <div
                                    style="display:flex;align-items:center;justify-content:center;height:100%;font-size:5rem;color:rgba(255,255,255,0.4);">
                                    📄</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div
                                style="display:flex;align-items:center;justify-content:center;height:100%;font-size:5rem;color:rgba(255,255,255,0.4);">
                                <?= e($w['category_icon'] ?? '🏆') ?></div>
                        <?php endif; ?>
                        <div class="glow"></div>
                        <?php if (!empty($w['category_name'])): ?>
                            <div class="ribbon"><?= e($w['category_icon'] ?? '') ?>             <?= e($w['category_name']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($w['is_featured'])): ?>
                            <div class="featured-mark">★ Featured</div>
                        <?php endif; ?>
                    </div>
                    <div class="gallery-meta">
                        <div class="gallery-title"><?= e($w['title']) ?></div>
                        <div class="gallery-author">
                            โดย <strong><?= e($w['full_name']) ?></strong> ·
                            ม.<?= (int) $w['class_year'] ?>/<?= e($w['class_room']) ?>
                        </div>
                        <?php if (!empty($w['award'])): ?>
                            <div style="margin-top: 10px; font-size: 0.85rem; color: #b45309; font-weight: 700;">
                                🏆 <?= e($w['award']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="gallery-tags">
                            <?php if (!empty($w['achievement_level'])): ?>
                                <span class="level-flag level-<?= e($w['achievement_level']) ?>">
                                    <?= e($w['achievement_level']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($w['points_awarded'])): ?>
                                <span class="gallery-tag">⭐ <?= (int) $w['points_awarded'] ?>/100</span>
                            <?php endif; ?>
                            <span class="gallery-tag">👁️ <?= (int) ($w['view_count'] ?? 0) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>