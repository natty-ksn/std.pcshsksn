<?php $pageTitle = 'ไม่มีสิทธิ์เข้าถึง'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — ไม่มีสิทธิ์เข้าถึง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>

<body>
    <div class="auth-page">
        <div class="auth-card text-center">
            <div style="font-size: 5rem; margin-bottom: 16px;">🔒</div>
            <h1 style="font-size: 3rem; color: var(--gray-300); margin-bottom: 8px;">403</h1>
            <h2 style="margin-bottom: 12px;">ไม่มีสิทธิ์เข้าถึง</h2>
            <p style="color: var(--gray-500); margin-bottom: 24px;">คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้</p>
            <a href="<?= url('dashboard') ?>" class="btn btn-primary">🏠 กลับหน้าหลัก</a>
        </div>
    </div>
</body>

</html>