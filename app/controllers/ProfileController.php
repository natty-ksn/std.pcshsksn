<?php
/**
 * ProfileController — จัดการโปรไฟล์นักเรียน
 */
class ProfileController extends Controller
{
    /**
     * แสดงโปรไฟล์
     */
    public function show(): void
    {
        $userId = Auth::id();
        $profile = Database::fetch(
            "SELECT sp.*, u.email, u.username, u.role, u.position, u.created_at as joined_at, u.last_login
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE sp.user_id = ?",
            [$userId]
        );

        if (!$profile) {
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

            if ($user && $user['role'] === 'student') {
                Database::execute(
                    "INSERT INTO student_profiles (user_id, full_name, student_code) VALUES (?, ?, ?)",
                    [$userId, $user['full_name'] ?: $user['username'], $user['username']]
                );
                App::redirect('profile/edit');
                return;
            }

            // สำหรับครู ผู้บริหาร และผู้ดูแลระบบ ไม่สร้างแถวใน student_profiles เพื่อไม่ให้ข้อมูลนักเรียนปนเปื้อน
            $profile = [
                'id' => 0,
                'user_id' => $userId,
                'full_name' => $user['full_name'] ?: ($user['username'] ?? Auth::name()),
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? Auth::role(),
                'position' => $user['position'] ?? '',
                'student_code' => $user['username'] ?? '',
                'class_year' => 0,
                'class_room' => '',
                'nickname' => '',
                'date_of_birth' => null,
                'phone' => '',
                'bio' => '',
                'avatar_path' => Session::get('user_avatar'),
                'joined_at' => $user['created_at'] ?? date('Y-m-d H:i:s'),
                'last_login' => $user['last_login'] ?? null,
            ];
        }

        // Portfolio stats
        $portfolioCount = $profile['id'] ? (Database::fetch(
            "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND status = 'published'",
            [$profile['id']]
        )['count'] ?? 0) : 0;

        // GPA
        $gpa = $profile['id'] ? (Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa FROM academic_records WHERE student_id = ?",
            [$profile['id']]
        )['gpa'] ?? 0) : 0;

        // Interests
        $interests = $profile['id'] ? Database::fetchAll(
            "SELECT * FROM student_interests WHERE student_id = ? ORDER BY type, proficiency DESC",
            [$profile['id']]
        ) : [];

        // Goals
        $goals = $profile['id'] ? Database::fetchAll(
            "SELECT * FROM student_goals WHERE student_id = ? ORDER BY status, deadline",
            [$profile['id']]
        ) : [];

        // Badges
        $badges = Database::fetchAll(
            "SELECT bm.* FROM user_badges ub JOIN badges_master bm ON ub.badge_code = bm.code WHERE ub.user_id = ?",
            [$userId]
        );

        // Profile completeness
        $completeness = ($profile['role'] ?? 'student') === 'student'
            ? $this->calculateCompleteness($profile, $portfolioCount, count($interests), count($goals))
            : 100;

