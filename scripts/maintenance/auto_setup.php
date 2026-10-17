<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('LOG_PATH', ROOT_PATH . '/logs');

require_once CORE_PATH . '/Env.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/MigrationRunner.php';

Env::load(ROOT_PATH . '/.env');

echo "<div style='font-family:monospace; background:#111827; color:#10b981; padding:20px; border-radius:10px;'>";
echo "<h3>🔧 One-Click Auto Setup</h3><hr style='border-color:#374151;'><pre style='color:#fff;'>";

try {
    $db = Database::connect();
    
    echo "1. Applying Advancement Patch...\n";
    $columns = $db->query("SHOW COLUMNS FROM student_profiles")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('cefr_level', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN cefr_level ENUM('None','A1','A2','B1','B2','C1','C2') DEFAULT 'None' AFTER class_room");
    }
    if (!in_array('internship_history', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN internship_history TEXT AFTER cefr_level");
    }
    if (!in_array('activity_summary', $columns)) {
        $db->exec("ALTER TABLE student_profiles ADD COLUMN activity_summary TEXT AFTER internship_history");
    }
    $portCols = $db->query("SHOW COLUMNS FROM portfolio_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('poster_path', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN poster_path VARCHAR(255) AFTER file_path");
    }
    if (!in_array('classroom_subject', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN classroom_subject VARCHAR(150) AFTER poster_path");
    }
    if (!in_array('verification_status', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER status");
    }
    if (!in_array('points_awarded', $portCols)) {
        $db->exec("ALTER TABLE portfolio_items ADD COLUMN points_awarded DECIMAL(5,2) DEFAULT NULL AFTER verification_status");
    }

    echo "1.5 Fixing Collaborators Table...\n";
    $collabCols = $db->query("SHOW COLUMNS FROM portfolio_collaborators")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('acceptance_status', $collabCols)) {
        $db->exec("ALTER TABLE portfolio_collaborators ADD COLUMN acceptance_status ENUM('pending','accepted','rejected') DEFAULT 'pending' AFTER student_id");
    }
    if (!in_array('responded_at', $collabCols)) {
        $db->exec("ALTER TABLE portfolio_collaborators ADD COLUMN responded_at TIMESTAMP NULL AFTER acceptance_status");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS readiness_scores (
        id              INT PRIMARY KEY AUTO_INCREMENT,
        student_id      INT UNIQUE NOT NULL,
        overall_pct     TINYINT DEFAULT 0,
        academic_score  TINYINT DEFAULT 0,
        portfolio_score TINYINT DEFAULT 0,
        skill_score     TINYINT DEFAULT 0,
        analysis_data   JSON,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Populate Templates
    $db->exec("TRUNCATE TABLE goal_templates");
    $stmt = $db->prepare("INSERT INTO goal_templates (track_code, track_name, min_gpa, requirements, recommendations, radar_target) VALUES (?, ?, ?, ?, ?, ?)");
    $templates = [
        ['med', 'สายวิทยาศาตร์สุขภาพ', 3.50, '{}', '[]', '{}'],
        ['eng', 'สายวิศวกรรมศาสตร์', 3.00, '{}', '[]', '{}'],
        ['sci', 'สายวิทยาศาสต์และวิจัย', 3.25, '{}', '[]', '{}']
    ];
    foreach ($templates as $t) { $stmt->execute($t); }

    echo "2. Applying Phase 2 Patch (Annotations)...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS portfolio_annotations (
        id              INT PRIMARY KEY AUTO_INCREMENT,
        portfolio_id    INT NOT NULL,
        teacher_id      INT NOT NULL,
        target_field    VARCHAR(50) DEFAULT 'general',
        snippet         TEXT,
        severity        ENUM('info','suggestion','issue','critical') DEFAULT 'suggestion',
        comment         TEXT NOT NULL,
        is_resolved     TINYINT(1) DEFAULT 0,
        resolved_at     DATETIME NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "3. Running Standard Migrations...\n";
    MigrationRunner::up();

    echo "\n</pre><hr style='border-color:#374151;'><h3 style='color:#10b981;'>✅ SETUP COMPLETE 100%!</h3></div>";
} catch (\Throwable $e) {
    echo "\n</pre><hr style='border-color:#374151;'><h3 style='color:#ef4444;'>❌ ERROR: " . $e->getMessage() . "</h3></div>";
}
