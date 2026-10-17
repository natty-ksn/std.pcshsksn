<?php
/**
 * BadgeService — Dynamic Gamification Engine (Phase 5)
 *
 * ระบบประเมินและมอบเหรียญตราอัตโนมัติ โดยดึงข้อมูลจาก AIService (Phase 4)
 * มาสร้างเป็นเงื่อนไขการรับเหรียญ (Badge Triggers) ที่สร้างแรงจูงใจให้นักเรียน
 * พัฒนาพอร์ตโฟลิโออย่างต่อเนื่อง
 *
 * การทำงาน:
 *   1. BadgeService::checkAll($userId)     — ตรวจเงื่อนไขทั้งหมด มอบเหรียญที่ยังไม่มี
 *   2. BadgeService::checkTier($userId)     — ตรวจเฉพาะ tier badges (ตามระดับความสำเร็จ)
 *   3. BadgeService::checkAI($userId)       — ตรวจ badges จาก AI Insights (Phase 4 integration)
 *   4. BadgeService::onEvent($userId, $event) — Event-driven badge check (เรียกหลังกระทำสำคัญ)
 */
class BadgeService
{
    /** Cached badge definitions (initialized on first access) */
    private static ?array $badgesCache = null;

    /**
     * Get badge definitions (lazy-loaded to avoid const closure issue)
     */
    private static function badges(): array
    {
        if (self::$badgesCache !== null) {
            return self::$badgesCache;
        }

        self::$badgesCache = [
            // ========================================
            // BRONZE TIER — เริ่มต้นเดินทาง
            // ========================================
            'bronze_first_step' => [
                'tier' => 'bronze',
                'name' => 'ก้าวแรก',
                'description' => 'เพิ่มผลงานแรกเข้าสู่ระบบ — ทุกการเดินทางเริ่มจากก้าวแรก',
                'icon' => '🌟',
                'color' => '#cd7f32',
                'check' => function (int $userId): bool {
                    return self::portfolioCount($userId) >= 1;
                },
            ],

            'bronze_explorer' => [
                'tier' => 'bronze',
                'name' => 'นักสำรวจ',
                'description' => 'ทำแบบประเมิน RIASEC สำเร็จ — รู้จักตัวเองคือจุดเริ่มต้นของความสำเร็จ',
                'icon' => '🧭',
                'color' => '#cd7f32',
                'check' => function (int $userId): bool {
                    return self::hasRIASEC($userId);
                },
            ],

            'bronze_goal_setter' => [
                'tier' => 'bronze',
                'name' => 'นักตั้งเป้าหมาย',
                'description' => 'ตั้งเป้าหมายการศึกษาอย่างน้อย 1 รายการ',
                'icon' => '🎯',
                'color' => '#cd7f32',
                'check' => function (int $userId): bool {
                    return self::goalCount($userId) >= 1;
                },
            ],

            'bronze_logbook_starter' => [
                'tier' => 'bronze',
                'name' => 'นักบันทึกมือใหม่',
                'description' => 'บันทึก Logbook ครั้งแรก — ทุกชั่วโมงมีค่า',
                'icon' => '📝',
                'color' => '#cd7f32',
                'check' => function (int $userId): bool {
                    return self::logbookCount($userId) >= 1;
                },
            ],

            'bronze_team_joiner' => [
                'tier' => 'bronze',
                'name' => 'สมาชิกทีม',
                'description' => 'เข้าร่วมโครงงานครั้งแรก — การทำงานร่วมกันคือทักษะที่สำคัญ',
                'icon' => '🤝',
                'color' => '#cd7f32',
                'check' => function (int $userId): bool {
                    return self::projectCount($userId) >= 1;
                },
            ],

            // ========================================
            // SILVER TIER — พัฒนาอย่างต่อเนื่อง
            // ========================================
            'silver_rising_star' => [
                'tier' => 'silver',
                'name' => 'ดาวรุ่ง',
                'description' => 'มีผลงาน 5 รายการ — แสงสว่างที่กำลังเจิดจ้า',
                'icon' => '⭐',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::portfolioCount($userId) >= 5;
                },
            ],

            'silver_social_butterfly' => [
                'tier' => 'silver',
                'name' => 'นักสื่อสาร',
                'description' => 'มีผลงานหมวดภาษา & การสื่อสาร อย่างน้อย 2 ชิ้น',
                'icon' => '🗣️',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'language') >= 2;
                },
            ],

            'silver_tech_savvy' => [
                'tier' => 'silver',
                'name' => 'สายเทค',
                'description' => 'มีผลงานเทคโนโลยี & คอมพิวเตอร์ อย่างน้อย 3 ชิ้น',
                'icon' => '💻',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'technology') >= 3;
                },
            ],

            'silver_volunteer' => [
                'tier' => 'silver',
                'name' => 'จิตอาสา',
                'description' => 'มีผลงานจิตอาสา อย่างน้อย 2 ชิ้น — การให้คือการรับที่ยิ่งใหญ่',
                'icon' => '💚',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'volunteer') >= 2;
                },
            ],

            'silver_logbook_10' => [
                'tier' => 'silver',
                'name' => 'นักบันทึกมุ่งมั่น',
                'description' => 'บันทึก Logbook ครบ 10 ครั้ง — ความสม่ำเสมอคือกุญแจสู่ความสำเร็จ',
                'icon' => '📓',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::logbookCount($userId) >= 10;
                },
            ],

            'silver_researcher' => [
                'tier' => 'silver',
                'name' => 'นักวิจัย',
                'description' => 'มีผลงานวิจัย/สิ่งประดิษฐ์ อย่างน้อย 1 ชิ้น',
                'icon' => '🔬',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'research') >= 1;
                },
            ],

            'silver_leader' => [
                'tier' => 'silver',
                'name' => 'ผู้นำ',
                'description' => 'เป็นหัวหน้าทีมโครงงานอย่างน้อย 1 โครงงาน',
                'icon' => '👑',
                'color' => '#c0c0c0',
                'check' => function (int $userId): bool {
                    return self::leaderCount($userId) >= 1;
                },
            ],

            // ========================================
            // GOLD TIER — โดดเด่น
            // ========================================
            'gold_all_rounder' => [
                'tier' => 'gold',
                'name' => 'รอบรู้',
                'description' => 'มีผลงานครบ 5 หมวดหมู่ขึ้นไป — คนเดียวที่ทำทุกอย่างได้',
                'icon' => '🏅',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::categoryDiversity($userId) >= 5;
                },
            ],

            'gold_science_champ' => [
                'tier' => 'gold',
                'name' => 'นักวิทย์ดีเด่น',
                'description' => 'มีผลงานวิทยาศาสตร์ 5+ ชิ้น และระดับความสำเร็จอย่างน้อย province',
                'icon' => '🧪',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'science-project') >= 5
                        && self::hasAchievement($userId, 'province');
                },
            ],

            'gold_math_wizard' => [
                'tier' => 'gold',
                'name' => 'อัจฉริยะคณิต',
                'description' => 'มีผลงานคณิตศาสตร์ 3+ ชิ้น และ GPA คณิต ≥ 3.5',
                'icon' => '📐',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::categoryCount($userId, 'mathematics') >= 3
                        && self::subjectGPA($userId, 'คณิตศาสตร์') >= 3.5;
                },
            ],

            'gold_consistent' => [
                'tier' => 'gold',
                'name' => 'นักสู้ต่อเนื่อง',
                'description' => 'เพิ่มผลงานอย่างต่อเนื่องเป็นเวลา 3 เดือนติดต่อกัน',
                'icon' => '📈',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::monthlyStreak($userId, 3);
                },
            ],

            'gold_award_winner' => [
                'tier' => 'gold',
                'name' => 'ผู้พิชิตรางวัล',
                'description' => 'มีรางวัล/เกียรติบัตร อย่างน้อย 3 รายการ',
                'icon' => '🏆',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::awardCount($userId) >= 3;
                },
            ],

            'gold_logbook_master' => [
                'tier' => 'gold',
                'name' => 'เจ้าแห่ง Logbook',
                'description' => 'สะสมชั่วโมง Logbook ครบ 50 ชั่วโมง',
                'icon' => '⏱️',
                'color' => '#ffd700',
                'check' => function (int $userId): bool {
                    return self::logbookHours($userId) >= 50;
                },
            ],

            // ========================================
            // PLATINUM TIER — ตำนาน
            // ========================================
            'platinum_national_hero' => [
                'tier' => 'platinum',
                'name' => 'วีรบุรุษระดับชาติ',
                'description' => 'มีผลงานระดับชาติ (national) อย่างน้อย 1 รายการ — ชื่อเสียงระดับประเทศ',
                'icon' => '🇹🇭',
                'color' => '#e5e4e2',
                'check' => function (int $userId): bool {
                    return self::hasAchievement($userId, 'national');
                },
            ],

            'platinum_international' => [
                'tier' => 'platinum',
                'name' => 'ระดับโลก',
                'description' => 'มีผลงานระดับนานาชาติ — สร้างชื่อเสียงให้โรงเรียน',
                'icon' => '🌍',
                'color' => '#e5e4e2',
                'check' => function (int $userId): bool {
                    return self::hasAchievement($userId, 'international');
                },
            ],

            'platinum_perfect_portfolio' => [
                'tier' => 'platinum',
                'name' => 'พอร์ตสมบูรณ์',
                'description' => 'มีผลงาน 15+ ชิ้น ครบ 8 หมวดหมู่ มีรางวัล 5+ และ GPA ≥ 3.5',
                'icon' => '💎',
                'color' => '#e5e4e2',
                'check' => function (int $userId): bool {
                    return self::portfolioCount($userId) >= 15
                        && self::categoryDiversity($userId) >= 8
                        && self::awardCount($userId) >= 5
                        && self::gpa($userId) >= 3.5;
                },
            ],

            'platinum_ai_champion' => [
                'tier' => 'platinum',
                'name' => 'AI Champion',
                'description' => 'มีความพร้อมรายสาขาวิชา ≥ 90% ในสาขาใดสาขาหนึ่ง (วิเคราะห์โดย AI)',
                'icon' => '🤖',
                'color' => '#e5e4e2',
                'check' => function (int $userId): bool {
                    return self::aiReadinessTopScore($userId) >= 90;
                },
            ],

            // ========================================
            // SECRET TIER — เงื่อนไขลับ
            // ========================================
            'secret_night_owl' => [
                'tier' => 'secret',
                'name' => 'นกฮูกเที่ยงคืน',
                'description' => 'เพิ่มผลงานระหว่างเที่ยงคืนถึงตีสี่ — คนเก่งไม่เลือกเวลา',
                'icon' => '🦉',
                'color' => '#8b5cf6',
                'check' => function (int $userId): bool {
                    return self::hasLateNightActivity($userId);
                },
            ],

            'secret_multi_project' => [
                'tier' => 'secret',
                'name' => 'จอมยุทธ์พหุโครงงาน',
                'description' => 'เป็นสมาชิกโครงงานพร้อมกัน 5 โครงงาน — ทำได้ทุกอย่าง',
                'icon' => '🐙',
                'color' => '#8b5cf6',
                'check' => function (int $userId): bool {
                    return self::activeProjectCount($userId) >= 5;
                },
            ],

            'secret_skill_master' => [
                'tier' => 'secret',
                'name' => 'ปรมาจารย์ทักษะ',
                'description' => 'มีทักษะ (skills) ที่บันทึกไว้ครบ 10 ทักษะขึ้นไป โดยแต่ละทักษะ ≥ 3',
                'icon' => '🥋',
                'color' => '#8b5cf6',
                'check' => function (int $userId): bool {
                    return self::skillMasteryCount($userId) >= 10;
                },
            ],
        ]; // end of badge definitions

        return self::$badgesCache;
    }

    /** Get all badge definitions (public accessor for iteration) */
    public static function allBadges(): array
    {
        return self::badges();
    }

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * ตรวจเงื่อนไข badge ทั้งหมด → มอบเหรียญที่ยังไม่มี
     *
     * @param int $userId
     * @return array { 'new_badges' => [...], 'total' => int }
     */
    public static function checkAll(int $userId): array
    {
        $newBadges = [];

        foreach (self::badges() as $code => $def) {
            if (self::hasBadge($userId, $code)) {
                continue;
            }

            try {
                if (($def['check'])($userId)) {
                    self::awardBadge($userId, $code, $def);
                    $newBadges[] = [
                        'code' => $code,
                        'name' => $def['name'],
                        'icon' => $def['icon'],
                        'tier' => $def['tier'],
                        'description' => $def['description'],
                        'color' => $def['color'],
                    ];
                }
            } catch (\Throwable $e) {
                // Log but don't break — badge check should never crash the app
                App::log("Badge check failed for {$code}: " . $e->getMessage(), 'WARNING');
            }
        }

        return [
            'new_badges' => $newBadges,
            'total' => count($newBadges),
        ];
    }

    /**
     * ตรวจ badge เฉพาะ tier
     */
    public static function checkTier(int $userId, string $tier): array
    {
        $newBadges = [];

        foreach (self::badges() as $code => $def) {
            if ($def['tier'] !== $tier)
                continue;
            if (self::hasBadge($userId, $code))
                continue;

            try {
                if (($def['check'])($userId)) {
                    self::awardBadge($userId, $code, $def);
                    $newBadges[] = [
                        'code' => $code,
                        'name' => $def['name'],
                        'icon' => $def['icon'],
                        'tier' => $def['tier'],
                        'description' => $def['description'],
                        'color' => $def['color'],
                    ];
                }
            } catch (\Throwable $e) {
                App::log("Badge check failed for {$code}: " . $e->getMessage(), 'WARNING');
            }
        }

        return ['new_badges' => $newBadges, 'total' => count($newBadges)];
    }

    /**
     * ตรวจ badge จาก AI Insights (Phase 4 Integration)
     *
     * badge ที่เกี่ยวข้องกับ:
     *   - ai_field_ready: มีความพร้อม ≥ 70% ในสาขาใดสาขาหนึ่ง
     *   - ai_gap_fighter: ลดช่องว่างในสาขาเป้าหมายลงได้ ≥ 50%
     *   - ai_portfolio_a: ได้เกรด A จาก Portfolio Quality Audit
     */
    public static function checkAI(int $userId): array
    {
        $newBadges = [];
        $studentId = self::getStudentId($userId);
        if (!$studentId)
            return ['new_badges' => [], 'total' => 0];

        // 1. AI Field Ready — readiness ≥ 70% in any field
        $aiCode = 'ai_field_ready';
        if (!self::hasBadge($userId, $aiCode)) {
            try {
                $analysis = AIService::analyzeFieldReadiness($userId);
                if (!isset($analysis['error']) && !empty($analysis['top_matches'])) {
                    $topPct = (float) ($analysis['top_matches'][0]['readiness_pct'] ?? 0);
                    if ($topPct >= 70) {
                        self::awardBadge($userId, $aiCode, self::aiBadgeDef($aiCode, $topPct));
                        $newBadges[] = ['code' => $aiCode, 'name' => 'พร้อมสู่สนามจริง', 'icon' => '🚀', 'tier' => 'gold', 'description' => 'มีความพร้อม ≥ 70% ในสาขา ' . $analysis['top_matches'][0]['track_name'], 'color' => '#ffd700'];
                    }
                }
            } catch (\Throwable $e) {
                App::log("AI badge check failed: " . $e->getMessage(), 'WARNING');
            }
        }

        // 2. AI Portfolio A — avg grade = A
        $aGradeCode = 'ai_portfolio_a';
        if (!self::hasBadge($userId, $aGradeCode)) {
            try {
                $audit = AIService::auditPortfolioQuality($studentId);
                if ($audit['avg_grade'] === 'A' && $audit['total_items'] >= 3) {
                    self::awardBadge($userId, $aGradeCode, [
                        'tier' => 'gold',
                        'name' => 'ผลงานเกรด A',
                        'icon' => '📝',
                        'color' => '#ffd700',
                        'description' => 'คุณภาพพอร์ตโฟลิโอเฉลี่ยระดับ A',
                    ]);
                    $newBadges[] = ['code' => $aGradeCode, 'name' => 'ผลงานเกรด A', 'icon' => '📝', 'tier' => 'gold', 'description' => 'คุณภาพพอร์ตโฟลิโอเฉลี่ยระดับ A', 'color' => '#ffd700'];
                }
            } catch (\Throwable $e) {
                App::log("AI grade badge check failed: " . $e->getMessage(), 'WARNING');
            }
        }

        return ['new_badges' => $newBadges, 'total' => count($newBadges)];
    }

    /**
     * Event-driven badge check — เรียกหลังจากกระทำสำคัญ
     *
     * Events:
     *   'portfolio_created'    — เพิ่มผลงานใหม่
     *   'project_joined'       — เข้าร่วมกับโครงงาน
     *   'logbook_added'        — บันทึก logbook
     *   'project_leaded'       — เป็นหัวหน้าทีม
     *   'award_added'          — ระบุรางวัล
     *   'goal_set'             — ตั้งเป้าหมาย
     *   'riasec_taken'         — ทำ RIASEC
     *   'category_added'       — เพิ่มผลงานในหมวดหมู่ใหม่
     */
    public static function onEvent(int $userId, string $event, array $context = []): array
    {
        $newBadges = [];

        // Determine which badges might have been unlocked by this event
        $relevantBadges = self::getRelevantBadges($event);

        foreach ($relevantBadges as $code) {
            if (self::hasBadge($userId, $code))
                continue;
            $def = self::badges()[$code] ?? null;
            if (!$def)
                continue;

            try {
                if (($def['check'])($userId)) {
                    self::awardBadge($userId, $code, $def);
                    $newBadges[] = [
                        'code' => $code,
                        'name' => $def['name'],
                        'icon' => $def['icon'],
                        'tier' => $def['tier'],
                        'description' => $def['description'],
                        'color' => $def['color'],
                    ];
                }
            } catch (\Throwable $e) {
                App::log("Event badge check failed for {$code}: " . $e->getMessage(), 'WARNING');
            }
        }

        return ['new_badges' => $newBadges, 'total' => count($newBadges)];
    }

    /**
     * ดึงรายการ badge ทั้งหมดของนักเรียน
     */
    public static function getUserBadges(int $userId): array
    {
        $badges = Database::fetchAll(
            "SELECT ub.badge_code, ub.earned_at, bm.name, bm.description, bm.icon, bm.criteria
             FROM user_badges ub
             JOIN badges_master bm ON ub.badge_code = bm.code
             WHERE ub.user_id = ?
             ORDER BY ub.earned_at DESC",
            [$userId]
        );

        // Enrich with tier/color from local definitions
        foreach ($badges as &$b) {
            $def = self::badges()[$b['badge_code']] ?? null;
            if ($def) {
                $b['tier'] = $def['tier'];
                $b['color'] = $def['color'];
            } else {
                $b['tier'] = 'bronze';
                $b['color'] = '#cd7f32';
            }
        }

        return $badges;
    }

    /**
     * ดึง badge collection summary — สำหรับแสดงใน dashboard
     */
    public static function getCollectionSummary(int $userId): array
    {
        $badges = self::getUserBadges($userId);
        $total = count(self::badges());
        $earned = count($badges);

        $byTier = [];
        foreach (['bronze', 'silver', 'gold', 'platinum', 'secret'] as $tier) {
            $tierBadges = array_filter($badges, fn($b) => ($b['tier'] ?? 'bronze') === $tier);
            $tierTotal = count(array_filter(self::badges(), fn($d) => $d['tier'] === $tier));
            $byTier[$tier] = [
                'earned' => count($tierBadges),
                'total' => $tierTotal,
                'badges' => $tierBadges,
            ];
        }

        return [
            'total_badges' => $total,
            'earned' => $earned,
            'progress_pct' => $total > 0 ? round(($earned / $total) * 100, 1) : 0,
            'by_tier' => $byTier,
            'recent' => array_slice($badges, 0, 3),
        ];
    }

    /**
     * ดึง badge definitions ทั้งหมด (สำหรับแสดงหน้า collection)
     */
    public static function getAllDefinitions(): array
    {
        $defs = [];
        foreach (self::badges() as $code => $def) {
            $defs[$code] = [
                'code' => $code,
                'tier' => $def['tier'],
                'name' => $def['name'],
                'description' => $def['description'],
                'icon' => $def['icon'],
                'color' => $def['color'],
            ];
        }
        return $defs;
    }

    /**
     * ตรวจสอบ badge เฉพาะรายการ
     */
    public static function checkSingle(int $userId, string $code): array
    {
        $def = self::badges()[$code] ?? null;
        if (!$def)
            return ['error' => 'ไม่พบ badge ที่ระบุ'];

        $has = self::hasBadge($userId, $code);
        $qualified = false;
        if (!$has) {
            try {
                $qualified = ($def['check'])($userId);
            } catch (\Throwable $e) {
                $qualified = false;
            }
        }

        return [
            'code' => $code,
            'name' => $def['name'],
            'description' => $def['description'],
            'icon' => $def['icon'],
            'tier' => $def['tier'],
            'color' => $def['color'],
            'has' => $has,
            'qualified' => $has || $qualified,
            'earned_at' => $has ? self::getEarnedAt($userId, $code) : null,
        ];
    }

    // ============================================================
    // INTERNAL: BADGE AWARDING
    // ============================================================

    private static function awardBadge(int $userId, string $code, array $def): void
    {
        try {
            // Ensure badge exists in database
            Database::execute(
                "INSERT IGNORE INTO badges_master (code, name, description, icon, criteria) VALUES (?, ?, ?, ?, ?)",
                [
                    $code,
                    $def['name'],
                    $def['description'],
                    $def['icon'],
                    json_encode(['tier' => $def['tier'] ?? 'bronze', 'color' => $def['color'] ?? '#cd7f32', 'type' => 'dynamic'], JSON_UNESCAPED_UNICODE),
                ]
            );

            // Award to user
            Database::execute(
                "INSERT INTO user_badges (user_id, badge_code) VALUES (?, ?)",
                [$userId, $code]
            );

            // Send notification
            Notifier::badgeAwarded($userId, $code);

            // Give EXP with tier bonus
            EXPService::award($userId, 'badge_earned', ['badge_tier' => $def['tier'] ?? 'bronze']);

            Auth::logAction('badge_earned', 'badge', 0, null, ['badge_code' => $code]);

        } catch (\Throwable $e) {
            // Duplicate key or FK issue — badge already earned or config mismatch, ignore silently
            App::log("awardBadge skipped [{$code}] for user {$userId}: " . $e->getMessage(), 'WARNING');
        }
    }

    // ============================================================
    // INTERNAL: DATA FETCHERS (Metric Helpers)
    // ============================================================

    private static function getStudentId(int $userId): ?int
    {
        $r = Database::fetch("SELECT id FROM student_profiles WHERE user_id = ?", [$userId]);
        return $r ? (int) $r['id'] : null;
    }

    private static function hasBadge(int $userId, string $code): bool
    {
        $r = Database::fetch("SELECT 1 FROM user_badges WHERE user_id = ? AND badge_code = ?", [$userId, $code]);
        return (bool) $r;
    }

    private static function getEarnedAt(int $userId, string $code): ?string
    {
        $r = Database::fetch("SELECT earned_at FROM user_badges WHERE user_id = ? AND badge_code = ?", [$userId, $code]);
        return $r['earned_at'] ?? null;
    }

    private static function portfolioCount(int $userId): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items WHERE student_id = ? AND status = 'published'",
            [$sid]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function categoryCount(int $userId, string $slug): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT COUNT(DISTINCT pi.id) as cnt
             FROM portfolio_items pi
             JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published' AND pc.slug = ?",
            [$sid, $slug]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function categoryDiversity(int $userId): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT COUNT(DISTINCT pc.id) as cnt
             FROM portfolio_items pi
             JOIN portfolio_categories pc ON pi.category_id = pc.id
             WHERE pi.student_id = ? AND pi.status = 'published'",
            [$sid]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function hasAchievement(int $userId, string $level): bool
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return false;
        $r = Database::fetch(
            "SELECT 1 FROM portfolio_items
             WHERE student_id = ? AND status = 'published' AND achievement_level = ?",
            [$sid, $level]
        );
        return (bool) $r;
    }

    private static function awardCount(int $userId): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM portfolio_items
             WHERE student_id = ? AND status = 'published' AND award IS NOT NULL AND award != ''",
            [$sid]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function logbookCount(int $userId): int
    {
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM logbooks WHERE student_id = ?",
            [$userId]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function logbookHours(int $userId): float
    {
        $r = Database::fetch(
            "SELECT COALESCE(SUM(hours_spent), 0) as total FROM logbooks WHERE student_id = ?",
            [$userId]
        );
        return round((float) ($r['total'] ?? 0), 1);
    }

    private static function projectCount(int $userId): int
    {
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM project_members WHERE student_id = ?",
            [$userId]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function leaderCount(int $userId): int
    {
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM project_members
             WHERE student_id = ? AND role_in_project = 'leader'",
            [$userId]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function activeProjectCount(int $userId): int
    {
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt
             FROM project_members pm
             JOIN projects p ON pm.project_id = p.id
             WHERE pm.student_id = ? AND p.status IN ('draft','proposal_submitted','proposal_approved','in_progress')",
            [$userId]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function goalCount(int $userId): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT COUNT(*) as cnt FROM student_goals WHERE student_id = ?",
            [$sid]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function hasRIASEC(int $userId): bool
    {
        $r = Database::fetch(
            "SELECT 1 FROM student_assessments WHERE user_id = ? AND assessment_type = 'riasec'",
            [$userId]
        );
        return (bool) $r;
    }

    private static function gpa(int $userId): float
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT AVG(grade) as avg_gpa FROM academic_records WHERE student_id = ?",
            [$sid]
        );
        return round((float) ($r['avg_gpa'] ?? 0), 2);
    }

    private static function subjectGPA(int $userId, string $subject): float
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;
        $r = Database::fetch(
            "SELECT AVG(grade) as avg_grade FROM academic_records
             WHERE student_id = ? AND LOWER(subject) LIKE ?",
            [$sid, '%' . mb_strtolower($subject) . '%']
        );
        return round((float) ($r['avg_grade'] ?? 0), 2);
    }

    private static function monthlyStreak(int $userId, int $months): bool
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return false;

        $items = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym
             FROM portfolio_items
             WHERE student_id = ? AND status = 'published'
             GROUP BY ym
             ORDER BY ym DESC
             LIMIT ?",
            [$sid, $months]
        );

        if (count($items) < $months)
            return false;

        // Check if months are consecutive
        for ($i = 0; $i < count($items) - 1; $i++) {
            $current = strtotime($items[$i]['ym'] . '-01');
            $prev = strtotime($items[$i + 1]['ym'] . '-01');
            $diffMonths = ($current - $prev) / (30 * 24 * 60 * 60);
            if (round($diffMonths) > 1.5)
                return false; // Gap > 1 month
        }

        return true;
    }

    private static function hasLateNightActivity(int $userId): bool
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return false;

        $r = Database::fetch(
            "SELECT 1 FROM portfolio_items
             WHERE student_id = ? AND status = 'published'
             AND (HOUR(created_at) >= 0 AND HOUR(created_at) < 4)",
            [$sid]
        );
        return (bool) $r;
    }

    private static function skillMasteryCount(int $userId): int
    {
        $sid = self::getStudentId($userId);
        if (!$sid)
            return 0;

        $r = Database::fetch(
            "SELECT COUNT(DISTINCT sm.id) as cnt
             FROM portfolio_items pi
             JOIN portfolio_skills ps ON pi.id = ps.portfolio_id
             JOIN skills_master sm ON ps.skill_id = sm.id
             WHERE pi.student_id = ? AND pi.status = 'published' AND ps.proficiency >= 3
             GROUP BY pi.student_id",
            [$sid]
        );
        return (int) ($r['cnt'] ?? 0);
    }

    private static function aiReadinessTopScore(int $userId): float
    {
        try {
            $analysis = AIService::analyzeFieldReadiness($userId);
            if (isset($analysis['error']) || empty($analysis['top_matches']))
                return 0;
            return (float) ($analysis['top_matches'][0]['readiness_pct'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function aiBadgeDef(string $code, float $pct): array
    {
        $defs = [
            'ai_field_ready' => [
                'tier' => 'gold',
                'name' => 'พร้อมสู่สนามจริง',
                'icon' => '🚀',
                'color' => '#ffd700',
                'description' => "ความพร้อม {$pct}% ในสาขาที่เหมาะสมที่สุด",
            ],
            'ai_gap_fighter' => [
                'tier' => 'gold',
                'name' => 'นักสู้ช่องว่าง',
                'icon' => '⚔️',
                'color' => '#ffd700',
                'description' => 'ลดช่องว่างในสาขาเป้าหมายลงได้ ≥ 50%',
            ],
            'ai_portfolio_a' => [
                'tier' => 'gold',
                'name' => 'ผลงานเกรด A',
                'icon' => '📝',
                'color' => '#ffd700',
                'description' => 'คุณภาพพอร์ตโฟลิโอเฉลี่ยระดับ A',
            ],
        ];
        return $defs[$code] ?? ['tier' => 'gold', 'name' => 'AI Badge', 'icon' => '🤖', 'color' => '#ffd700', 'description' => ''];
    }

    // ============================================================
    // INTERNAL: EVENT → BADGE MAPPING
    // ============================================================

    private static function getRelevantBadges(string $event): array
    {
        $mapping = [
            'portfolio_created' => [
                'bronze_first_step',
                'silver_rising_star',
                'gold_all_rounder',
                'gold_science_champ',
                'gold_consistent',
                'platinum_perfect_portfolio',
                'secret_night_owl',
            ],
            'project_joined' => ['bronze_team_joiner', 'secret_multi_project'],
            'logbook_added' => ['bronze_logbook_starter', 'silver_logbook_10', 'gold_logbook_master'],
            'project_leaded' => ['silver_leader'],
            'award_added' => ['gold_award_winner', 'platinum_perfect_portfolio'],
            'goal_set' => ['bronze_goal_setter'],
            'riasec_taken' => ['bronze_explorer'],
            'category_added' => ['gold_all_rounder', 'platinum_perfect_portfolio'],
        ];
        return $mapping[$event] ?? [];
    }
}
