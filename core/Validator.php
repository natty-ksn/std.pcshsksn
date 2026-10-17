<?php
/**
 * Validator — Input Validation Engine
 *
 * รองรับ Rules:
 *   required, email, numeric, integer, boolean, url, ip, date
 *   min:X, max:X, between:X:Y, size:X
 *   in:val1,val2,val3, not_in:val1,val2
 *   regex:pattern
 *   unique:table,column,except:id
 *   exists:table,column
 *   same:field, different:field
 *   accepted (checkbox)
 *   phone (Thai format)
 *   thai_id_card
 *
 * ตัวอย่างการใช้งาน:
 *   $errors = Validator::make($_POST, [
 *       'title' => 'required|min:5|max:200',
 *       'email' => 'required|email|unique:users,email',
 *       'age'   => 'required|integer|between:1,120',
 *   ]);
 *   // if (!empty($errors)) handle errors
 */
class Validator
{
    private static array $errors = [];
    private static array $data = [];
    private static array $customMessages = [];

    /**
     * Validate data
     */
    public static function make(array $data, array $rules, array $customMessages = []): array
    {
        self::$errors = [];
        self::$data = $data;
        self::$customMessages = $customMessages;

        foreach ($rules as $field => $ruleString) {
            self::validateField($field, $ruleString);
        }

        return self::$errors;
    }

    /**
     * Check if validation passed
     */
    public static function passes(array $data, array $rules, array $customMessages = []): bool
    {
        return empty(self::make($data, $rules, $customMessages));
    }

    /**
     * Check if validation failed
     */
    public static function fails(array $data, array $rules, array $customMessages = []): bool
    {
        return !self::passes($data, $rules, $customMessages);
    }

    /**
     * Validate and redirect back with errors on failure
     */
    public static function validateOrRedirect(array $data, array $rules, string $redirectUrl, array $customMessages = []): bool
    {
        $errors = self::make($data, $rules, $customMessages);
        if (!empty($errors)) {
            Session::flash('validation_errors', $errors);
            Session::flash('old_input', $data);
            App::redirect($redirectUrl);
            return false;
        }
        return true;
    }

    /**
     * Get validation errors for display
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Get first error for a field
     */
    public static function getError(string $field): ?string
    {
        return self::$errors[$field] ?? null;
    }

    /**
     * Check if field has error
     */
    public static function hasError(string $field): bool
    {
        return isset(self::$errors[$field]);
    }

    /**
     * Validate a single field
     */
    private static function validateField(string $field, string $ruleString): void
    {
        $rules = explode('|', $ruleString);
        $value = self::$data[$field] ?? null;
        $hasRequired = in_array('required', $rules);

        // If not required and empty, skip
        if (!$hasRequired && (is_null($value) || trim((string) $value) === '')) {
            return;
        }

        foreach ($rules as $rule) {
            self::applyRule($field, $value, $rule);
        }
    }

    /**
     * Apply a single validation rule
     */
    private static function applyRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule and parameters
        if (str_contains($rule, ':')) {
            [$ruleName, $params] = explode(':', $rule, 2);
            $params = explode(',', $params);
        } else {
            $ruleName = $rule;
            $params = [];
        }

        $label = self::getFieldLabel($field);
        $message = self::getMessage($field, $ruleName, $label, $params);

