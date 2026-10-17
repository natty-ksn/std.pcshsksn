<?php
/**
 * Phase 3 Installer — ดาวน์โหลด Sarabun font จาก Google Fonts (GitHub)
 *
 * วิธีรัน: เปิด http://localhost/app/public/install_pdf_fonts.php ในเบราว์เซอร์
 * รันครั้งเดียวหลังติดตั้งระบบ ไม่ต้องรันซ้ำ
 */

set_time_limit(180);

$root = dirname(__DIR__);
$targetDir = $root . '/storage/fonts';

if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
    die('❌ ไม่สามารถสร้างโฟลเดอร์ ' . htmlspecialchars($targetDir));
}

// Source: Google Fonts official GitHub repository
$base = 'https://github.com/google/fonts/raw/main/ofl/sarabun/';
$files = [
    'Sarabun-Regular.ttf'    => 'Sarabun-Regular.ttf',
    'Sarabun-Bold.ttf'       => 'Sarabun-Bold.ttf',
    'Sarabun-Italic.ttf'     => 'Sarabun-Italic.ttf',
    'Sarabun-BoldItalic.ttf' => 'Sarabun-BoldItalic.ttf',
];

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PDF Font Installer</title>';
echo '<style>body{font-family:Sarabun,Tahoma,sans-serif;max-width:760px;margin:40px auto;padding:24px;background:#f8fafc;color:#0f172a}';
echo '.box{background:#fff;border-radius:14px;padding:28px;box-shadow:0 8px 30px rgba(15,23,42,.08)}';
echo 'h1{margin-top:0;color:#1e3a8a}.ok{color:#166534;font-weight:bold}.err{color:#991b1b;font-weight:bold}';
echo '.row{padding:8px 0;border-bottom:1px dashed #e2e8f0}.row:last-child{border:none}</style></head><body><div class="box">';
echo '<h1>📥 PCSHS KSN — PDF Font Installer</h1>';
echo '<p>ดาวน์โหลดฟอนต์ Sarabun จาก Google Fonts (GitHub) ไปยัง <code>' . htmlspecialchars($targetDir) . '</code></p><hr>';

$ok = 0; $fail = 0;

foreach ($files as $remote => $local) {
    $dest = $targetDir . '/' . $local;
    echo '<div class="row">📄 <strong>' . $local . '</strong> &mdash; ';

    if (file_exists($dest) && filesize($dest) > 50000) {
        echo '<span class="ok">มีอยู่แล้ว ข้าม</span></div>';
        $ok++;
        continue;
    }

    $url = $base . $remote . '?raw=true';
    $ctx = stream_context_create([
        'http' => [
            'follow_location' => 1,
            'max_redirects'   => 5,
            'timeout'         => 60,
            'header'          => "User-Agent: PCSHS-KSN-Installer/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $bytes = @file_get_contents($url, false, $ctx);

    if ($bytes !== false && strlen($bytes) > 50000) {
        file_put_contents($dest, $bytes);
        echo '<span class="ok">✅ ดาวน์โหลดสำเร็จ (' . number_format(strlen($bytes) / 1024) . ' KB)</span>';
        $ok++;
    } else {
        echo '<span class="err">❌ ดาวน์โหลดล้มเหลว</span>';
        $fail++;
    }
    echo '</div>';
}

echo '<hr><p>';
if ($fail === 0) {
    echo '<span class="ok">🎉 เสร็จสมบูรณ์ ทั้งหมด ' . $ok . ' ไฟล์</span><br>';
    echo 'ระบบ PDFService จะเลือกใช้ฟอนต์ Sarabun โดยอัตโนมัติเมื่อสร้างเอกสารครั้งถัดไป';
} else {
    echo '<span class="err">⚠️ ดาวน์โหลดล้มเหลว ' . $fail . ' ไฟล์</span><br>';
    echo 'กรุณาดาวน์โหลดด้วยตนเองจาก <a href="https://fonts.google.com/specimen/Sarabun" target="_blank">Google Fonts: Sarabun</a> ';
    echo 'แล้ววางไฟล์ <code>Sarabun-Regular.ttf</code>, <code>Sarabun-Bold.ttf</code>, <code>Sarabun-Italic.ttf</code>, <code>Sarabun-BoldItalic.ttf</code> ลงในโฟลเดอร์ ';
    echo '<code>storage/fonts/</code><br><br>หากดาวน์โหลดไม่สำเร็จเพราะ SSL หรือ allow_url_fopen ปิดอยู่ ให้เปิด <code>php.ini</code> แล้วเปิดใช้ <code>allow_url_fopen=On</code>';
}
echo '</p><p class="row" style="margin-top:20px;color:#64748b;font-size:13px">💡 หากระบบไม่พบฟอนต์ Sarabun ระบบจะใช้ฟอนต์ Garuda (built-in) แทนโดยอัตโนมัติ — ไม่กระทบการใช้งาน</p>';
echo '</div></body></html>';
