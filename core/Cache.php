<?php
namespace Core;

use PDO;

class Cache
{
    /**
     * Fallback directory for file-based cache when database is unavailable.
     */
    private const FILE_CACHE_DIR = ROOT_PATH . '/tmp/cache';

    /**
     * Whether the database cache is available (computed once).
     */
    private static ?bool $dbAvailable = null;

    /**
     * Determine if database cache is usable.
     */
    private static function isDbAvailable(): bool
    {
        if (self::$dbAvailable !== null) {
            return self::$dbAvailable;
        }
        try {
            \Database::fetch("SELECT 1 FROM analytics_cache LIMIT 1");
            self::$dbAvailable = true;
        } catch (\Throwable $e) {
            self::$dbAvailable = false;
        }
        return self::$dbAvailable;
    }

    /**
     * Ensure the file cache directory exists.
     */
    private static function ensureFileCacheDir(): void
    {
        if (!is_dir(self::FILE_CACHE_DIR)) {
            mkdir(self::FILE_CACHE_DIR, 0755, true);
        }
    }

    /**
     * Get the file path for a cache key.
     */
    private static function fileCachePath(string $key): string
    {
        return self::FILE_CACHE_DIR . '/' . md5($key) . '.json';
    }

    /**
     * Put an item into the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl time-to-live in seconds
     * @param array $tags
     */
    public static function put(string $key, $value, int $ttl, array $tags = []): void
    {
        if (self::isDbAvailable()) {
            self::putDb($key, $value, $ttl, $tags);
        } else {
            self::putFile($key, $value, $ttl, $tags);
        }
    }

    /**
     * Put an item into the database cache.
     */
    private static function putDb(string $key, $value, int $ttl, array $tags = []): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
        $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $encodedTags = json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $sql = "INSERT INTO analytics_cache (metric_key, metric_value, filters, expires_at)
                VALUES (:key, :value, :tags, :expires_at)
                ON DUPLICATE KEY UPDATE
                metric_value = VALUES(metric_value),
                filters = VALUES(filters),
                expires_at = VALUES(expires_at),
                computed_at = CURRENT_TIMESTAMP";

        try {
            \Database::execute($sql, [
                ':key' => $key,
                ':value' => $encodedValue,
                ':tags' => $encodedTags,
                ':expires_at' => $expiresAt
            ]);
        } catch (\PDOException $e) {
            // Fallback: delete then insert if UNIQUE constraint isn't set yet
            \Database::execute("DELETE FROM analytics_cache WHERE metric_key = ?", [$key]);
            \Database::execute(
                "INSERT INTO analytics_cache (metric_key, metric_value, filters, expires_at) VALUES (?, ?, ?, ?)",
                [$key, $encodedValue, $encodedTags, $expiresAt]
            );
        }
    }

    /**
     * Put an item into the file-based cache.
     */
    private static function putFile(string $key, $value, int $ttl, array $tags = []): void
    {
        self::ensureFileCacheDir();
        $payload = [
            'value' => $value,
            'tags' => $tags,
            'expires_at' => time() + $ttl,
        ];
        file_put_contents(
            self::fileCachePath($key),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            LOCK_EX
        );
    }

    /**
     * Get an item from the cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (self::isDbAvailable()) {
            return self::getDb($key, $default);
        }
        return self::getFile($key, $default);
    }

    /**
     * Get an item from the database cache.
     */
    private static function getDb(string $key, $default = null)
    {
        $row = \Database::fetch(
            "SELECT metric_value, expires_at FROM analytics_cache WHERE metric_key = ?",
            [$key]
        );

        if (!$row) {
            return $default;
        }

        if ($row['expires_at'] && strtotime($row['expires_at']) < time()) {
            self::forget($key);
            return $default;
        }

        return json_decode($row['metric_value'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get an item from the file cache.
     */
    private static function getFile(string $key, $default = null)
    {
        $path = self::fileCachePath($key);
        if (!file_exists($path)) {
            return $default;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $default;
        }

        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if ($payload['expires_at'] < time()) {
            self::forget($key);
            return $default;
        }

        return $payload['value'];
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     */
    public static function forget(string $key): void
    {
        if (self::isDbAvailable()) {
            try {
                \Database::execute("DELETE FROM analytics_cache WHERE metric_key = ?", [$key]);
            } catch (\Throwable $e) {
                // Silently ignore DB errors on forget
            }
        }

        // Also clean file cache if exists
        $path = self::fileCachePath($key);
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Invalidate all items that contain any of the given tags.
     *
     * @param array $tags
     */
    public static function invalidateTags(array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        if (self::isDbAvailable()) {
            foreach ($tags as $tag) {
                $jsonTag = json_encode($tag, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                try {
                    \Database::execute(
                        "DELETE FROM analytics_cache WHERE JSON_CONTAINS(filters, ?)",
                        [$jsonTag]
                    );
                } catch (\Throwable $e) {
                    // Silently ignore if JSON_CONTAINS not supported
                }
            }
        }

        // File cache tag invalidation
        self::ensureFileCacheDir();
        foreach (glob(self::FILE_CACHE_DIR . '/*.json') as $file) {
            $raw = file_get_contents($file);
            if ($raw === false) {
                continue;
            }
            $payload = json_decode($raw, true);
            if ($payload && !empty($payload['tags'])) {
                foreach ($tags as $tag) {
                    if (in_array($tag, $payload['tags'], true)) {
                        @unlink($file);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param int $ttl time-to-live in seconds
     * @param callable $callback
     * @param array $tags
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback, array $tags = [])
    {
        // Use a sentinel to distinguish between "cache miss" and "cached null"
        $sentinel = new \stdClass();
        $value = self::get($key, $sentinel);

        if ($value !== $sentinel) {
            return $value;
        }

        $value = $callback();
        self::put($key, $value, $ttl, $tags);

        return $value;
    }

    /**
     * Flush all expired cache entries from the database.
     * Call this periodically via a cron job or scheduled task.
     *
     * @return int Number of flushed entries
     */
    public static function flushExpired(): int
    {
        if (!self::isDbAvailable()) {
            return 0;
        }

        try {
            return \Database::execute("DELETE FROM analytics_cache WHERE expires_at IS NOT NULL AND expires_at < NOW()");
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Flush ALL cache entries (use with caution).
     */
    public static function flushAll(): void
    {
        if (self::isDbAvailable()) {
            try {
                \Database::execute("DELETE FROM analytics_cache");
            } catch (\Throwable $e) {
                // Silently ignore
            }
        }

        // Also flush file cache
        self::ensureFileCacheDir();
        foreach (glob(self::FILE_CACHE_DIR . '/*.json') as $file) {
            @unlink($file);
        }
    }
}
