<?php
/**
 * AIService — AI Insights & Field-Specific Readiness Analysis (Phase 4)
 *
 * วิเคราะห์ "ความพร้อมรายสาขาวิชา" โดยดึงข้อมูลจาก:
 *   - Portfolio Categories + Achievement Levels
 *   - Skills Master + Portfolio Skills
 *   - Student Goals (target career/major)
 *   - RIASEC Assessment vs Target Profile
 *   - Academic Records (GPA, top subjects)
 *   - Logbook Hours & Activity Patterns
 *
 * ใช้ Rule-Based Analysis เป็นแกนหลัก (ทำงานได้แม้ไม่มี Gemini API key)
 * พร้อม interface สำหรับส่งข้อมูลไป AIEngine::generateResponse() สำหรับคำอธิบายเชิงลึก
 */
class AIService
{
    // ============================================================
    // CONSTANTS: มาตรฐานความพร้อมรายสาขาวิชา
    // ============================================================

    /**
     * เกณฑ์ขั้นต่ำรายสาขาวิชา
     *
     * structure:
     *   'track_code' => [
     *       'name'            => ชื่อสาขา,
     *       'min_gpa'         => GPA ขั้นต่ำ,
     *       'required_cats'   => หมวดหมู่ผลงานที่ต้องมี [slug => min_count],
     *       'required_skills' => ทักษะหลักที่ต้องการ [skill_name => min_proficiency],
     *       'riasec_target'   => ค่า RIASEC ที่คาดหวัง [trait => min_score],
     *       'min_logbook_h'   => ชั่วโมง logbook ขั้นต่ำ,
     *       'min_achie_level' => ระดับความสำเร็จขั้นต่ำที่คาดหวัง,
     *       'min_awards'      => จำนวนรางวัลขั้นต่ำ,
     *   ]
     */
    public const FIELD_REQUIREMENTS = [
        // สายวิทยาศาสตร์สุขภาพ (แพทย์, ทันตะ, เภสัช, สัตวแพทย์)
        'MED_HEALTH' => [
            'name' => 'วิทยาศาสตร์สุขภาพ (แพทย์/ทันตะ/เภสัช)',
            'min_gpa' => 3.50,
            'required_cats' => [
                'science-project' => 2,
                'research' => 1,
                'volunteer' => 2,
                'leadership' => 1,
            ],
            'required_skills' => [
                'การทดลอง' => 3,
                'การสังเกต' => 3,
                'การแก้ปัญหา' => 3,
                'การสื่อสาร' => 3,
            ],
            'riasec_target' => ['I' => 7, 'S' => 6, 'R' => 5],
            'min_logbook_h' => 30,
            'min_achie_level' => 'province',
            'min_awards' => 2,
        ],

        // สายวิศวกรรม (คอมพิวเตอร์, เครื่องกล, ไฟฟ้า, โยธา)
        'ENGINEERING' => [
            'name' => 'วิศวกรรมศาสตร์',
            'min_gpa' => 3.00,
            'required_cats' => [
                'science-project' => 2,
                'technology' => 2,
                'mathematics' => 1,
                'research' => 1,
            ],
            'required_skills' => [
                'การคิดเชิงวิพากษ์' => 3,
                'การแก้ปัญหา' => 3,
                'การเขียนโปรแกรม' => 3,
                'การออกแบบ' => 3,
            ],
            'riasec_target' => ['R' => 7, 'I' => 7, 'C' => 5],
            'min_logbook_h' => 30,
            'min_achie_level' => 'province',
            'min_awards' => 1,
        ],

        // สายวิทยาศาสตร์บริสุทธิ์ (ฟิสิกส์, เคมี, ชีววิทยา, คณิตศาสตร์)
        'PURE_SCIENCE' => [
            'name' => 'วิทยาศาสตร์บริสุทธิ์',
            'min_gpa' => 3.30,
            'required_cats' => [
                'science-project' => 3,
                'research' => 2,
                'mathematics' => 1,
            ],
            'required_skills' => [
                'การทดลอง' => 4,
                'การสังเกต' => 4,
                'การคิดเชิงวิพากษ์' => 3,
                'การสร้างแบบจำลอง' => 3,
            ],
            'riasec_target' => ['I' => 8, 'R' => 6, 'C' => 5],
            'min_logbook_h' => 40,
            'min_achie_level' => 'province',
            'min_awards' => 2,
        ],

        // สายเทคโนโลยีสารสนเทศ / คอมพิวเตอร์ / Data Science
        'COMPUTING' => [
            'name' => 'เทคโนโลยีสารสนเทศและคอมพิวเตอร์',
            'min_gpa' => 2.75,
            'required_cats' => [
                'technology' => 3,
                'science-project' => 1,
                'mathematics' => 1,
            ],
            'required_skills' => [
                'การเขียนโปรแกรม' => 4,
                'AI และ Machine Learning' => 3,
                'การพัฒนาเว็บ' => 3,
                'การวิเคราะห์ข้อมูล' => 3,
            ],
            'riasec_target' => ['I' => 7, 'C' => 6, 'R' => 5],
            'min_logbook_h' => 30,
            'min_achie_level' => 'district',
            'min_awards' => 1,
        ],

        // สายศิลปศาสตร์ / นิติศาสตร์ / รัฐศาสตร์ / มนุษยศาสตร์
        'LIBERAL_ARTS' => [
            'name' => 'ศิลปศาสตร์ / มนุษยศาสตร์ / นิติศาสตร์',
            'min_gpa' => 2.75,
            'required_cats' => [
                'language' => 2,
                'art-music' => 1,
                'leadership' => 1,
                'volunteer' => 1,
            ],
            'required_skills' => [
                'การสื่อสาร' => 4,
                'การเขียนวิชาการ' => 3,
                'การนำเสนอ' => 3,
                'การคิดเชิงวิพากษ์' => 3,
            ],
            'riasec_target' => ['A' => 6, 'S' => 6, 'E' => 5],
            'min_logbook_h' => 20,
            'min_achie_level' => 'school',
            'min_awards' => 1,
        ],

        // สายบริหารธุรกิจ / บริหารจัดการ / บัญชี
        'BUSINESS' => [
            'name' => 'บริหารธุรกิจ / บัญชี / การจัดการ',
            'min_gpa' => 2.75,
            'required_cats' => [
                'leadership' => 2,
                'volunteer' => 1,
                'mathematics' => 1,
            ],
            'required_skills' => [
                'ภาวะผู้นำ' => 4,
                'การทำงานร่วมกัน' => 3,
                'การบริหารเวลา' => 3,
                'การนำเสนอ' => 3,
            ],
            'riasec_target' => ['E' => 7, 'C' => 6, 'S' => 5],
            'min_logbook_h' => 20,
            'min_achie_level' => 'school',
            'min_awards' => 1,
        ],

        // สายสถาปัตยกรรม / ศิลปะการออกแบบ
        'DESIGN' => [
            'name' => 'สถาปัตยกรรม / ศิลปะ / การออกแบบ',
            'min_gpa' => 2.50,
            'required_cats' => [
                'art-music' => 3,
                'technology' => 1,
                'science-project' => 1,
            ],
            'required_skills' => [
                'การออกแบบ' => 4,
                'ความคิดสร้างสรรค์' => 4,
                'การคิดเชิงวิพากษ์' => 3,
            ],
            'riasec_target' => ['A' => 8, 'R' => 5, 'I' => 4],
            'min_logbook_h' => 20,
            'min_achie_level' => 'school',
            'min_awards' => 1,
        ],
    ];

    /** ระดับความสำเร็จ — ลำดับความยาก (น้อย → มาก) */
    private const ACHIEVEMENT_ORDER = [
        'school' => 1,
        'district' => 2,
        'province' => 3,
        'national' => 4,
        'international' => 5,
    ];

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * วิเคราะห์ความพร้อมรายสาขาวิชาทั้งหมดสำหรับนักเรียนคนหนึ่ง
     *
     * @param int $userId  users.id
     * @return array {
     *     'student_profile' => array,
     *     'field_readiness' => array<track_code => readiness_detail>,
     *     'top_matches'     => array<array{track_code, track_name, readiness_pct, gap_summary}>,
     *     'portfolio_profile' => array,
     *     'skills_profile'  => array,
     *     'riasec_profile'  => array,
     *     'gaps'            => array,
     *     'recommendations' => array,
     * }
     */
    public static function analyzeFieldReadiness(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) {
            return ['error' => 'ไม่พบโปรไฟล์นักเรียน'];
        }

        // ---- 1. Gather all profiles ----
        $profile = self::getStudentProfile($studentId, $userId);
        $portfolio = self::getPortfolioProfile($studentId);
        $skills = self::getSkillsProfile($studentId);
        $riasec = self::getRIASECProfile($userId);
        $gpa = self::getStudentGPA($studentId);
        $logbookHrs = self::getLogbookHours($userId);
        $goals = self::getStudentGoals($studentId);

        // ---- 2. Score every field track ----
        $fieldResults = [];
        foreach (self::FIELD_REQUIREMENTS as $trackCode => $reqs) {
            $fieldResults[$trackCode] = self::scoreFieldTrack([
                'track' => $trackCode,
                'requirements' => $reqs,
                'profile' => $profile,
                'portfolio' => $portfolio,
                'skills' => $skills,
                'riasec' => $riasec,
                'gpa' => $gpa,
                'logbookHrs' => $logbookHrs,
                'goals' => $goals,
            ]);
        }

