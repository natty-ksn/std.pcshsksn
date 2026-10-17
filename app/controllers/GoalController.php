<?php
/**
 * GoalController — เป้าหมายการศึกษาต่อ
 */
class GoalController extends Controller
{
    public function index(): void
    {
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        if (!$profile) {
            App::redirect('profile/edit');
            return;
        }

        $goals = Database::fetchAll(
            "SELECT * FROM student_goals WHERE student_id = ? ORDER BY 
             FIELD(status, 'inprogress', 'planning', 'achieved', 'cancelled'), deadline ASC",
            [$profile['id']]
        );

        $stats = [
            'total' => count($goals),
            'achieved' => count(array_filter($goals, fn($g) => $g['status'] === 'achieved')),
            'active' => count(array_filter($goals, fn($g) => in_array($g['status'], ['planning', 'inprogress']))),
        ];

        // Attach dynamic AI/Heuristic analysis to each goal
        foreach ($goals as &$goal) {
            $goal['analysis'] = $this->evaluateGoalReadiness($goal, $profile['id']);
        }

        // --- Data for Triangulation Chart ---
        $assessment = Database::fetch(
            "SELECT result_data FROM student_assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [Auth::id()]
        );
        $riasecData = $assessment ? json_decode($assessment['result_data'], true) : null;
        
        // If no assessment data or incomplete, provide stable defaults
        $defaultScores = ['R' => 5, 'I' => 5, 'A' => 5, 'S' => 5, 'E' => 5, 'C' => 5];
        if (!$riasecData) {
            $riasecScores = $defaultScores;
        } else {
            $actualScores = $riasecData['scores'] ?? $riasecData;
            // Merge with defaults to ensure all keys (R,I,A,S,E,C) exist
            $riasecScores = array_merge($defaultScores, $actualScores);
        }

        $portfolioStats = Database::fetchAll(
            "SELECT pc.id, pc.name, COUNT(pi.id) as count 
             FROM portfolio_categories pc 
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.student_id = ? AND pi.status = 'published'
             GROUP BY pc.id",
            [$profile['id']]
        );

        $this->view('goals.index', [
            'goals' => $goals,
            'stats' => $stats,
            'riasec' => $riasecScores,
            'portfolioStats' => $portfolioStats
        ]);
    }

