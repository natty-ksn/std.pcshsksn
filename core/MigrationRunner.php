<?php
/**
 * Migration Runner — จัดการ database schema แบบ versioned
 * 
 * ใช้งาน:
 *   php core/MigrationRunner.php          — แสดงสถานะ migration
 *   php core/MigrationRunner.php up       — รัน migration ที่เหลือทั้งหมด
 *   php core/MigrationRunner.php up 3     — รันถึง migration ลำดับที่ 3
 *   php core/MigrationRunner.php down 2   — rollback ไป migration ลำดับที่ 2
 */
class MigrationRunner
{
    private static string $migrationTable = 'migrations';
    private static string $migrationsDir = '';

    /**
     * Initialize และสร้าง migration table ถ้ายังไม่มี
     */
    public static function init(): void
    {
        self::$migrationsDir = ROOT_PATH . '/database/migrations';
        $db = Database::connect();

        // Create migrations tracking table
        $db->exec("
            CREATE TABLE IF NOT EXISTS `" . self::$migrationTable . "` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL,
                `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Get list of executed migrations
     */
    public static function getExecuted(): array
    {
        self::init();
        return Database::fetchAll(
            "SELECT migration, batch FROM " . self::$migrationTable . " ORDER BY id ASC"
        );
    }

    /**
     * Get all available migration files
     */
    public static function getAvailable(): array
    {
        if (!is_dir(self::$migrationsDir)) {
            return [];
        }

        $files = glob(str_replace('\\', '/', self::$migrationsDir) . '/???_*.sql');
        sort($files);
        return array_map('basename', $files);
    }

    /**
     * Get pending migrations
     */
    public static function getPending(): array
    {
        $executed = array_column(self::getExecuted(), 'migration');
        return array_diff(self::getAvailable(), $executed);
    }

    /**
     * Run all pending migrations
     */
    public static function up(?int $targetBatch = null): void
    {
        self::init();
        $pending = self::getPending();

        if (empty($pending)) {
            echo "✅ Nothing to migrate. All migrations are up to date.\n";
            return;
        }

        $currentBatch = self::getCurrentBatch() + 1;
        if ($targetBatch !== null) {
            $currentBatch = $targetBatch;
        }

        echo "🚀 Running migrations...\n";
        echo str_repeat('─', 60) . "\n";

        foreach ($pending as $migration) {
            $filePath = self::$migrationsDir . '/' . $migration;
            echo "  Running: {$migration}... ";

            try {
                self::executeSqlFile($filePath);

                // Record migration
                Database::insert(
                    "INSERT INTO " . self::$migrationTable . " (migration, batch) VALUES (?, ?)",
                    [$migration, $currentBatch]
                );

                echo "✅ Done\n";
            } catch (Exception $e) {
                echo "❌ Failed\n";
                echo "  Error: " . $e->getMessage() . "\n";
                echo "\n⚠️ Migration stopped. Fix the error and try again.\n";
                return;
            }
        }

        echo str_repeat('─', 60) . "\n";
        echo "✅ Migrated " . count($pending) . " file(s) successfully.\n";
    }

    /**
     * Rollback migrations
     */
    public static function down(int $targetBatch = null): void
    {
        self::init();
        $executed = self::getExecuted();

        if (empty($executed)) {
            echo "✅ Nothing to rollback.\n";
            return;
        }

        // Determine target batch
        if ($targetBatch === null) {
            // Default: rollback last batch
            $targetBatch = max(array_column($executed, 'batch'));
        }

        // Get migrations to rollback (reverse order)
        $toRollback = array_filter($executed, fn($m) => $m['batch'] >= $targetBatch);
        $toRollback = array_reverse($toRollback);

        if (empty($toRollback)) {
            echo "✅ No migrations to rollback.\n";
            return;
        }

        echo "⏪ Rolling back to batch {$targetBatch}...\n";
        echo str_repeat('─', 60) . "\n";

        foreach ($toRollback as $migration) {
            $migrationName = $migration['migration'];
            $filePath = self::$migrationsDir . '/' . $migrationName;
            $rollbackFile = self::$migrationsDir . '/rollback_' . $migrationName;

            echo "  Rolling back: {$migrationName}... ";

            try {
                if (file_exists($rollbackFile)) {
                    self::executeSqlFile($rollbackFile);
                    echo "✅ Done (with rollback)\n";
                } else {
                    echo "⚠️ No rollback file, skipping SQL undo\n";
                }

                // Remove migration record
                Database::execute(
                    "DELETE FROM " . self::$migrationTable . " WHERE migration = ?",
                    [$migrationName]
                );
            } catch (Exception $e) {
                echo "❌ Failed: " . $e->getMessage() . "\n";
                return;
            }
        }

        echo str_repeat('─', 60) . "\n";
        echo "✅ Rolled back " . count($toRollback) . " migration(s).\n";
    }

    /**
     * Show migration status
     */
    public static function status(): void
    {
        self::init();
        $executed = self::getExecuted();
        $executedNames = array_column($executed, 'migration');
        $available = self::getAvailable();

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║          PCSHSKN e-Portfolio Migration Status           ║\n";
        echo "╠══════════════════════════════════════════════════════════╣\n";
        printf("║  %-40s │ %-6s │ %-8s ║\n", "Migration", "Batch", "Status");
        echo "╠══════════════════════════════════════════════════════════╣\n";

        foreach ($available as $file) {
            $isExecuted = in_array($file, $executedNames);
            $batch = $isExecuted
                ? (array_values(array_filter($executed, fn($m) => $m['migration'] === $file))[0]['batch'] ?? '-')
                : '-';
            $status = $isExecuted ? '✅ Done' : '⏳ Pending';
            printf("║  %-40s │ %-6s │ %-8s ║\n", $file, $batch, $status);
        }

        echo "╚══════════════════════════════════════════════════════════╝\n";

        $pending = count($available) - count($executed);
        echo "\n📊 Total: " . count($available) . " | Executed: " . count($executed) . " | Pending: {$pending}\n\n";
    }

    /**
     * Execute SQL file
     */
    private static function executeSqlFile(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception("Cannot read file: {$filePath}");
        }

        // Remove comments and split by semicolons
        $lines = explode("\n", $sql);
        $queries = [];
        $current = '';

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments
            if (empty($line) || str_starts_with($line, '--') || str_starts_with($line, '/*')) {
                continue;
            }
            $current .= ' ' . $line;
            if (str_ends_with($line, ';')) {
                $queries[] = trim($current);
                $current = '';
            }
        }

        // Execute each query
        foreach ($queries as $query) {
            if (!empty(trim($query))) {
                Database::execute(trim($query));
            }
        }
    }

    /**
     * Get current batch number
     */
    private static function getCurrentBatch(): int
    {
        $result = Database::fetch("SELECT MAX(batch) as max_batch FROM " . self::$migrationTable);
        return (int) ($result['max_batch'] ?? 0);
    }

    /**
     * Generate a new migration file
     */
    public static function make(string $name): void
    {
        self::init();
        $executed = self::getExecuted();
        $nextNum = count($executed) + 1;
        $numStr = str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
        $filename = "{$numStr}_{$name}.sql";
        $filePath = self::$migrationsDir . '/' . $filename;

        if (file_exists($filePath)) {
            echo "❌ Migration file already exists: {$filename}\n";
            return;
        }

        $template = "-- ============================================\n";
        $template .= "-- Migration: {$filename}\n";
        $template .= "-- Description: [ DESCRIBE WHAT THIS MIGRATION DOES ]\n";
        $template .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $template .= "-- ============================================\n\n";
        $template .= "-- Write your SQL here\n";
        $template .= "-- Example: ALTER TABLE users ADD COLUMN test VARCHAR(100);\n\n";

        file_put_contents($filePath, $template);
        echo "✅ Created migration: {$filename}\n";
        echo "   Path: {$filePath}\n";
    }
}

// CLI entry point
if (php_sapi_name() === 'cli') {
    // Bootstrap
    define('ROOT_PATH', dirname(__DIR__));
    require_once ROOT_PATH . '/core/Env.php';
    require_once ROOT_PATH . '/core/Database.php';
    Env::load(ROOT_PATH . '/.env');

    $command = $argv[1] ?? 'status';
    $arg = $argv[2] ?? null;

    switch ($command) {
        case 'up':
            MigrationRunner::up($arg !== null ? (int) $arg : null);
            break;
        case 'down':
            MigrationRunner::down($arg !== null ? (int) $arg : null);
            break;
        case 'status':
            MigrationRunner::status();
            break;
        case 'make':
            if ($arg === null) {
                echo "❌ Usage: php core/MigrationRunner.php make migration_name\n";
            } else {
                MigrationRunner::make($arg);
            }
            break;
        default:
            echo "Usage: php core/MigrationRunner.php [up|down|status|make] [arg]\n";
    }
}
