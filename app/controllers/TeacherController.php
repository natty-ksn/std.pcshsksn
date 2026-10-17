<?php
/**
 * TeacherController — ระบบสำหรับครู
 */
class TeacherController extends Controller
{
    public function students(): void
    {
        $search = $this->query('search');
        $classYear = $this->query('class_year');
        $page = max(1, (int) $this->query('page', '1'));
        $perPage = 20;

        $where = "1=1";
        $params = [];

        if ($search) {
            $where .= " AND (sp.full_name LIKE ? OR sp.student_code LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($classYear) {
            $where .= " AND sp.class_year = ?";
            $params[] = (int) $classYear;
        }

        $total = Database::fetch(
            "SELECT COUNT(sp.id) as c 
             FROM student_profiles sp 
             JOIN users u ON sp.user_id = u.id 
             WHERE u.role = 'student' AND {$where}",
            $params
        )['c'] ?? 0;

        $offset = ($page - 1) * $perPage;

        $students = Database::fetchAll(
            "SELECT sp.*, u.username,
                    (SELECT COUNT(*) FROM portfolio_items pi WHERE pi.student_id = sp.id AND pi.status = 'published') as work_count,
                    (SELECT ROUND(SUM(grade*credit)/NULLIF(SUM(credit),0),2) FROM academic_records ar WHERE ar.student_id = sp.id) as gpa
             FROM student_profiles sp 
             JOIN users u ON sp.user_id = u.id
             WHERE u.role = 'student' AND {$where}
             ORDER BY sp.class_year, sp.class_room, sp.full_name
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->view('teacher.students', [
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int) ceil($total / $perPage),
            'search' => $search,
            'classYear' => $classYear,
        ]);
    }

    public function viewStudent(string $id): void
    {
        $idInt = (int) $id;
        $profile = Database::fetch(
            "SELECT sp.*, u.email, u.username FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE (sp.id = ? OR sp.user_id = ?) AND u.role = 'student' ORDER BY (sp.id = ?) DESC LIMIT 1",
            [$idInt, $idInt, $idInt]
        );
        if (!$profile) {
            Session::flash('error', 'ไม่พบนักเรียน');
            App::redirect('teacher/students');
            return;
        }

        $profileId = (int) $profile['id'];

        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon FROM portfolio_items pi LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id WHERE pi.student_id = ? AND pi.status = 'published' ORDER BY pi.created_at DESC",
            [$profileId]
        );

        $gpa = Database::fetch("SELECT ROUND(SUM(grade*credit)/NULLIF(SUM(credit),0),2) as gpa FROM academic_records WHERE student_id = ?", [$profileId])['gpa'] ?? 0;

