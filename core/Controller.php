<?php
/**
 * Base Controller
 * Controller ทุกตัวต้อง extends class นี้
 */
class Controller
{
    /**
     * Render view with layout
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extract data to variables
        extract($data);

        // Set page content path
        $contentView = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($contentView)) {
            App::log("View not found: {$view}", 'ERROR');
            echo "View not found: {$view}";
            return;
        }

        // Start output buffering for content
        ob_start();
        require $contentView;
        $content = ob_get_clean();

        // Load layout
        $layoutFile = VIEW_PATH . "/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render view without layout (for partials, AJAX)
     */
    protected function partial(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void
    {
        App::json($data, $code);
    }

    /**
     * Get POST data (sanitized)
     */
    protected function input(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get raw POST data (for HTML content)
     */
    protected function rawInput(string $key, string $default = ''): string
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET parameter
     */
    protected function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate required fields
     */
    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $_POST[$field] ?? '';
            $ruleList = explode('|', $ruleSet);
            $fieldLabel = $field;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && empty(trim($value))) {
                    $errors[$field] = "กรุณากรอก {$fieldLabel}";
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "{$fieldLabel} ต้องมีอย่างน้อย {$min} ตัวอักษร";
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = "{$fieldLabel} ต้องไม่เกิน {$max} ตัวอักษร";
                    }
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "รูปแบบ Email ไม่ถูกต้อง";
                }
                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = "{$fieldLabel} ต้องเป็นตัวเลขเท่านั้น";
                }
            }
        }

        return $errors;
    }

    /**
     * Handle file upload
     */
    protected function uploadFile(string $fieldName, string $subDir = ''): ?array
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $maxSize = (int) Env::get('UPLOAD_MAX_SIZE', '10485760'); // 10MB per file
        $allowedExts = explode(',', Env::get('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif'));

        // Check Individual File Size
        if ($file['size'] > $maxSize) {
            Session::flash('error', 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด ' . round($maxSize / 1048576) . ' MB)');
            return null;
        }

        // Check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            Session::flash('error', 'ไม่รองรับไฟล์ประเภท .' . $ext . ' (กรุณาใช้รูปภาพเท่านั้น)');
            return null;
        }

        // Validate MIME type with finfo Magic Bytes
        $realMime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($realMime, $allowedMimes, true)) {
                Session::flash('error', 'เนื้อหาไฟล์ไม่ถูกต้องตามมาตรฐานรูปภาพที่ปลอดภัย');
                return null;
            }
        }

        // --- Check 50MB Storage Quota per User ---
        $profile = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        $targetProfileId = $profile ? (int) $profile['id'] : Auth::id();
        $usedSpace = Database::fetch("SELECT SUM(file_size) as total FROM portfolio_items WHERE student_id = ?", [$targetProfileId])['total'] ?? 0;
        $quota = 50 * 1024 * 1024; // 50MB limit
        if (($usedSpace + $file['size']) > $quota) {
            Session::flash('error', 'ข้อผิดพลาด: พื้นที่จัดเก็บข้อมูลของคุณเต็ม (โควตา 50 MB / ใช้ไป ' . round($usedSpace / 1048576, 2) . ' MB) กรุณาลบผลงานเก่าที่ไม่จำเป็นออกก่อน');
            return null;
        }

        // Generate unique filename and setup dir
        $newName = uniqid('file_', true) . '.' . $ext;
        $uploadDir = UPLOAD_PATH . ($subDir ? '/' . trim($subDir, '/') : '');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destination = $uploadDir . '/' . $newName;

        // --- Auto Compress & Resize Image (Max 1200px) ---
        $info = getimagesize($file['tmp_name']);
        if ($info && function_exists('imagecreatetruecolor')) {
            $width = $info[0];
            $height = $info[1];
            $maxWidth = 1200;
            $maxHeight = 1200;
            
            // Calculate new dimensions keeping aspect ratio
            list($newWidth, $newHeight) = $this->calculateDimensions($width, $height, $maxWidth, $maxHeight);

            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            
            switch ($info['mime']) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($file['tmp_name']);
                    imagealphablending($newImg, false);
                    imagesavealpha($newImg, true);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($file['tmp_name']);
                    break;
                default:
                    $source = false;
            }

            if ($source) {
                // Resize and compress
                imagecopyresampled($newImg, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                switch ($info['mime']) {
                    case 'image/jpeg':
                        imagejpeg($newImg, $destination, 80); // 80% quality
                        break;
                    case 'image/png':
                        imagepng($newImg, $destination, 8);
                        break;
                    case 'image/gif':
                        imagegif($newImg, $destination);
                        break;
                }
                imagedestroy($newImg);
                imagedestroy($source);
            } else {
                move_uploaded_file($file['tmp_name'], $destination);
            }
        } else {
            move_uploaded_file($file['tmp_name'], $destination);
        }

        // Ensure file exists and get new compressed size
        if (file_exists($destination)) {
            $finalSize = filesize($destination);
            $thumbnail = $this->createThumbnail($destination, $uploadDir, $newName);

            return [
                'original_name' => $file['name'],
                'file_name' => $newName,
                'file_path' => ($subDir ? $subDir . '/' : '') . $newName,
                'file_size' => $finalSize,
                'file_type' => $realMime ?: $file['type'],
                'extension' => $ext,
                'thumbnail' => $thumbnail
            ];
        }

        return null;
    }

    private function calculateDimensions($width, $height, $maxWidth, $maxHeight) {
        if ($width <= $maxWidth && $height <= $maxHeight) return [$width, $height];
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        return [(int)($width * $ratio), (int)($height * $ratio)];
    }

    /**
     * Create thumbnail for image
     */
    private function createThumbnail(string $source, string $dir, string $name): ?string
    {
        $thumbDir = $dir . '/thumbs';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $thumbPath = $thumbDir . '/thumb_' . $name;
        $info = getimagesize($source);

        if (!$info)
            return null;

        $width = $info[0];
        $height = $info[1];
        $thumbWidth = 300;
        $thumbHeight = (int) ($height * ($thumbWidth / $width));

        if (!function_exists('imagecreatetruecolor')) {
            App::log("GD library is not enabled. Thumbnail creation skipped.", "WARNING");
            return null;
        }

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        switch ($info['mime']) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $img = imagecreatefrompng($source);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($source);
                break;
            default:
                return null;
        }

        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        switch ($info['mime']) {
            case 'image/jpeg':
                imagejpeg($thumb, $thumbPath, 85);
                break;
            case 'image/png':
                imagepng($thumb, $thumbPath, 8);
                break;
            case 'image/gif':
                imagegif($thumb, $thumbPath);
                break;
        }

        imagedestroy($thumb);
        imagedestroy($img);

        return 'thumbs/thumb_' . $name;
    }
}