        switch ($ruleName) {
            case 'required':
                if (is_null($value) || trim((string) $value) === '') {
                    self::$errors[$field] = $message;
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'boolean':
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'ip':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_IP)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'date':
                if (!empty($value) && strtotime($value) === false) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'min':
                $min = (int) $params[0];
                if (is_numeric($value)) {
                    if ($value < $min) {
                        self::$errors[$field] = $message;
                    }
                } elseif (strlen((string) $value) < $min) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'max':
                $max = (int) $params[0];
                if (is_numeric($value)) {
                    if ($value > $max) {
                        self::$errors[$field] = $message;
                    }
                } elseif (strlen((string) $value) > $max) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'between':
                $min = (int) $params[0];
                $max = (int) $params[1];
                $len = is_numeric($value) ? $value : strlen((string) $value);
                if ($len < $min || $len > $max) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'size':
                $size = (int) $params[0];
                $len = is_numeric($value) ? $value : strlen((string) $value);
                if ($len !== $size) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'not_in':
                if (!empty($value) && in_array($value, $params)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'regex':
                $pattern = $params[0];
                if (!empty($value) && !preg_match($pattern, (string) $value)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'unique':
                // unique:table,column,except:id
                $table = $params[0];
                $column = $params[1] ?? $field;
                $exceptField = $params[2] ?? null;
                $exceptValue = $params[3] ?? null;

                if (!empty($value)) {
                    $sql = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE `{$column}` = ?";
                    $bindParams = [$value];

                    if ($exceptField && $exceptValue) {
                        $sql .= " AND `{$exceptField}` != ?";
                        $bindParams[] = $exceptValue;
                    }

                    $result = Database::fetch($sql, $bindParams);
                    if ($result && $result['cnt'] > 0) {
                        self::$errors[$field] = $message;
                    }
                }
                break;

            case 'exists':
                $table = $params[0];
                $column = $params[1] ?? $field;
                if (!empty($value)) {
                    $result = Database::fetch(
                        "SELECT COUNT(*) as cnt FROM `{$table}` WHERE `{$column}` = ?",
                        [$value]
                    );
                    if (!$result || $result['cnt'] === 0) {
                        self::$errors[$field] = $message;
                    }
                }
                break;

            case 'same':
                $otherField = $params[0];
                $otherValue = self::$data[$otherField] ?? null;
                if ($value !== $otherValue) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'different':
                $otherField = $params[0];
                $otherValue = self::$data[$otherField] ?? null;
                if ($value === $otherValue) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'accepted':
                if (!in_array($value, ['yes', 'on', 1, '1', true], true)) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'phone':
                // Thai phone: 0XX-XXX-XXXX or 0XXXXXXXXX
                if (!empty($value) && !preg_match('/^0[0-9]{1,2}-?[0-9]{3}-?[0-9]{4}$/', preg_replace('/\s/', '', (string) $value))) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'thai_id_card':
                // Thai ID: 13 digits with dashes
                if (!empty($value) && !preg_match('/^[0-9]{1}-?[0-9]{4}-?[0-9]{5}-?[0-9]{2}-?[0-9]{1}$/', preg_replace('/\s/', '', (string) $value))) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha(preg_replace('/[\s\-]/', '', (string) $value))) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'alpha_num':
                if (!empty($value) && !ctype_alnum(preg_replace('/[\s\-_]/', '', (string) $value))) {
                    self::$errors[$field] = $message;
                }
                break;

            case 'json':
                if (!empty($value)) {
                    json_decode((string) $value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        self::$errors[$field] = $message;
                    }
                }
                break;
        }
    }

