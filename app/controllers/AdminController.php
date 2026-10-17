<?php
/**
 * AdminController — ระบบผู้ดูแล
 */
class AdminController extends Controller
{
    public function index(): void
    {
        // สรุปข้อมูลหน้า Dashboard
        $stats = [
            'total_users' => Database::fetch("SELECT COUNT(*) as count FROM users")['count'],
            'total_students' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'],
            'total_teachers' => Database::fetch("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")['count'],
            'total_items' => Database::fetch("SELECT COUNT(*) as count FROM portfolio_items WHERE status = 'published'")['count'],
            'recent_users' => Database::fetchAll("SELECT username, role, created_at FROM users ORDER BY created_at DESC LIMIT 5"),
            'recent_logs' => Database::fetchAll("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10")
        ];

        $this->view('admin.dashboard', ['stats' => $stats]);
    }

    public function logs(): void
    {
        $limit = 50;
        $page = max(1, (int) ($this->query('page') ?: 1));
        $offset = ($page - 1) * $limit;

        $total = (int) (Database::fetch("SELECT COUNT(*) as c FROM audit_logs")['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $limit));

        $logs = Database::fetchAll("SELECT al.*, u.username 
                                   FROM audit_logs al 
                                   LEFT JOIN users u ON al.user_id = u.id 
                                   ORDER BY al.created_at DESC 
                                   LIMIT ? OFFSET ?", [$limit, $offset]);

        $this->view('admin.logs', [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function categories(): void
    {
        $categories = Database::fetchAll("SELECT c.*, (SELECT COUNT(*) FROM portfolio_items WHERE category_id = c.id) as item_count 
                                        FROM portfolio_categories c 
                                        ORDER BY sort_order ASC, name ASC");
        $this->view('admin.categories', ['categories' => $categories]);
    }

    public function storeCategory(): void
    {
        CSRF::check();
        $name = $this->input('name');
        $icon = $this->input('icon') ?: '📁';
        $color = $this->input('color') ?: '#6366f1';
        $order = (int) $this->input('sort_order', '0');

        if (empty($name)) {
            Session::flash('error', 'กรุณาระบุชื่อหมวดหมู่');
            App::redirect('admin/categories');
            return;
        }

        Database::execute(
            "INSERT INTO portfolio_categories (name, icon, color, sort_order) VALUES (?, ?, ?, ?)",
            [$name, $icon, $color, $order]
        );

        Session::flash('success', 'เพิ่มหมวดหมู่เรียบร้อย');
        App::redirect('admin/categories');
    }

    public function updateCategory(string $id): void
    {
        CSRF::check();
        $catId = (int) $id;
        $name = trim($this->input('name') ?? '');
        $icon = trim($this->input('icon') ?? '') ?: '📁';
        $color = trim($this->input('color') ?? '') ?: '#6366f1';
        $order = (int) $this->input('sort_order', '0');
        $isActive = $this->input('is_active') !== null ? (int) (bool) $this->input('is_active') : 1;

        if ($name === '') {
            Session::flash('error', 'กรุณาระบุชื่อหมวดหมู่');
            App::redirect('admin/categories');
            return;
        }

        Database::execute(
            "UPDATE portfolio_categories SET name = ?, icon = ?, color = ?, sort_order = ?, is_active = ? WHERE id = ?",
            [$name, $icon, $color, $order, $isActive, $catId]
        );

        Auth::logAction('update_category', 'category', $catId);
        Session::flash('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
        App::redirect('admin/categories');
    }

    public function deleteCategory(string $id): void
    {
        CSRF::check();
        $catId = (int) $id;

        $usageCount = (int) (Database::fetch("SELECT COUNT(*) as c FROM portfolio_items WHERE category_id = ?", [$catId])['c'] ?? 0);
        if ($usageCount > 0) {
            Session::flash('warning', "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีผลงานใช้งานอยู่ {$usageCount} รายการ");
            App::redirect('admin/categories');
            return;
        }

        Database::execute("DELETE FROM portfolio_categories WHERE id = ?", [$catId]);
        Auth::logAction('delete_category', 'category', $catId);
        Session::flash('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
        App::redirect('admin/categories');
    }

    public function users(): void
    {
        $search = $this->query('search');
        $role = $this->query('role');
        $where = "1=1";
        $params = [];

        if ($search) {
            $where .= " AND (u.username LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($role) {
            $where .= " AND u.role = ?";
            $params[] = $role;
        }

        $users = Database::fetchAll(
            "SELECT u.*, sp.avatar_path 
             FROM users u 
             LEFT JOIN student_profiles sp ON u.id = sp.user_id 
             WHERE {$where} 
             ORDER BY u.created_at DESC", 
            $params
        );
        $this->view('admin.users', ['users' => $users, 'search' => $search, 'role' => $role]);
    }

    public function createUser(): void
    {
        CSRF::check();
        $errors = $this->validate(['username' => 'required', 'full_name' => 'required', 'role' => 'required']);
        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('admin/users');
            return;
        }

        $username = trim($this->input('username'));
        $fullName = trim($this->input('full_name'));
        $role = trim($this->input('role'));
        $email = trim($this->input('email') ?? '');

        $existing = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            Session::flash('error', 'Username นี้ถูกใช้แล้ว');
            App::redirect('admin/users');
            return;
        }

        $password = $this->input('password') ?: 'pccks1234';
        $userId = Database::insert(
            "INSERT INTO users (username, password, full_name, role, email) VALUES (?, ?, ?, ?, ?)",
            [$username, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $fullName, $role, $email ?: null]
        );

        if ($role === 'student' && $userId) {
            Database::execute(
                "INSERT INTO student_profiles (user_id, full_name, student_code, class_year, class_room) VALUES (?, ?, ?, 4, 1)",
                [(int) $userId, $fullName, $username]
            );
        }

        Auth::logAction('create_user', 'user', (int) $userId);
        Session::flash('success', 'สร้างบัญชีผู้ใช้เรียบร้อย (Password: ' . $password . ')');
        App::redirect('admin/users');
    }

    public function toggleUser(string $id): void
    {
        CSRF::check();
        if ((int) $id === Auth::id()) {
            Session::flash('error', 'ไม่สามารถปิดบัญชีตัวเองได้');
            App::redirect('admin/users');
            return;
        }
        Database::execute("UPDATE users SET is_active = NOT is_active WHERE id = ?", [(int) $id]);
        Session::flash('success', 'อัพเดทสถานะผู้ใช้เรียบร้อย');
        App::redirect('admin/users');
    }

    public function editUser(string $id): void
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [(int)$id]);
        if (!$user) {
            Session::flash('error', 'ไม่พบผู้ใช้');
            App::redirect('admin/users');
            return;
        }
        $this->view('admin.edit_user', ['user' => $user]);
    }

    public function updateUser(string $id): void
    {
        CSRF::check();
        $id = (int)$id;
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (!$user) {
            Session::flash('error', 'ไม่พบผู้ใช้');
            App::redirect('admin/users');
            return;
        }

        $errors = $this->validate([
            'username' => 'required',
            'full_name' => 'required',
            'role' => 'required'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('admin/users/' . $id . '/edit');
            return;
        }

        $username = $this->input('username');
        $fullName = $this->input('full_name');
        $role = $this->input('role');
        $position = $this->input('position');
        $email = $this->input('email');
        $password = $this->input('password');

        // Check duplicate username
        $existing = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
        if ($existing) {
            Session::flash('error', 'Username นี้ถูกใช้โดยผู้อื่นแล้ว');
            App::redirect('admin/users/' . $id . '/edit');
            return;
        }

        $sql = "UPDATE users SET username = ?, full_name = ?, role = ?, position = ?, email = ?";
        $params = [$username, $fullName, $role, $position, $email];

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        Database::execute($sql, $params);

        // Sync with student_profile if role is student or was student
        if ($role === 'student') {
            $hasProfile = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$id]);
            if ($hasProfile) {
                Database::execute(
                    "UPDATE student_profiles SET full_name = ?, student_code = ? WHERE user_id = ?",
                    [$fullName, $username, $id]
                );
            } else {
                Database::execute(
                    "INSERT INTO student_profiles (user_id, full_name, student_code, class_year, class_room) VALUES (?, ?, ?, 4, 1)",
                    [$id, $fullName, $username]
                );
            }
        } elseif ($user['role'] === 'student') {
            Database::execute("DELETE FROM student_profiles WHERE user_id = ?", [$id]);
        }

        Auth::logAction('update_user', 'user', $id);
        Session::flash('success', 'อัปเดตข้อมูลผู้ใช้เรียบร้อย');
        App::redirect('admin/users');
    }

    public function deleteUser(string $id): void
    {
        CSRF::check();
        $id = (int)$id;

        if ($id === Auth::id()) {
            Session::flash('error', 'ไม่สามารถลบบัญชีตัวเองได้');
            App::redirect('admin/users');
            return;
        }

        try {
            Database::execute("DELETE FROM student_profiles WHERE user_id = ?", [$id]);
            Database::execute("DELETE FROM users WHERE id = ?", [$id]);

            Auth::logAction('delete_user', 'user', $id);
            Session::flash('success', 'ลบบัญชีผู้ใช้เรียบร้อย');
        } catch (\Throwable $e) {
            Session::flash('error', 'ไม่สามารถลบผู้ใช้นี้ได้เนื่องจากมีข้อมูลโครงงานหรือการตรวจงานผูกอยู่ แนะนำให้ใช้ปุ่ม "ระงับการใช้งาน" แทน');
        }
        App::redirect('admin/users');
    }

    /**
     * สำหรับการอัปเกรดยศ (Role) ผู้ใช้ใหม่ที่เป็น Student ให้เป็น Teacher อย่างรวดเร็ว
     */
    public function promoteToTeacher(string $id): void
    {
        CSRF::check();
        Auth::requireRole('admin');
        $id = (int)$id;

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);

        if (!$user) {
            Session::flash('error', 'ไม่พบผู้ใช้');
            App::redirect('admin/users');
            return;
        }

        if ($user['role'] === 'teacher') {
            Session::flash('warning', 'ผู้ใช้งานรายนี้มีสถานะเป็นครูอยู่แล้ว');
            App::redirect('admin/users');
            return;
        }

        Database::execute("UPDATE users SET role = 'teacher' WHERE id = ?", [$id]);
        
        // ลบข้อมูล Profile นักเรียน (ถ้ามี) เนื่องจากเปลี่ยนสถานะเป็นครู
        Database::execute("DELETE FROM student_profiles WHERE user_id = ?", [$id]);

        Auth::logAction('promote_to_teacher', 'user', $id);
        Session::flash('success', "อัปเกรดบัญชี {$user['full_name']} เป็น ครูอัตราจ้าง/ข้าราชการ สำเร็จแล้ว!");
        App::redirect('admin/users');
    }

    public function reports(): void
    {
        $stats = [
            'by_class' => Database::fetchAll("SELECT sp.class_year, sp.class_room, COUNT(*) as count FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE u.role = 'student' GROUP BY sp.class_year, sp.class_room ORDER BY sp.class_year, sp.class_room"),
            'by_category' => Database::fetchAll("SELECT pc.name, pc.icon, COUNT(pi.id) as count FROM portfolio_categories pc LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.status = 'published' WHERE pc.is_active = 1 GROUP BY pc.id ORDER BY count DESC"),
            'monthly' => Database::fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM portfolio_items WHERE status = 'published' GROUP BY month ORDER BY month DESC LIMIT 12"),
            
            // Executive KPI Metrics
            'gpa_by_class' => Database::fetchAll("
                SELECT sp.class_year, ROUND(AVG(gpa_table.gpa), 2) as avg_gpa
                FROM student_profiles sp
                JOIN users u ON sp.user_id = u.id
                LEFT JOIN (
                    SELECT student_id, SUM(grade*credit)/NULLIF(SUM(credit), 0) as gpa FROM academic_records GROUP BY student_id
                ) gpa_table ON sp.id = gpa_table.student_id
                WHERE u.role = 'student'
                GROUP BY sp.class_year ORDER BY sp.class_year
            "),
            'at_risk' => Database::fetchAll("
                SELECT sp.class_year, COUNT(sp.id) as total_students,
                       SUM(CASE WHEN 
                           (SELECT SUM(grade*credit)/NULLIF(SUM(credit), 0) FROM academic_records WHERE student_id = sp.id) < 2.5 
                           OR (SELECT COUNT(*) FROM portfolio_items WHERE student_id = sp.id AND status = 'published') = 0
                       THEN 1 ELSE 0 END) as risk_count
                FROM student_profiles sp
                JOIN users u ON sp.user_id = u.id
                WHERE u.role = 'student'
                GROUP BY sp.class_year ORDER BY sp.class_year
            "),
            'projects' => Database::fetch("
                SELECT COUNT(id) as total_projects, 
                       IFNULL(SUM(budget_requested), 0) as requested, 
                       IFNULL(SUM(budget_approved), 0) as approved,
                       SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM projects
            ")
        ];
        $this->view('admin.reports', ['stats' => $stats]);
    }

    public function settings(): void
    {
        $settings = Database::fetchAll("SELECT * FROM settings ORDER BY id ASC");
        $this->view('admin.settings', ['settings' => $settings]);
    }

    public function updateSettings(): void
    {
        CSRF::check();
        $configs = $_POST['config'] ?? [];

        foreach ($configs as $key => $value) {
            Settings::set($key, (string) $value);
        }

        Auth::logAction('update_settings', 'system', 0);
        Session::flash('success', 'บันทึกการตั้งค่าระบบเรียบร้อย');
        App::redirect('admin/settings');
    }

    public function importUsers(): void
    {
        CSRF::check();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'กรุณาเลือกไฟล์ CSV สำหรับนำเข้าข้อมูล');
            App::redirect('admin/users');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        // ข้าม header
        fgetcsv($handle);

        $success = 0;
        $failed = 0;
        $allowedRoles = ['student', 'teacher', 'admin', 'executive', 'parent'];

        while (($data = fgetcsv($handle)) !== FALSE) {
            // CSV format: username, full_name, role, email, password
            if (count($data) < 4)
                continue;

            $username = preg_replace('/^\xEF\xBB\xBF/', '', trim($data[0]));
            $fullName = trim($data[1]);
            $role = strtolower(trim($data[2]));
            if (!in_array($role, $allowedRoles, true)) {
                $role = 'student';
            }
            $email = trim($data[3]);
            $password = !empty($data[4]) ? trim($data[4]) : 'pccks1234';

            // ตรวจสอบว่ามีอยู่แล้วหรือไม่
            $existing = Database::fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);

            if ($existing) {
                $failed++;
                continue;
            }

            try {
                $userId = Database::insert(
                    "INSERT INTO users (username, full_name, role, email, password) VALUES (?, ?, ?, ?, ?)",
                    [$username, $fullName, $role, $email, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])]
                );

                if ($role === 'student' && $userId) {
                    // สร้าง student_profile อัตโนมัติ
                    Database::execute(
                        "INSERT INTO student_profiles (user_id, full_name, student_code) VALUES (?, ?, ?)",
                        [$userId, $fullName, $username]
                    );
                }
                $success++;
            } catch (Exception $e) {
                $failed++;
                App::log("Import failed for user {$username}: " . $e->getMessage(), 'ERROR');
            }
        }
        fclose($handle);

        Auth::logAction('import_users', 'user', 0, null, ['success' => $success, 'failed' => $failed]);
        Session::flash('success', "นำเข้าข้อมูลสำเร็จ {$success} รายการ" . ($failed > 0 ? ", ผิดพลาด {$failed} รายการ" : ""));
        App::redirect('admin/users');
    }

    /**
     * ระบบเนรมิตข้อมูลจำลอง (Mock Data Generator) สำหรับทดสอบระบบ
     */
    public function generateMockData(): void
    {
        CSRF::check();
        Auth::requireRole('admin');
        set_time_limit(120);

        // Auto-clear before generation to avoid duplicates
        $mockUsers = Database::fetchAll("SELECT id FROM users WHERE email LIKE '%@pccks-mock.ac.th'");
        if (!empty($mockUsers)) {
             $userIds = array_column($mockUsers, 'id');
             $userIdStr = implode(',', $userIds);
             $profiles = Database::fetchAll("SELECT id FROM student_profiles WHERE user_id IN ($userIdStr)");
             if (!empty($profiles)) {
                 $profileIds = array_column($profiles, 'id');
                 $profileIdStr = implode(',', $profileIds);
                 Database::execute("DELETE FROM portfolio_items WHERE student_id IN ($profileIdStr)");
                 Database::execute("DELETE FROM academic_records WHERE student_id IN ($profileIdStr)");
                 Database::execute("DELETE FROM student_goals WHERE student_id IN ($profileIdStr)");
                 Database::execute("DELETE FROM student_profiles WHERE id IN ($profileIdStr)");
             }
             Database::execute("DELETE FROM users WHERE id IN ($userIdStr)");
        }

        $firstNames = [
            'สมชาย', 'สมหญิง', 'ปริญญา', 'ณัฐพล', 'ธนพล', 'วิไล', 'กมลวรรณ', 'อภิสิทธิ์', 'สุรชัย', 'กิตติศักดิ์',
            'นงนุช', 'พรวิมล', 'จีรนันท์', 'พงศธร', 'วรวุฒิ', 'ศิริพร', 'ปิยะมาศ', 'ธนาวุฒิ', 'กิตติ', 'สิรินธร',
            'อนุพงษ์', 'ภัทรพล', 'ชนิดา', 'ธัญญารัตน์', 'รุ่งอรุณ', 'เกศินี', 'โสภณ', 'มาร์ค', 'เจมส์', 'แอน',
            'บดินทร์', 'ดวงกมล', 'จารุวรรณ', 'นิรันดร์', 'พิมพิลาศ', 'ไพศาล', 'รัตนา', 'ยุพิน', 'ชินกร', 'นพดล',
            'รุ่งนภา', 'บุญส่ง', 'มาลี', 'สมพงษ์', 'อาทิตย์', 'นภา', 'ขวัญใจ', 'ประภาส'
        ];
        $keywords = [
            'med' => ['แพทย์', 'หมอ', 'พยาบาล', 'เภสัช', 'ทันตะ', 'สาธารณสุข', 'กายภาพ', 'สัตวแพทย์', 'เทคนิคการแพทย์', 'รังสี'],
            'eng' => ['วิศว', 'คอมพิวเตอร์', 'โปรแกรมเมอร์', 'ไอที', 'it', 'ai', 'ซอฟต์แวร์', 'วิทยาการคอม', 'ดิจิทัล', 'เทคโนโลยี'],
            'sci' => ['วิทย์', 'วิทยาศาสตร์', 'ชีววิทยา', 'เคมี', 'ฟิสิกส์', 'นวัตกรรม', 'วิจัย'],
            'art' => ['ศิลปะ', 'นิเทศ', 'ออกแบบ', 'สถาปัตย', 'ภาษา', 'อักษร', 'มนุษย', 'สังคม']
        ];
        
        $lastNames = [
            'ใจดี', 'รักการเรียน', 'เรียนเก่ง', 'มุ่งมั่น', 'ปัญญาเลิศ', 'วิจิตรศิลป์', 'ศรีสุวรรณ', 'รัตนโกสินทร์', 'ธรรมรักษา', 'วงษ์คำดี',
            'ศิริเวช', 'ประดิษฐ์งาน', 'วิชาการ', 'มีปัญญา', 'ใจซื่อ', 'ยอดเยี่ยม', 'ก้าวหน้า', 'รุ่งเรือง', 'เปี่ยมสุข', 'วัฒนศักดิ์',
            'พงศ์ประยูร', 'อนันตศิลป์', 'ชูชาติ', 'ประเสริฐสุข', 'จิตบริสุทธิ์', 'ภักดี', 'มั่นคง', 'กล้าหาญ', 'สุโขทัย', 'อยุธยา',
            'เชียงใหม่', 'ขอนแก่น', 'รักถิ่น', 'พึ่งบุญ', 'มั่งคั่ง', 'ร่ำรวย', 'มหากุล', 'ยอดดี', 'ขยันยิ่ง', 'ใจเย็น',
            'ใจกว้าง', 'รักษาดี', 'คงกระพัน', 'บุญรักษา', 'เทวา', 'นาคา', 'พิทักษ์', 'สมนาม'
        ];

        $categories = Database::fetchAll("SELECT id FROM portfolio_categories");
        $catIds = array_column($categories, 'id');

        $achievements = ['school', 'district', 'province', 'national', 'international'];
        $targets = ['คณะแพทยศาสตร์', 'คณะวิศวกรรมศาสตร์', 'คณะสาธารณสุขศาสตร์', 'คณะวิทยาศาสตร์', 'คณะมนุษยศาสตร์', 'คณะนิติศาสตร์', 'คณะสถาปัตยกรรมศาสตร์', 'คณะดนตรีและการแสดง', 'วิทยาลัยการคอมพิวเตอร์'];

        $count = 0;
        for ($i = 0; $i < 48; $i++) {
            $username = 'std' . (1000 + $i);
            $fullName = $firstNames[$i] . ' ' . $lastNames[$i];
            $password = password_hash('12345', PASSWORD_BCRYPT);
            $role = 'student';
            $email = $username . '@pccks-mock.ac.th';

            // Create User
            $userId = Database::insert(
                "INSERT INTO users (username, password, full_name, role, email, is_active) VALUES (?, ?, ?, ?, ?, 1)",
                [$username, $password, $fullName, $role, $email]
            );

            if ($userId) {
                // Create Profile
                $studentCode = 'M64' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
                $classYear = rand(4, 6);
                $classRoom = rand(1, 4);
                
                $profileId = Database::insert(
                    "INSERT INTO student_profiles (user_id, full_name, student_code, class_year, class_room, bio) VALUES (?, ?, ?, ?, ?, ?)",
                    [$userId, $fullName, $studentCode, $classYear, $classRoom, 'ฉันเป็นนักเรียนที่ตั้งใจเรียนและชอบทำกิจกรรมด้าน ' . $targets[array_rand($targets)]]
                );

                // Add Portfolio Items (Realistically varied)
                $portfolioCount = ($classYear >= 6) ? rand(12, 20) : rand(8, 15);
                for ($j = 0; $j < $portfolioCount; $j++) {
                    $catId = $catIds[array_rand($catIds)];
                    $level = $achievements[array_rand($achievements)];
                    $isFeatured = rand(1, 10) > 8 ? 1 : 0; // 20% are featured
                    $award = rand(1, 5) > 3 ? 'รางวัลชนะเลิศอันดับ ' . rand(1, 3) : '';
                    
                    Database::insert(
                        "INSERT INTO portfolio_items (student_id, category_id, title, description, achievement_level, award, event_date, status, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?)",
                        [
                            $profileId, 
                            $catId, 
                            'ผลงานที่ ' . ($j + 1) . ' ของนักเรียนรหัส ' . $studentCode, 
                            'ผลงานที่สะท้อนให้เห็นถึงความมุ่งมั่นและความคิดสร้างสรรค์ทางวิชาการและวิจารณญาณที่นำไปสู่ผลลัพธ์ที่เป็นประโยชน์ต่อสังคมและโรงเรียน',
                            $level,
                            $award,
                            date('Y-m-d', strtotime('-' . rand(1, 500) . ' days')),
                            $isFeatured
                        ]
                    );
                }

                // Add Academic Records
                for ($m = 1; $m <= 8; $m++) {
                    Database::insert(
                        "INSERT INTO academic_records (student_id, semester, subject, grade, credit) VALUES (?, ?, ?, ?, ?)",
                        [$profileId, rand(1, 6), 'วิชาพื้นฐาน ' . $m, (rand(26, 40) / 10), rand(1, 2)]
                    );
                }

                // Add RIASEC Assessment (School DNA Data)
                $traits = ['R', 'I', 'A', 'S', 'E', 'C'];
                $traitNames = [
                    'R' => 'นักปฏิบัติ (Realistic)',
                    'I' => 'นักวิเคราะห์ (Investigative)',
                    'A' => 'นักสร้างสรรค์ (Artistic)',
                    'S' => 'นักสังคมสงเคราะห์ (Social)',
                    'E' => 'นักบริหาร (Enterprising)',
                    'C' => 'นักจัดการ (Conventional)'
                ];
                
                $scores = [];
                foreach($traits as $t) {
                    $scores[$t] = ($t === 'I' || $t === 'R') ? rand(5, 10) : rand(1, 7); // Higher for science students
                }
                
                asort($scores); // For finding top traits
                $topTypes = array_keys(array_slice($scores, -3, 3, true));
                $dominantTrait = implode('', array_reverse($topTypes));
                
                $traitsDetails = [];
                foreach(array_reverse($topTypes) as $t) {
                    $traitsDetails[] = $traitNames[$t];
                }

                $resultData = [
                    'scores' => $scores,
                    'top_traits' => $traitsDetails,
                    'holland_code' => $dominantTrait
                ];
                
                Database::insert(
                    "INSERT INTO student_assessments (user_id, assessment_type, dominant_trait, result_data) VALUES (?, ?, ?, ?)",
                    [$userId, 'riasec', $dominantTrait, json_encode($resultData, JSON_UNESCAPED_UNICODE)]
                );

                // Add Goals
                $goalTargets = [
                    ['target' => 'คณะแพทยศาสตร์ มหาวิทยาลัยขอนแก่น', 'type' => 'university'],
                    ['target' => 'คณะวิศวกรรมศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย', 'type' => 'university'],
                    ['target' => 'คณะวิทยาศาสตร์ มหาวิทยาลัยมหิดล', 'type' => 'university'],
                    ['target' => 'คณะเทคโนโลยีสารสนเทศ สจล.', 'type' => 'university'],
                    ['target' => 'คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่', 'type' => 'university'],
                ];
                $g = $goalTargets[array_rand($goalTargets)];
                
                Database::insert(
                    "INSERT INTO student_goals (student_id, goal_type, target, status) VALUES (?, ?, ?, 'inprogress')",
                    [$profileId, $g['type'], $g['target']]
                );

                $count++;
            }
        }

        Session::flash('success', "สำเร็จ! เนรมิตนักเรียนจำลอง {$count} คน พร้อมผลงานคละกันเรียบร้อย");
        App::redirect('admin');
    }

    /**
     * ล้างข้อมูลจำลองเพื่อคืนพื้นที่เซิร์ฟเวอร์
     */
    public function clearMockData(): void
    {
        CSRF::check();
        Auth::requireRole('admin');
        
        // Find all mock users
        $mockUsers = Database::fetchAll("SELECT id FROM users WHERE email LIKE '%@pccks-mock.ac.th'");
        $userIds = array_column($mockUsers, 'id');

        if (empty($userIds)) {
            Session::flash('info', 'ไม่พบข้อมูลจำลองที่ต้องลบ');
            App::redirect('admin');
            return;
        }

        $userIdStr = implode(',', $userIds);
        
        // Get profile IDs correctly
        $profiles = Database::fetchAll("SELECT id FROM student_profiles WHERE user_id IN ($userIdStr)");
        $profileIds = array_column($profiles, 'id');
        $profileIdStr = implode(',', $profileIds);

        if ($profileIdStr) {
            Database::execute("DELETE FROM student_assessments WHERE user_id IN ($userIdStr)");
            Database::execute("DELETE FROM portfolio_items WHERE student_id IN ($profileIdStr)");
            Database::execute("DELETE FROM academic_records WHERE student_id IN ($profileIdStr)");
            Database::execute("DELETE FROM student_goals WHERE student_id IN ($profileIdStr)");
            Database::execute("DELETE FROM student_profiles WHERE id IN ($profileIdStr)");
        }
        
        Database::execute("DELETE FROM users WHERE id IN ($userIdStr)");

        Session::flash('success', 'ล้างข้อมูลจำลองทั้งหมดเรียบร้อยแล้ว');
        App::redirect('admin');
    }
    /**
     * ระบบกู้คืนสิทธิ์แอดมินฉุกเฉิน — ปิดใช้งานถาวรด้วยเหตุผลด้านความปลอดภัย
     * (เดิมเป็น Public Route ที่ใช้รหัสฮาร์ดโค้ดยกระดับสิทธิ์เป็น admin ได้)
     * กู้คืนสิทธิ์ผ่าน CLI แทน: php scripts/maintenance/fix_admin.php
     */
    public function rescueForm(): void
    {
        ErrorHandler::notFound();
    }

    public function rescueProcess(): void
    {
        ErrorHandler::notFound();
    }

    /**
     * Database Doctor — เครื่องมือตรวจสอบและปรับปรุงฐานข้อมูลอัตโนมัติ
     */
    public function dbDoctor(): void
    {
        require_once ROOT_PATH . '/public/db_doctor.php';
        exit;
    }
}

