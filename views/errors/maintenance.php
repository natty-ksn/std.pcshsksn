<?php
/**
 * Maintenance Mode Page
 */
$maintenanceMessage = $message ?? 'ระบบอยู่ระหว่างปรับปรุง กรุณาลองใหม่อีกครั้งในภายหลัง';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบปรับปรุง — PCSHSKSN e-Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            color: #fff;
            overflow: hidden;
        }
        .container {
            text-align: center;
            padding: 60px 40px;
            max-width: 550px;
        }
        .gear-icon {
            font-size: 5em;
            animation: spin 4s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        p {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .progress-bar {
            width: 200px;
            height: 6px;
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
            margin: 0 auto 30px;
            overflow: hidden;
        }
        .progress-fill {
            width: 60%;
            height: 100%;
            background: #fff;
            border-radius: 3px;
            animation: progress 2s ease-in-out infinite;
        }
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        .refresh-btn {
            display: inline-block;
            padding: 14px 36px;
            background: #fff;
            color: #6366f1;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05em;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .refresh-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .footer {
            margin-top: 50px;
            font-size: 0.85em;
            opacity: 0.6;
        }
        .floating {
            position: fixed;
            font-size: 2em;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .f1 { top: 10%; left: 10%; animation-delay: 0s; }
        .f2 { top: 20%; right: 15%; animation-delay: 1s; }
        .f3 { bottom: 20%; left: 20%; animation-delay: 2s; }
        .f4 { bottom: 10%; right: 10%; animation-delay: 3s; }
    </style>
</head>
<body>
    <!-- Floating decorations -->
    <div class="floating f1">🔧</div>
    <div class="floating f2">⚙️</div>
    <div class="floating f3">🛠️</div>
    <div class="floating f4">📋</div>

    <div class="container">
        <div class="gear-icon">⚙️</div>
        <h1>ระบบอยู่ระหว่างปรับปรุง</h1>
        <p><?= e($maintenanceMessage) ?></p>
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <a href="javascript:location.reload()" class="refresh-btn">🔄 ลองอีกครั้ง</a>
        <div class="footer">
            PCSHSKSN e-Portfolio — โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์<br>
            © 2026 PCSHSKSN
        </div>
    </div>
</body>
</html>
