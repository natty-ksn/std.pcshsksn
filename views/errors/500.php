<?php
/**
 * 500 Internal Server Error
 */
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — เกิดข้อผิดพลาด | PCSHSKSN e-Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .container {
            text-align: center;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 60px 40px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            max-width: 500px;
            margin: 20px;
        }
        .error-code {
            font-size: 6em;
            font-weight: 700;
            line-height: 1;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .error-icon { font-size: 3em; margin-bottom: 10px; }
        h2 { font-size: 1.4em; font-weight: 600; margin: 10px 0; }
        p { opacity: 0.8; font-size: 1.05em; line-height: 1.6; margin-bottom: 25px; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #fff;
            color: #6366f1;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }
        .debug-info {
            margin-top: 25px;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            text-align: left;
            font-size: 0.85em;
            word-break: break-all;
        }
        .debug-info code { color: #a5f3fc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h2>เกิดข้อผิดพลาดภายในระบบ</h2>
        <p>
            ขออภัย เกิดข้อผิดพลาดที่ไม่คาดคิด<br>
            ระบบกำลังบันทึกข้อผิดพลาดนี้ กรุณาลองใหม่อีกครั้ง
        </p>
        <div class="actions">
            <a href="<?= function_exists('url') ? url('/') : '/app/' ?>" class="btn btn-primary">← กลับหน้าหลัก</a>
            <a href="javascript:location.reload()" class="btn btn-secondary">🔄 ลองอีกครั้ง</a>
        </div>
        <?php if (!empty($exception)): ?>
        <details style="margin-top: 25px; text-align: left; background: rgba(0,0,0,0.4); padding: 15px; border-radius: 8px; font-size: 0.85em; border: 1px solid rgba(255,255,255,0.2);">
            <summary style="cursor: pointer; color: #fbbf24; font-weight: 600;">🔍 ดูรายละเอียดข้อผิดพลาด (Technical Error Details)</summary>
            <div style="margin-top: 10px; font-family: monospace; word-break: break-all; color: #a5f3fc;">
                <p style="margin-bottom: 6px;"><strong style="color: #f87171;">ข้อความ:</strong> <?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
                <p style="margin-bottom: 6px;"><strong style="color: #fcd34d;">ตำแหน่ง:</strong> <?= htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') ?>:<?= $exception->getLine() ?></p>
                <p style="margin-bottom: 0;"><strong style="color: #cbd5e1;">ประเภท:</strong> <?= htmlspecialchars(get_class($exception), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </details>
        <?php endif; ?>
    </div>
</body>
</html>
