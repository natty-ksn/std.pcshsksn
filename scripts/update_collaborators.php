<?php
include 'core/Env.php';
$host = 'localhost';
$name = 'pccks_eportfolio';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    // 1. Create portfolio_collaborators table
    $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_collaborators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        portfolio_item_id INT NOT NULL,
        student_id INT NOT NULL, -- user_id of the collaborator student
        role_in_project VARCHAR(100) DEFAULT 'Teammate',
        acceptance_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        responded_at TIMESTAMP NULL,
        FOREIGN KEY (portfolio_item_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "SUCCESS: portfolio_collaborators table created.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