        // ---- 3. Identify top matches ----
        $sorted = $fieldResults;
        usort($sorted, fn($a, $b) => $b['readiness_pct'] <=> $a['readiness_pct']);
        $topMatches = array_map(fn($r) => [
            'track_code' => $r['track_code'],
            'track_name' => $r['track_name'],
            'readiness_pct' => $r['readiness_pct'],
            'gap_count' => $r['gap_count'],
            'gap_summary' => implode('; ', array_slice($r['gaps'], 0, 3)),
            'strengths' => $r['strengths'],
        ], $sorted);

        // ---- 4. Global gaps & recommendations ----
        $allGaps = [];
        $allRecs = [];
        foreach ($fieldResults as $fr) {
            $allGaps = array_merge($allGaps, $fr['gaps']);
            $allRecs = array_merge($allRecs, $fr['recommendations']);
        }

        return [
            'student_profile' => $profile,
            'field_readiness' => $fieldResults,
            'top_matches' => array_slice($topMatches, 0, 5),
            'portfolio_profile' => $portfolio,
            'skills_profile' => $skills,
            'riasec_profile' => $riasec,
            'gpa' => $gpa,
            'logbook_hours' => $logbookHrs,
            'current_goal' => $goals['top_match'] ?? null,
            'gaps' => array_values(array_unique($allGaps)),
            'recommendations' => array_values(array_unique($allRecs)),
        ];
    }

    /**
     * วิเคราะห์ Gap เฉพาะสาขาวิชาที่นักเรียนเลือก
     *
     * @param int    $userId
     * @param string $trackCode  เช่น 'MED_HEALTH', 'ENGINEERING'
     * @return array
     */
    public static function analyzeSingleField(int $userId, string $trackCode): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) {
            return ['error' => 'ไม่พบโปรไฟล์นักเรียน'];
        }
        if (!isset(self::FIELD_REQUIREMENTS[$trackCode])) {
            return ['error' => 'ไม่พบสาขาวิชาที่ระบุ'];
        }

        $profile = self::getStudentProfile($studentId, $userId);
        $portfolio = self::getPortfolioProfile($studentId);
        $skills = self::getSkillsProfile($studentId);
        $riasec = self::getRIASECProfile($userId);
        $gpa = self::getStudentGPA($studentId);
        $logbookHrs = self::getLogbookHours($userId);
        $goals = self::getStudentGoals($studentId);

        return self::scoreFieldTrack([
            'track' => $trackCode,
            'requirements' => self::FIELD_REQUIREMENTS[$trackCode],
            'profile' => $profile,
            'portfolio' => $portfolio,
            'skills' => $skills,
            'riasec' => $riasec,
            'gpa' => $gpa,
            'logbookHrs' => $logbookHrs,
            'goals' => $goals,
            'detail_mode' => true,
        ]);
    }

    /**
     * วิเคราะห์ Portfolio Content Quality — ตรวจคุณภาพเนื้อหาผลงาน
     *
     * ตรวจสอบ: ความยาวคำอธิบาย, การระบุทักษะ, ระดับความสำเร็จ,
     * การมีรูป/ไฟล์แนบ, ความสอดคล้องกับหมวดหมู่
     *
     * @param int $studentId
     * @return array
     */
    public static function auditPortfolioQuality(int $studentId): array
    {
        $items = Database::fetchAll(
            "SELECT pi.*, pc.slug as category_slug, pc.name as category_name
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC",
            [$studentId]
        );

        $results = [];
        $totalScore = 0;
        $issues = [];

        foreach ($items as $item) {
            $score = 0;
            $itemIssues = [];

            // 1. Description quality (max 25 pts)
            $descLen = mb_strlen($item['description'] ?? '');
            if ($descLen >= 200) {
                $score += 25;
            } elseif ($descLen >= 100) {
                $score += 15;
            } elseif ($descLen >= 50) {
                $score += 8;
            } else {
                $score += max(0, $descLen / 50 * 8);
                if ($descLen < 50) {
                    $itemIssues[] = 'คำอธิบายสั้นเกินไป (' . $descLen . ' ตัวอักษร) — ควรเขียนอย่างน้อย 200 ตัวอักษร';
                }
            }

            // 2. Skills attached (max 20 pts)
            $skillCount = (int) Database::fetch(
                "SELECT COUNT(*) as cnt FROM portfolio_skills WHERE portfolio_id = ?",
                [(int) $item['id']]
            )['cnt'];
            $score += min($skillCount * 5, 20);
            if ($skillCount === 0) {
                $itemIssues[] = 'ไม่ได้ระบุทักษะที่ใช้ — ควรเลือกอย่างน้อย 2 ทักษะ';
            }

            // 3. Achievement level (max 20 pts)
            $level = $item['achievement_level'] ?? null;
            $levelScore = ($level && isset(self::ACHIEVEMENT_ORDER[$level]))
                ? self::ACHIEVEMENT_ORDER[$level]
                : 0;
            $score += $levelScore * 4; // school=4, international=20
            if (!$level) {
                $itemIssues[] = 'ไม่ได้ระบุระดับความสำเร็จ';
            }

            // 4. Award/recognition (max 15 pts)
            if (!empty($item['award'])) {
                $score += 15;
            }

            // 5. File attachment (max 10 pts)
            if (!empty($item['file_path'])) {
                $score += 10;
            } elseif (!empty($item['thumbnail'])) {
                $score += 5;
            } else {
                $itemIssues[] = 'ไม่มีไฟล์แนบหรือรูปภาพ';
            }

            // 6. Category fit (max 10 pts) — bonus if category matches content keywords
            $score += 10; // default full — hard to auto-detect reliably

            $totalScore += $score;
            $results[] = [
                'id' => (int) $item['id'],
                'title' => $item['title'],
                'category' => $item['category_name'],
                'score' => $score,
                'max_score' => 100,
                'grade' => self::scoreToGrade($score),
                'issues' => $itemIssues,
            ];

            if (!empty($itemIssues)) {
                $issues[] = [
                    'portfolio_id' => (int) $item['id'],
                    'title' => $item['title'],
                    'issues' => $itemIssues,
                ];
            }
        }

        $count = max(1, count($items));
        return [
            'total_items' => count($items),
            'avg_score' => round($totalScore / $count, 1),
            'avg_grade' => self::scoreToGrade(round($totalScore / $count)),
            'items' => $results,
            'items_with_issues' => $issues,
            'issue_count' => count($issues),
        ];
    }

    /**
     * Smart Recommendations — คำแนะนำอัจฉริยะที่สร้างจาก Gap Analysis
     *
     * ผสาน gap จากทุกสาขาวิชา → จัดลำดับความสำคัญ → ให้คำแนะนำที่เป็นรูปธรรม
     *
     * @param int $userId
     * @return array
     */
    public static function getSmartRecommendations(int $userId): array
    {
        $analysis = self::analyzeFieldReadiness($userId);

        if (isset($analysis['error'])) {
            return $analysis;
        }

        // ---- Priority scoring for each recommendation ----
        $prioritized = [];
        $seen = [];

        foreach ($analysis['recommendations'] as $rec) {
            $key = md5($rec);
            if (isset($seen[$key]))
                continue;
            $seen[$key] = true;

            $priority = self::calculateRecommendationPriority($rec, $analysis);

            $prioritized[] = [
                'text' => $rec,
                'priority' => $priority, // high | medium | low
                'icon' => self::recommendationIcon($rec),
            ];
        }

        // Sort: high first
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($prioritized, fn($a, $b) => $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']]);

        return [
            'student_name' => $analysis['student_profile']['full_name'] ?? 'นักเรียน',
            'top_track' => $analysis['top_matches'][0] ?? null,
            'recommendations' => $prioritized,
            'total_gaps' => count($analysis['gaps']),
        ];
    }

    /**
     * Build AI prompt context สำหรับส่งให้ Gemini
     *
     * ใช้เมื่อต้องการคำอธิบายเชิงลึกจาก LLM (ต้องมี API key)
     *
     * @param int $userId
     * @param string $question คำถามที่นักเรียนถาม
     * @return array
     */
    public static function buildAIContext(int $userId, string $question = ''): array
    {
        $analysis = self::analyzeFieldReadiness($userId);

        if (isset($analysis['error'])) {
            return $analysis;
        }

        // Build a comprehensive context string for AI prompts
        $context = "ข้อมูลนักเรียน: {$analysis['student_profile']['full_name']}\n";
        $context .= "GPA: {$analysis['gpa']}\n";
        $context .= "ชั่วโมง Logbook: {$analysis['logbook_hours']}\n";
        $context .= "ผลงานรวม: {$analysis['portfolio_profile']['total_count']} ชิ้น\n";
        $context .= "หมวดหมู่ผลงาน: ";
        foreach ($analysis['portfolio_profile']['by_category'] as $cat) {
            $context .= "{$cat['name']} ({$cat['count']}), ";
        }
        $context .= "\nทักษะหลัก: ";
        foreach ($analysis['skills_profile'] as $sk) {
            $context .= "{$sk['skill_name']} ({$sk['avg_proficiency']}/5), ";
        }
        $context .= "\nRIASEC: " . ($analysis['riasec_profile']['dominant'] ?? 'ยังไม่ประเมิน');
        $context .= "\n\nสาขาที่เหมาะสมที่สุด:\n";
        foreach ($analysis['top_matches'] as $tm) {
            $context .= "  {$tm['track_name']} ({$tm['track_code']}) — ความพร้อม {$tm['readiness_pct']}%\n";
        }
        $context .= "\nช่องว่างที่พบ:\n";
        foreach ($analysis['gaps'] as $gap) {
            $context .= "  - {$gap}\n";
        }

        return [
            'context' => $context,
            'analysis' => $analysis,
            'question' => $question,
            'prompt_hint' => self::generatePromptHint($analysis),
        ];
    }

    // ============================================================
    // INTERNAL: DATA FETCHERS
    // ============================================================

    private static function getStudentProfile(int $studentId, int $userId): array
    {
        return Database::fetch(
            "SELECT sp.*, u.username, u.email, u.role
             FROM student_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE sp.id = ?",
            [$studentId]
        ) ?: [];
    }

    private static function getPortfolioProfile(int $studentId): array
    {
        $items = Database::fetchAll(
            "SELECT pi.*, pc.slug as category_slug, pc.name as category_name, pc.icon as category_icon
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC",
            [$studentId]
        );

        // By category
        $byCat = Database::fetchAll(
            "SELECT pc.name, pc.slug, pc.icon, COUNT(pi.id) as count
             FROM portfolio_categories pc
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.status = 'published' AND pi.student_id = ?
             GROUP BY pc.id
             HAVING count > 0
             ORDER BY count DESC",
            [$studentId]
        );

        // Awards count
        $awards = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items
             WHERE student_id = ? AND status = 'published' AND award IS NOT NULL AND award != ''",
            [$studentId]
        )['cnt'];

        // Max achievement
        $maxLevel = Database::fetch(
            "SELECT achievement_level FROM portfolio_items
             WHERE student_id = ? AND status = 'published' AND achievement_level IS NOT NULL
             ORDER BY FIELD(achievement_level, 'international', 'national', 'province', 'district', 'school')
             LIMIT 1",
            [$studentId]
        );

        return [
            'total_count' => count($items),
            'items' => $items,
            'by_category' => $byCat,
            'awards_count' => $awards,
            'max_achievement' => $maxLevel['achievement_level'] ?? null,
        ];
    }

    private static function getSkillsProfile(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT sm.skill_name, sm.category_id, sc.name as skill_category,
                    AVG(ps.proficiency) as avg_proficiency,
                    COUNT(DISTINCT pi.id) as usage_count
             FROM portfolio_items pi
             JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             JOIN skills_master sm ON ps.skill_id = sm.id
             LEFT JOIN skill_categories sc ON sm.category_id = sc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             GROUP BY sm.id
             ORDER BY avg_proficiency DESC",
            [$studentId]
        );
    }

    private static function getRIASECProfile(int $userId): array
    {
        $latest = Database::fetch(
            "SELECT dominant_trait, result_data
             FROM student_assessments
             WHERE user_id = ? AND assessment_type = 'riasec'
             ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );

        if (!$latest) {
            return ['dominant' => null, 'scores' => [], 'assessed' => false];
        }

        $scores = [];
        if ($latest['result_data']) {
            $data = json_decode($latest['result_data'], true) ?: [];
            $scores = isset($data['scores']) && is_array($data['scores']) ? $data['scores'] : $data;
        }

        return [
            'dominant' => $latest['dominant_trait'],
            'scores' => $scores,
            'assessed' => true,
        ];
    }

    private static function getStudentGPA(int $studentId): float
    {
        $result = Database::fetch(
            "SELECT AVG(grade) as avg_gpa FROM academic_records WHERE student_id = ?",
            [$studentId]
        );
        return round((float) ($result['avg_gpa'] ?? 0), 2);
    }

    private static function getLogbookHours(int $studentId): float
    {
        $result = Database::fetch(
            "SELECT COALESCE(SUM(hours_spent), 0) as total_hours
             FROM logbooks WHERE student_id = ?",
            [$studentId]
        );
        return round((float) ($result['total_hours'] ?? 0), 1);
    }

    private static function getStudentGoals(int $studentId): array
    {
        $goals = Database::fetchAll(
            "SELECT * FROM student_goals
             WHERE student_id = ? AND status IN ('planning', 'inprogress')
             ORDER BY
                 FIELD(goal_type, 'career', 'major', 'university', 'longterm', 'shortterm'),
                 created_at DESC",
            [$studentId]
        );

        // Try to match goals to field tracks
        $topMatch = null;
        foreach ($goals as $goal) {
            $matchedTrack = self::matchGoalToTrack($goal);
            if ($matchedTrack) {
                $topMatch = [
                    'goal' => $goal,
                    'track_code' => $matchedTrack,
                    'track_name' => self::FIELD_REQUIREMENTS[$matchedTrack]['name'] ?? $matchedTrack,
                ];
                break;
            }
        }

        return [
            'goals' => $goals,
            'top_match' => $topMatch,
        ];
    }

    // ============================================================
    // INTERNAL: SCORING ENGINE
    // ============================================================

    /**
     * คะแนนความพร้อมรายสาขา (0-100%)
     *
     * Weight breakdown:
     *   GPA:              20%
     *   Required Categories: 25%
     *   Required Skills:   15%
     *   RIASEC Match:      15%
     *   Achievement Level: 10%
     *   Logbook Hours:      5%
     *   Awards:             10%
     */
    private static function scoreFieldTrack(array $ctx): array
    {
        $reqs = $ctx['requirements'];
        $track = $ctx['track'];
        $detail = $ctx['detail_mode'] ?? false;

        $gaps = [];
        $strengths = [];
        $scores = [];

        // --- 1. GPA (20%) ---
        $gpa = $ctx['gpa'];
        $minGpa = $reqs['min_gpa'];
        $gpaPct = min(100, ($gpa / $minGpa) * 100);
        $scores['gpa'] = round($gpaPct, 1);
        if ($gpa >= $minGpa) {
            $strengths[] = "GPA {$gpa} ผ่านเกณฑ์ขั้นต่ำ ({$minGpa})";
        } else {
            $diff = round($minGpa - $gpa, 2);
            $gaps[] = "GPA {$gpa} ต่ำกว่าเกณฑ์ {$diff} (ต้องการ {$minGpa})";
        }

        // --- 2. Required Categories (25%) ---
        $catScore = 0;
        $catTotal = count($reqs['required_cats']);
        $catDetails = [];

        foreach ($reqs['required_cats'] as $catSlug => $minCount) {
            $actual = 0;
            foreach ($ctx['portfolio']['by_category'] as $pc) {
                if ($pc['slug'] === $catSlug) {
                    $actual = (int) $pc['count'];
                    break;
                }
            }
            $catPct = min(100, ($actual / $minCount) * 100);
            $catScore += $catPct;

            if ($detail) {
                $catDetails[] = [
                    'category' => $catSlug,
                    'required' => $minCount,
                    'actual' => $actual,
                    'pct' => round($catPct, 1),
                ];
            }

            if ($actual >= $minCount) {
                $strengths[] = "มีผลงานหมวด {$catSlug} {$actual} ชิ้น (ต้องการ {$minCount})";
            } else {
                $catName = self::getCategoryName($catSlug);
                $gaps[] = "ขาดผลงานหมวด '{$catName}' — มี {$actual} ต้องการ {$minCount}";
            }
        }
        $scores['categories'] = $catTotal > 0 ? round($catScore / $catTotal, 1) : 100;

        // --- 3. Required Skills (15%) ---
        $skillScore = 0;
        $skillTotal = count($reqs['required_skills']);
        $skillDetails = [];

        foreach ($reqs['required_skills'] as $skillName => $minProf) {
            $actualProf = 0;
            foreach ($ctx['skills'] as $sk) {
                if (stripos($sk['skill_name'], $skillName) !== false || $sk['skill_name'] === $skillName) {
                    $actualProf = (float) $sk['avg_proficiency'];
                    break;
                }
            }
            $skillPct = min(100, ($actualProf / $minProf) * 100);
            $skillScore += $skillPct;

            if ($detail) {
                $skillDetails[] = [
                    'skill' => $skillName,
                    'required' => $minProf,
                    'actual' => $actualProf,
                    'pct' => round($skillPct, 1),
                ];
            }

            if ($actualProf >= $minProf) {
                $strengths[] = "ทักษะ {$skillName} อยู่ระดับ {$actualProf}/5";
            } else {
                $gaps[] = "ทักษะ '{$skillName}' อ่อน ({$actualProf}/5 — ต้องการ {$minProf}/5)";
            }
        }
        $scores['skills'] = $skillTotal > 0 ? round($skillScore / $skillTotal, 1) : 100;

        // --- 4. RIASEC Match (15%) ---
        $riasecScore = 0;
        $riasecTotal = count($reqs['riasec_target']);
        $riasecDetails = [];

        foreach ($reqs['riasec_target'] as $trait => $minVal) {
            $actualVal = $ctx['riasec']['scores'][$trait] ?? 0;
            $rPct = min(100, ($actualVal / $minVal) * 100);
            $riasecScore += $rPct;

            if ($detail) {
                $riasecDetails[] = [
                    'trait' => $trait,
                    'required' => $minVal,
                    'actual' => $actualVal,
                    'pct' => round($rPct, 1),
                ];
            }

            if ($actualVal < $minVal && $ctx['riasec']['assessed']) {
                $gaps[] = "RIASEC trait '{$trait}' = {$actualVal} ต่ำกว่าเกณฑ์ ({$minVal})";
            }
        }
        $scores['riasec'] = $riasecTotal > 0 ? round($riasecScore / $riasecTotal, 1) : 50;
        if (!$ctx['riasec']['assessed']) {
            $gaps[] = 'ยังไม่ทำแบบประเมิน RIASEC';
        }

        // --- 5. Achievement Level (10%) ---
        $maxLevel = $ctx['portfolio']['max_achievement'] ?? null;
        $minLevel = $reqs['min_achie_level'];
        $maxOrder = self::ACHIEVEMENT_ORDER[$maxLevel] ?? 0;
        $minOrder = self::ACHIEVEMENT_ORDER[$minLevel] ?? 1;
        $achPct = min(100, ($maxOrder / $minOrder) * 100);
        $scores['achievement'] = round($achPct, 1);
        if ($maxOrder >= $minOrder) {
            $strengths[] = "ระดับความสำเร็จสูงสุด {$maxLevel} ผ่านเกณฑ์";
        } else {
            $gaps[] = "ระดับความสำเร็จสูงสุด '{$maxLevel}' ต่ำกว่าเกณฑ์ '{$minLevel}'";
        }

        // --- 6. Logbook Hours (5%) ---
        $hrs = $ctx['logbookHrs'];
        $minHrs = $reqs['min_logbook_h'];
        $hrsPct = min(100, ($hrs / $minHrs) * 100);
        $scores['logbook'] = round($hrsPct, 1);
        if ($hrs >= $minHrs) {
            $strengths[] = "ชั่วโมง Logbook {$hrs} ชม. ผ่านเกณฑ์";
        } else {
            $gaps[] = "ชั่วโมง Logbook {$hrs} ชม. ต่ำกว่าเกณฑ์ ({$minHrs} ชม.)";
        }

        // --- 7. Awards (10%) ---
        $awards = $ctx['portfolio']['awards_count'];
        $minAwards = $reqs['min_awards'];
        $awardsPct = min(100, ($awards / $minAwards) * 100);
        $scores['awards'] = round($awardsPct, 1);
        if ($awards >= $minAwards) {
            $strengths[] = "มีรางวัล {$awards} รายการ ผ่านเกณฑ์";
        } else {
            $gaps[] = "มีรางวัล {$awards} รายการ ต่ำกว่าเกณฑ์ ({$minAwards})";
        }

        // --- Weighted Total ---
        $weights = [
            'gpa' => 0.20,
            'categories' => 0.25,
            'skills' => 0.15,
            'riasec' => 0.15,
            'achievement' => 0.10,
            'logbook' => 0.05,
            'awards' => 0.10,
        ];

        $totalScore = 0;
        foreach ($weights as $key => $w) {
            $totalScore += ($scores[$key] ?? 0) * $w;
        }
        $totalScore = round($totalScore, 1);

        $result = [
            'track_code' => $track,
            'track_name' => $reqs['name'],
            'readiness_pct' => $totalScore,
            'gap_count' => count($gaps),
            'gaps' => $gaps,
            'strengths' => $strengths,
            'scores' => $scores,
            'recommendations' => self::generateRecommendations($track, $reqs, $gaps, $ctx),
        ];

        if ($detail) {
            $result['category_details'] = $catDetails ?? [];
            $result['skill_details'] = $skillDetails ?? [];
            $result['riasec_details'] = $riasecDetails ?? [];
        }

        return $result;
    }

    // ============================================================
    // INTERNAL: RECOMMENDATION GENERATORS
    // ============================================================

    private static function generateRecommendations(
        string $track,
        array $reqs,
        array $gaps,
        array $ctx
    ): array {
        $recs = [];

        // Generate actionable recommendations from gaps
        foreach ($gaps as $gap) {
            $rec = self::gapToRecommendation($gap, $track, $reqs, $ctx);
            if ($rec) {
                $recs[] = $rec;
            }
        }

        // Add proactive suggestions even without gaps
        $portfolio = $ctx['portfolio'];
        if ($portfolio['total_count'] < 10) {
            $recs[] = 'เพิ่มผลงานให้ได้อย่างน้อย 10 ชิ้นเพื่อสร้างความน่าเชื่อถือ';
        }
        if (!$ctx['riasec']['assessed']) {
            $recs[] = 'ทำแบบประเมิน RIASEC เพื่อวิเคราะห์ความเหมาะสมรายสาขาวิชา';
        }

        return $recs;
    }

    private static function gapToRecommendation(string $gap, string $track, array $reqs, array $ctx): ?string
    {
        // Category gaps
        if (stripos($gap, 'หมวด') !== false || stripos($gap, 'ขาดผลงาน') !== false) {
            return "สร้างผลงานในหมวดที่เกี่ยวข้องกับ{$reqs['name']} — เข้าร่วมกิจกรรมวิชาการ ชมรม หรือแข่งขัน";
        }

        // Skill gaps
        if (stripos($gap, 'ทักษะ') !== false && stripos($gap, 'อ่อน') !== false) {
            $skillName = preg_replace("/ทักษะ\s*'([^']+)'.*/u", '$1', $gap);
            return "พัฒนาทักษะ '{$skillName}' ผ่านการฝึกปฏิบัติและบันทึกใน Logbook";
        }

        // GPA gaps
        if (stripos($gap, 'GPA') !== false && stripos($gap, 'ต่ำกว่า') !== false) {
            return 'วางแผนการเรียนกับครูที่ปรึกษาเพื่อเพิ่ม GPA ให้ผ่านเกณฑ์';
        }

        // RIASEC gaps
        if (stripos($gap, 'RIASEC') !== false && stripos($gap, 'ประเมิน') === false) {
            return 'ตรวจสอบผล RIASEC และทำแบบประเมินใหม่หากคะแนนต่ำกว่าเกณฑ์';
        }

        // Logbook gaps
        if (stripos($gap, 'Logbook') !== false) {
            return 'บันทึกกิจกรรม Logbook อย่างสม่ำเสมอ — ตั้งเป้าหมายอย่างน้อยสัปดาห์ละ 2 ชั่วโมง';
        }

        // Award gaps
        if (stripos($gap, 'รางวัล') !== false) {
            return 'เข้าร่วมแข่งขันวิชาการระดับภายนอกเพื่อสะสมรางวัล';
        }

        // Achievement gaps
        if (stripos($gap, 'ระดับความสำเร็จ') !== false) {
            return 'ยกระดับผลงานจากภายในโรงเรียนสู่ระดับภายนอก (district/province ขึ้นไป)';
        }

        // Fallback
        return null;
    }

    // ============================================================
    // INTERNAL: HELPERS
    // ============================================================

    private static function getStudentProfileId(int $userId): ?int
    {
        $result = Database::fetch(
            "SELECT id FROM student_profiles WHERE user_id = ?",
            [$userId]
        );
        return $result ? (int) $result['id'] : null;
    }

    private static function matchGoalToTrack(array $goal): ?string
    {
        $target = strtolower(($goal['target'] ?? '') . ' ' . ($goal['target_details'] ?? ''));

        // Keyword mapping
        $keywords = [
            'MED_HEALTH' => ['แพทย์', 'ทันต', 'เภสัช', 'สัตว', 'พยาบาล', 'สาธารณสุข', 'สุขภาพ', 'med', 'doctor'],
            'ENGINEERING' => ['วิศว', 'คอมพิวเตอ', 'เครื่องกล', 'ไฟฟ้า', 'โยธา', 'อุตสาหการ', 'eng', 'engineering'],
            'PURE_SCIENCE' => ['วิทยา', 'ฟิกส์', 'เคมี', 'ชีว', 'คณิต', 'science', 'physic', 'chem'],
            'COMPUTING' => ['เทคโนโลยี', 'ไอที', 'ข้อมูล', 'data', 'computer', 'it', 'software', 'ai'],
            'LIBERAL_ARTS' => ['ศิลป', 'นิติ', 'รัฐ', 'มนุษ', 'ภาษา', 'อังกฤษ', 'วรรณ', 'arts', 'law'],
            'BUSINESS' => ['บริหาร', 'บัญชี', 'จัดการ', 'ธุรกิจ', 'econom', 'business', 'commerce'],
            'DESIGN' => ['สถาปัต', 'ออกแบบ', 'design', 'architect', 'art', 'fine art'],
        ];

        foreach ($keywords as $track => $words) {
            foreach ($words as $word) {
                if (stripos($target, $word) !== false) {
                    return $track;
                }
            }
        }

        return null;
    }

    private static function getCategoryName(string $slug): string
    {
        $nameMap = [
            'science-project' => 'โครงงานวิทยาศาสตร์',
            'mathematics' => 'คณิตศาสตร์',
            'technology' => 'เทคโนโลยี',
            'language' => 'ภาษา',
            'art-music' => 'ศิลปะ/ดนตรี',
            'sports' => 'กีฬา',
            'volunteer' => 'จิตอาสา',
            'leadership' => 'ผู้นำ',
            'scholarship-award' => 'ทุน/รางวัล',
            'research' => 'วิจัย',
            'others' => 'อื่นๆ',
        ];
        return $nameMap[$slug] ?? $slug;
    }

    private static function scoreToGrade(int $score): string
    {
        if ($score >= 90)
            return 'A';
        if ($score >= 80)
            return 'B+';
        if ($score >= 70)
            return 'B';
        if ($score >= 60)
            return 'C+';
        if ($score >= 50)
            return 'C';
        if ($score >= 40)
            return 'D';
        return 'F';
    }

    private static function calculateRecommendationPriority(string $rec, array $analysis): string
    {
        // High priority: gaps in top-matched field
        $topTrack = $analysis['top_matches'][0]['track_code'] ?? null;
        if ($topTrack && isset($analysis['field_readiness'][$topTrack])) {
            $fieldGaps = $analysis['field_readiness'][$topTrack]['gaps'] ?? [];
            foreach ($fieldGaps as $gap) {
                if (stripos($rec, $gap) !== false || stripos($gap, preg_replace('/[^\p{L}\p{N}]/u', '', $rec)) !== false) {
                    return 'high';
                }
            }
        }

        // Medium: any GPA, RIASEC, or logbook mention
        if (stripos($rec, 'GPA') !== false || stripos($rec, 'RIASEC') !== false || stripos($rec, 'Logbook') !== false) {
            return 'medium';
        }

        return 'low';
    }

    private static function recommendationIcon(string $rec): string
    {
        if (stripos($rec, 'GPA') !== false)
            return '📚';
        if (stripos($rec, 'RIASEC') !== false)
            return '🧭';
        if (stripos($rec, 'Logbook') !== false)
            return '📓';
        if (stripos($rec, 'รางวัล') !== false)
            return '🏆';
        if (stripos($rec, 'ทักษะ') !== false)
            return '🔧';
        if (stripos($rec, 'ผลงาน') !== false || stripos($rec, 'หมวด') !== false)
            return '📁';
        if (stripos($rec, 'แข่งขัน') !== false)
            return '⚡';
        if (stripos($rec, 'วางแผน') !== false)
            return '📋';
        return '💡';
    }

    private static function generatePromptHint(array $analysis): string
    {
        $parts = [];

        if (!empty($analysis['top_matches'])) {
            $top = $analysis['top_matches'][0];
            $parts[] = "นักเรียนเหมาะสมกับ '{$top['track_name']}' มากที่สุด ({$top['readiness_pct']}%)";
        }

        if ($analysis['gpa'] < 3.0) {
            $parts[] = "GPA {$analysis['gpa']} ควรเร่งพัฒนาผลการศึกษา";
        }

        if ($analysis['logbook_hours'] < 20) {
            $parts[] = "ชั่วโมงกิจกรรมน้อย ({$analysis['logbook_hours']} ชม.) ควรบันทึกสม่ำเสมอ";
        }

        if (!$analysis['riasec_profile']['assessed']) {
            $parts[] = 'ยังไม่ทำ RIASEC assessment';
        }

        return implode('. ', $parts);
    }

    // ============================================================
    // PHASE 5.1: AI-Driven TCAS Gap Analysis (Gemini-Powered)
    // ============================================================

    /**
     * Cache TTL for TCAS Gap Analysis — 30 days.
     */
    private const TCAS_CACHE_TTL_DAYS = 30;

    /**
     * Gemini API endpoint for content generation.
     */
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Faculty track mapping — user-friendly key → AIService track code.
     */
    private const FACULTY_TRACK_MAP = [
        // MED_HEALTH (สายสุขภาพ)
        'medicine' => 'MED_HEALTH',
        'dentistry' => 'MED_HEALTH',
        'pharmacy' => 'MED_HEALTH',
        'nursing' => 'MED_HEALTH',
        'veterinary' => 'MED_HEALTH',
        // ENGINEERING & COMPUTING (สายวิศวะนวัตกรรม)
        'engineering' => 'ENGINEERING',
        'computing' => 'COMPUTING',
        // DESIGN (ศิลปกรรม/สถาปัตยกรรม)
        'architecture' => 'DESIGN',
        'design' => 'DESIGN',
        // PURE_SCIENCE (วิทยาศาสตร์)
        'science' => 'PURE_SCIENCE',
        // BUSINESS (บริหารธุรกิจ)
        'business' => 'BUSINESS',
        // LIBERAL_ARTS (มนุษยศาสตร์/สังคมศาสตร์)
        'law' => 'LIBERAL_ARTS',
        'education' => 'LIBERAL_ARTS',
        'liberal_arts' => 'LIBERAL_ARTS',
    ];

    /**
     * Generate a TCAS Gap Analysis for a student targeting a specific faculty.
     *
     * Flow:
     *   1. Check cache (ai_tcas_gap_analysis) — return if valid
     *   2. Compile anonymized Student Dossier
     *   3. Build engineered System Prompt for Gemini
     *   4. Call Gemini API via native cURL
     *   5. Parse JSON response (strip markdown wrappers)
     *   6. Cache result with 30-day expiration
     *   7. On API failure → fall back to rule-based analysis
     *
     * @param int    $studentId     The user ID (users.id)
     * @param string $targetFaculty One of: medicine, engineering, science, computing, liberal_arts, business, design
     * @return array Parsed analysis with 'ai_generated' => true|false flag
     */
    public static function generateTCASGapAnalysis(int $studentId, string $targetFaculty, bool $forceRefresh = false): array
    {
        // Normalize faculty key
        $targetFaculty = strtolower(trim($targetFaculty));
        $trackCode = self::FACULTY_TRACK_MAP[$targetFaculty] ?? null;
        if (!$trackCode) {
            return ['error' => "คณะ '{$targetFaculty}' ไม่รองรับ — ตัวเลือก: " . implode(', ', array_keys(self::FACULTY_TRACK_MAP))];
        }

        // ---- 1. Check cache (skip fallback cache if API key is configured or forceRefresh is true) ----
        if (!$forceRefresh) {
            $cached = self::getCachedTCASAnalysis($studentId, $targetFaculty);
            if ($cached) {
                return $cached;
            }
        }

        // ---- 2. Compile Student Dossier ----
        $dossier = self::buildStudentDossier($studentId);
        if (isset($dossier['error'])) {
            return $dossier;
        }

        // ---- 3. Try Gemini AI ----
        $geminiResult = self::callGeminiAPI($dossier, $targetFaculty, $trackCode);

        if ($geminiResult) {
            // Parse and validate JSON
            $parsed = self::parseGeminiJSON($geminiResult);
            if ($parsed) {
                $parsed['ai_generated'] = true;
                $parsed['ai_model'] = AIEngine::getLastUsedModel();
                $parsed['target_faculty'] = $targetFaculty;
                $parsed['track_code'] = $trackCode;

                // ---- 6. Cache AI result (30 days) ----
                self::cacheTCASAnalysis($studentId, $targetFaculty, $parsed, false);

                return $parsed;
            }
        }

        // ---- 7. Fallback: Rule-based analysis ----
        $fallbackResult = self::ruleBasedTCASGap($studentId, $targetFaculty, $trackCode, $dossier);
        $fallbackResult['ai_generated'] = false;
        $fallbackResult['ai_model'] = 'rule_based_fallback_v1';
        $fallbackResult['target_faculty'] = $targetFaculty;
        $fallbackResult['track_code'] = $trackCode;

        $lastErr = AIEngine::getLastError();
        if ($lastErr && str_contains(strtolower($lastErr), 'billing')) {
            $fallbackResult['fallback_message'] = 'เปิด Vertex AI API สำเร็จแล้ว แต่คีย์ Vertex AI (AQ...) บังคับให้ผูกบัญชี Billing บน Google Cloud Project #340499679446 (หรือแนะนำให้ใช้คีย์ฟรีที่ขึ้นต้นด้วย AIzaSy... จาก aistudio.google.com/apikey แทน)';
        } elseif ($lastErr && (str_contains($lastErr, 'denied access') || str_contains($lastErr, 'disabled') || str_contains($lastErr, 'API has not been used'))) {
            $fallbackResult['fallback_message'] = 'คีย์ AI ปัจจุบันยังไม่ได้เปิดสิทธิ์ Vertex AI / Gemini API บน Google Cloud Console — ระบบใช้การวิเคราะห์เชิงสถิติ (Rule-based) แทนชั่วคราว';
        } elseif ($lastErr && str_contains($lastErr, 'quota')) {
            $fallbackResult['fallback_message'] = 'โควต้า AI ภายนอกเต็มชั่วคราว — ใช้การวิเคราะห์เชิงสถิติ (Rule-based) แทน';
        } else {
            $fallbackResult['fallback_message'] = 'AI ภายนอกไม่พร้อมใช้งาน — ใช้การวิเคราะห์แบบ Rule-based แทน';
        }

        // Cache the fallback result for a short window (5 minutes) so newly updated API keys take effect quickly
        self::cacheTCASAnalysis($studentId, $targetFaculty, $fallbackResult, true);

        return $fallbackResult;
    }

    /**
     * Check for a valid cached TCAS analysis.
     * Ignores fallback records when an API key is configured so real AI can be attempted.
     */
    private static function getCachedTCASAnalysis(int $studentId, string $targetFaculty): ?array
    {
        $hasKey = !empty(Settings::get('gemini_api_key') ?: Env::get('GEMINI_API_KEY', ''));

        $sql = "SELECT * FROM ai_tcas_gap_analysis
                WHERE student_id = ?
                  AND target_faculty = ?
                  AND expires_at > NOW()";
        if ($hasKey) {
            $sql .= " AND is_fallback = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT 1";

        $row = Database::fetch($sql, [$studentId, $targetFaculty]);

        if (!$row) {
            return null;
        }

        $data = json_decode($row['analysis_json'], true);
        if (!$data) {
            return null;
        }

        $data['cached'] = true;
        $data['cached_at'] = $row['created_at'];
        $data['is_fallback'] = (bool) $row['is_fallback'];

        return $data;
    }

    /**
     * Cache a TCAS analysis result.
     */
    private static function cacheTCASAnalysis(int $studentId, string $targetFaculty, array $data, bool $isFallback): void
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $expiresAt = $isFallback
            ? date('Y-m-d H:i:s', strtotime('+1 hour'))
            : date('Y-m-d H:i:s', strtotime('+' . self::TCAS_CACHE_TTL_DAYS . ' days'));
        $aiModel = $data['ai_model'] ?? 'unknown';

        // Try update first (ON DUPLICATE KEY)
        $existing = Database::fetch(
            "SELECT id FROM ai_tcas_gap_analysis WHERE student_id = ? AND target_faculty = ? AND ai_model = ?",
            [$studentId, $targetFaculty, $aiModel]
        );

        if ($existing) {
            Database::execute(
                "UPDATE ai_tcas_gap_analysis
                 SET analysis_json = ?, overall_readiness = ?, is_fallback = ?, expires_at = ?
                 WHERE id = ?",
                [
                    $json,
                    $data['overall_readiness_pct'] ?? 0,
                    (int) $isFallback,
                    $expiresAt,
                    (int) $existing['id'],
                ]
            );
        } else {
            Database::insert(
                "INSERT INTO ai_tcas_gap_analysis
                    (student_id, target_faculty, ai_model, analysis_json, overall_readiness, is_fallback, expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $studentId,
                    $targetFaculty,
                    $aiModel,
                    $json,
                    $data['overall_readiness_pct'] ?? 0,
                    (int) $isFallback,
                    $expiresAt,
                ]
            );
        }
    }

    /**
     * Build an anonymized Student Dossier from multiple data sources.
     * This is the "payload" we send to Gemini AI.
     */
    private static function buildStudentDossier(int $userId): array
    {
        $studentId = self::getStudentProfileId($userId);
        if (!$studentId) {
            return ['error' => 'ไม่พบโปรไฟล์นักเรียน'];
        }

        // Basic profile
        $profile = Database::fetch(
            "SELECT full_name, nickname, class_year, class_room, bio, date_of_birth
             FROM student_profiles WHERE id = ?",
            [$studentId]
        );

        if (!$profile) {
            return ['error' => 'ข้อมูลโปรไฟล์ไม่ครบถ้วน'];
        }

        // GPA
        $gpaRow = Database::fetch(
            "SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa
             FROM academic_records WHERE student_id = ?",
            [$studentId]
        );
        $gpa = (float) ($gpaRow['gpa'] ?? 0);

        // Academic records by subject
        $academics = Database::fetchAll(
            "SELECT subject, grade, credit, semester, academic_year
             FROM academic_records
             WHERE student_id = ?
             ORDER BY academic_year DESC, semester
             LIMIT 30",
            [$studentId]
        );

        // Portfolio summary
        $portfolio = Database::fetchAll(
            "SELECT pi.id, pi.title, pi.description, pi.category_id, pi.achievement_level,
                    pi.award, pi.event_date, pc.name as category_name,
                    (SELECT COUNT(*) FROM portfolio_skills WHERE portfolio_id = pi.id) as skill_count
             FROM portfolio_items pi
             LEFT JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'
             ORDER BY pi.created_at DESC
             LIMIT 20",
            [$studentId]
        );

        // Skills
        $skills = Database::fetchAll(
            "SELECT DISTINCT sm.skill_name, sm.description as skill_desc, MAX(ps.proficiency) as proficiency
             FROM portfolio_skills ps
             JOIN skills_master sm ON ps.skill_id = sm.id
             JOIN portfolio_items pi ON ps.portfolio_id = pi.id
             WHERE pi.student_id = ?
             GROUP BY sm.skill_name, sm.description
             ORDER BY proficiency DESC",
            [$studentId]
        );

        // RIASEC
        $riasec = Database::fetch(
            "SELECT assessment_type, dominant_trait, result_data
             FROM student_assessments
             WHERE user_id = ? AND assessment_type = 'riasec'
             ORDER BY created_at DESC
             LIMIT 1",
            [$userId]
        );

        // Logbook hours
        $logbookRow = Database::fetch(
            "SELECT COALESCE(SUM(hours_spent), 0) as total_hours,
                    COUNT(*) as entry_count
             FROM logbooks
             WHERE student_id = ?",
            [$userId]
        );

        // Badges
        $badges = Database::fetchAll(
            "SELECT bm.name, bm.icon, ub.earned_at
             FROM user_badges ub
             JOIN badges_master bm ON ub.badge_code = bm.code
             WHERE ub.user_id = ?
             ORDER BY ub.earned_at DESC
             LIMIT 10",
            [$userId]
        );

        // Goals
        $goals = Database::fetchAll(
            "SELECT goal_type, target, target_details, status
             FROM student_goals
             WHERE student_id = ?
             ORDER BY status",
            [$studentId]
        );

        // Build anonymized dossier (no student_code, no exact DOB — privacy)
        return [
            'class_year' => (int) $profile['class_year'],
            'class_room' => $profile['class_room'],
            'bio' => $profile['bio'] ?? '',
            'gpa' => $gpa,
            'academic_records' => $academics,
            'portfolio_count' => count($portfolio),
            'portfolio_items' => array_map(function ($p) {
                return [
                    'title' => $p['title'],
                    'category' => $p['category_name'] ?? 'N/A',
                    'description_length' => mb_strlen($p['description'] ?? ''),
                    'achievement_level' => $p['achievement_level'] ?? 'N/A',
                    'has_award' => !empty($p['award']),
                    'award' => $p['award'] ?? null,
                    'skill_count' => (int) $p['skill_count'],
                    'event_date' => $p['event_date'],
                ];
            }, $portfolio),
            'skills' => $skills,
            'riasec' => $riasec ? [
                'dominant_trait' => $riasec['dominant_trait'],
                'result_data' => !empty($riasec['result_data']) ? json_decode($riasec['result_data'], true) : null,
            ] : null,
            'logbook_hours' => (float) ($logbookRow['total_hours'] ?? 0),
            'logbook_entries' => (int) ($logbookRow['entry_count'] ?? 0),
            'badges' => $badges,
            'goals' => $goals,
        ];
    }

    /**
     * Build the engineered System Prompt for Gemini.
     * This prompt is carefully crafted to force a pure JSON response.
     */
    private static function buildGeminiSystemPrompt(string $targetFaculty, string $trackCode): string
    {
        $facultyNames = [
            'medicine' => 'แพทยศาสตร์ (Medicine)',
            'engineering' => 'วิศวกรรมศาสตร์ (Engineering)',
            'science' => 'วิทยาศาสตร์ (Science)',
            'computing' => 'วิทยาการคอมพิวเตอร์ (Computing/IT)',
            'liberal_arts' => 'อักษรศาสตร์/มนุษยศาสตร์ (Liberal Arts)',
            'business' => 'บริหารธุรกิจ/พาณิชยศาสตร์ (Business)',
            'design' => 'ศิลปกรรมศาสตร์/ออกแบบ (Design)',
        ];

        $facultyName = $facultyNames[$targetFaculty] ?? $targetFaculty;
        $trackReqs = self::FIELD_REQUIREMENTS[$trackCode] ?? [];

        return <<<SYSTEM
คุณคือ "ที่ปรึกษาการเข้าศึกษาต่อระดับมหาวิทยาลัยชั้นนำของไทย" (Elite Thai University Admission Counselor)
มีประสบการณ์กว่า 20 ปี ในการแนะแนวนักเรียนโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย

หน้าที่ของคุณ:
วิเคราะห์ "ช่องว่าง" (Gap Analysis) ระหว่างข้อมูลปัจจุบันของนักเรียน กับข้อกำหนดการรับเข้าศึกษาของคณะ{$facultyName}
ผ่านระบบ TCAS (Thai University Central Admission System)

กฎสำคัญ:
1. คุณต้องตอบกลับเป็น JSON string เท่านั้น — ห้ามมีข้อความอื่นนอกเหนือจาก JSON
2. ห้ามใช้ Markdown code block (เช่น ```json ... ```) — ตอบ JSON เพียวๆ
3. ใช้ภาษาไทยในฟิลด์ข้อความทั้งหมด ยกเว้น key เป็นภาษาอังกฤษ
4. วิเคราะห์อย่างเป็นกลาง ตรงไปตรงมา แต่ให้กำลังใจ
5. ให้ข้อมูลที่ปฏิบัติได้จริง (actionable) เท่านั้น

โครงสร้าง JSON ที่ต้องการ (ต้องครบทุก key):
{
  "overall_readiness_pct": ตัวเลข 0-100,
  "faculty_name": "ชื่อคณะ",
  "gpa_analysis": {
    "current_gpa": ตัวเลข,
    "expected_gpa": ตัวเลขที่ต้องการ,
    "gap": ตัวเลข (expected - current),
    "verdict": "พร้อม/ต้องปรับปรุง/วิกฤต",
    "advice": "คำแนะนำ"
  },
  "portfolio_analysis": {
    "total_items": ตัวเลข,
    "relevant_categories": ["หมวดที่เกี่ยวข้องกับคณะ"],
    "missing_categories": ["หมวดที่ขาด"],
    "quality_score_pct": ตัวเลข 0-100,
    "advice": "คำแนะนำ"
  },
  "skills_gap": {
    "existing_skills": ["ทักษะที่มี"],
    "missing_skills": ["ทักษะที่ขาด"],
    "advice": "คำแนะนำ"
  },
  "riasec_alignment": {
    "dominant_traits": ["RIASEC traits เด่น"],
    "alignment": "สอดคล้อง/ปานกลาง/ไม่สอดคล้อง",
    "advice": "คำแนะนำ"
  },
  "soft_skills_missing": ["soft skills ที่ควรพัฒนา"],
  "extracurricular_gaps": ["กิจกรรมที่ควรทำเพิ่ม"],
  "action_plan": [
    {"priority": "สูง/กลาง/ต่ำ", "action": "สิ่งที่ต้องทำ", "timeline": "กรอบเวลา", "impact": "ผลกระทบ"}
  ],
  "encouragement": "ข้อความให้กำลังใจ"
}

ข้อมูลขั้นต่ำของคณะ{$facultyName}:
SYSTEM;
    }

    /**
     * Build the user prompt containing the student dossier.
     */
    private static function buildGeminiUserPrompt(array $dossier): string
    {
        return "นี่คือข้อมูลนักเรียน (Student Dossier) — กรุณาวิเคราะห์ Gap Analysis ให้:\n\n"
            . json_encode($dossier, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Call Gemini API via AIEngine (automatically reads Settings/.env, closes DB before cURL, and handles model fallback).
     *
     * @return string|null Raw API response text, or null on failure
     */
    private static function callGeminiAPI(array $dossier, string $targetFaculty, string $trackCode): ?string
    {
        $systemPrompt = self::buildGeminiSystemPrompt($targetFaculty, $trackCode);
        $userPrompt = self::buildGeminiUserPrompt($dossier);

        return AIEngine::generateResponse($userPrompt, $systemPrompt, [
            'temperature' => 0.4,
            'topK' => 32,
            'topP' => 0.9,
            'maxOutputTokens' => 4096,
            'responseMimeType' => 'application/json',
        ]);
    }

    /**
     * Parse Gemini's JSON response, stripping markdown code block wrappers.
     *
     * @return array|null Parsed JSON array, or null on failure
     */
    private static function parseGeminiJSON(string $raw): ?array
    {
        // Strip markdown code block wrappers
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/\s*```$/m', '', $cleaned);
        $cleaned = trim($cleaned);

        $parsed = json_decode($cleaned, true);
        if (!$parsed) {
            // Try harder: find JSON object between first { and last }
            $firstBrace = strpos($cleaned, '{');
            $lastBrace = strrpos($cleaned, '}');
            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $extracted = substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1);
                $parsed = json_decode($extracted, true);
            }
        }

        return $parsed;
    }

    /**
     * Rule-based TCAS Gap Analysis — fallback when Gemini AI is unavailable.
     * Uses FIELD_REQUIREMENTS constants + dossier data for structured analysis.
     */
    private static function ruleBasedTCASGap(int $userId, string $targetFaculty, string $trackCode, array $dossier): array
    {
        $reqs = self::FIELD_REQUIREMENTS[$trackCode] ?? null;
        if (!$reqs) {
            return self::genericFallbackAnalysis($targetFaculty, $dossier);
        }

        $gpa = (float) ($dossier['gpa'] ?? 0);
        $portfolio = $dossier['portfolio_items'] ?? [];
        $skills = $dossier['skills'] ?? [];
        $riasec = $dossier['riasec'] ?? null;
        $logbookHrs = (float) ($dossier['logbook_hours'] ?? 0);

        // ---- GPA Analysis ----
        $minGpa = (float) $reqs['min_gpa'];
        $gpaGap = $minGpa - $gpa;
        $gpaVerdict = $gpa >= $minGpa ? 'พร้อม' : ($gpa >= $minGpa - 0.3 ? 'ต้องปรับปรุง' : 'วิกฤต');
        $gpaAdvice = $gpa >= $minGpa
            ? "GPA {$gpa} ผ่านเกณฑ์ขั้นต่ำ ({$minGpa}) ของ{$reqs['name']}แล้ว รักษาระดับนี้ไว้"
            : "GPA {$gpa} ยังต่ำกว่าเกณฑ์ {$minGpa} ต้องเร่งพัฒนาอีก " . number_format(abs($gpaGap), 2) . " หน่วย";

        // ---- Portfolio Analysis ----
        $cats = [];
        foreach ($portfolio as $item) {
            $cat = $item['category'] ?? '';
            $cats[$cat] = ($cats[$cat] ?? 0) + 1;
        }

        $relevantCats = [];
        $missingCats = [];
        foreach ($reqs['required_cats'] as $reqCat => $reqCount) {
            // Map track requirement slug to category name keyword
            $catMap = [
                'science-project' => 'โครงงานวิทยาศาสตร์',
                'research' => 'วิจัย',
                'volunteer' => 'จิตอาสา',
                'leadership' => 'ผู้นำ',
                'technology' => 'เทคโนโลยี',
                'mathematics' => 'คณิตศาสตร์',
                'competition' => 'แข่งขัน',
                'academic' => 'วิชาการ',
            ];
            $mappedName = $catMap[$reqCat] ?? $reqCat;
            $haveCount = 0;
            foreach ($cats as $catName => $cnt) {
                if ($catName === $mappedName || ($mappedName !== '' && mb_strpos($catName, $mappedName) !== false)) {
                    $haveCount += $cnt;
                }
            }

            if ($haveCount >= $reqCount) {
                $relevantCats[] = $mappedName . " ({$haveCount})";
            } else {
                $missingCats[] = $mappedName . " (ต้องการ {$reqCount} มี {$haveCount})";
            }
        }

        $portfolioQuality = count($portfolio) > 0
            ? min(100, round((count($portfolio) / max(1, array_sum($reqs['required_cats']))) * 60
                + (count(array_filter($portfolio, fn($p) => $p['has_award'])) / max(1, count($portfolio))) * 40))
            : 0;

        $portfolioAdvice = empty($missingCats)
            ? 'พอร์ตโฟลิโอมีหมวดหมู่ครบถ้วนแล้ว เพิ่มคุณภาพโดยบรรยายผลงานให้ละเอียดขึ้น'
            : 'ควรเพิ่มผลงานหมวดหมู่: ' . implode(', ', $missingCats);

        // ---- Skills Gap ----
        $existingSkillNames = array_column($skills, 'skill_name');
        $missingSkills = [];
        $existingSkillsList = [];
        foreach ($reqs['required_skills'] as $skillName => $minProf) {
            if (in_array($skillName, $existingSkillNames)) {
                $existingSkillsList[] = $skillName;
            } else {
                $missingSkills[] = $skillName;
            }
        }

        $skillsAdvice = empty($missingSkills)
            ? 'ทักษะครบถ้วนแล้ว — พัฒนาให้เชี่ยวชาญขึ้น'
            : 'ควรพัฒนาทักษะ: ' . implode(', ', $missingSkills);

        // ---- RIASEC Alignment ----
        $riasecAlignment = 'ปานกลาง';
        $riasecAdvice = 'ควรทำแบบทดสอบ RIASEC เพื่อประเมินความเหมาะสม';
        if ($riasec && !empty($riasec['result_data'])) {
            $resultData = $riasec['result_data'];
            // ดึง scores ออกมาถ้าถูกหุ้มอยู่ในคีย์ scores ตามโครงสร้างที่เก็บจริง
            $scores = isset($resultData['scores']) && is_array($resultData['scores']) ? $resultData['scores'] : $resultData;
            $matchCount = 0;
            $totalChecked = 0;
            foreach ($reqs['riasec_target'] as $trait => $minScore) {
                $score = (int) ($scores[$trait] ?? 0);
                $totalChecked++;
                if ($score >= $minScore) {
                    $matchCount++;
                }
            }
            $ratio = $totalChecked > 0 ? $matchCount / $totalChecked : 0;
            $riasecAlignment = $ratio >= 0.8 ? 'สอดคล้อง' : ($ratio >= 0.5 ? 'ปานกลาง' : 'ไม่สอดคล้อง');
            $riasecAdvice = $ratio >= 0.8
                ? 'RIASEC สอดคล้องกับคณะเป็นอย่างดี'
                : 'RIASEC บางมิติยังไม่สอดคล้อง — ควรเน้นพัฒนา traits: ' . implode(', ', array_keys($reqs['riasec_target']));
        }

        // ---- Soft Skills & Extracurricular Gaps ----
        $softSkillsMissing = [];
        $extracurricularGaps = [];

        if ($logbookHrs < ($reqs['min_logbook_h'] ?? 0)) {
            $extracurricularGaps[] = "ชั่วโมงกิจกรรม {$logbookHrs} ชม. ต่ำกว่าเกณฑ์ {$reqs['min_logbook_h']} ชม.";
        }

        $awardsCount = count(array_filter($portfolio, fn($p) => $p['has_award']));
        if ($awardsCount < ($reqs['min_awards'] ?? 0)) {
            $softSkillsMissing[] = 'ควรเข้าร่วมแข่งขันระดับจังหวัด/ประเทศ';
        }

        if (empty($softSkillsMissing)) {
            $softSkillsMissing[] = 'พื้นฐานดี — ควรเน้นการเป็นผู้นำและการทำงานเป็นทีม';
        }
        if (empty($extracurricularGaps)) {
            $extracurricularGaps[] = 'กิจกรรมเพียงพอ — หาโอกาสเข้าร่วมค่าย/อบรมเพิ่ม';
        }

        // ---- Action Plan ----
        $actionPlan = [];
        if ($gpaGap > 0) {
            $actionPlan[] = [
                'priority' => 'สูง',
                'action' => "เร่งพัฒนา GPA จาก {$gpa} เป็น {$minGpa}",
                'timeline' => 'ภาคเรียนถัดไป',
                'impact' => 'เพิ่มโอกาสผ่านเกณฑ์คัดกรอง TCAS',
            ];
        }
        if (!empty($missingSkills)) {
            $actionPlan[] = [
                'priority' => 'สูง',
                'action' => 'เรียนรู้ทักษะ: ' . implode(', ', array_slice($missingSkills, 0, 3)),
                'timeline' => '3-6 เดือน',
                'impact' => 'เติมเต็มความสามารถที่คณะต้องการ',
            ];
        }
        if (!empty($missingCats)) {
            $actionPlan[] = [
                'priority' => 'กลาง',
                'action' => 'สร้างผลงานหมวด: ' . implode(', ', array_slice($missingCats, 0, 2)),
                'timeline' => '6-12 เดือน',
                'impact' => 'พอร์ตโฟลิโอครอบคลุมทุกมิติ',
            ];
        }
        $actionPlan[] = [
            'priority' => 'กลาง',
            'action' => 'บันทึก Logbook อย่างสม่ำเสมอ',
            'timeline' => 'ทุกสัปดาห์',
            'impact' => 'เพิ่มชั่วโมงกิจกรรมและหลักฐานเชิงประจักษ์',
        ];
        $actionPlan[] = [
            'priority' => 'ต่ำ',
            'action' => 'เข้าร่วมแข่งขัน/ค่ายวิชาการระดับชาติ',
            'timeline' => 'ตลอดปีการศึกษา',
            'impact' => 'สร้างรางวัลและเครือข่าย',
        ];

        // ---- Overall Readiness ----
        $readinessPct = 0;
        // GPA weight: 30%
        $gpaScore = $gpa >= $minGpa ? 100 : max(0, ($gpa / $minGpa) * 100);
        // Portfolio weight: 25%
        $portfolioScore = $portfolioQuality;
        // Skills weight: 20%
        $skillsScore = count($reqs['required_skills']) > 0
            ? max(0, (1 - count($missingSkills) / count($reqs['required_skills'])) * 100)
            : 100;
        // RIASEC weight: 15%
        $riasecScore = $riasecAlignment === 'สอดคล้อง' ? 100 : ($riasecAlignment === 'ปานกลาง' ? 60 : 30);
        // Logbook weight: 10%
        $logbookScore = $logbookHrs >= ($reqs['min_logbook_h'] ?? 0)
            ? 100
            : max(0, ($logbookHrs / max(1, $reqs['min_logbook_h'])) * 100);

        $readinessPct = round(
            $gpaScore * 0.30
            + $portfolioScore * 0.25
            + $skillsScore * 0.20
            + $riasecScore * 0.15
            + $logbookScore * 0.10
        );

        // ---- Encouragement ----
        $encouragement = $readinessPct >= 80
            ? "ยอดเยี่ยม! คุณมีความพร้อม {$readinessPct}% สำหรับ{$reqs['name']} — รักษาโมเมนตัมนี้ไว้ แล้วฝันจะเป็นจริง! 🎉"
            : ($readinessPct >= 60
                ? "ดีมาก! ความพร้อม {$readinessPct}% สำหรับ{$reqs['name']} — อีกนิดเดียวก็ถึงเป้า สู้ๆ! 💪"
                : "เริ่มต้นดี! ความพร้อม {$readinessPct}% สำหรับ{$reqs['name']} — ทำตามแผนปฏิบัติการ แล้วคุณจะพัฒนาขึ้นอย่างแน่นอน 🚀");

        $dominantTraits = [];
        if ($riasec && !empty($riasec['result_data'])) {
            $rData = $riasec['result_data'];
            $rScores = isset($rData['scores']) && is_array($rData['scores']) ? $rData['scores'] : (is_array($rData) ? $rData : []);
            $rScores = array_filter($rScores, fn($v, $k) => in_array($k, ['R', 'I', 'A', 'S', 'E', 'C']) && is_numeric($v), ARRAY_FILTER_USE_BOTH);
            arsort($rScores);
            $dominantTraits = array_slice(array_keys($rScores), 0, 3);
        }

        return [
            'overall_readiness_pct' => $readinessPct,
            'faculty_name' => $reqs['name'],
            'gpa_analysis' => [
                'current_gpa' => $gpa,
                'expected_gpa' => $minGpa,
                'gap' => round($gpaGap, 2),
                'verdict' => $gpaVerdict,
                'advice' => $gpaAdvice,
            ],
            'portfolio_analysis' => [
                'total_items' => count($portfolio),
                'relevant_categories' => $relevantCats,
                'missing_categories' => $missingCats,
                'quality_score_pct' => $portfolioQuality,
                'advice' => $portfolioAdvice,
            ],
            'skills_gap' => [
                'existing_skills' => $existingSkillsList,
                'missing_skills' => $missingSkills,
                'advice' => $skillsAdvice,
            ],
            'riasec_alignment' => [
                'dominant_traits' => $dominantTraits,
                'alignment' => $riasecAlignment,
                'advice' => $riasecAdvice,
            ],
            'soft_skills_missing' => $softSkillsMissing,
            'extracurricular_gaps' => $extracurricularGaps,
            'action_plan' => $actionPlan,
            'encouragement' => $encouragement,
        ];
    }

    /**
     * Generic fallback for faculties not in FIELD_REQUIREMENTS.
     */
    private static function genericFallbackAnalysis(string $targetFaculty, array $dossier): array
    {
        $gpa = (float) ($dossier['gpa'] ?? 0);
        $portfolioCount = count($dossier['portfolio_items'] ?? []);

        $readinessPct = min(100, max(10, round($gpa * 20 + $portfolioCount * 3)));

        return [
            'overall_readiness_pct' => $readinessPct,
            'faculty_name' => $targetFaculty,
            'gpa_analysis' => [
                'current_gpa' => $gpa,
                'expected_gpa' => $gpa >= 3.0 ? 3.0 : 3.5,
                'gap' => 0,
                'verdict' => $gpa >= 3.0 ? 'พร้อม' : 'ต้องปรับปรุง',
                'advice' => "GPA {$gpa} — รักษามาตรฐานหรือพัฒนาเพิ่ม",
            ],
            'portfolio_analysis' => [
                'total_items' => $portfolioCount,
                'relevant_categories' => [],
                'missing_categories' => ['ควรเพิ่มผลงานหลากหลายหมวด'],
                'quality_score_pct' => min(100, $portfolioCount * 10),
                'advice' => 'สร้างผลงานอย่างน้อย 5-10 รายการ',
            ],
            'skills_gap' => [
                'existing_skills' => array_column($dossier['skills'] ?? [], 'skill_name'),
                'missing_skills' => ['ควรประเมินทักษะที่ต้องการสำหรับคณะนี้'],
                'advice' => 'ปรึกษาครูที่ปรึกษาเพื่อวางแผนทักษะ',
            ],
            'riasec_alignment' => [
                'dominant_traits' => [],
                'alignment' => 'ปานกลาง',
                'advice' => 'ควรทำแบบทดสอบ RIASEC',
            ],
            'soft_skills_missing' => ['การคิดเชิงวิพากษ์', 'การแก้ปัญหา', 'การสื่อสาร'],
            'extracurricular_gaps' => ['ควรเข้าร่วมกิจกรรมนอกหลักสูตรเพิ่ม'],
            'action_plan' => [
                ['priority' => 'สูง', 'action' => 'รักษาหรือพัฒนา GPA', 'timeline' => 'ทุกภาคเรียน', 'impact' => 'ผ่านเกณฑ์พื้นฐาน TCAS'],
                ['priority' => 'กลาง', 'action' => 'สร้างพอร์ตโฟลิโอให้ครบ 5+ รายการ', 'timeline' => '1 ปี', 'impact' => 'มีหลักฐานเชิงประจักษ์'],
                ['priority' => 'ต่ำ', 'action' => 'ทำแบบทดสอบ RIASEC', 'timeline' => '1 เดือน', 'impact' => 'ประเมินความเหมาะสมคณะ'],
            ],
            'encouragement' => "ความพร้อม {$readinessPct}% สำหรับ{$targetFaculty} — ทุกก้าวสำคัญ เริ่มวันนี้สำเร็จในวันหน้า! 🌟",
        ];
    }
}
