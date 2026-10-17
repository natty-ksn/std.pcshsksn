<?php
/**
 * Settings Manager — จัดการค่าคอนฟิกของระบบ
 */
class Settings
{
    private static array $cache = [];

    /**
     * ดึงค่า setting ตาม key
     */
    public static function get(string $key, string $default = ''): string
    {
        if (empty(self::$cache)) {
            self::load();
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * โหลด settings ทั้งหมดมาไว้ใน cache
     */
    public static function load(): void
    {
        try {
            $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            App::log("Failed to load settings: " . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * อัพเดทค่า setting
     */
    public static function set(string $key, string $value): bool
    {
        $updated = Database::execute(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$key, $value]
        );
        if ($updated !== false) {
            self::$cache[$key] = $value;
            return true;
        }
        return false;
    }
}
