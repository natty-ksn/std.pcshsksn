<?php
/**
 * RewardController — ระบบแลกรางวัล (Redemption System) + Gamification Hub
 */
class RewardController extends Controller
{
    /**
     * แสดงรายการของรางวัล + Badge Collection
     */
    public function index(): void
    {
        Auth::sync(); // Sync EXP/Points

        $view = $this->query('view') ?? 'rewards';

        if ($view === 'badges') {
            $this->showBadges();
            return;
        }

        $category = $this->query('category');
        $where = "is_active = 1";
        $params = [];

        if ($category) {
            $where .= " AND category = ?";
            $params[] = $category;
        }

        $rewards = Database::fetchAll("SELECT * FROM rewards WHERE $where ORDER BY points_required ASC", $params);
        $myRedemptions = Database::fetchAll(
            "SELECT ur.*, r.title, r.image_path FROM user_rewards ur
             JOIN rewards r ON ur.reward_id = r.id
             WHERE ur.user_id = ? ORDER BY ur.redeemed_at DESC",
            [Auth::id()]
        );

        $this->view('rewards.index', [
            'rewards' => $rewards,
            'myRedemptions' => $myRedemptions,
            'userPoints' => Auth::user()['points'] ?? 0,
            'selectedCategory' => $category
        ]);
    }

    /**
     * Badge Collection — หน้ารวมเหรียญตรา (Phase 5)
     */
    public function showBadges(): void
    {
        $userId = Auth::id();

        $summary = BadgeService::getCollectionSummary($userId);
        $allDefs = BadgeService::getAllDefinitions();
        $userBadges = BadgeService::getUserBadges($userId);

        // Auto-check all badges on visit
        $newBadges = BadgeService::checkAll($userId);

        // Re-fetch after auto-check
        if ($newBadges['total'] > 0) {
            $summary = BadgeService::getCollectionSummary($userId);
            $userBadges = BadgeService::getUserBadges($userId);
        }

        $this->view('rewards.badges', [
            'title' => '🏅 ห้องเกียรติยศ | Badge Collection',
            'summary' => $summary,
            'allDefs' => $allDefs,
            'userBadges' => $userBadges,
            'newBadges' => $newBadges,
        ]);
    }

    /**
     * ดำเนินการแลกรางวัล
     */
    public function redeem(string $id): void
    {
        CSRF::check();
        $id = (int) $id;

        $reward = Database::fetch("SELECT * FROM rewards WHERE id = ? AND is_active = 1", [$id]);

        if (!$reward) {
            Session::flash('error', 'ไม่พบรายการรางวัลนี้');
            App::redirect('rewards');
            return;
        }

        $user = Database::fetch("SELECT points FROM users WHERE id = ?", [Auth::id()]);

        if ($user['points'] < $reward['points_required']) {
            Session::flash('error', 'ขออภัย คุณมีคะแนนไม่เพียงพอสำหรับการแลกรางวัลนี้');
            App::redirect('rewards');
            return;
        }

        if ($reward['stock_quantity'] !== -1 && $reward['stock_quantity'] <= 0) {
            Session::flash('error', 'ขออภัย รางวัลนี้หมดชั่วคราว');
            App::redirect('rewards');
            return;
        }

        try {
            Database::beginTransaction();

            // 1. Deduct points atomically with condition
            $stmt = Database::connect()->prepare(
                "UPDATE users SET points = points - ? WHERE id = ? AND points >= ?"
            );
            $stmt->execute([(int) $reward['points_required'], Auth::id(), (int) $reward['points_required']]);

            if ($stmt->rowCount() === 0) {
                Database::rollback();
                Session::flash('error', 'ขออภัย คะแนนสะสมของคุณไม่เพียงพอสำหรับการแลกรางวัลนี้');
                App::redirect('rewards');
                return;
            }

            // 2. Decrease stock atomically if needed
            if ($reward['stock_quantity'] !== -1) {
                $stockStmt = Database::connect()->prepare(
                    "UPDATE rewards SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0"
                );
                $stockStmt->execute([(int) $id]);
                if ($stockStmt->rowCount() === 0) {
                    Database::rollback();
                    Session::flash('error', 'ขออภัย รางวัลนี้หมดชั่วคราว');
                    App::redirect('rewards');
                    return;
                }
            }

            // 3. Record redemption
            Database::insert(
                "INSERT INTO user_rewards (user_id, reward_id, status) VALUES (?, ?, 'pending')",
                [Auth::id(), $id]
            );

            Database::commit();
            Auth::logAction('redeem_reward', 'reward', $id, null, ['points_spent' => $reward['points_required']]);

            Session::flash('success', 'ยินดีด้วย! คุณได้แลกรางวัล "' . $reward['title'] . '" เรียบร้อยแล้ว กรุณาตรวจสอบสิทธิ์ที่หน้าระวัติการแลก');
            App::redirect('rewards');

        } catch (Exception $e) {
            Database::rollback();
            App::log("Redemption error: " . $e->getMessage(), 'ERROR');
            Session::flash('error', 'เกิดข้อผิดพลาดในการแลกรางวัล กรุณาลองใหม่อีกครั้ง');
            App::redirect('rewards');
        }
    }
}
