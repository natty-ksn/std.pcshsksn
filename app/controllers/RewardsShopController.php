<?php
/**
 * RewardsShopController — Phase 4.1: Points Shop (MVP)
 *
 * Displays the Rewards Shop catalog and handles point-based redemptions
 * with full transactional integrity and audit logging.
 *
 * @package  PCSHSKSN e-Portfolio
 * @category Controllers
 */
class RewardsShopController extends Controller
{
    /**
     * Display the Rewards Shop catalog with user's current points balance.
     */
    public function index(): void
    {
        Auth::sync(); // Ensure EXP/Points are synced from DB

        $userId = Auth::id();
        $user = Auth::user();
        $userPoints = (int) ($user['points'] ?? 0);

        // Fetch active shop items ordered by type then cost
        $shopItems = Database::fetchAll(
            "SELECT * FROM rewards_shop
             WHERE is_active = 1
             ORDER BY
                 FIELD(type, 'badge', 'theme', 'privilege'),
                 points_cost ASC"
        );

        // Fetch user's redemption history from the shop
        $myRedemptions = Database::fetchAll(
            "SELECT ur.*, rs.name as reward_name, rs.type as reward_type, rs.image_icon
             FROM user_reward_redemptions ur
             JOIN rewards_shop rs ON ur.reward_id = rs.id
             WHERE ur.user_id = ?
             ORDER BY ur.redeemed_at DESC
             LIMIT 20",
            [$userId]
        );

        // Decode metadata JSON for each item
        $shopItems = array_map(function ($item) {
            if (!empty($item['metadata'])) {
                $item['metadata'] = json_decode($item['metadata'], true) ?: [];
            }
            return $item;
        }, $shopItems);

        $this->view('rewards.shop', [
            'title' => '🛍️ Rewards Shop — เปลี่ยน Points เป็นรางวัล',
            'shopItems' => $shopItems,
            'userPoints' => $userPoints,
            'myRedemptions' => $myRedemptions,
        ]);
    }

    /**
     * Process a reward redemption with full transactional integrity.
     *
     * Flow:
     *   1. Validate CSRF & reward existence
     *   2. Check stock availability
     *   3. Verify user has sufficient points
     *   4. BEGIN TRANSACTION
     *      a. Deduct points from users table
     *      b. Insert redemption record
     *      c. Decrement stock (if not unlimited)
     *      d. Grant the reward (apply theme/badge/privilege)
     *      e. Log to AuditLog
     *   5. COMMIT (or ROLLBACK on any failure)
     */
    public function redeem(string $id): void
    {
        CSRF::check();
        $userId = Auth::id();
        $rewardId = (int) $id;

        // Fetch reward item
        $reward = Database::fetch(
            "SELECT * FROM rewards_shop WHERE id = ? AND is_active = 1",
            [$rewardId]
        );

        if (!$reward) {
            Session::flash('error', 'ไม่พบรายการรางวัลนี้ หรือรางวัลไม่เปิดให้บริการแล้ว');
            App::redirect('shop');
            return;
        }

        // Check stock
        $stock = (int) $reward['stock'];
        if ($stock !== -1 && $stock <= 0) {
            Session::flash('error', '❌ ขออภัย รางวัลนี้หมดชั่วคราว โปรดติดตามรางวัลใหม่เร็วๆ นี้');
            App::redirect('shop');
            return;
        }

        // Check points balance
        $user = Database::fetch(
            "SELECT points, exp, level FROM users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            Session::flash('error', 'ไม่พบข้อมูลผู้ใช้');
            App::redirect('shop');
            return;
        }

        $userPoints = (int) $user['points'];
        $cost = (int) $reward['points_cost'];

        if ($userPoints < $cost) {
            $shortfall = $cost - $userPoints;
            Session::flash(
                'error',
                "❌ Points ไม่เพียงพอ — คุณต้องการอีก <strong>" . number_format($shortfall) . " Points</strong> เพื่อแลกรางวัลนี้"
            );
            App::redirect('shop');
            return;
        }

        // Check if user already redeemed this specific item (prevent duplicates for badges/themes)
        $existingRedemption = Database::fetch(
            "SELECT id FROM user_reward_redemptions WHERE user_id = ? AND reward_id = ? AND status IN ('pending', 'granted')",
            [$userId, $rewardId]
        );

        if ($existingRedemption && in_array($reward['type'], ['badge'])) {
            Session::flash('error', 'คุณเคยแลกเหรียญตรานี้แล้ว ไม่สามารถแลกซ้ำได้');
            App::redirect('shop');
            return;
        }

        // ====== ATOMIC TRANSACTION ======
        try {
            Database::beginTransaction();

            // Step 1: Deduct points
            $affected = Database::execute(
                "UPDATE users SET points = points - ? WHERE id = ? AND points >= ?",
                [$cost, $userId, $cost]
            );

            if ($affected === 0) {
                throw new RuntimeException('จุดไม่เพียงพอในระหว่างการทำรายการ (race condition protection)');
            }

            // Step 2: Record the redemption
            $redemptionId = Database::insert(
                "INSERT INTO user_reward_redemptions (user_id, reward_id, points_spent, status)
                 VALUES (?, ?, ?, 'granted')",
                [$userId, $rewardId, $cost]
            );

            // Step 3: Decrement stock if not unlimited (prevents overselling)
            if ($stock !== -1) {
                $stockAffected = Database::execute(
                    "UPDATE rewards_shop SET stock = stock - 1 WHERE id = ? AND stock > 0",
                    [$rewardId]
                );
                if ($stockAffected === 0) {
                    throw new RuntimeException('ของรางวัลนี้หมดแล้ว');
                }
            }

            // Step 4: Grant the reward based on type
            $this->grantReward($reward, $userId);

            Database::commit();

            // Step 5: Audit log (outside transaction to avoid locking)
            AuditLog::log(
                $userId,
                'reward.redeemed',
                'rewards_shop',
                $rewardId,
                ['user_points_before' => $userPoints],
                [
                    'user_points_after' => $userPoints - $cost,
                    'reward_name' => $reward['name'],
                    'points_spent' => $cost,
                    'redemption_id' => $redemptionId,
                ]
            );

            // Sync session
            Auth::sync();

            Session::flash(
                'success',
                "🎉 สำเร็จ! คุณได้แลก <strong>" . e($reward['name']) . "</strong> เรียบร้อยแล้ว — หัก " . number_format($cost) . " Points"
            );
            App::redirect('shop');

        } catch (Exception $e) {
            Database::rollback();
            App::log("RewardsShop redemption error (user #{$userId}): " . $e->getMessage(), 'ERROR');
            $msg = ($e instanceof RuntimeException) ? $e->getMessage() : 'เกิดข้อผิดพลาดในการทำรายการ กรุณาลองใหม่อีกครั้ง';
            Session::flash('error', '❌ ' . $msg);
            App::redirect('shop');
        }
    }

