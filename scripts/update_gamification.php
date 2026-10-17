<?php
include 'core/Env.php';
$host = 'localhost';
$name = 'pccks_eportfolio';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    // Check and add columns
    $columns = [
        'exp' => 'INT DEFAULT 0',
        'level' => 'INT DEFAULT 1',
        'points' => 'INT DEFAULT 0'
    ];
    
    foreach ($columns as $col => $def) {
        $check = $pdo->query("SHOW COLUMNS FROM users LIKE '$col'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $def");
            echo "Column $col added.\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }
    
    // Seed some test data for the current user if needed
    echo "SUCCESS: Gamification table updated.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