    /**
     * วิเคราะห์ความพร้อมของเป้าหมาย (ด้วยระบบแม่แบบและแคชที่ปรับเพิ่มประสิทธิภาพ)
     */
    private function evaluateGoalReadiness(array $goal, int $studentId): ?array
    {
        if (in_array($goal['status'], ['achieved', 'cancelled'])) {
            return null;
        }

        // 1. ตรวจสอบแคช (Check Cache) - หากวิเคราะห์ไว้ไม่เกิน 24 ชม. ให้ใช้ของเดิมเพื่อ Performance
        if (!empty($goal['analysis_cache']) && !empty($goal['analyzed_at'])) {
            $lastAnalyzed = strtotime($goal['analyzed_at']);
            if (time() - $lastAnalyzed < 86400) { // 24 Hours Cache
                return json_decode($goal['analysis_cache'], true);
            }
        }

        // 2. ดึงข้อมูลเกรดเฉลี่ย
        $gpaResult = Database::fetch("SELECT ROUND(SUM(grade * credit) / NULLIF(SUM(credit), 0), 2) as gpa FROM academic_records WHERE student_id = ?", [$studentId]);
        $gpa = (float)($gpaResult['gpa'] ?? 0);
        
        // 3. วิเคราะห์หา Track จากชื่อเป้าหมาย
        $targetPhrase = mb_strtolower($goal['target'] . ' ' . $goal['target_details']);
        $trackCode = 'general';
        
        $keywords = [
            'med' => ['แพทย์', 'หมอ', 'พยาบาล', 'เภสัช', 'ทันตะ', 'สาธารณสุข', 'กายภาพ', 'สัตวแพทย์', 'เทคนิคการแพทย์', 'รังสี'],
            'eng' => ['วิศว', 'คอมพิวเตอร์', 'โปรแกรมเมอร์', 'ไอที', 'it', 'ai', 'ซอฟต์แวร์', 'วิทยาการคอม', 'ดิจิทัล', 'เทคโนโลยี'],
            'sci' => ['วิทย์', 'วิทยาศาสตร์', 'ชีววิทยา', 'เคมี', 'ฟิสิกส์', 'นวัตกรรม', 'วิจัย'],
            'art' => ['ศิลปะ', 'นิเทศ', 'ออกแบบ', 'สถาปัตย', 'ภาษา', 'อักษร', 'มนุษย', 'สังคม']
        ];

        foreach ($keywords as $key => $words) {
            foreach ($words as $word) {
                if (mb_strpos($targetPhrase, $word) !== false) {
                    $trackCode = $key;
                    break 2;
                }
            }
        }

        // 4. ดึงเกณฑ์จากแม่แบบ (Goal Templates)
        $template = Database::fetch("SELECT * FROM goal_templates WHERE track_code = ?", [$trackCode]);
        
        $analysis = [
            'readiness' => 0,
            'recommendations' => [],
            'track_name' => $template['track_name'] ?? 'เป้าหมายทั่วไป',
            'track' => $trackCode
        ];

        // 5. ดึงข้อมูลผลงานแยกตามหมวดหมู่ (Gap Analysis Data)
        $currentPortfolio = Database::fetchAll(
            "SELECT pc.name, COUNT(pi.id) as count 
             FROM portfolio_categories pc 
             LEFT JOIN portfolio_items pi ON pc.id = pi.category_id AND pi.student_id = ? AND pi.status = 'published'
             GROUP BY pc.id",
            [$studentId]
        );
        $catCounts = [];
        foreach($currentPortfolio as $cp) {
            $catCounts[$cp['name']] = (int)$cp['count'];
        }

        // 6. การประมวลผลคะแนน (Scoring Logic)
        if ($gpa > 0) $analysis['readiness'] += 30; // มีเกรดในระบบ
        
        if ($template) {
            // เช็คเกรดขั้นต่ำ
            if ($gpa < $template['min_gpa']) {
                $analysis['recommendations'][] = "📈 **GPAX:** เป้าหมายนี้แนะนำเกรดเฉลี่ยขั้นต่ำ " . number_format($template['min_gpa'], 2) . " (ปัจจุบันคุณมี $gpa)";
            } else {
                $analysis['readiness'] += 15;
            }

            // เช็คผลงานรายหมวด (Requirements Gap)
            $reqs = json_decode($template['requirements'], true) ?? [];
            if (!empty($reqs)) {
                $pointsPerReq = 45 / count($reqs);
                foreach ($reqs as $catName => $minCount) {
                    $current = $catCounts[$catName] ?? 0;
                    if ($current < $minCount) {
                        $gap = $minCount - $current;
                        $analysis['recommendations'][] = "📝 **ขาดผลงาน:** หมวด \"$catName\" ยังต้องการเพิ่มอีก $gap ชิ้น เพื่อประวัติที่สมบูรณ์";
                    } else {
                        $analysis['readiness'] += $pointsPerReq;
                    }
                }
            }

            // ดึงคำแนะนำเพิ่มเติมจากแม่แบบ
            $extraRecs = json_decode($template['recommendations'], true) ?? [];
            foreach ($extraRecs as $rec) {
                if (count($analysis['recommendations']) < 5) {
                    $analysis['recommendations'][] = $rec;
                }
            }
        } else {
            // General Track Fallback
            if ($gpa >= 3.0) $analysis['readiness'] += 20;
            $hasWorks = Database::fetch("SELECT id FROM portfolio_items WHERE student_id = ? LIMIT 1", [$studentId]);
            if ($hasWorks) $analysis['readiness'] += 20;
            $analysis['recommendations'][] = "🧭 ค้นหากิจกรรมที่ตรงกับเป้าหมายนี้ และบันทึกลงในระบบเพื่อสะสมความพร้อม";
        }

        $analysis['readiness'] = min(100, round($analysis['readiness']));

        // 7. บันทึกลงแคช (เกราะป้องกัน — ป้องกัน 500 Error หากคอลัมน์หาย)
        try {
            Database::execute(
                "UPDATE student_goals SET analysis_cache = ?, analyzed_at = NOW() WHERE id = ?",
                [json_encode($analysis, JSON_UNESCAPED_UNICODE), $goal['id']]
            );
        } catch (\Exception $e) {
            // เงียบสนิท: หากตารางยังไม่อัพเดต ให้ข้ามไป ป้องกันระบบล่ม
            App::log("Goal analysis cache failed (Schema gap?): " . $e->getMessage(), 'WARNING');
        }

        return $analysis;
    }