    /**
     * Grant the reward based on its type.
     * This is the "fulfillment" step — apply the actual benefit.
     */
    private function grantReward(array $reward, int $userId): void
    {
        $metadata = !empty($reward['metadata'])
            ? json_decode($reward['metadata'], true)
            : [];

        switch ($reward['type']) {
            case 'badge':
                // Award the badge via BadgeService if a badge_code is defined
                if (!empty($metadata['badge_code'])) {
                    $badgeCode = $metadata['badge_code'];

                    // Check if badge exists in badges_master
                    $badge = Database::fetch(
                        "SELECT * FROM badges_master WHERE code = ?",
                        [$badgeCode]
                    );

                    if ($badge) {
                        // Check if user already has this badge
                        $existing = Database::fetch(
                            "SELECT id FROM user_badges WHERE user_id = ? AND badge_code = ?",
                            [$userId, $badgeCode]
                        );

                        if (!$existing) {
                            Database::insert(
                                "INSERT INTO user_badges (user_id, badge_code) VALUES (?, ?)",
                                [$userId, $badgeCode]
                            );
                        }
                    }
                }
                break;

            case 'theme':
                // Store theme preference for the user (can be used by theme renderer later)
                if (!empty($metadata['theme_key'])) {
                    $settingKey = "user_{$userId}_portfolio_theme";
                    Settings::set($settingKey, (string) $metadata['theme_key']);
                }
                break;

            case 'privilege':
                // Store privilege grant with optional expiry
                if (!empty($metadata['privilege_key'])) {
                    $durationDays = $metadata['duration_days'] ?? 30;
                    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));
                    $settingKey = "user_{$userId}_privilege_" . $metadata['privilege_key'];

                    Settings::set(
                        $settingKey,
                        json_encode([
                            'status' => 'active',
                            'granted_at' => date('Y-m-d H:i:s'),
                            'expires_at' => $expiresAt,
                            'features' => $metadata['features'] ?? [],
                        ], JSON_UNESCAPED_UNICODE)
                    );
                }
                break;

            default:
                // Generic: just log the redemption, no specific grant action
                break;
        }
    }
}
