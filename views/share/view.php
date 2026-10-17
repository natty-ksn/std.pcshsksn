<?php
/**
 * Shared Portfolio View (Public)
 * หน้าแสดงผลงานแบบสาธารณะสำหรับบุคคลภายนอก
 */
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Portfolio |
        <?= e($share['full_name']) ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&family=Inter:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .share-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .share-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, #f8fafc, transparent);
        }

        .profile-section {
            max-width: 1000px;
            margin: -60px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 32px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                text-align: center;
                padding: 30px;
            }
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: var(--shadow-lg);
        }

        .portfolio-grid-shared {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            max-width: 1000px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .floating-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>

    <div class="share-header">
        <div class="floating-badge">🔒 ลิงก์เข้าถึงเฉพาะกิจ</div>
        <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 800;">E-PORTFOLIO</h1>
        <p style="opacity: 0.8; font-weight: 500; letter-spacing: 2px;">PRINCESS CHULABHORN SCIENCE HIGH SCHOOL KALASIN
        </p>
    </div>

    <div class="profile-section">
        <div class="profile-card fade-in">
            <img src="<?= $share['avatar_path'] ? uploadUrl($share['avatar_path']) : defaultAvatar() ?>"
                class="profile-avatar">
            <div style="flex: 1;">
                <h2 style="font-size: 2rem; margin-bottom: 8px;">
                    <?= e($share['full_name']) ?>
                </h2>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
                    <span class="badge badge-school">ม.
                        <?= e($share['class_year']) ?>/
                        <?= e($share['class_room']) ?>
                    </span>
                    <span class="badge badge-province">รหัสนักเรียน
                        <?= e($share['student_code']) ?>
                    </span>
                </div>
                <p style="color: var(--gray-600); line-height: 1.7; font-size: 1.05rem;">
                    <?= nl2br(e($share['bio'] ?: 'นักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์ ที่มุ่งมั่นพัฒนาตนเองในด้านวิทยาศาสตร์และเทคโนโลยี')) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="portfolio-grid-shared">
        <?php foreach ($works as $work): ?>
            <div class="portfolio-card fade-in">
                <div class="portfolio-card-image">
                    <?php if ($work['file_path'] && in_array(pathinfo($work['file_path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
                        <img src="<?= uploadUrl('portfolio/' . $work['file_path']) ?>" alt="<?= e($work['title']) ?>"
                            style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <span class="placeholder-icon">
                            <?= e($work['category_icon']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="portfolio-card-body">
                    <span class="portfolio-card-category"
                        style="background: <?= e($work['category_color']) ?>15; color: <?= e($work['category_color']) ?>;">
                        <?= e($work['category_icon']) ?>
                        <?= e($work['category_name']) ?>
                    </span>
                    <h3 class="portfolio-card-title">
                        <?= e($work['title']) ?>
                    </h3>
                    <?php if ($work['award']): ?>
                        <div style="color: var(--accent-600); font-weight: 600; font-size: 0.8rem; margin-bottom: 8px;">🏆
                            <?= e($work['award']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="portfolio-card-meta">
                        <span>📅
                            <?= thaiDate($work['event_date'], 'short') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <footer style="text-align: center; padding: 40px; color: var(--gray-400); font-size: 0.9rem;">
        <p>©
            <?= date('Y') ?> PCCKS e-Portfolio System
        </p>
        <p>สร้างโดยนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</p>
    </footer>

</body>

</html>