    public function store(): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);

        $errors = $this->validate([
            'target' => 'required|max:300',
            'goal_type' => 'required',
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            App::redirect('goals');
            return;
        }

        Database::insert(
            "INSERT INTO student_goals (student_id, goal_type, target, target_details, target_score, current_score, deadline, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'planning', ?)",
            [
                $profile['id'],
                $this->input('goal_type'),
                $this->input('target'),
                $this->rawInput('target_details'),
                $this->input('target_score') ? (float) $this->input('target_score') : null,
                $this->input('current_score') ? (float) $this->input('current_score') : null,
                $this->input('deadline') ?: null,
                $this->rawInput('notes'),
            ]
        );

        Database::query("INSERT IGNORE INTO user_badges (user_id, badge_code) VALUES (?, 'goal_setter')", [Auth::id()]);
        Auth::logAction('create_goal', 'student_goal', 0);

        // Phase 5: Badge event trigger — goal_set
        try {
            BadgeService::onEvent(Auth::id(), 'goal_set');
            EXPService::award(Auth::id(), 'goal_set');
        } catch (\Throwable $e) {
            App::log("Badge check after goal_set failed: " . $e->getMessage(), 'WARNING');
        }

        Session::flash('success', 'เพิ่มเป้าหมายเรียบร้อย! 🎯');
        App::redirect('goals');
    }

    public function update(string $id): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);

        $goal = Database::fetch(
            "SELECT * FROM student_goals WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );

        if (!$goal) {
            Session::flash('error', 'ไม่พบเป้าหมาย');
            App::redirect('goals');
            return;
        }

        $newStatus = $this->input('status');
        $currentScore = $this->input('current_score') ? (float) $this->input('current_score') : $goal['current_score'];

        Database::execute(
            "UPDATE student_goals SET status = ?, current_score = ?, notes = ?, analysis_cache = NULL WHERE id = ?",
            [$newStatus ?: $goal['status'], $currentScore, $this->rawInput('notes') ?: $goal['notes'], (int) $id]
        );

        if ($newStatus === 'achieved') {
            Database::query("INSERT IGNORE INTO user_badges (user_id, badge_code) VALUES (?, 'goal_achiever')", [Auth::id()]);
            EXPService::award(Auth::id(), 'goal_achieved');
            Session::flash('success', '🎉 ยินดีด้วย! คุณบรรลุเป้าหมายแล้ว!');
        } else {
            Session::flash('success', 'อัพเดทเป้าหมายเรียบร้อย');
        }

        App::redirect('goals');
    }

    public function delete(string $id): void
    {
        CSRF::check();
        $profile = Database::fetch("SELECT * FROM student_profiles WHERE user_id = ?", [Auth::id()]);
        Database::execute(
            "DELETE FROM student_goals WHERE id = ? AND student_id = ?",
            [(int) $id, $profile['id']]
        );
        Session::flash('success', 'ลบเป้าหมายแล้ว');
        App::redirect('goals');
    }
}
