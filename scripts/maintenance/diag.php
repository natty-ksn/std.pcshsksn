<?php
/**
 * PCSHSKSN e-Portfolio Diagnostics Tool
 * ใช้สำหรับตรวจสอบปัญหา 500 Error บน Production
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 PCSHSKSN e-Portfolio Diagnostics</h1>";
echo "<hr>";

// 1. PHP Version Check
echo "<h3>1. PHP Version</h3>";
$phpVersion = PHP_VERSION;
$isPhp8 = version_compare($phpVersion, '8.0.0', '>=');
echo "Current Version: <strong>$phpVersion</strong> ";
echo $isPhp8 ? "<span style='color:green;'>✅ (OK for PHP 8 features)</span>" : "<span style='color:red;'>⚠️ (Warning: Higher risk of failure with PHP 8 code)</span>";

// 2. Directory Permissions
echo "<h3>2. Directory Permissions</h3>";
$dirs = [
    '../logs' => 'Logs Directory',
    '../tmp' => 'Temp Directory',
    '../tmp/sessions' => 'Session Storage',
    '../uploads' => 'Uploads Directory'
];

foreach ($dirs as $path => $label) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
    $isWritable = is_writable($path);
    echo "$label ($path): " . ($isWritable ? "<span style='color:green;'>✅ Writable</span>" : "<span style='color:red;'>❌ NOT Writable</span>") . "<br>";
}

// 3. Database Connectivity
echo "<h3>3. Database Connectivity Check</h3>";
if (file_exists('../.env') || file_exists('../.env.production')) {
    $envFile = file_exists('../.env') ? '../.env' : '../.env.production';
    $envContent = file_get_contents($envFile);
    
    // Simple parser
    $db_host = $db_name = $db_user = $db_pass = '';
    if (preg_match('/DB_HOST=(.*)/', $envContent, $m)) $db_host = trim($m[1], "\"' ");
    if (preg_match('/DB_NAME=(.*)/', $envContent, $m)) $db_name = trim($m[1], "\"' ");
    if (preg_match('/DB_USER=(.*)/', $envContent, $m)) $db_user = trim($m[1], "\"' ");
    if (preg_match('/DB_PASS=(.*)/', $envContent, $m)) $db_pass = trim($m[1], "\"' ");

    echo "Attempting connection to: <strong>$db_name</strong> on <strong>$db_host</strong>...<br>";

    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<span style='color:green;'>✅ Database Connected Successfully!</span>";
    } catch (Exception $e) {
        echo "<span style='color:red;'>❌ Database Connection Failed:</span> " . $e->getMessage();
    }
} else {
    echo "<span style='color:orange;'>⚠️ No .env file found to check database.</span>";
}

// 4. Critical Extensions
echo "<h3>4. Required Extensions</h3>";
$exts = ['pdo_mysql', 'mbstring', 'openssl', 'curl', 'gd', 'json'];
foreach ($exts as $ext) {
    echo "Extension '$ext': " . (extension_loaded($ext) ? "<span style='color:green;'>✅ Loaded</span>" : "<span style='color:red;'>❌ NOT Loaded</span>") . "<br>";
}

echo "<hr>";
echo "<p><em>หากทุกอย่างขึ้นสีเขียว แต่ยัง 500 Error แสดงว่ามีปัญหา Syntax ในไฟล์ Framework คครับ</em></p>";
?>