        $this->view('profile.show', [
            'profile' => $profile,
            'portfolioCount' => $portfolioCount,
            'gpa' => $gpa,
            'interests' => $interests,
            'goals' => $goals,
            'badges' => $badges,
            'completeness' => $completeness,
        ]);
    }

    /**
     * ฟอร์มแก้ไขโปรไฟล์
     */
    public function edit(): void
    {
        $userId = Auth::id();
        $profile = Database::fetch(
            "SELECT sp.*, u.role, u.position, u.email, u.username
             FROM student_profiles sp 
             JOIN users u ON sp.user_id = u.id 
             WHERE sp.user_id = ?",
            [$userId]
        );

        if (!$profile) {
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            $profile = [
                'id' => 0,
                'user_id' => $userId,
                'full_name' => $user['full_name'] ?? '',
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? Auth::role(),
                'position' => $user['position'] ?? '',
                'student_code' => $user['username'] ?? '',
                'class_year' => 4,
                'class_room' => '1',
                'nickname' => '',
                'date_of_birth' => null,
                'phone' => '',
                'bio' => '',
                'avatar_path' => Session::get('user_avatar'),
            ];
        }

        $this->view('profile.edit', [
            'profile' => $profile,
        ]);
    }

    /**
     * อัพเดทโปรไฟล์
     */
    public function update(): void
    {
        CSRF::check();
        $userId = Auth::id();
        $user = Auth::user();

        $data = [
            'full_name' => $this->input('full_name'),
            'nickname' => $this->input('nickname'),
            'class_year' => (int) $this->input('class_year'),
            'class_room' => $this->input('class_room'),
            'date_of_birth' => $this->input('date_of_birth') ?: null,
            'phone' => $this->input('phone'),
            'bio' => $this->rawInput('bio'),
        ];

        $position = $this->input('position');
        $newUsername = $this->input('username');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');

        // Validate
        if (empty($data['full_name'])) {
            Session::flash('error', 'กรุณากรอกชื่อ-นามสกุล');
            App::redirect('profile/edit');
            return;
        }

        // Check Username Uniqueness if changed
        if ($newUsername !== $user['username']) {
            $taken = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$newUsername, $userId]);
            if ($taken) {
                Session::flash('error', 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาเลือกชื่ออื่น');
                App::redirect('profile/edit');
                return;
            }
        }

        // Password logic
        $hashedNewPassword = null;
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                Session::flash('error', 'การยืนยันรหัสผ่านไม่ตรงกัน');
                App::redirect('profile/edit');
                return;
            }
            if (strlen($newPassword) < 6) {
                Session::flash('error', 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                App::redirect('profile/edit');
                return;
            }
            $hashedNewPassword = Auth::hashPassword($newPassword);
        }

        $existing = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);

        try {
            Database::beginTransaction();

            if ($user['role'] === 'student') {
                if ($existing) {
                    Database::execute(
                        "UPDATE student_profiles SET full_name=?, nickname=?, class_year=?, class_room=?, date_of_birth=?, phone=?, bio=? WHERE user_id=?",
                        [...array_values($data), $userId]
                    );
                } else {
                    $data['user_id'] = $userId;
                    $data['student_code'] = $newUsername ?: $user['username'];
                    Database::insert(
                        "INSERT INTO student_profiles (user_id, student_code, full_name, nickname, class_year, class_room, date_of_birth, phone, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$userId, $data['student_code'], $data['full_name'], $data['nickname'], $data['class_year'], $data['class_room'], $data['date_of_birth'], $data['phone'], $data['bio']]
                    );
                }
            }

            // Update user credentials & professional info
            $updateSql = "UPDATE users SET username = ?, full_name = ?";
            $updateParams = [$newUsername, $data['full_name']];

            if ($user['role'] !== 'student' && !empty($position)) {
                $updateSql .= ", position = ?";
                $updateParams[] = $position;
                Session::set('user_position', $position);
            }

            if ($hashedNewPassword) {
                $updateSql .= ", password = ?";
                $updateParams[] = $hashedNewPassword;
            }

            $updateSql .= " WHERE id = ?";
            $updateParams[] = $userId;

            Database::execute($updateSql, $updateParams);

            Database::commit();

            // Update session
            Session::set('user_name', $data['full_name']);
            Session::set('user_username', $newUsername);
            if ($user['role'] === 'student') {
                Session::set('class_year', $data['class_year']);
                Session::set('class_room', $data['class_room']);
            }

            Auth::logAction('update_profile_security', 'user', $userId);

            // Check if profile is now complete (100%) and award EXP (for students only)
            $updatedProfile = Database::fetch(
                "SELECT * FROM student_profiles WHERE user_id = ?",
                [$userId]
            );

            if ($updatedProfile && $user['role'] === 'student') {
                $profileId = (int) $updatedProfile['id'];
                $portfolioCount = Database::fetch(
                    "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND status = 'published'",
                    [$profileId]
                )['count'] ?? 0;

                $interests = Database::fetchAll(
                    "SELECT * FROM student_interests WHERE student_id = ?",
                    [$profileId]
                );

                $goals = Database::fetchAll(
                    "SELECT * FROM student_goals WHERE student_id = ?",
                    [$profileId]
                );

                $completeness = $this->calculateCompleteness(
                    $updatedProfile,
                    $portfolioCount,
                    count($interests),
                    count($goals)
                );

                if ($completeness >= 100) {
                    $wasCompleteBefore = Session::get('profile_was_complete', false);

                    if (!$wasCompleteBefore) {
                        EXPService::award($userId, 'profile_completed');
                        Session::set('profile_was_complete', true);
                        Session::flash('success', 'อัพเดทโปรไฟล์และข้อมูลเรียบร้อยแล้ว 🎉 โปรไฟล์สมบูรณ์ 100%!');
                    } else {
                        Session::flash('success', 'อัพเดทโปรไฟล์และข้อมูลเรียบร้อยแล้ว');
                    }
                } else {
                    Session::flash('success', 'อัพเดทโปรไฟล์และข้อมูลบัญชีเรียบร้อยแล้ว');
                }
            } else {
                Session::flash('success', 'อัพเดทโปรไฟล์และข้อมูลบัญชีเรียบร้อยแล้ว');
            }

            App::redirect('profile');

        } catch (Exception $e) {
            Database::rollback();
            Session::flash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            App::redirect('profile/edit');
        }
    }

    /**
     * อัพโหลด Avatar
     */
    public function updateAvatar(): void
    {
        CSRF::check();
        $fileData = $this->uploadFile('avatar', 'avatars');

        if (!$fileData) {
            Session::flash('error', 'ไม่สามารถอัพโหลดรูปได้');
            App::redirect('profile/edit');
            return;
        }

        Database::execute(
            "UPDATE student_profiles SET avatar_path = ? WHERE user_id = ?",
            [$fileData['file_path'], Auth::id()]
        );

        Session::set('user_avatar', $fileData['file_path']);
        Session::flash('success', 'อัพเดทรูปโปรไฟล์เรียบร้อย');
        App::redirect('profile');
    }

    /**
     * คำนวณ % ความสมบูรณ์ของโปรไฟล์
     */
    private function calculateCompleteness(array $profile, int $works, int $interests, int $goals): int
    {
        $score = 0;
        $total = 10;

        if (!empty($profile['full_name']))
            $score++;
        if (!empty($profile['nickname']))
            $score++;
        if (!empty($profile['date_of_birth']))
            $score++;
        if (!empty($profile['phone']))
            $score++;
        if (!empty($profile['bio']))
            $score++;
        if (!empty($profile['avatar_path']))
            $score++;
        if ($works > 0)
            $score++;
        if ($works >= 5)
            $score++;
        if ($interests > 0)
            $score++;
        if ($goals > 0)
            $score++;

        return (int) round(($score / $total) * 100);
    }

    /**
     * Student 360° Profile — ข้อมูลนักเรียนครบในหน้าเดียว
     */
    public function show360(): void
    {
        $userId = Auth::id();
        $profile360 = StudentService::getStudent360($userId);

        if (!$profile360 || empty($profile360['student_code'])) {
            Session::flash('error', 'กรุณาสร้างโปรไฟล์ก่อน');
            App::redirect('profile/edit');
            return;
        }

        $this->view('profile/360', [
            'title' => '🎓 Student 360° Profile | ' . $profile360['full_name'],
            'profile' => $profile360,
        ]);
    }
}
