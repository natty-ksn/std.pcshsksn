<?php
/**
 * Helper Functions
 * ฟังก์ชันช่วยเหลือที่ใช้บ่อย (PHP 7.4+ Compatible)
 */

/**
 * str_starts_with Polyfill for PHP < 8.0
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

/**
 * str_ends_with Polyfill for PHP < 8.0
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}

/**
 * str_contains Polyfill for PHP < 8.0
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

/**
 * Escape HTML output (ป้องกัน XSS)
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate base URL
 */
function url(string $path = ''): string
{
    return App::baseUrl($path);
}

/**
 * Generate asset URL
 */
function asset(string $path): string
{
    return App::asset($path);
}

/**
 * Get upload URL (Smart: Handles local and external URLs like Google Profile)
 */
function uploadUrl(string $path): string
{
    if (empty($path)) return '';
    if (str_starts_with($path, 'http')) return $path;
    return App::baseUrl('uploads/' . ltrim($path, '/'));
}

/**
 * Format date to Thai
 */
function thaiDate(string $date, string $format = 'long'): string
{
    if (empty($date))
        return '-';

    $timestamp = strtotime($date);
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $thaiMonthsShort = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 
        6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 
        11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];

    $day = (int) date('j', $timestamp);
    $month = (int) date('n', $timestamp);
    $year = (int) date('Y', $timestamp) + 543; // พ.ศ.
    $time = date('H:i', $timestamp);

    switch ($format) {
        case 'short':
            return "{$day} {$thaiMonthsShort[$month]} {$year}";
        case 'full':
            return "{$day} {$thaiMonths[$month]} {$year} เวลา {$time} น.";
        case 'date':
            return date('d/m/', $timestamp) . $year;
        case 'long':
        default:
            return "{$day} {$thaiMonths[$month]} {$year}";
    }
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length)
        return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Get achievement level badge HTML
 */
function achievementBadge(string $level): string
{
    $badges = [
        'school' => '<span class="badge badge-school">🏫 ระดับโรงเรียน</span>',
        'district' => '<span class="badge badge-district">🏘️ ระดับเขต</span>',
        'province' => '<span class="badge badge-province">🏛️ ระดับจังหวัด</span>',
        'national' => '<span class="badge badge-national">🇹🇭 ระดับชาติ</span>',
        'international' => '<span class="badge badge-intl">🌏 ระดับนานาชาติ</span>',
    ];
    return $badges[$level] ?? '<span class="badge">ไม่ระบุ</span>';
}

/**
 * Get Thai class year label
 */
function classYearLabel(int $year): string
{
    $labels = [
        1 => 'ม.1', 2 => 'ม.2', 3 => 'ม.3',
        4 => 'ม.4', 5 => 'ม.5', 6 => 'ม.6'
    ];
    return $labels[$year] ?? "ม.{$year}";
}

/**
 * Generate UI Avatar or Gravatar URL (fallback avatar)
 */
function defaultAvatar(string $email = '', string $name = ''): string
{
    $displayName = !empty($name) ? urlencode($name) : 'User';
    $default = "https://ui-avatars.com/api/?name={$displayName}&background=6366f1&color=fff&size=200";

    if (!empty($email)) {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=" . urlencode($default) . "&s=200";
    }
    
    return $default;
}

/**
 * Check if current page matches path (for active nav)
 */
function isActivePage(string $path, bool $exact = false): string
{
    $current = trim(App::currentPath(), '/');
    $target = trim($path, '/');

    if ($exact || $target === 'admin') {
        return $current === $target ? 'active' : '';
    }

    // Prevent parent index items from highlighting alongside dedicated sibling menu items
    if ($target === 'profile' && str_starts_with($current, 'profile/360')) {
        return '';
    }
    if ($target === 'portfolio' && $current === 'portfolio/create') {
        return '';
    }
    if ($target === 'projects' && $current === 'projects/create') {
        return '';
    }
    if ($target === 'rewards' && str_starts_with($current, 'rewards/badges')) {
        return '';
    }

    // Highlight 'teacher/students' when viewing individual student 'teacher/student/{id}'
    if ($target === 'teacher/students' && str_starts_with($current, 'teacher/student/')) {
        return 'active';
    }

    if ($current === $target || str_starts_with($current, $target . '/')) {
        return 'active';
    }

    return '';
}

/**
 * Time ago format (Thai)
 */
function timeAgo(string $datetime): string
{
    $now = time();
    $timestamp = strtotime($datetime);
    $diff = $now - $timestamp;

    if ($diff < 60) return 'เมื่อสักครู่';
    if ($diff < 3600) return floor($diff / 60) . ' นาทีที่แล้ว';
    if ($diff < 86400) return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
    if ($diff < 2592000) return floor($diff / 86400) . ' วันที่แล้ว';
    if ($diff < 31536000) return floor($diff / 2592000) . ' เดือนที่แล้ว';
    return floor($diff / 31536000) . ' ปีที่แล้ว';
}

/**
 * แปลงชื่อ Role เป็นภาษาไทย
 */
function roleLabel(string $role): string
{
    $labels = [
        'admin' => 'ผู้ดูแลระบบ',
        'teacher' => 'คุณครู',
        'student' => 'นักเรียน',
        'parent' => 'ผู้ปกครอง',
        'executive' => 'ฝ่ายบริหาร'
    ];

    return $labels[$role] ?? $role;
}

/**
 * คำนวณความคืบหน้า EXP (Level Progress)
 */
function getLevelProgress(int $exp): int
{
    $baseExp = 1000; // EXP ที่ต้องการต่อเลเวล (Simplified)
    return round(($exp % $baseExp) / $baseExp * 100);
}

/**
 * ดึง Rank Badge ตาม Level
 */
function getRankBadge(int $level): array
{
    if ($level >= 50) return ['label' => 'Legendary', 'class' => 'rank-legend', 'icon' => '🔥'];
    if ($level >= 40) return ['label' => 'Diamond', 'class' => 'rank-diamond', 'icon' => '💎'];
    if ($level >= 30) return ['label' => 'Platinum', 'class' => 'rank-platinum', 'icon' => '🛡️'];
    if ($level >= 20) return ['label' => 'Gold', 'class' => 'rank-gold', 'icon' => '🏆'];
    if ($level >= 10) return ['label' => 'Silver', 'class' => 'rank-silver', 'icon' => '🥈'];
    return ['label' => 'Bronze', 'class' => 'rank-bronze', 'icon' => '🥉'];
}

/**
 * ดึงค่า Old Input จาก Session
 */
function old(string $key, $default = ''): string
{
    $val = Session::old($key, $default);
    return is_string($val) ? e($val) : (string) $val;
}
