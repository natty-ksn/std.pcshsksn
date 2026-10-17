<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PCCKS e-Portfolio Debug Mode</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "<p style='color:green'>✅ พบไฟล์ .env</p>";
} else {
    echo "<p style='color:red'>❌ ไม่พบไฟล์ .env</p>";
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=pccks_eportfolio", "root", "");
    echo "<p style='color:green'>✅ เชื่อมต่อ Database สำเร็จ</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ เชื่อมต่อ Database ไม่ได้: " . $e->getMessage() . "</p>";
}

session_start();
echo "<p>Session ID: " . session_id() . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background:yellow;padding:10px;'>🚀 POST Detected! Data: " . print_r($_POST, true) . "</div>";
}

echo "<h3>ทดสอบการส่งฟอร์ม (POST)</h3>";
echo "<form method='POST'><input type='text' name='test_data' value='Hello'><button type='submit'>คลิกทดสอบ POST</button></form>";

echo "<p>คลิกเพื่อไปที่: <a href='index.php'>หน้าหลัก (public/index.php)</a></p>";