        $this->view('teacher.view_student', ['profile' => $profile, 'works' => $works, 'gpa' => $gpa]);
    }

    public function addComment(): void
    {
        CSRF::check();
        $portfolioId = (int) $this->input('portfolio_id');
        $comment = $this->rawInput('comment');

        if (empty($comment)) {
            Session::flash('error', 'กรุณาเขียนความคิดเห็น');
            App::redirect('portfolio/' . $portfolioId);
            return;
        }

        Database::insert(
            "INSERT INTO teacher_comments (portfolio_id, teacher_id, comment) VALUES (?, ?, ?)",
            [$portfolioId, Auth::id(), $comment]
        );

        // Notify student using Notifier
        $portfolio = Database::fetch("SELECT pi.title, sp.user_id FROM portfolio_items pi JOIN student_profiles sp ON pi.student_id = sp.id WHERE pi.id = ?", [$portfolioId]);
        if ($portfolio) {
            Notifier::commentAdded($portfolioId, Auth::id(), (int) $portfolio['user_id']);

            // Award EXP to student for receiving comment
            EXPService::award((int) $portfolio['user_id'], 'comment_received');

            // Dispatch event for audit logging
            \Core\EventDispatcher::dispatch('teacher.comment_added', (int) $portfolio['user_id'], $portfolioId);
        }

        Session::flash('success', 'ส่งความคิดเห็นเรียบร้อย');
        App::redirect('portfolio/' . $portfolioId);
    }

    public function verifyPortfolio(string $id): void
    {
        CSRF::check();
        $id = (int) $id;

        $verificationStatus = $this->input('verification_status');
        $points = (int) $this->input('points_awarded', 0);
        $comment = $this->rawInput('comment');

        if (!in_array($verificationStatus, ['verified', 'rejected'])) {
            Session::flash('error', 'สถานะการตรวจทานไม่ถูกต้อง');
            App::redirect('portfolio/' . $id);
            return;
        }

        $isVerified = ($verificationStatus === 'verified') ? 1 : 0;
        Database::execute(
            "UPDATE portfolio_items SET verification_status = ?, is_verified = ?, verified_by = ?, verified_at = NOW(), points_awarded = ? WHERE id = ?",
            [$verificationStatus, $isVerified, Auth::id(), $points, $id]
        );

        if (!empty($comment)) {
            Database::insert(
                "INSERT INTO teacher_comments (portfolio_id, teacher_id, comment) VALUES (?, ?, ?)",
                [$id, Auth::id(), "[การตรวจรับรอง: {$verificationStatus}] " . $comment]
            );
        }

        // แจ้งเตือนนักเรียนและคำนวณแต้ม (Gamification Engine)
        $portfolio = Database::fetch("SELECT pi.title, pi.points_awarded, sp.user_id, u.exp, u.points 
                                     FROM portfolio_items pi 
                                     JOIN student_profiles sp ON pi.student_id = sp.id 
                                     JOIN users u ON sp.user_id = u.id
                                     WHERE pi.id = ?", [$id]);

        if ($portfolio && $verificationStatus === 'verified') {
            $studentUserId = (int) $portfolio['user_id'];
            $awardedPoints = (int) $portfolio['points_awarded'];

            if ($awardedPoints > 0) {
                Database::execute("UPDATE users SET points = points + ? WHERE id = ?", [$awardedPoints, $studentUserId]);
            }

            // Use EXPService for standardized EXP award
            $expResult = EXPService::award($studentUserId, 'portfolio_verified');

            // Trigger badge check for verified portfolio
            try {
                BadgeService::onEvent($studentUserId, 'portfolio_verified');
            } catch (\Throwable $e) {
                App::log("Badge check on portfolio verification failed: " . $e->getMessage(), 'WARNING');
            }

            // Notify Student
            $titleMsg = '✅ ผลงานได้รับการรับรอง';
            $msg = 'ยินดีด้วย! ผลงาน "' . $portfolio['title'] . '" ได้รับการรับรองโดยครู ' . Auth::name() .
                " คุณได้รับ +{$expResult['exp_gained']} EXP! 💎";

            Notifier::send(
                $studentUserId,
                'verification',
                $titleMsg,
                $msg,
                'portfolio/' . $id
            );
        } elseif ($portfolio && $verificationStatus === 'rejected') {
            $titleMsg = '❌ ผลงานถูกปฏิเสธการรับรอง';
            Notifier::send(
                (int) $portfolio['user_id'],
                'verification',
                $titleMsg,
                'ผลงาน "' . $portfolio['title'] . '" ไม่ผ่านการประเมิน กรุณาตรวจสอบข้อเสนอแนะและแก้ไขใหม่',
                'portfolio/' . $id
            );
        }

        Auth::logAction('verify_portfolio', 'portfolio', $id, null, ['status' => $verificationStatus, 'points' => $points]);

        // Dispatch cache invalidation event
        if ($verificationStatus === 'verified' && isset($studentUserId)) {
            \Core\EventDispatcher::dispatch('portfolio.verified', $studentUserId);
        } elseif ($verificationStatus === 'rejected' && $portfolio) {
            \Core\EventDispatcher::dispatch('portfolio.rejected', (int) $portfolio['user_id']);
        }

        Session::flash('success', 'ประเมินและตั้งรับรองผลงานเรียบร้อยแล้ว');
        App::redirect('portfolio/' . $id);
    }

    public function exportStudentToMarkdown(string $id): void
    {
        $idInt = (int) $id;
        $profile = Database::fetch(
            "SELECT sp.*, u.email FROM student_profiles sp JOIN users u ON sp.user_id = u.id WHERE (sp.id = ? OR sp.user_id = ?) AND u.role = 'student' ORDER BY (sp.id = ?) DESC LIMIT 1",
            [$idInt, $idInt, $idInt]
        );
        if (!$profile) {
            ErrorHandler::notFound();
            return;
        }

        $profileId = (int) $profile['id'];

        $works = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name FROM portfolio_items pi LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id WHERE pi.student_id = ? AND pi.status = 'published' ORDER BY pi.created_at DESC",
            [$profileId]
        );

        $gpaData = Database::fetch("SELECT ROUND(SUM(grade*credit)/NULLIF(SUM(credit),0),2) as gpa FROM academic_records WHERE student_id = ?", [$profileId]);
        $gpa = $gpaData['gpa'] ?? 0;

        $readiness = ReportService::calculateTCASReadiness((int) $profile['user_id']);

        $md = "# ข้อมูลประเมินผลนักเรียน (Student Profile for AI Analysis)\n\n";
        $md .= "## ข้อมูลทั่วไป\n";
        $md .= "- **ชื่อ-สกุล**: " . $profile['full_name'] . "\n";
        $md .= "- **รหัสนักเรียน**: " . $profile['student_code'] . "\n";
        $md .= "- **ระดับชั้น**: ม." . $profile['class_year'] . "/" . $profile['class_room'] . "\n";
        $md .= "- **GPA เฉลี่ยสะสม**: " . $gpa . "\n";
        $md .= "- **Bio/คำอธิบายตนเอง**: " . ($profile['bio'] ?: '-') . "\n\n";

        $md .= "## ผลสัมฤทธิ์เด่น (TCAS AI Score: " . ($readiness['overall_score'] ?? 'N/A') . "/100)\n";
        if (isset($readiness['components'])) {
            foreach ($readiness['components'] as $key => $r) {
                $md .= "- {$r['label']}: {$r['value']} (ระดับ: {$r['status']})\n";
            }
        }
        $md .= "\n";

        $md .= "## โครงงานและผลงาน (Portfolios)\n";
        if (empty($works)) {
            $md .= "- ยังไม่มีผลงาน\n";
        } else {
            foreach ($works as $w) {
                $md .= "### " . $w['title'] . "\n";
                $md .= "- **หมวดหมู่**: " . $w['category_name'] . "\n";
                $dateStr = !empty($w['event_date']) ? date('Y-m-d', strtotime($w['event_date'])) : 'ไม่ระบุ';
                $md .= "- **วันที่**: " . $dateStr . "\n";
                $md .= "- **รายละเอียด**: " . strip_tags($w['description']) . "\n";
                $md .= "- **การรับรอง**: " . $w['verification_status'] . " (คะแนน: " . $w['points_awarded'] . ")\n\n";
            }
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_profile_' . $profile['student_code'] . '.md"');
        echo $md;
        exit;
    }

    /**
     * หน้าจอตรวจงานเร่งด่วน (Bulk Approval Dashboard)
     */
    public function pendingReviews(): void
    {
        $classYear = $this->query('class_year');
        $category = $this->query('category');

        $where = "pi.verification_status = 'pending' AND pi.status = 'published'";
        $params = [];

        if ($classYear) {
            $where .= " AND sp.class_year = ?";
            $params[] = (int) $classYear;
        }
        if ($category) {
            $where .= " AND pi.category_id = ?";
            $params[] = (int) $category;
        }

        $pendingWorks = Database::fetchAll(
            "SELECT pi.*, pc.name as category_name, pc.icon as category_icon, sp.full_name, sp.student_code, sp.class_year, sp.class_room
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE {$where}
             ORDER BY pi.created_at ASC",
            $params
        );

        $categories = Database::fetchAll("SELECT * FROM portfolio_categories WHERE is_active = 1");

        $this->view('teacher.pending_reviews', [
            'pendingWorks' => $pendingWorks,
            'categories' => $categories,
            'classYear' => $classYear,
            'category' => $category
        ]);
    }

    /**
     * ดำเนินการรับรองผลงานแบบกลุ่ม
     */
    public function bulkVerify(): void
    {
        CSRF::check();
        $ids = $_POST['portfolio_ids'] ?? [];
        $action = $this->input('bulk_action'); // 'verify' or 'reject'
        $points = (int) $this->input('points', 80);

        if (empty($ids)) {
            Session::flash('error', 'กรุณาเลือกผลงานที่ต้องการตรวจ');
            App::redirect('teacher/pending-reviews');
            return;
        }

        $status = ($action === 'verify') ? 'verified' : 'rejected';
        $isVerified = ($status === 'verified') ? 1 : 0;
        $successCount = 0;

        foreach ($ids as $id) {
            $id = (int) $id;

            Database::execute(
                "UPDATE portfolio_items SET verification_status = ?, is_verified = ?, verified_by = ?, verified_at = NOW(), points_awarded = ? WHERE id = ?",
                [$status, $isVerified, Auth::id(), $points, $id]
            );

            if ($status === 'verified') {
                $portfolio = Database::fetch("SELECT pi.title, sp.user_id, u.exp, u.points
                                             FROM portfolio_items pi
                                             JOIN student_profiles sp ON pi.student_id = sp.id
                                             JOIN users u ON sp.user_id = u.id
                                             WHERE pi.id = ?", [$id]);

                if ($portfolio) {
                    $studentUserId = (int) $portfolio['user_id'];
                    $expGain = 100 + ($points * 2);
                    $newExp = $portfolio['exp'] + $expGain;
                    $newPoints = $portfolio['points'] + $points;
                    $newLevel = floor($newExp / 1000) + 1;

                    Database::execute(
                        "UPDATE users SET exp = ?, level = ?, points = ? WHERE id = ?",
                        [$newExp, $newLevel, $newPoints, $studentUserId]
                    );

                    Notifier::send(
                        $studentUserId,
                        'verification',
                        '✅ ผลงานได้รับการรับรอง (Bulk)',
                        "ผลงาน \"{$portfolio['title']}\" ได้รับการรับรองแบบกลุ่มโดยครู " . Auth::name(),
                        "portfolio/{$id}"
                    );

                    // Dispatch event for cache invalidation
                    \Core\EventDispatcher::dispatch('portfolio.verified', $studentUserId);
                }
            }
            $successCount++;
        }

        Session::flash('success', "ดำเนินการตรวจรับรองเรียบร้อยแล้ว {$successCount} รายการ 🎉");
        App::redirect('teacher/pending-reviews');
    }

    /**
     * Quick Sweep — สแกนหาผลงานที่ยังไม่สมบูรณ์แบบอัตโนมัติ
     * ตรวจหาจุดบกพร่องเช่น ไม่มีรูป / บทคัดย่อสั้น / ขาดแท็ก / ไม่ระบุวันที่ ฯลฯ
     * รองรับทั้งการเรียกเป็นหน้าเว็บ (HTML) และ JSON (API)
     */
    public function quickSweep(): void
    {
        $classYear = $this->query('class_year');
        $minDescLen = (int) ($this->query('min_desc', '120'));
        if ($minDescLen <= 0)
            $minDescLen = 120;
        $format = $this->query('format'); // 'json' หรือว่าง = HTML

        $where = "pi.status = 'published' AND pi.verification_status = 'pending'";
        $params = [];
        if ($classYear) {
            $where .= " AND sp.class_year = ?";
            $params[] = (int) $classYear;
        }

        $items = Database::fetchAll(
            "SELECT pi.id, pi.title, pi.description, pi.thumbnail, pi.file_path,
                    pi.event_date, pi.tags, pi.achievement_level, pi.award, pi.category_id,
                    pi.created_at,
                    pc.name as category_name, pc.icon as category_icon,
                    sp.full_name, sp.student_code, sp.class_year, sp.class_room
             FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE {$where}
             ORDER BY pi.created_at DESC
             LIMIT 200",
            $params
        );

        $report = [];
        foreach ($items as $item) {
            $issues = $this->scanPortfolioIssues($item, $minDescLen);
            if (!empty($issues)) {
                $severity = $this->computeSeverity($issues);
                $item['issues'] = $issues;
                $item['issue_count'] = count($issues);
                $item['overall_severity'] = $severity;
                $report[] = $item;
            }
        }

        // จัดเรียงตามความรุนแรง: critical > issue > suggestion > info
        $rank = ['critical' => 0, 'issue' => 1, 'suggestion' => 2, 'info' => 3];
        usort($report, function ($a, $b) use ($rank) {
            return ($rank[$a['overall_severity']] ?? 9) <=> ($rank[$b['overall_severity']] ?? 9);
        });

        if ($format === 'json') {
            $this->json([
                'scanned_count' => count($items),
                'flagged_count' => count($report),
                'min_desc_threshold' => $minDescLen,
                'items' => $report,
            ]);
            return;
        }

        $this->view('teacher.quick_sweep', [
            'report' => $report,
            'scannedCount' => count($items),
            'minDescLen' => $minDescLen,
            'classYear' => $classYear,
        ]);
    }

    /**
     * ตรวจหาจุดบกพร่องในผลงาน 1 รายการ
     * @return array<int,array{code:string,severity:string,message:string}>
     */
    private function scanPortfolioIssues(array $item, int $minDescLen): array
    {
        $issues = [];

        $desc = trim((string) ($item['description'] ?? ''));
        $descLen = mb_strlen(strip_tags($desc));

        if (empty($item['file_path']) && empty($item['thumbnail'])) {
            $issues[] = [
                'code' => 'NO_MEDIA',
                'severity' => 'critical',
                'message' => 'ไม่มีไฟล์แนบหรือภาพประกอบใด ๆ — ผลงานอาจไม่น่าเชื่อถือ'
            ];
        }
        if ($descLen === 0) {
            $issues[] = [
                'code' => 'EMPTY_DESC',
                'severity' => 'critical',
                'message' => 'ไม่มีคำอธิบายหรือบทคัดย่อใด ๆ'
            ];
        } elseif ($descLen < $minDescLen) {
            $issues[] = [
                'code' => 'SHORT_DESC',
                'severity' => 'issue',
                'message' => "บทคัดย่อสั้นกว่าเกณฑ์ ({$descLen}/{$minDescLen} ตัวอักษร)"
            ];
        }
        if (empty($item['category_id'])) {
            $issues[] = [
                'code' => 'NO_CATEGORY',
                'severity' => 'issue',
                'message' => 'ยังไม่ได้เลือกหมวดหมู่ผลงาน'
            ];
        }
        if (empty($item['event_date'])) {
            $issues[] = [
                'code' => 'NO_DATE',
                'severity' => 'suggestion',
                'message' => 'ไม่ระบุวันที่ของผลงาน/กิจกรรม'
            ];
        }
        $tags = json_decode($item['tags'] ?? '[]', true);
        if (!is_array($tags) || count($tags) === 0) {
            $issues[] = [
                'code' => 'NO_TAGS',
                'severity' => 'suggestion',
                'message' => 'ไม่ระบุแท็ก/คำสำคัญ ทำให้ค้นหาได้ยาก'
            ];
        }
        if (!empty($item['achievement_level']) && in_array($item['achievement_level'], ['province', 'national', 'international']) && empty($item['award'])) {
            $issues[] = [
                'code' => 'NO_AWARD_NAME',
                'severity' => 'suggestion',
                'message' => 'ระบุระดับรางวัลแต่ไม่มีชื่อรางวัล'
            ];
        }

        return $issues;
    }

    private function computeSeverity(array $issues): string
    {
        $rank = ['info' => 0, 'suggestion' => 1, 'issue' => 2, 'critical' => 3];
        $max = 'info';
        foreach ($issues as $i) {
            if (($rank[$i['severity']] ?? 0) > ($rank[$max] ?? 0)) {
                $max = $i['severity'];
            }
        }
        return $max;
    }

    /**
     * เพิ่ม Annotation (คอมเมนต์เฉพาะจุด) ให้กับผลงานนักเรียน
     */
    public function annotate(string $id): void
    {
        CSRF::check();
        $portfolioId = (int) $id;

        $targetField = $this->input('target_field', 'general');
        $snippet = $this->rawInput('snippet');
        $severity = $this->input('severity', 'suggestion');
        $comment = trim($this->rawInput('comment'));

        $allowedFields = ['general', 'title', 'description', 'thumbnail', 'file', 'tags', 'category', 'award'];
        $allowedSeverity = ['info', 'suggestion', 'issue', 'critical'];

        if (!in_array($targetField, $allowedFields, true))
            $targetField = 'general';
        if (!in_array($severity, $allowedSeverity, true))
            $severity = 'suggestion';

        if ($comment === '') {
            Session::flash('error', 'กรุณาเขียนความคิดเห็นก่อนบันทึก');
            App::redirect('portfolio/' . $portfolioId);
            return;
        }

        Database::insert(
            "INSERT INTO portfolio_annotations (portfolio_id, teacher_id, target_field, snippet, severity, comment)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$portfolioId, Auth::id(), $targetField, $snippet ?: null, $severity, $comment]
        );

        // แจ้งเตือนนักเรียน
        $owner = Database::fetch(
            "SELECT pi.title, sp.user_id FROM portfolio_items pi
             JOIN student_profiles sp ON pi.student_id = sp.id
             WHERE pi.id = ?",
            [$portfolioId]
        );
        if ($owner) {
            $sevLabel = [
                'info' => 'ℹ️ ข้อมูลเพิ่มเติม',
                'suggestion' => '💡 ข้อเสนอแนะ',
                'issue' => '⚠️ จุดที่ควรแก้ไข',
                'critical' => '🚨 จุดสำคัญที่ต้องแก้',
            ][$severity] ?? '💬 ความเห็นจากครู';

            Database::insert(
                "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'annotation', ?, ?, ?)",
                [
                    (int) $owner['user_id'],
                    $sevLabel . ' บนผลงานของคุณ',
                    'ครู ' . Auth::name() . ' ได้แสดงความเห็นในส่วน "' . $targetField . '" ของผลงาน "' . $owner['title'] . '"',
                    'portfolio/' . $portfolioId
                ]
            );

            // Dispatch event for audit logging
            \Core\EventDispatcher::dispatch('portfolio.annotated', (int) $owner['user_id'], $portfolioId, $targetField, $severity);
        }

        Session::flash('success', 'บันทึกข้อเสนอแนะเฉพาะจุดเรียบร้อย');
        App::redirect('portfolio/' . $portfolioId);
    }

    /**
     * นักเรียน/ครู กด "แก้ไขเรียบร้อย" เพื่อปิดสถานะ Annotation
     */
    public function resolveAnnotation(string $id): void
    {
        CSRF::check();
        $annId = (int) $id;

        $ann = Database::fetch("SELECT pa.*, sp.user_id as owner_user_id
                                FROM portfolio_annotations pa
                                JOIN portfolio_items pi ON pa.portfolio_id = pi.id
                                JOIN student_profiles sp ON pi.student_id = sp.id
                                WHERE pa.id = ?", [$annId]);
        if (!$ann) {
            Session::flash('error', 'ไม่พบรายการข้อเสนอแนะ');
            App::redirect('dashboard');
            return;
        }

        // อนุญาตให้เจ้าของผลงานหรือครูปิดได้
        if (Auth::id() !== (int) $ann['owner_user_id'] && !Auth::hasRole('teacher') && !Auth::hasRole('admin')) {
            Session::flash('error', 'ไม่มีสิทธิ์ดำเนินการ');
            App::redirect('portfolio/' . $ann['portfolio_id']);
            return;
        }

        Database::execute(
            "UPDATE portfolio_annotations SET is_resolved = 1, resolved_at = NOW() WHERE id = ?",
            [$annId]
        );

        // Dispatch event for audit logging
        \Core\EventDispatcher::dispatch('portfolio.annotation_resolved', (int) $ann['owner_user_id'], $ann['portfolio_id'], $annId);

        Session::flash('success', 'ทำเครื่องหมาย "แก้ไขแล้ว" เรียบร้อย');
        App::redirect('portfolio/' . $ann['portfolio_id']);
    }

    /**
     * หน้าจอวิเคราะห์นักเรียนในความดูแล (Mentorship Intelligence)
     */
    public function mentorDashboard(): void
    {
        $classYear = $this->query('class_year');
        $room = $this->query('room');
        $inactiveDays = (int) $this->query('days', 30);

        $where = "u.role = 'student'";
        $params = [];

        if ($classYear) {
            $where .= " AND sp.class_year = ?";
            $params[] = (int) $classYear;
        }
        if ($room) {
            $where .= " AND sp.class_room = ?";
            $params[] = $room;
        }

        $inactiveStudents = Database::fetchAll(
            "SELECT sp.*, u.username,
                    (SELECT MAX(created_at) FROM portfolio_items WHERE student_id = sp.id AND status = 'published') as last_upload,
                    (SELECT COUNT(*) FROM portfolio_items WHERE student_id = sp.id AND status = 'published') as total_works
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE {$where}
             HAVING (last_upload IS NULL OR last_upload < DATE_SUB(NOW(), INTERVAL ? DAY))
             ORDER BY last_upload ASC, sp.full_name ASC",
            array_merge($params, [$inactiveDays])
        );

        $this->view('teacher.mentor_intelligence', [
            'inactiveStudents' => $inactiveStudents,
            'inactiveDays' => $inactiveDays,
            'classYear' => $classYear,
            'room' => $room
        ]);
    }
}

