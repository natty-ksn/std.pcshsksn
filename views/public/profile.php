<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Portfolio | <?= e($profile['full_name'] ?? 'Student Profile') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= App::asset('css/style.css') ?>">
    <!-- Inline styles specific for public showcase and printing -->
    <style>
        :root {
            --print-font: 'Sarabun', 'Noto Sans Thai', sans-serif;
            --page-bg: #f8fafc;
            --text-dark: #1e293b;
            --primary: #4f46e5;
        }
        body {
            background-color: var(--page-bg);
            color: var(--text-dark);
            font-family: var(--print-font);
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        /* Mobile-first adjustments */
        @media (max-width: 768px) {
            .container { padding: 10px; }
        }

        /* --- PDF Print Layout --- */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body { 
                margin: 10mm; 
                background: white; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important;
            }
            .container { max-width: 100%; border: none; box-shadow: none; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            
            /* Clean up UI elements for paper */
            .card { box-shadow: none !important; border: 1px solid #e2e8f0; margin-bottom: 15px; page-break-inside: avoid; }
            img { max-width: 100% !important; height: auto; }
            .badge-item { border: 1px solid #cbd5e1; background: white !important; }
        }
        
        .print-only { display: none; }
        
        /* Floating Action Buttons */
        .fab-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }
        .fab-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: transform 0.2s;
        }
        .fab-btn:hover { transform: scale(1.05); }

        /* Showcase Header */
        .showcase-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border-radius: 16px;
            padding: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .showcase-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            background: #fff;
            position: relative;
            z-index: 2;
        }
        .showcase-info {
            flex: 1;
            z-index: 2;
        }
        .showcase-title { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .showcase-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; }
        .print-header { text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Portfolio Grid */
        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .showcase-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
        }
        .showcase-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.15); }
        .showcase-img { width: 100%; height: 180px; object-fit: cover; }
        .showcase-content { padding: 20px; flex: 1; display: flex; flex-direction: column; }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Print-only Title -->
    <div class="print-only print-header">
        <img src="<?= App::asset('images/logo.png') ?>" alt="School Logo" style="height: 70px; margin-bottom: 10px; object-fit: contain;">
        <h2 style="font-size: 24px; font-weight: bold; margin: 0; color: #1e3a8a;">แฟ้มสะสมผลงานนักเรียน (e-Portfolio)</h2>
        <p style="margin: 5px 0 0; color: #64748b;">โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</p>
    </div>

    <!-- Header Section -->
    <div class="showcase-header">
        <div style="position: absolute; right: -20px; top: -50px; font-size: 15rem; opacity: 0.1; transform: rotate(-15deg); z-index: 1;">🎓</div>
        
        <?php $avatar = !empty($profile['avatar_path']) ? uploadUrl($profile['avatar_path']) : defaultAvatar($profile['email'] ?? '', $profile['full_name'] ?? ''); ?>
        <img src="<?= $avatar ?>" alt="Avatar" class="showcase-avatar">
        
        <div class="showcase-info">
            <div class="showcase-title"><?= e($profile['full_name']) ?></div>
            <div style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 8px;">
                รหัสนักเรียน: <?= e($profile['student_code']) ?> | ม.<?= e($profile['class_year']) ?>/<?= e($profile['class_room']) ?>
            </div>
            
            <div class="showcase-badges">
                <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 50px; font-size: 0.85rem;">
                    📈 GPA: <?= e($gpa) ?>
                </span>
                <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 50px; font-size: 0.85rem;">
                    🏆 ผลงานเด่น: <?= count($featuredWorks) ?> ชิ้น
                </span>
            </div>
            
            <?php if (!empty($profile['bio'])): ?>
                <p style="margin-top: 16px; font-size: 0.9rem; opacity: 0.8; max-width: 600px; line-height: 1.5; border-left: 3px solid rgba(255,255,255,0.5); padding-left: 10px;">
                    "<?= nl2br(e($profile['bio'])) ?>"
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section Title -->
    <h3 style="margin-bottom: 20px; font-size: 1.5rem; color: #1e293b; border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 5px;">
        ⭐ ผลงานและกิจกรรมเด่น
    </h3>

    <!-- Portfolio Grid -->
    <div class="showcase-grid">
        <?php if (empty($featuredWorks)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
                ยังไม่มีการบันทึกผลงานเด่น
            </div>
        <?php else: ?>
            <?php foreach ($featuredWorks as $work): ?>
                <div class="showcase-card">
                    <?php if (!empty($work['file_path']) && in_array(strtolower(pathinfo($work['file_path'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <?php 
                        $relPath = str_starts_with($work['file_path'], 'portfolio/') ? $work['file_path'] : 'portfolio/' . $work['file_path'];
                        $imagePath = uploadUrl($relPath); 
                        if (!empty($work['thumbnail'])) {
                            $thumbRel = str_starts_with($work['thumbnail'], 'portfolio/') ? $work['thumbnail'] : 'portfolio/' . $work['thumbnail'];
                            if (file_exists(UPLOAD_PATH . '/' . $thumbRel)) {
                                $imagePath = uploadUrl($thumbRel);
                            }
                        }
                        ?>
                        <div style="background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <img src="<?= $imagePath ?>" class="showcase-img" alt="<?= e($work['title']) ?>">
                        </div>
                    <?php else: ?>
                        <div class="showcase-img" style="background: linear-gradient(135deg, var(--blue-100), var(--primary-100)); display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                            <?= e($work['category_icon'] ?? '📁') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="showcase-content">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <span style="font-size: 0.75rem; background: var(--primary-50); color: var(--primary-700); padding: 4px 8px; border-radius: 4px; font-weight: 600;">
                                <?= e($work['category_name']) ?>
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b;">
                                <?= !empty($work['event_date']) ? date('d M Y', strtotime($work['event_date'])) : '' ?>
                            </span>
                        </div>
                        <h4 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #1e293b; line-height: 1.4;">
                            <?= e($work['title']) ?>
                        </h4>
                        
                        <?php if(!empty($work['award'])): ?>
                            <div style="margin-bottom: 12px; font-size: 0.85rem; color: #d97706; font-weight: 600; display:flex; align-items:center; gap:5px;">
                                <span>🏆</span> <?= e($work['award']) ?>
                            </div>
                        <?php endif; ?>

                        <p style="font-size: 0.85rem; color: #475569; line-height: 1.6; margin: 0; flex: 1;">
                            <?= e(truncate(strip_tags($work['description'] ?? ''), 120)) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Print Footer -->
    <div class="print-only text-center" style="margin-top: 40px; padding-top: 20px; border-top: 1px dashed #cbd5e1; font-size: 0.8rem; color: #94a3b8;">
        เอกสารนี้สร้างขึ้นจากระบบ E-Portfolio โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์<br>
        วันที่พิมพ์: <?= date('d/m/Y H:i A') ?>
    </div>

</div>

<!-- Floating Action Buttons for UX -->
<div class="fab-container no-print">
    <!-- QR Code Scan Button (if we want to show it on mobile) -->
    <button class="fab-btn" style="background: white; color: var(--primary); border: 1px solid var(--primary-200);" onclick="alert('แชร์ลิงก์ให้กรรมการ: <?= App::baseUrl('p/' . $profile['student_code']) ?>');">
        🔗
    </button>
    
    <!-- Print / Export to PDF Button -->
    <button class="fab-btn" onclick="window.print();" title="พิมพ์หรือบันทึกเป็น PDF">
        🖨️
    </button>
</div>

</body>
</html>
