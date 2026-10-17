<?php
/**
 * PCSHS KSN: Phase 2 — Teacher Insight & Quality Control Patch
 * เพิ่มตาราง portfolio_annotations สำหรับระบบให้คอมเมนต์เฉพาะจุด (Annotated Mentorship)
 */
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/App.php';

echo "============================================\n";
echo "  PCSHS KSN: Phase 2 Patch (Annotations)\n";
echo "============================================\n\n";

try {
    $db = Database::connect();

    echo "[1/1] Creating portfolio_annotations table...\n";
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
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_portfolio (portfolio_id),
        INDEX idx_resolved (is_resolved)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  - Table 'portfolio_annotations' ready\n";

    echo "\n[SUCCESS] Phase 2 Patch applied successfully!\n";
} catch (PDOException $e) {
    echo "\n[ERROR] Patch failed: " . $e->getMessage() . "\n";
}
