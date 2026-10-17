<?php
/**
 * AIController — AI Guidance Engine (Gemini API)
 */
class AIController extends Controller
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        // ลำดับการหา: 1. จาก Database Settings (Admin ตั้งค่า) | 2. จาก .env
        $this->apiKey = Settings::get('gemini_api_key') ?: Env::get('GEMINI_API_KEY', '');
        $this->model = Settings::get('gemini_model') ?: Env::get('GEMINI_MODEL', 'gemini-2.0-flash');
    }

    public function index(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        // ดึงคำแนะนำก่อนหน้า
        $recommendations = Database::fetchAll(
            "SELECT * FROM ai_recommendations WHERE student_id = ? ORDER BY generated_at DESC LIMIT 10",
            [$profile['id']]
        );

        $this->view('ai.index', [
            'profile' => $profile,
            'recommendations' => $recommendations,
            'hasApiKey' => !empty($this->apiKey),
        ]);
    }

    public function analyze(): void
    {
        CSRF::check();

        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        $analysisType = $this->input('analysis_type') ?: 'portfolio_gap';

        // รวบรวมข้อมูลนักเรียน
        $context = $this->buildStudentContext($profile);

        // สร้าง Prompt ตามประเภทการวิเคราะห์
        $promptInfo = $this->buildPrompt($analysisType, $context);
        $prompt = $promptInfo['prompt'];
        $system = $promptInfo['system'];

        $response = AIEngine::generateResponse($prompt, $system);

        // หากเรียก API ไม่สำเร็จ (โควต้าเต็ม, เน็ตหลุด) หรือไม่มี API Key -> ใช้ระบบสำรอง (Rule-based)
        if (empty($response)) {
            $response = $this->ruleBasedAnalysis($analysisType, $context);
        }

        if ($response) {
            Database::insert(
                "INSERT INTO ai_recommendations (student_id, analysis_type, prompt_context, response_data) VALUES (?, ?, ?, ?)",
                [
                    $profile['id'],
                    $analysisType,
                    json_encode($context, JSON_UNESCAPED_UNICODE),
                    json_encode($response, JSON_UNESCAPED_UNICODE),
                ]
            );

            Auth::logAction('ai_analyze', 'ai_recommendation', 0);
            Session::flash('success', 'วิเคราะห์เสร็จเรียบร้อย! 🤖');
        } else {
            Session::flash('error', 'ระบบวิเคราะห์ขัดข้องชั่วคราว กรุณาตรวจสอบข้อมูลพื้นฐานของคุณ');
        }

        App::redirect('ai-guide');
    }

    /**
     * AJAX: Refine Portfolio Description (AI Portfolio Builder)
     */
    public function refineDescription(): void
    {
        CSRF::check();
        $title = $this->input('title');
        $description = $this->input('description');
        $category = $this->input('category');

        if (empty($description)) {
            $this->json(['error' => 'กรุณากรอกข้อมูลเบื้องต้น']);
            return;
        }

        $system = "คุณเป็นที่ปรึกษาการเขียน Portfolio มืออาชีพ สำหรับนักเรียนเก่งด้านวิทยาศาสตร์ (โรงเรียนจุฬาภรณฯ)
                  ภารกิจ: นำข้อมูล 'ดิบ' หรือ 'Note' ต่อไปนี้ มาเรียบเรียงเป็นคำบรรยายผลงานที่น่าประทับใจ เข้าใจง่าย และดูเป็นทางการ (Professional)";

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        $safeDesc = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

        $prompt = "<work_metadata>\n";
        $prompt .= "  <title>{$safeTitle}</title>\n";
        $prompt .= "  <category>{$safeCategory}</category>\n";
        $prompt .= "  <raw_notes>{$safeDesc}</raw_notes>\n";
        $prompt .= "</work_metadata>\n\n";
        $prompt .= "หลักการเขียน:\n";
        $prompt .= "1. ใช้ภาษาที่สุภาพและเป็นทางการ\n";
        $prompt .= "2. เน้นผลลัพธ์ (Impact) หรือ สิ่งที่ได้รับ (Key Learning)\n";
        $prompt .= "3. หากเป็นโครงงานวิทย์ ให้เน้นกระบวนการแก้ปัญหา\n";
        $prompt .= "4. ความยาวประมาณ 2-4 ประโยคกระชับๆ\n\n";
        $prompt .= "กรุณาให้ผลลัพธ์เฉพาะ 'เนื้อหาที่เรียบเรียงแล้ว' เท่านั้น ไม่ต้องมีคำเกริ่น";

        $refined = AIEngine::generateResponse($prompt, $system);

        // Fallback if API fails (e.g., quota exceeded) or no key
        if (empty($refined)) {
            $descSanitized = rtrim(trim($description), '.');
            $refined = "ผลงานเรื่อง \"{$title}\" เป็นการแสดงศักยภาพในด้าน{$category} ซึ่งจากการทุ่มเทและประยุกต์ใช้องค์ความรู้อย่างเป็นระบบ ส่งผลให้ {$descSanitized} ความสำเร็จในครั้งนี้นับเป็นก้าวสำคัญที่ช่วยกระตุ้นการเรียนรู้เชิงลึก พัฒนาทักษะการแก้ปัญหาเชิงซ้อน (Complex Problem Solving) และยกระดับภาวะผู้นำในการทำงานร่วมกับผู้อื่น ซึ่งเป็นพื้นฐานสำคัญอย่างยิ่งสำหรับการศึกษาต่อในระดับอุดมศึกษาและการประกอบวิชาชีพในอนาคต";
        }

        $this->json(['refined' => $refined]);
    }

    /**
     * Opportunity Scout: Matching activities to student
     */
    public function opportunities(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        // Fetch student goals to identify track
        $goals = Database::fetchAll("SELECT target FROM student_goals WHERE student_id = ? AND status='inprogress'", [$profile['id']]);
        $targetStr = mb_strtolower(implode(' ', array_column($goals, 'target')));

        // Simulated Opportunity DB (Can be moved to DB later)
        $allOpportunities = [
            ['title' => 'ค่ายอัจฉริยภาพวิศวกร รุ่นที่ 15', 'type' => 'camp', 'track' => 'eng', 'provider' => 'คณะวิศวกรรมศาสตร์ มข.', 'tag' => 'Engineering'],
            ['title' => 'โครงการเส้นทางสู่ฝันการเป็นหมอ', 'type' => 'camp', 'track' => 'med', 'provider' => 'คณะแพทยศาสตร์ มหิดล', 'tag' => 'Medical'],
            ['title' => 'แข่งขันโครงงานวิทยาศาสตร์ระดับชาติ', 'type' => 'contest', 'track' => 'science', 'provider' => 'กระทรวงการอุดมศึกษาฯ', 'tag' => 'Research'],
            ['title' => 'ฝึกงานอาสาสมัครโรงพยาบาลภูมิภาค', 'type' => 'volunteer', 'track' => 'med', 'provider' => 'รพ.กาฬสินธุ์', 'tag' => 'Service'],
            ['title' => 'กิจกรรม Young Innovators 2026', 'type' => 'contest', 'track' => 'tech', 'provider' => 'สวทช.', 'tag' => 'Technology'],
            ['title' => 'เวิร์กช็อปวาดภาพเชิงสร้างสรรค์', 'type' => 'contest', 'track' => 'art', 'provider' => 'หอศิลป์กรงเทพฯ', 'tag' => 'Art'],
        ];

        // Matching Logic
        $matches = [];
        foreach ($allOpportunities as $opp) {
            if ($opp['track'] === 'all') {
                $matches[] = $opp;
                continue;
            }
            // Simple keyword matching for demo
            if (mb_strpos($targetStr, 'แพทย์') !== false && $opp['track'] === 'med')
                $matches[] = $opp;
            if (mb_strpos($targetStr, 'วิศว') !== false && $opp['track'] === 'eng')
                $matches[] = $opp;
            if (mb_strpos($targetStr, 'วิทย์') !== false && $opp['track'] === 'science')
                $matches[] = $opp;
            if (mb_strpos($targetStr, 'คอม') !== false && $opp['track'] === 'tech')
                $matches[] = $opp;
        }

        $this->view('ai.opportunities', [
            'matches' => empty($matches) ? array_slice($allOpportunities, 0, 3) : $matches
        ]);
    }

    // ============================================================
    // PHASE 4: AI Insights & Field-Specific Readiness
    // ============================================================

    /**
     * Field Readiness Analysis — วิเคราะห์ความพร้อมรายสาขาวิชา
     * แสดงหน้า Dashboard ว่านักเรียนเหมาะสมกับสาขาวิชาใดมากที่สุด
     */
    public function fieldReadiness(): void
    {
        if (!Auth::hasAnyRole(['student', 'admin', 'teacher', 'executive'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        $userId = Auth::id();
        $analysis = AIService::analyzeFieldReadiness($userId);

        if (isset($analysis['error'])) {
            Session::flash('error', $analysis['error']);
            App::redirect('profile/edit');
            return;
        }

        // Phase 5: Check AI-based badges
        $aiBadges = BadgeService::checkAI($userId);
        if ($aiBadges['total'] > 0) {
            $badgeNames = implode(', ', array_map(fn($b) => $b['icon'] . ' ' . $b['name'], $aiBadges['new_badges']));
            Session::flash('success', '🏅 เหรียญใหม่จาก AI: ' . $badgeNames);
        }

        // Single field detail view
        $selectedTrack = $this->query('track');
        $singleAnalysis = null;
        if ($selectedTrack) {
            $singleAnalysis = AIService::analyzeSingleField($userId, $selectedTrack);
        }

        $this->view('ai.field_readiness', [
            'title' => '🎯 วิเคราะห์ความพร้อมรายสาขาวิชา | Field Readiness Analyzer',
            'analysis' => $analysis,
            'single' => $singleAnalysis,
            'selectedTrack' => $selectedTrack,
        ]);
    }

    /**
     * Portfolio Quality Audit — ตรวจสอบคุณภาพเนื้อหาผลงาน
     */
    public function portfolioAudit(): void
    {
        if (!Auth::hasAnyRole(['student', 'admin', 'teacher'])) {
            ErrorHandler::forbidden('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            return;
        }

        $userId = Auth::id();
        $profile = Database::fetch(
            "SELECT id FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        if (!$profile) {
            Session::flash('error', 'ไม่พบโปรไฟล์นักเรียน');
            App::redirect('profile/edit');
            return;
        }

        $audit = AIService::auditPortfolioQuality((int) $profile['id']);
        $smartRecs = AIService::getSmartRecommendations($userId);

        $this->view('ai.portfolio_audit', [
            'title' => '🔍 ตรวจสอบคุณภาพพอร์ตโฟลิโอ | Portfolio Quality Audit',
            'audit' => $audit,
            'smartRecs' => $smartRecs,
        ]);
    }

    /**
     * AJAX: Get smart recommendations JSON
     */
    public function getRecommendations(): void
    {
        if (!Auth::hasAnyRole(['student', 'admin', 'teacher'])) {
            $this->json(['error' => 'ไม่มีสิทธิ์'], 403);
            return;
        }

        $recs = AIService::getSmartRecommendations(Auth::id());
        $this->json($recs);
    }

    /**
     * AJAX: Get field readiness for a specific track
     */
    public function getFieldReadiness(): void
    {
        if (!Auth::hasAnyRole(['student', 'admin', 'teacher'])) {
            $this->json(['error' => 'ไม่มีสิทธิ์'], 403);
            return;
        }

        $track = $this->input('track');
        if (!$track || !isset(AIService::FIELD_REQUIREMENTS[$track])) {
            $this->json(['error' => 'ไม่พบสาขาวิชา']);
            return;
        }

        $result = AIService::analyzeSingleField(Auth::id(), $track);
        $this->json($result);
    }

    /**
     * รวบรวมข้อมูลนักเรียนสำหรับส่งให้ AI
     */
    private function buildStudentContext(array $profile): array
    {
        $studentId = $profile['id'];

        // GPA
        $gpa = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa FROM academic_records WHERE student_id = ?",
            [$studentId]
        )['gpa'] ?? 0;

        // Portfolio summary
        $portfolioStats = Database::fetchAll(
            "SELECT pc.name, COUNT(pi.id) as count FROM portfolio_categories pc
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.student_id = ? AND pi.status = 'published'
             WHERE pc.is_active = 1 GROUP BY pc.id HAVING count > 0",
            [$studentId]
        );

        $totalWorks = Database::fetch(
            "SELECT COUNT(*) as count FROM portfolio_items WHERE student_id = ? AND status = 'published'",
            [$studentId]
        )['count'] ?? 0;

        // Awards
        $awards = Database::fetchAll(
            "SELECT title, achievement_level, award FROM portfolio_items WHERE student_id = ? AND award IS NOT NULL AND award != '' AND status = 'published'",
            [$studentId]
        );

        // Interests
        $interests = Database::fetchAll(
            "SELECT type, item, proficiency FROM student_interests WHERE student_id = ?",
            [$studentId]
        );

        // Goals
        $goals = Database::fetchAll(
            "SELECT goal_type, target, status FROM student_goals WHERE student_id = ?",
            [$studentId]
        );

        // Top subjects
        $topSubjects = Database::fetchAll(
            "SELECT subject, ROUND(AVG(grade), 2) as avg_grade FROM academic_records WHERE student_id = ? GROUP BY subject ORDER BY avg_grade DESC LIMIT 5",
            [$studentId]
        );

        // Readiness & Gap Data (Phase 4 integration)
        $readinessRows = Database::fetchAll("SELECT score_type, score FROM readiness_scores WHERE student_id = ?", [$studentId]);
        $rMap = [];
        foreach ($readinessRows as $rRow) {
            $rMap[$rRow['score_type']] = $rRow['score'];
        }
        $riasecRecord = Database::fetch("SELECT result_data FROM student_assessments WHERE user_id = ? AND assessment_type = 'riasec' ORDER BY created_at DESC LIMIT 1", [$profile['user_id']]);

        $primaryGoalFull = Database::fetch(
            "SELECT sg.target, gt.radar_target, gt.track_name 
             FROM student_goals sg
             LEFT JOIN goal_templates gt ON (sg.target LIKE CONCAT('%', gt.track_code, '%') OR gt.track_name LIKE CONCAT('%', sg.target, '%') OR gt.track_name = sg.target)
             WHERE sg.student_id = ? AND sg.status IN ('planning','inprogress') 
             ORDER BY sg.updated_at DESC LIMIT 1",
            [$studentId]
        );

        return [
            'name' => $profile['full_name'],
            'class_year' => $profile['class_year'],
            'gpa' => $gpa,
            'total_works' => $totalWorks,
            'portfolio_by_category' => $portfolioStats,
            'awards' => $awards,
            'interests' => $interests,
            'goals' => $goals,
            'top_subjects' => $topSubjects,
            'school' => 'โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์',
            'readiness' => !empty($rMap) ? "Overall: " . ($rMap['overall'] ?? 0) . "%, Academic: " . ($rMap['academic'] ?? 0) . "%, Portfolio: " . ($rMap['portfolio'] ?? 0) . "%, Skills: " . ($rMap['skill'] ?? 0) . "%" : 'N/A',
            'riasec_current' => $riasecRecord ? $riasecRecord['result_data'] : 'N/A',
            'riasec_target' => ($primaryGoalFull && $primaryGoalFull['radar_target']) ? $primaryGoalFull['radar_target'] : 'N/A',
            'goal_track' => $primaryGoalFull['track_name'] ?? 'N/A',
        ];
    }

    /**
     * สร้าง Prompt ตามประเภท (คืนค่าเป็น Array: [system, prompt])
     */
    private function buildPrompt(string $type, array $ctx): array
    {
        $system = "คุณเป็นที่ปรึกษาด้านการศึกษาผู้เชี่ยวชาญ สำหรับนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์
                  คุณเข้าใจหลักสูตรวิทยาศาสตร์เข้มข้น และเกณฑ์การรับเข้ามหาวิทยาลัยชั้นนำ (TCAS) ทั้งในและต่างประเทศ
                  ให้คำแนะนำแบบเจาะจง เฉียบคม และเป็นแผนปฏิบัติงาน (Action Plan) ที่นักเรียนทำได้จริง";

        $prompt = "ข้อมูลนักเรียน:\n";
        $prompt .= "- ชื่อ: {$ctx['name']}\n";
        $prompt .= "- ชั้น: ม.{$ctx['class_year']}\n";
        $prompt .= "- GPA: {$ctx['gpa']}\n";
        $prompt .= "- ผลงานรวม: {$ctx['total_works']} ชิ้น\n";

        if (!empty($ctx['interests'])) {
            $prompt .= "- ความสนใจ: " . implode(', ', array_column($ctx['interests'], 'item')) . "\n";
        }
        if (!empty($ctx['goals'])) {
            $prompt .= "- เป้าหมาย: " . implode(', ', array_column($ctx['goals'], 'target')) . "\n";
        }
        if (!empty($ctx['top_subjects'])) {
            $subjects = array_map(fn($s) => "{$s['subject']} ({$s['avg_grade']})", $ctx['top_subjects']);
            $prompt .= "- วิชาเด่น: " . implode(', ', $subjects) . "\n";
        }
        if (!empty($ctx['awards'])) {
            $awardList = array_map(fn($a) => $a['title'] . ($a['award'] ? " ({$a['award']})" : ''), array_slice($ctx['awards'], 0, 5));
            $prompt .= "- รางวัล: " . implode(', ', $awardList) . "\n";
        }

        $prompt .= "- ความพร้อมภาพรวม: {$ctx['readiness']}\n";
        $prompt .= "- เป้าหมายคณะ/สาขาหลัก: {$ctx['goal_track']}\n";
        $prompt .= "- ทักษะ RIASEC ปัจจุบัน (คะแนนตัวตน): {$ctx['riasec_current']}\n";
        $prompt .= "- ทักษะ RIASEC ที่คณะนี้ต้องการ: {$ctx['riasec_target']}\n\n";

        $taskPrompt = match ($type) {
            'portfolio_gap' => "จงวิเคราะห์ 'ช่องว่าง (Gap)' ระหว่างทักษะและผลงานที่มีอยู่ กับเกณฑ์การรับเข้าคณะ {$ctx['goal_track']}\nแนะนำแผนปฏิบัติการ 3 ข้อ เพื่ออุดช่องโหว่นั้น โดยเน้นการวิจัยหรือนโยบายของจุฬาภรณ์ฯ\nตอบเป็นภาษาไทย รูปแบบ Markdown กระชับ",
            'study_guide' => "จัดทำแผนการเรียนและพัฒนาตนเอง (Roadmap) ตลอดเทอมนี้เพื่อยกระดับคะแนนความพร้อมภาพรวม\nตอบเป็นภาษาไทย รูปแบบ Markdown",
            'career_match' => "วิเคราะห์อาชีพหรือสาขาวิชาที่นักเรียนมีความเป็นไปได้สูงสุด และแนะนำ 2 สาขาสำรองที่คุณเห็นในแววของนักเรียนคนนี้\nตอบเป็นภาษาไทย รูปแบบ Markdown",
            'skill_suggest' => "แนะนำทักษะ Soft Skill (เช่น Leadership, Presentation) และ Hard Skill ที่จำเป็นต่อการเรียนต่อสาขา {$ctx['goal_track']} ที่นักเรียนคนนี้ยังไม่มี\nตอบเป็นภาษาไทย รูปแบบ Markdown",
            default => "กรุณาให้คำแนะนำแนวทางเติมเต็มพอร์ตโฟลิโอสำหรับเตรียมสอบเข้ามหาวิทยาลัย ตอบเป็นภาษาไทย",
        };

        return [
            'system' => $system,
            'prompt' => $prompt . $taskPrompt
        ];
    }

    /**
     * เรียก AI API ผ่าน AIEngine (รองรับทั้ง Groq, OpenRouter, Gemini และปิด DB ระหว่างรอ)
     */
    private function callGeminiAPI(string $prompt): ?string
    {
        return AIEngine::generateResponse($prompt);
    }

    /**
     * Rule-based Analysis (Fallback เมื่อ API ภายนอกไม่พร้อมใช้งาน — แยกวิเคราะห์เชิงลึกตามโหมดที่เลือก)
     */
    private function ruleBasedAnalysis(string $type, array $ctx): string
    {
        $minWorks = ($ctx['class_year'] >= 6) ? 15 : 10;
        $categoryNames = array_column($ctx['portfolio_by_category'], 'name');
        $goalTrack = ($ctx['goal_track'] ?? 'N/A') !== 'N/A' ? $ctx['goal_track'] : 'สายวิทยาศาสตร์และเทคโนโลยี';
        $gpa = (float) ($ctx['gpa'] ?? 0);

        $header = match ($type) {
            'portfolio_gap' => "### 📋 รายงานวิเคราะห์จุดอ่อน-จุดแข็งพอร์ตโฟลิโอ (Gap Analysis)\n**เป้าหมายหลัก:** {$goalTrack} | **ชั้น:** ม.{$ctx['class_year']} | **GPA:** {$gpa}",
            'career_match'  => "### 🎓 รายงานวิเคราะห์คณะ/สาขาวิชาที่เหมาะสมกับศักยภาพ (Career Match)\n**โปรไฟล์ปัจจุบัน:** ชั้น ม.{$ctx['class_year']} | **GPA:** {$gpa} | **ผลงานสะสม:** {$ctx['total_works']} ชิ้น",
            'study_guide'   => "### 📚 แผนการเรียนและพัฒนาตนเอง (Academic & Portfolio Roadmap)\n**ระดับชั้นปัจจุบัน:** ม.{$ctx['class_year']} | **เป้าหมาย:** {$goalTrack}",
            'skill_suggest' => "### 🧩 แผนพัฒนาทักษะแห่งอนาคต (Hard & Soft Skills Blueprint)\n**สาขาเป้าหมาย:** {$goalTrack} | **ความพร้อมปัจจุบัน:** {$ctx['readiness']}",
            default         => "### 🤖 สรุปคำแนะนำการพัฒนาแฟ้มสะสมผลงาน",
        };

        $sections = [$header];

        if ($type === 'career_match') {
            $topSubjects = !empty($ctx['top_subjects'])
                ? implode(', ', array_map(fn($s) => "{$s['subject']} ({$s['avg_grade']})", $ctx['top_subjects']))
                : 'ยังไม่มีข้อมูลวิชาเด่น';
            $sections[] = "🔍 **1. จุดแข็งทางวิชาการและตัวตน (RIASEC):**\n- **วิชาที่ทำได้ดีโดดเด่น:** {$topSubjects}\n- **โปรไฟล์ RIASEC ปัจจุบัน:** `{$ctx['riasec_current']}`";
            $sections[] = "🎯 **2. สาขาวิชาที่มีโอกาสสอบติดและเหมาะสมสูงสุด 3 อันดับ:**\n"
                . "1. **{$goalTrack}** — สอดคล้องกับเป้าหมายหลักและหลักสูตรโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย\n"
                . "2. **กลุ่มวิศวกรรมศาสตร์ / วิทยาการคอมพิวเตอร์และนวัตกรรม AI** — เหมาะสำหรับต่อยอดโครงงานวิทยาศาสตร์และทักษะการคิดวิเคราะห์เชิงระบบ\n"
                . "3. **กลุ่มวิทยาศาสตร์สุขภาพ / วิจัยวิทยาศาสตร์บริสุทธิ์ (ทุน พสวท. / โควตา ม.ชั้นนำ)** — ใช้จุดแข็งด้านการทำแล็บและงานวิจัยของนักเรียน จภ.";
        } elseif ($type === 'study_guide') {
            $yearRoadmap = match ((int) $ctx['class_year']) {
                1, 2, 3 => "- **ระยะที่ 1 (เดือนที่ 1–2):** สำรวจความถนัดผ่านค่ายวิชาการและทำแบบประเมิน RIASEC\n- **ระยะที่ 2 (เดือนที่ 3–4):** สะสมผลงานการแข่งขันทักษะวิชาการระดับโรงเรียน/เขตพื้นที่อย่างน้อย 2 ชิ้น",
                4 => "- **ระยะที่ 1 (เดือนที่ 1–2):** ตั้งหัวข้อโครงงานวิทยาศาสตร์และสอบคัดเลือกค่าย สอวน. ค่าย 1\n- **ระยะที่ 2 (เดือนที่ 3–4):** สะสมชั่วโมงจิตอาสาและกิจกรรมผู้นำให้ครบอย่างน้อย 20 ชั่วโมง",
                5 => "- **ระยะที่ 1 (เดือนที่ 1–2):** เร่งทำโครงงานวิจัยให้ได้ผลการทดลองที่สมบูรณ์ เพื่อส่งประกวดระดับภูมิภาค/ชาติ\n- **ระยะที่ 2 (เดือนที่ 3–4):** เตรียมสอบวัดระดับภาษาอังกฤษและสรุปผลงานเด่นลง Portfolio ให้ครบ 10–12 ชิ้น",
                6 => "- **ระยะที่ 1 (เดือนที่ 1–2):** คัดกรองผลงานเด่น (Featured Works) 10 ชิ้นที่ตรงกับเกณฑ์ **{$goalTrack}** รอบที่ 1 (Portfolio)\n- **ระยะที่ 2 (เดือนที่ 3–4):** ฝึกเขียน SOP (Statement of Purpose) และซ้อมสัมภาษณ์เชิงวิชาการจากโครงงานวิจัยที่ทำ",
                default => "- วางแผนสะสมผลงานวิชาการและโครงงานวิทยาศาสตร์อย่างต่อเนื่องทุกภาคเรียน",
            };
            $sections[] = "🗓️ **แผนปฏิบัติการรายภาคเรียน (Semester Action Roadmap):**\n{$yearRoadmap}";
        } elseif ($type === 'skill_suggest') {
            $sections[] = "💻 **1. ทักษะวิชาชีพเฉพาะทาง (Hard Skills ที่ควรเสริมสำหรับ {$goalTrack}):**\n"
                . "- **ระเบียบวิธีวิจัยและการวิเคราะห์ข้อมูลสถิติ (Research Methodology & Data Analysis):** หัวใจสำคัญในการยื่นพอร์ตสายวิทย์\n"
                . "- **ทักษะภาษาอังกฤษเชิงวิชาการ (Academic English / IELTS / TGAT1):** เพิ่มความได้เปรียบในการอ่านเปเปอร์วิจัยและยื่นรอบ 1\n"
                . "- **การประยุกต์ใช้เทคโนโลยีดิจิทัลและ AI ในสาขาวิชา:** แสดงให้กรรมการเห็นถึงทักษะการเรียนรู้ยุคใหม่";
            $sections[] = "🤝 **2. ทักษะสำคัญด้านพฤติกรรมและการทำงานร่วมกัน (Soft Skills):**\n"
                . "- **Scientific Communication & Storytelling:** การนำเสนอโครงงานที่ซับซ้อนให้กระชับและเห็นผลกระทบ (Impact) ชัดเจนภายใน 3 นาที\n"
                . "- **Leadership & Social Empathy:** ภาวะผู้นำจากการทำงานกลุ่มโครงงานและกิจกรรมจิตอาสาเพื่อสังคม";
        }

        // ส่วนวิเคราะห์ช่องว่างมาตรฐาน (Gap & Readiness Check)
        $tips = [];
        if ($ctx['total_works'] < $minWorks) {
            $tips[] = "📁 **ปริมาณผลงานสะสม ({$ctx['total_works']}/{$minWorks} ชิ้น):** สำหรับชั้น ม.{$ctx['class_year']} ควรเพิ่มผลงานที่เผยแพร่แล้วอีกอย่างน้อย " . ($minWorks - $ctx['total_works']) . " ชิ้น เพื่อให้พอร์ตโฟลิโอมีความหนาแน่นเพียงพอ";
        } else {
            $tips[] = "✅ **ปริมาณผลงานสะสม ({$ctx['total_works']} ชิ้น):** ผ่านเกณฑ์มาตรฐานขั้นสูงของระดับชั้น ม.{$ctx['class_year']} แล้ว ควรเน้นการคัดเลือกชิ้นที่โดดเด่นเป็น Featured";
        }

        $essentialCheck = [
            'โครงงานวิทยาศาสตร์' => '🔬 **ช่องว่างด้านงานวิจัย:** ยังไม่พบผลงานในหมวด "โครงงานวิทยาศาสตร์" ซึ่งเป็นจุดขายหลักของนักเรียน จภ. แนะนำให้ส่งออกโครงงานจากเมนูโครงงานวิทยาศาสตร์เข้าสู่พอร์ตทันที',
            'จิตอาสา & บำเพ็ญประโยชน์' => '🤝 **ช่องว่างด้านจิตอาสา:** ยังขาดผลงานหมวด "จิตอาสา & บำเพ็ญประโยชน์" ซึ่งจำเป็นมากในการประเมินสมรรถนะด้านความรับผิดชอบต่อสังคม',
            'ผู้นำ & กิจกรรมนักเรียน' => '👑 **ช่องว่างด้านภาวะผู้นำ:** แนะนำให้เพิ่มหลักฐานการเป็นหัวหน้าทีมโครงงาน คณะกรรมการนักเรียน หรือผู้จัดกิจกรรมโรงเรียน',
        ];
        foreach ($essentialCheck as $cat => $msg) {
            if (!in_array($cat, $categoryNames)) {
                $tips[] = $msg;
            }
        }

        if ($gpa >= 3.5) {
            $tips[] = "📈 **เกรดเฉลี่ยสะสม (GPA {$gpa}):** อยู่ในเกณฑ์ดีเยี่ยม ผ่านเกณฑ์ขั้นต่ำของโครงการพิเศษและโควตารอบ Portfolio ส่วนใหญ่";
        } elseif ($gpa >= 3.0) {
            $tips[] = "📈 **เกรดเฉลี่ยสะสม (GPA {$gpa}):** อยู่ในเกณฑ์ดี แนะนำให้ดึงเกรดรายวิชาวิทยาศาสตร์-คณิตศาสตร์ให้สูงกว่า 3.50 เพื่อเพิ่มน้ำหนักคะแนนวิชาการ";
        } elseif ($gpa > 0) {
            $tips[] = "📈 **เกรดเฉลี่ยสะสม (GPA {$gpa}):** ควรเร่งวางแผนปรับปรุงเกรดในเทอมถัดไปควบคู่กับการใช้ผลงานโครงงาน/รางวัลระดับชาติช่วยเสริมน้ำหนัก";
        }

        if (empty($ctx['awards'])) {
            $tips[] = "🏆 **รางวัลและการแข่งขัน:** ลองส่งผลงานเข้าร่วมการแข่งขัน เช่น สอวน., Science Fair, หรือการประกวดนวัตกรรม เพื่อเพิ่มหลักฐานเชิงประจักษ์ระดับจังหวัด/ภูมิภาค/ชาติ";
        }

        $sections[] = "📌 **ข้อเสนอแนะเชิงปฏิบัติการ (Action Plan):**\n- " . implode("\n- ", $tips);

        return implode("\n\n", $sections);
    }

    // ============================================================
    // PHASE 5.1: TCAS AI Advisor — Gemini-Powered Gap Analysis
    // ============================================================

    /**
     * Display the TCAS AI Advisor page.
     */
    public function tcasAdvisor(): void
    {
        $userId = Auth::id();

        // Get student profile for display
        $profile = Database::fetch(
            "SELECT full_name, class_year, class_room FROM student_profiles WHERE user_id = ?",
            [$userId]
        );

        if (!$profile) {
            Session::flash('error', 'กรุณาสร้างโปรไฟล์ก่อนใช้ AI Advisor');
            App::redirect('profile/edit');
            return;
        }

        // Get GPA
        $gpaRow = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa
             FROM academic_records
             JOIN student_profiles sp ON academic_records.student_id = sp.id
             WHERE sp.user_id = ?",
            [$userId]
        );
        $gpa = (float) ($gpaRow['gpa'] ?? 0);

        // Check if there's a cached analysis for the user's target faculty
        $targetFaculty = $this->query('faculty') ?: 'medicine';

        // Faculty options (Expanded to 14 Faculties)
        $faculties = [
            // กลุ่มวิทยาศาสตร์สุขภาพ
            'medicine' => '🩺 แพทยศาสตร์',
            'dentistry' => '🦷 ทันตแพทยศาสตร์',
            'pharmacy' => '💊 เภสัชศาสตร์',
            'nursing' => '💉 พยาบาลศาสตร์ / สาธารณสุข',
            'veterinary' => '🐕 สัตวแพทยศาสตร์',
            
            // กลุ่มวิศวกรรมและเทคโนโลยี
            'engineering' => '⚙️ วิศวกรรมศาสตร์',
            'computing' => '💻 วิทยาการคอมพิวเตอร์ / IT',
            'architecture' => '📐 สถาปัตยกรรมศาสตร์',
            
            // กลุ่มวิทยาศาสตร์ประยุกต์และวิจัย
            'science' => '🔬 วิทยาศาสตร์ / เกษตรศาสตร์',
            
            // กลุ่มสังคมศาสตร์และธุรกิจ
            'law' => '⚖️ นิติศาสตร์ / รัฐศาสตร์',
            'education' => '📚 ครุศาสตร์ / ศึกษาศาสตร์',
            'liberal_arts' => '📖 อักษรศาสตร์ / นิเทศศาสตร์',
            'business' => '📊 บริหารธุรกิจ / บัญชี',
            'design' => '🎨 ศิลปกรรม / ออกแบบสื่อสร้างสรรค์',
        ];

        $this->view('ai.tcas_advisor', [
            'title' => '🤖 TCAS AI Advisor — วิเคราะห์ช่องว่างเข้ามหาวิทยาลัย',
            'profile' => $profile,
            'gpa' => $gpa,
            'faculties' => $faculties,
            'selectedFaculty' => $targetFaculty,
        ]);
    }

    /**
     * AJAX endpoint: Trigger TCAS Gap Analysis.
     * Returns JSON with the full analysis result.
     */
    public function analyzeTCASGap(): void
    {
        CSRF::check();
        try {
            $userId = Auth::id();
            $targetFaculty = $this->input('faculty') ?: 'medicine';
            $forceRefresh = $this->input('force_refresh') === '1';

            // Release session lock before heavy AI analysis so concurrent requests from the user aren't blocked
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            $result = AIService::generateTCASGapAnalysis($userId, $targetFaculty, $forceRefresh);

            if (isset($result['error'])) {
                $this->json(['success' => false, 'error' => $result['error']], 400);
                return;
            }

            $this->json(['success' => true, 'analysis' => $result]);
        } catch (\Throwable $e) {
            App::log("TCAS Gap Analysis Error: " . $e->getMessage(), 'ERROR');
            App::log("Stack trace: " . $e->getTraceAsString(), 'DEBUG');
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

