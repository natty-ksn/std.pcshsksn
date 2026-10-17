<?php
/**
 * Environment Variable Loader
 * อ่านค่าจากไฟล์ .env
 */
class Env
{
    private static array $variables = [];

    /**
     * โหลดไฟล์ .env (Failsafe Version)
     */
    public static function load(string $path): void
    {
        // หากไม่เจอเส้นทางตรงๆ ให้ลองสแกนหาไฟล์แวดล้อมสำรอง
        if (!file_exists($path)) {
            $altPath = dirname($path) . '/.env.production';
            if (file_exists($altPath)) {
                $path = $altPath;
            } else {
                // หากไม่มีเลย ให้เงียบไว้แล้วรันด้วยค่า Default (Failsafe)
                error_log("Environment file not found: {$path}. Application will run with default settings.");
                return;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // ข้าม comment
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                // ลบ quotes ถ้ามี
                $value = trim($value, '"\'');

                self::$variables[$key] = $value;
                // ตั้งค่าใน $_ENV ด้วย
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * อ่านค่า environment variable
     */
    public static function get(string $key, string $default = ''): string
    {
        return self::$variables[$key] ?? $_ENV[$key] ?? $default;
    }
}
