<?php
/**
 * PortfolioController — จัดการผลงาน CRUD
 */
class PortfolioController extends Controller
{
    /**
     * รายการผลงานทั้งหมด
     */
    public function index(): void
    {
        $userId = Auth::id();
        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        if (!$profile) {
            Session::flash('error', 'กรุณาสร้างโปรไฟล์ก่อน');
            App::redirect('profile/edit');
            return;
        }

        $category = $this->query('category');
        $search = $this->query('search');
        $page = max(1, (int) $this->query('page', '1'));

        $where = "(pi.student_id = ? OR (pcol.student_id = ? AND pcol.acceptance_status = 'accepted')) AND pi.status = 'published'";
        $params = [$profile['id'], $profile['id']];

        if ($category) {
            $where .= " AND pi.category_id = ?";
            $params[] = $category;
        }

        if ($search) {
            $where .= " AND (pi.title LIKE ? OR pi.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $total = Database::fetch(
            "SELECT COUNT(DISTINCT pi.id) as count 
             FROM portfolio_items pi 
             LEFT JOIN portfolio_collaborators pcol ON pi.id = pcol.portfolio_id
             WHERE {$where}",
            $params
        )['count'] ?? 0;

        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, pc.color as category_color,
                    sp_owner.full_name as full_name, sp_owner.full_name as owner_name, sp_owner.student_code as owner_code,
                    sp_owner.class_year, sp_owner.class_room,
                    GROUP_CONCAT(DISTINCT sm.skill_name SEPARATOR ', ') as skillset
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             LEFT JOIN portfolio_collaborators pcol ON pi.id = pcol.portfolio_id
             JOIN student_profiles sp_owner ON pi.student_id = sp_owner.id
             LEFT JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             LEFT JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE {$where}
             GROUP BY pi.id
             ORDER BY pi.is_featured DESC, pi.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $categories = Database::fetchAll(
            "SELECT * FROM portfolio_categories WHERE is_active = 1 ORDER BY sort_order"
        );

        $this->view('portfolio.index', [
            'works' => $works,
            'categories' => $categories,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int) ceil($total / $perPage),
            'search' => $search,
            'category' => $category,
            'profile' => $profile,
        ]);
    }

    /**
     * ฟอร์มสร้างผลงานใหม่
     */
    public function create(): void
    {
        $categories = Database::fetchAll(
            "SELECT * FROM portfolio_categories WHERE is_active = 1 ORDER BY sort_order"
        );
        $skills = Database::fetchAll(
            "SELECT sm.*, sc.name as category_name FROM skills_master sm 
             JOIN skill_categories sc ON sm.category_id = sc.id 
             WHERE sm.is_approved = 1 ORDER BY sc.id, sm.skill_name"
        );

        $this->view('portfolio.create', [
            'categories' => $categories,
            'skills' => $skills,
        ]);
    }

    /**
     * บันทึกผลงานใหม่
     */
    public function store(): void
    {
        CSRF::check();

        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [Auth::id()]
        );

        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        // Validate
        $errors = $this->validate([
            'title' => 'required|max:200',
            'category_id' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            Session::flashInput($_POST);
            Session::flash('error', implode(', ', $errors));
            App::redirect('portfolio/create');
            return;
        }

        // Upload file
        $fileData = $this->uploadFile('file', 'portfolio');

        // Tags
        $tags = $this->input('tags');
        $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];

        // Check if external_link column exists in database schema
        static $hasExtLink = null;
        if ($hasExtLink === null) {
            try {
                $chk = Database::fetch("SHOW COLUMNS FROM portfolio_items LIKE 'external_link'");
                $hasExtLink = !empty($chk);
            } catch (\Throwable $e) {
                $hasExtLink = false;
            }
        }

        $cols = ['student_id', 'category_id', 'title', 'description', 'achievement_level', 'award', 'event_date', 'file_path', 'thumbnail', 'file_size', 'tags', 'status'];
        $vals = [
            $profile['id'],
            (int) $this->input('category_id'),
            $this->input('title'),
            $this->rawInput('description'),
            $this->input('achievement_level') ?: null,
            $this->input('award') ?: null,
            $this->input('event_date') ?: null,
            $fileData['file_path'] ?? null,
            $fileData['thumbnail'] ?? null,
            $fileData['file_size'] ?? 0,
            json_encode($tagsArray, JSON_UNESCAPED_UNICODE),
            'published'
        ];

        if ($hasExtLink) {
            $cols[] = 'external_link';
            $vals[] = $this->input('external_link') ?: null;
        }

        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $portfolioId = Database::insert(
            "INSERT INTO portfolio_items (" . implode(', ', $cols) . ") VALUES ({$placeholders})",
            $vals
        );

        // Save skills
        $selectedSkills = $_POST['skills'] ?? [];
        foreach ($selectedSkills as $skillId) {
            Database::insert(
                "INSERT IGNORE INTO portfolio_skills (portfolio_id, skill_id, proficiency) VALUES (?, ?, 3)",
                [$portfolioId, (int) $skillId]
            );
        }

        // Save Teammates
        $teammateIds = $_POST['teammates'] ?? [];
        foreach ($teammateIds as $tId) {
            $tProfile = Database::fetch(
                "SELECT id, user_id FROM student_profiles WHERE user_id = ? OR id = ? LIMIT 1",
                [(int) $tId, (int) $tId]
            );
            if (!$tProfile || (int) $tProfile['id'] === (int) $profile['id']) {
                continue;
            }
            Database::insert(
                "INSERT IGNORE INTO portfolio_collaborators (portfolio_id, student_id, acceptance_status) VALUES (?, ?, 'pending')",
                [$portfolioId, (int) $tProfile['id']]
            );

            // Notify teammate (using users.id)
            Notifier::send(
                (int) $tProfile['user_id'],
                'portfolio.invitation',
                'คุณถูกเชิญร่วมผลงานกลุ่ม',
                "{$profile['full_name']} ได้เพิ่มคุณเป็นผู้ร่วมจัดทำในผลงาน: {$this->input('title')}",
                "portfolio/{$portfolioId}"
            );
        }

        // Log
        Auth::logAction('create_portfolio', 'portfolio', $portfolioId);

        // Award EXP
        EXPService::award(Auth::id(), 'portfolio_created');
        if (!empty($this->input('award'))) {
            EXPService::award(Auth::id(), 'award_added');
        }

        // Check for badges
        try {
            $this->checkBadges($profile['id']);
        } catch (\Throwable $e) {
            App::log("Legacy checkBadges error: " . $e->getMessage(), 'WARNING');
        }

        // Phase 5: Dynamic Gamification — event-driven badge check
        try {
            $newBadgeResult = BadgeService::onEvent(Auth::id(), 'portfolio_created');
        } catch (\Throwable $e) {
            App::log("Badge check failed after portfolio store: " . $e->getMessage(), 'WARNING');
            $newBadgeResult = ['new_badges' => []];
        }

        // Additional badge events based on content
        try {
            if (!empty($this->input('award'))) {
                $awardResult = BadgeService::onEvent(Auth::id(), 'award_added');
                $newBadgeResult['new_badges'] = array_merge(
                    $newBadgeResult['new_badges'] ?? [],
                    $awardResult['new_badges'] ?? []
                );
                $newBadgeResult['total'] = ($newBadgeResult['total'] ?? 0) + ($awardResult['total'] ?? 0);
            }
            // Check category diversity
            BadgeService::onEvent(Auth::id(), 'category_added');
        } catch (\Throwable $e) {
            App::log("Badge check on portfolio extras failed: " . $e->getMessage(), 'WARNING');
        }

        // Trigger cache invalidation event
        \Core\EventDispatcher::dispatch('portfolio.created', $profile['id']);

        if ($newBadgeResult['total'] > 0) {
            $badgeNames = implode(', ', array_map(fn($b) => $b['icon'] . ' ' . $b['name'], $newBadgeResult['new_badges']));
            Session::flash('success', 'เพิ่มผลงานเรียบร้อยแล้ว! 🎉 เหรียญใหม่: ' . $badgeNames);
        } else {
            Session::flash('success', 'เพิ่มผลงานเรียบร้อยแล้ว! 🎉');
        }
        App::redirect('portfolio');
    }

    /**
     * แสดงรายละเอียดผลงาน
     */
    public function show(string $id): void
    {
        $work = Database::fetch(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, pc.color as category_color,
                    sp.full_name, sp.student_code, sp.class_year, sp.class_room
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             JOIN student_profiles sp ON pi.student_id = sp.id
             WHERE pi.id = ?",
            [(int) $id]
        );

        if (!$work) {
            Session::flash('error', 'ไม่พบผลงานที่ต้องการ');
            App::redirect('portfolio');
            return;
        }

        // เพิ่ม View count
        Database::execute("UPDATE portfolio_items SET view_count = view_count + 1 WHERE id = ?", [(int) $id]);

        // Skills ที่เกี่ยวข้อง
        $skills = Database::fetchAll(
            "SELECT sm.skill_name, ps.proficiency FROM portfolio_skills ps 
             JOIN skills_master sm ON ps.skill_id = sm.id WHERE ps.portfolio_id = ?",
            [(int) $id]
        );

        // Comments จากครู
        $comments = Database::fetchAll(
            "SELECT tc.*, COALESCE(u.full_name, u.username) as teacher_name FROM teacher_comments tc 
             JOIN users u ON tc.teacher_id = u.id 
             WHERE tc.portfolio_id = ? ORDER BY tc.created_at DESC",
            [(int) $id]
        );

        // Collaborators
        $collaborators = Database::fetchAll(
            "SELECT pc.*, sp.full_name, sp.user_id as student_user_id FROM portfolio_collaborators pc
             JOIN student_profiles sp ON pc.student_id = sp.id
             WHERE pc.portfolio_id = ?",
            [(int) $id]
        );

        // Annotations เฉพาะจุดจากครู (Annotated Mentorship Feedback)
        $annotations = [];
        try {
            $annotations = Database::fetchAll(
                "SELECT pa.*, COALESCE(u.full_name, u.username) as teacher_name
                 FROM portfolio_annotations pa
                 JOIN users u ON pa.teacher_id = u.id
                 WHERE pa.portfolio_id = ?
                 ORDER BY pa.is_resolved ASC, FIELD(pa.severity,'critical','issue','suggestion','info'), pa.created_at DESC",
                [(int) $id]
            );
        } catch (\Throwable $e) {
            // ตารางยังไม่ถูกสร้าง — ปล่อยเป็นค่าว่าง
            $annotations = [];
        }

        $this->view('portfolio.show', [
            'work' => $work,
            'skills' => $skills,
            'comments' => $comments,
            'collaborators' => $collaborators,
            'annotations' => $annotations,
        ]);
    }

    /**
     * ฟอร์มแก้ไขผลงาน
     */
    public function edit(string $id): void
    {
        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [Auth::id()]
        );

        $work = Database::fetch(
            "SELECT * FROM portfolio_items WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if (!$work) {
            Session::flash('error', 'ไม่พบผลงาน หรือคุณไม่มีสิทธิ์แก้ไข');
            App::redirect('portfolio');
            return;
        }

        $categories = Database::fetchAll(
            "SELECT * FROM portfolio_categories WHERE is_active = 1 ORDER BY sort_order"
        );

        $skills = Database::fetchAll(
            "SELECT sm.*, sc.name as category_name
             FROM skills_master sm
             JOIN skill_categories sc ON sm.category_id = sc.id
             WHERE sm.is_approved = 1
             ORDER BY sc.id, sm.skill_name"
        );

        $selectedSkills = Database::fetchAll(
            "SELECT skill_id FROM portfolio_skills WHERE portfolio_id = ?",
            [(int) $id]
        );
        $selectedSkillIds = array_column($selectedSkills, 'skill_id');

        $this->view('portfolio.edit', [
            'work' => $work,
            'categories' => $categories,
            'skills' => $skills,
            'selectedSkillIds' => $selectedSkillIds,
        ]);
    }

    /**
     * อัพเดทผลงาน
     */
    public function update(string $id): void
    {
        CSRF::check();

        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [Auth::id()]
        );

        $work = Database::fetch(
            "SELECT * FROM portfolio_items WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if (!$work) {
            Session::flash('error', 'ไม่พบผลงาน');
            App::redirect('portfolio');
            return;
        }

        // Check if external_link column exists
        static $hasExtLinkUpdate = null;
        if ($hasExtLinkUpdate === null) {
            try {
                $chk = Database::fetch("SHOW COLUMNS FROM portfolio_items LIKE 'external_link'");
                $hasExtLinkUpdate = !empty($chk);
            } catch (\Throwable $e) {
                $hasExtLinkUpdate = false;
            }
        }

        $updateData = [
            'title' => $this->input('title'),
            'category_id' => (int) $this->input('category_id'),
            'description' => $this->rawInput('description'),
            'achievement_level' => $this->input('achievement_level') ?: null,
            'award' => $this->input('award') ?: null,
            'event_date' => $this->input('event_date') ?: null,
        ];

        if ($hasExtLinkUpdate) {
            $updateData['external_link'] = $this->input('external_link') ?: null;
        }

        // Upload new file if provided
        $fileData = $this->uploadFile('file', 'portfolio');
        if ($fileData) {
            $updateData['file_path'] = $fileData['file_path'];
            $updateData['thumbnail'] = $fileData['thumbnail'];
            $updateData['file_size'] = $fileData['file_size'];
        }

        // Tags
        $tags = $this->input('tags');
        $updateData['tags'] = json_encode(
            $tags ? array_map('trim', explode(',', $tags)) : [],
            JSON_UNESCAPED_UNICODE
        );

        $setClauses = [];
        $values = [];
        foreach ($updateData as $key => $val) {
            $setClauses[] = "{$key} = ?";
            $values[] = $val;
        }
        $values[] = (int) $id;

        Database::execute(
            "UPDATE portfolio_items SET " . implode(', ', $setClauses) . " WHERE id = ?",
            $values
        );

        // Sync skills
        if (isset($_POST['skills'])) {
            Database::execute("DELETE FROM portfolio_skills WHERE portfolio_id = ?", [(int) $id]);
            $skillIds = (array) $_POST['skills'];
            foreach ($skillIds as $sId) {
                if (is_numeric($sId)) {
                    Database::insert(
                        "INSERT INTO portfolio_skills (portfolio_id, skill_id) VALUES (?, ?)",
                        [(int) $id, (int) $sId]
                    );
                }
            }
        }

        Auth::logAction('update_portfolio', 'portfolio', (int) $id, $work, $updateData);
        // Dispatch event for cache invalidation
        \Core\EventDispatcher::dispatch('portfolio.updated', $profile['id']);

        Session::flash('success', 'อัพเดทผลงานเรียบร้อยแล้ว');
        App::redirect('portfolio/' . $id);
    }

    /**
     * ลบผลงาน
     */
    public function delete(string $id): void
    {
        CSRF::check();

        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [Auth::id()]
        );

        $deleted = Database::execute(
            "UPDATE portfolio_items SET status = 'archived' WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if ($deleted) {
            Auth::logAction('archive_portfolio', 'portfolio', (int) $id);
            \Core\EventDispatcher::dispatch('portfolio.deleted', $profile['id']);
            Session::flash('success', 'ลบผลงานเรียบร้อยแล้ว');
        }
        App::redirect('portfolio');
    }

    /**
     * Toggle ปักหมุดผลงานเด่น
     */
    public function toggleFeatured(string $id): void
    {
        CSRF::check();

        $profile = Database::fetch(
            "SELECT * FROM student_profiles WHERE user_id = ?",
            [Auth::id()]
        );

        // Check current featured status before toggle
        $currentStatus = Database::fetch(
            "SELECT is_featured FROM portfolio_items WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        Database::execute(
            "UPDATE portfolio_items SET is_featured = NOT is_featured WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        // Award EXP when setting to featured (not when unsetting)
        if (!$currentStatus || !$currentStatus['is_featured']) {
            EXPService::award(Auth::id(), 'portfolio_featured');

            // Trigger badge check for featured portfolio
            try {
                BadgeService::onEvent(Auth::id(), 'portfolio_featured');
            } catch (\Throwable $e) {
                App::log("Badge check on portfolio_featured failed: " . $e->getMessage(), 'WARNING');
            }

            \Core\EventDispatcher::dispatch('portfolio.featured', $profile['id']);
        }

        App::redirect('portfolio/' . $id);
    }

    /**
     * ตรวจสอบและให้ Badge (Legacy Safe Check)
     */
    private function checkBadges(int $studentId): void
    {
        try {
            $userId = Auth::id();
            $count = Database::fetch(
                "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND status = 'published'",
                [$studentId]
            )['count'] ?? 0;

            // ตรวจสอบกับรหัส Badge ที่มีจริงใน badges_master
            $badgeChecks = [
                ['code' => 'first_portfolio', 'threshold' => 1],
                ['code' => 'rising_star', 'threshold' => 5],
            ];

            foreach ($badgeChecks as $badge) {
                if ($count >= $badge['threshold']) {
                    // ตรวจสอบก่อนว่ามี code นี้ใน badges_master จริง ป้องกัน Foreign Key Constraint Fail
                    $masterExists = Database::fetch(
                        "SELECT code FROM badges_master WHERE code = ?",
                        [$badge['code']]
                    );
                    if (!$masterExists) {
                        continue;
                    }

                    // Check if this is a new badge (not already earned)
                    $existing = Database::fetch(
                        "SELECT id FROM user_badges WHERE user_id = ? AND badge_code = ?",
                        [$userId, $badge['code']]
                    );

                    if (!$existing) {
                        Database::query(
                            "INSERT INTO user_badges (user_id, badge_code) VALUES (?, ?)",
                            [$userId, $badge['code']]
                        );
                        // Send notification via Notifier
                        Notifier::badgeAwarded($userId, $badge['code']);
                    }
                }
            }
        } catch (\Throwable $e) {
            App::log("checkBadges failed safely: " . $e->getMessage(), 'WARNING');
        }
    }
    /**
     * Export Portfolio Digest PDF (Phase 3 — Formal Excellence Engine)
     *
     * ออกรายงานแฟ้มสะสมงานรวมเล่มในรูปแบบ PDF ราชการ (ฟอนต์ Sarabun)
     * รวมข้อมูลส่วนตัว, สรุปทักษะ, Badges, และผลงานที่ผ่านการรับรอง (Verified)
     */
    public function exportDigest(): void
    {
        if (!Auth::check()) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        $userId = Auth::id();
        $studentIdParam = $this->query('student_id');
        $targetStudentId = ($studentIdParam && $studentIdParam !== '') ? (int) $studentIdParam : $userId;

        // ครู/ผู้ดูแล สามารถดูพอร์ตโฟลิโอของนักเรียนคนอื่นได้
        if ($targetStudentId !== $userId && !Auth::hasAnyRole(['admin', 'teacher', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์ดูพอร์ตโฟลิโอของนักเรียนคนอื่น');
            return;
        }

        // ===== 1. Student Profile =====
        $profile = Database::fetch(
            "SELECT sp.*, u.username, u.email
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE sp.user_id = ?",
            [$targetStudentId]
        );

        if (!$profile) {
            Session::flash('error', 'ไม่พบโปรไฟล์นักเรียน');
            App::redirect('portfolio');
            return;
        }

        // ===== 2. Verified Portfolio Items =====
        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, pc.color as category_color,
                    GROUP_CONCAT(sm.skill_name SEPARATOR ', ') as skill_list
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             LEFT JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             LEFT JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE pi.student_id = ? AND pi.status = 'published'
               AND (pi.is_verified = 1 OR pi.achievement_level IS NOT NULL)
             GROUP BY pi.id
             ORDER BY
                 CASE pi.achievement_level
                     WHEN 'international' THEN 1
                     WHEN 'national' THEN 2
                     WHEN 'province' THEN 3
                     WHEN 'district' THEN 4
                     WHEN 'school' THEN 5
                     ELSE 6
                 END,
                 pi.created_at DESC",
            [(int) $profile['id']]
        );

        // ===== 3. Skills Summary =====
        $skills = Database::fetchAll(
            "SELECT sm.skill_name, AVG(ps.proficiency) as avg_proficiency
             FROM portfolio_items pi
             JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE pi.student_id = ? AND pi.status = 'published'
               AND (pi.is_verified = 1 OR pi.achievement_level IS NOT NULL)
             GROUP BY sm.id
             ORDER BY avg_proficiency DESC",
            [(int) $profile['id']]
        );

        $skillsMap = [];
        foreach ($skills as $s) {
            $skillsMap[$s['skill_name']] = round((float) $s['avg_proficiency'], 1);
        }

        // ===== 4. Badges =====
        $badges = Database::fetchAll(
            "SELECT ub.*, bm.name, bm.icon, bm.description, ub.earned_at
             FROM user_badges ub
             JOIN badges_master bm ON ub.badge_code = bm.code
             WHERE ub.user_id = ?
             ORDER BY ub.earned_at DESC",
            [$targetStudentId]
        );

        // ===== 5. Stats =====
        $statsRaw = Database::fetch(
            "SELECT
                COUNT(*) as total_works,
                COALESCE(SUM(points_awarded), 0) as total_points,
                MAX(achievement_level) as top_level,
                COUNT(DISTINCT category_id) as category_count
             FROM portfolio_items
             WHERE student_id = ? AND status = 'published'
               AND (is_verified = 1 OR achievement_level IS NOT NULL)",
            [(int) $profile['id']]
        );

        $stats = [
            'total_works' => (int) ($statsRaw['total_works'] ?? 0),
            'total_points' => (int) ($statsRaw['total_points'] ?? 0),
            'top_level' => $statsRaw['top_level'] ?? 'school',
        ];

        // Category distribution
        $catCounts = Database::fetchAll(
            "SELECT pc.name, COUNT(pi.id) as count
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
               AND (pi.is_verified = 1 OR pi.achievement_level IS NOT NULL)
             GROUP BY pc.id
             ORDER BY count DESC",
            [(int) $profile['id']]
        );
        $stats['category_counts'] = [];
        foreach ($catCounts as $c) {
            $stats['category_counts'][$c['name'] ?? 'อื่นๆ'] = (int) $c['count'];
        }

        // ===== 6. Goals, Interests, Academic Records =====
        $goals = Database::fetchAll(
            "SELECT goal_type, target, target_details, deadline, status
             FROM student_goals
             WHERE student_id = ?
             ORDER BY FIELD(status,'achieved','inprogress','planning','cancelled'), created_at DESC",
            [(int) $profile['id']]
        );

        $interests = Database::fetchAll(
            "SELECT type, item, proficiency
             FROM student_interests
             WHERE student_id = ?
             ORDER BY type, proficiency DESC",
            [(int) $profile['id']]
        );

        $academics = Database::fetchAll(
            "SELECT academic_year, semester,
                    ROUND(AVG(grade), 2) as gpa,
                    COUNT(*) as subject_count
             FROM academic_records
             WHERE student_id = ?
             GROUP BY academic_year, semester
             ORDER BY academic_year DESC, semester DESC",
            [(int) $profile['id']]
        );

        // Avatar absolute path for mPDF
        $avatarAbs = null;
        if (!empty($profile['avatar_path'])) {
            $p = ROOT_PATH . '/uploads/' . ltrim($profile['avatar_path'], '/');
            if (file_exists($p)) $avatarAbs = $p;
        }

        // ===== 7. Render PDF =====
        PDFService::renderView('pdf.portfolio_digest', [
            'profile' => $profile,
            'works' => $works,
            'skills' => $skillsMap,
            'badges' => $badges,
            'stats' => $stats,
            'goals' => $goals,
            'interests' => $interests,
            'academics' => $academics,
            'avatarAbs' => $avatarAbs,
            'issuedAt' => PDFService::thaiDate(),
        ], [
            'title' => 'แฟ้มสะสมผลงาน — ' . ($profile['full_name'] ?? 'นักเรียน'),
            'filename' => 'Portfolio_' . ($profile['student_code'] ?? 'student') . '_' . date('Ymd'),
            'orientation' => 'P',
            'mode' => 'D', // Download by default; use 'I' for inline preview
        ]);
    }

    /**
     * ตอบรับคำเชิญร่วมผลงานกลุ่ม
     */
    public function acceptCollaboration(string $id): void
    {
        CSRF::check();
        $userId = Auth::id();
        $profile = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);
        $studentProfileId = (int) ($profile['id'] ?? 0);

        Database::execute(
            "UPDATE portfolio_collaborators SET acceptance_status = 'accepted', responded_at = NOW() 
             WHERE id = ? AND (student_id = ? OR student_id = ?)",
            [(int) $id, $studentProfileId, $userId]
        );

        $collab = Database::fetch("SELECT portfolio_id FROM portfolio_collaborators WHERE id = ?", [(int) $id]);
        Session::flash('success', 'คุณตอบรับการเป็นสมาชิกทีมเรียบร้อยแล้ว! ผลงานนี้จะแสดงใน Portfolio ของคุณครับ 🤝');
        App::redirect('portfolio/' . ($collab['portfolio_id'] ?? ''));
    }

    /**
     * ปฏิเสธคำเชิญร่วมผลงานกลุ่ม
     */
    public function rejectCollaboration(string $id): void
    {
        CSRF::check();
        $userId = Auth::id();
        $profile = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);
        $studentProfileId = (int) ($profile['id'] ?? 0);

        Database::execute(
            "UPDATE portfolio_collaborators SET acceptance_status = 'rejected', responded_at = NOW() 
             WHERE id = ? AND (student_id = ? OR student_id = ?)",
            [(int) $id, $studentProfileId, $userId]
        );

        Session::flash('info', 'คุณปฏิเสธคำเชิญเรียบร้อยแล้ว');
        App::redirect('portfolio');
    }
}
