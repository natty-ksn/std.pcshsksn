<?php
/**
 * EXPService — ระบบ Experience Points อัตโนมัติ
 *
 * ให้คะแนน EXP เมื่อนักเรียนทำกิจกรรมต่างๆ พร้อมคำนวณ Level อัตโนมัติ
 *
 * การใช้งาน:
 *   EXPService::award(Auth::id(), 'portfolio_created');
 *   EXPService::award(Auth::id(), 'badge_earned', ['badge_tier' => 'gold']);
 */
class EXPService
{
    /**
     * EXP rewards table — กำหนดจำนวน EXP ต่อกิจกรรม
     */
    private const EXP_TABLE = [
        // Portfolio actions
        'portfolio_created'   => 50,
        'portfolio_verified'  => 100,
        'portfolio_featured'  => 75,

        // Project actions
        'project_created'     => 80,
        'project_completed'   => 200,
        'logbook_added'       => 20,

        // Academic
        'goal_set'            => 30,
        'goal_achieved'       => 150,
        'riasec_taken'        => 40,

        // Badges (bonus multiplied by tier)
        'badge_earned'        => 50,  // base; gold=x2, platinum=x3
        'badge_earned_gold'   => 100,
        'badge_earned_platinum' => 150,
        'badge_earned_secret' => 200,

        // Social & engagement
        'comment_received'    => 10,
        'daily_login'         => 5,
        'profile_completed'   => 100,
    ];

    /**
     * EXP required per level (linear formula)
     * Level N requires N * BASE_EXP total EXP from level 0
     */
    private const BASE_EXP_PER_LEVEL = 1000;

    /**
     * Award EXP to a user
     *
     * @param int    $userId   User ID
     * @param string $action   Action key from EXP_TABLE
     * @param array  $meta     Optional metadata (e.g., badge_tier)
     * @return array ['exp_gained' => int, 'new_exp' => int, 'new_level' => int, 'leveled_up' => bool]
     */
    public static function award(int $userId, string $action, array $meta = []): array
    {
        $expGained = self::calculateExp($action, $meta);
        if ($expGained <= 0) {
            return ['exp_gained' => 0, 'new_exp' => 0, 'new_level' => 0, 'leveled_up' => false];
        }

        try {
            // Get current EXP and Level
            $user = Database::fetch("SELECT exp, level, points FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return ['exp_gained' => 0, 'new_exp' => 0, 'new_level' => 0, 'leveled_up' => false];
            }

            $oldExp = (int) ($user['exp'] ?? 0);
            $oldLevel = (int) ($user['level'] ?? 1);
            $oldPoints = (int) ($user['points'] ?? 0);

            $newExp = $oldExp + $expGained;
            $newLevel = self::calculateLevel($newExp);
            $newPoints = $oldPoints + $expGained; // Points track same as EXP
            $leveledUp = $newLevel > $oldLevel;

            // Update DB
            Database::execute(
                "UPDATE users SET exp = ?, level = ?, points = ? WHERE id = ?",
                [$newExp, $newLevel, $newPoints, $userId]
            );

            // Sync session if it's the current user
            if (Auth::id() === $userId) {
                Session::set('user_exp', $newExp);
                Session::set('user_level', $newLevel);
                Session::set('user_points', $newPoints);
            }

            // Log level up
            if ($leveledUp) {
                Notifier::send(
                    $userId,
                    'system.levelup',
                    "🎉 Level Up! คุณเลื่อนเป็น Lv.{$newLevel}",
                    "ยินดีด้วย! คุณมี EXP สะสม {$newExp} แล้ว",
                    '/dashboard'
                );
                App::log("User #{$userId} leveled up to Lv.{$newLevel} (EXP: {$newExp})", 'INFO');
            }

            return [
                'exp_gained' => $expGained,
                'new_exp' => $newExp,
                'new_level' => $newLevel,
                'leveled_up' => $leveledUp,
            ];

        } catch (\Throwable $e) {
            App::log("EXP award failed for user #{$userId}: " . $e->getMessage(), 'WARNING');
            return ['exp_gained' => 0, 'new_exp' => 0, 'new_level' => 0, 'leveled_up' => false];
        }
    }

    /**
     * Calculate EXP for an action
     */
    private static function calculateExp(string $action, array $meta = []): int
    {
        $base = self::EXP_TABLE[$action] ?? 0;

        // Badge tier multiplier
        if ($action === 'badge_earned' && !empty($meta['badge_tier'])) {
            $tierKey = 'badge_earned_' . $meta['badge_tier'];
            $base = self::EXP_TABLE[$tierKey] ?? $base;
        }

        return $base;
    }

    /**
     * Calculate level from total EXP
     * Level 1: 0-999, Level 2: 1000-1999, etc.
     */
    private static function calculateLevel(int $totalExp): int
    {
        return max(1, (int) floor($totalExp / self::BASE_EXP_PER_LEVEL) + 1);
    }

    /**
     * Get user stats summary
     */
    public static function getUserStats(int $userId): array
    {
        $user = Database::fetch("SELECT exp, level, points FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return ['exp' => 0, 'level' => 1, 'points' => 0, 'progress' => 0, 'next_level_exp' => 1000];
        }

        $exp = (int) ($user['exp'] ?? 0);
        $level = (int) ($user['level'] ?? 1);
        $nextLevelExp = $level * self::BASE_EXP_PER_LEVEL;
        $currentLevelExp = ($level - 1) * self::BASE_EXP_PER_LEVEL;
        $progress = $nextLevelExp > $currentLevelExp
            ? round(($exp - $currentLevelExp) / ($nextLevelExp - $currentLevelExp) * 100)
            : 0;

        return [
            'exp' => $exp,
            'level' => $level,
            'points' => (int) ($user['points'] ?? 0),
            'progress' => min(100, max(0, $progress)),
            'next_level_exp' => $nextLevelExp,
        ];
    }
}