    /**
     * Get field label from field name
     */
    private static function getFieldLabel(string $field): string
    {
        // Map common field names to Thai labels
        $labels = [
            'username' => 'ชื่อผู้ใช้',
            'email' => 'อีเมล',
            'password' => 'รหัสผ่าน',
            'password_confirmation' => 'ยืนยันรหัสผ่าน',
            'full_name' => 'ชื่อ-นามสกุล',
            'first_name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'phone' => 'เบอร์โทรศัพท์',
            'title' => 'ชื่อผลงาน',
            'description' => 'รายละเอียด',
            'category_id' => 'หมวดหมู่',
            'student_id' => 'รหัสนักเรียน',
            'advisor_id' => 'ที่ปรึกษา',
            'project_id' => 'โครงงาน',
            'goal_type' => 'ประเภทเป้าหมาย',
            'target' => 'เป้าหมาย',
            'grade' => 'เกรด',
            'subject' => 'วิชา',
            'subject_code' => 'รหัสวิชา',
            'semester' => 'ภาคเรียน',
            'academic_year' => 'ปีการศึกษา',
            'comment' => 'ความคิดเห็น',
            'message' => 'ข้อความ',
            'name' => 'ชื่อ',
            'type' => 'ประเภท',
            'status' => 'สถานะ',
        ];

        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Get error message
     */
    private static function getMessage(string $field, string $rule, string $label, array $params): string
    {
        // Check custom message first
        $key = "{$field}.{$rule}";
        if (isset(self::$customMessages[$key])) {
            return self::$customMessages[$key];
        }

        $messages = [
            'required' => "กรุณากรอก{$label}",
            'email' => "รูปแบบ{$label}ไม่ถูกต้อง",
            'numeric' => "{$label}ต้องเป็นตัวเลขเท่านั้น",
            'integer' => "{$label}ต้องเป็นจำนวนเต็มเท่านั้น",
            'boolean' => "{$label}ต้องเป็นค่าจริง/เท็จ",
            'url' => "{$label}ต้องเป็น URL ที่ถูกต้อง",
            'ip' => "{$label}ต้องเป็น IP address ที่ถูกต้อง",
            'date' => "{$label}ต้องเป็นวันที่ที่ถูกต้อง",
            'min' => "{$label}ต้องมีอย่างน้อย {$params[0]} ตัวอักษร",
            'max' => "{$label}ต้องไม่เกิน {$params[0]} ตัวอักษร",
            'between' => "{$label}ต้องมีค่าระหว่าง {$params[0]} ถึง {$params[1]}",
            'size' => "{$label}ต้องมีขนาด {$params[0]}",
            'in' => "{$label}ต้องเป็นค่าใดค่าหนึ่งใน: " . implode(', ', $params),
            'not_in' => "{$label}ไม่สามารถเป็นค่านี้ได้: " . implode(', ', $params),
            'regex' => "รูปแบบ{$label}ไม่ถูกต้อง",
            'unique' => "{$label}นี้มีอยู่ในระบบแล้ว",
            'exists' => "ไม่พบ{$label}ในระบบ",
            'same' => "{$label}ไม่ตรงกับ field ที่กำหนด",
            'different' => "{$label}ต้องไม่ซ้ำกับ field ที่กำหนด",
            'accepted' => "กรุณายอมรับ{$label}",
            'phone' => "รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง (เช่น 081-234-5678)",
            'thai_id_card' => "รูปแบบบัตรประชาชนไม่ถูกต้อง (13 หลัก)",
            'alpha' => "{$label}ต้องเป็นตัวอักษรเท่านั้น",
            'alpha_num' => "{$label}ต้องเป็นตัวอักษรและตัวเลขเท่านั้น",
            'json' => "{$label}ต้องเป็น JSON ที่ถูกต้อง",
        ];

        return $messages[$rule] ?? "{$label}ไม่ผ่านการตรวจสอบ";
    }

    /**
     * Sanitize string input
     */
    public static function sanitize(string $value, bool $allowHtml = false): string
    {
        if ($allowHtml) {
            // Allow safe HTML only
            return strip_tags($value, '<p><br><strong><em><ul><ol><li><a><img>');
        }
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize array of data
     */
    public static function sanitizeArray(array $data, array $allowHtmlFields = []): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitize($value, in_array($key, $allowHtmlFields));
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value, $allowHtmlFields);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Validate file upload
     */
    public static function validateFile(string $fieldName, array $rules = []): array
    {
        $errors = [];
        $file = $_FILES[$fieldName] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            if ($rules['required'] ?? false) {
                $errors[$fieldName] = 'กรุณาเลือกไฟล์';
            }
            return $errors;
        }

        $maxSize = $rules['max_size'] ?? (int) Env::get('UPLOAD_MAX_SIZE', 10485760);
        $allowedTypes = $rules['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/gif'];

        // Check size
        if ($file['size'] > $maxSize) {
            $maxMb = round($maxSize / 1048576);
            $errors[$fieldName] = "ไฟล์มีขนาดใหญ่เกินไป (สูงสุด {$maxMb} MB)";
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $errors[$fieldName] = "ไม่รองรับไฟล์ประเภทนี้";
        }

        return $errors;
    }
}
