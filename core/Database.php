<?php
/**
 * Database Connection (PDO Singleton)
 * ป้องกัน SQL Injection ด้วย Prepared Statements
 */
class Database
{
    private static ?PDO $connection = null;
    private static bool $shutdownRegistered = false;

    /**
     * Get database connection (with automatic micro-retry for burst concurrency / max_user_connections)
     */
    public static function connect(): PDO
    {
        if (self::$connection === null) {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_NAME') ?: Env::get('DB_DATABASE', 'pccks_eportfolio');
            $user = Env::get('DB_USER') ?: Env::get('DB_USERNAME', 'root');
            $pass = Env::get('DB_PASS') ?? Env::get('DB_PASSWORD', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_PERSISTENT => false,
            ];

            $maxAttempts = 4;
            $retryDelaysUs = [150000, 300000, 600000]; // 150ms, 300ms, 600ms

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                try {
                    self::$connection = new PDO($dsn, $user, $pass, $options);

                    if (!self::$shutdownRegistered) {
                        register_shutdown_function([self::class, 'close']);
                        self::$shutdownRegistered = true;
                    }
                    break;
                } catch (PDOException $e) {
                    $errMsg = $e->getMessage();
                    $errCode = (int) ($e->errorInfo[1] ?? $e->getCode());
                    // 1203 = ER_TOO_MANY_USER_CONNECTIONS, 1040 = ER_CON_COUNT_ERROR, 2002 = Connection refused burst
                    $isConcurrencyLimit = in_array($errCode, [1203, 1040, 2002], true)
                        || str_contains($errMsg, 'max_user_connections')
                        || str_contains($errMsg, 'Too many connections');

                    if ($isConcurrencyLimit && $attempt < $maxAttempts) {
                        $delay = $retryDelaysUs[$attempt - 1] ?? 300000;
                        // Add random jitter (0-50ms) to prevent thundering herd
                        usleep($delay + random_int(0, 50000));
                        continue;
                    }

                    App::log("Database connection failed (attempt {$attempt}/{$maxAttempts}): " . $errMsg, 'ERROR');
                    if (App::isDebug()) {
                        die("Database Error: " . $errMsg);
                    }
                    http_response_code(503);
                    die("ขณะนี้มีผู้ใช้งานพร้อมกันจำนวนมาก กรุณารอสักครู่แล้วกดรีเฟรชหน้าเว็บอีกครั้ง");
                }
            }
        }
        return self::$connection;
    }

    /**
     * Close database connection immediately to free MySQL connection slot
     */
    public static function close(): void
    {
        self::$connection = null;
    }

    /**
     * Execute a query with parameters
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get single row
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Get all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Insert and return last ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::connect()->lastInsertId();
    }

    /**
     * Update/Delete and return affected rows
     */
    public static function execute(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    /**
     * Transaction support
     */
    public static function beginTransaction(): void
    {
        self::connect()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connect()->commit();
    }

    public static function rollback(): void
    {
        self::connect()->rollBack();
    }

    /**
     * Check if a transaction is currently active
     */
    public static function inTransaction(): bool
    {
        return self::connect()->inTransaction();
    }
}
