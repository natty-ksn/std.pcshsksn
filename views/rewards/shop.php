<?php
$pageTitle = 'Rewards Shop ร้านค้าแลกแต้ม';
/**
 * Rewards Shop — Premium Glassmorphism UI
 * Phase 4.1: Points Shop MVP
 */

$typeLabels = [
    'theme' => '🎨 ธีม',
    'badge' => '🏅 เหรียญตรา',
    'privilege' => '🚀 สิทธิ์พิเศษ',
];

$typeColors = [
    'theme' => ['#6366f1', '#8b5cf6'],
    'badge' => ['#f59e0b', '#f97316'],
    'privilege' => ['#10b981', '#06b6d4'],
];
?>

<style>
/* ====== Glassmorphism Rewards Shop ====== */
.shop-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #0f172a 100%);
    padding: 40px 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background Orbs */
.shop-page::before,
.shop-page::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(120px);
    opacity: 0.3;
    animation: orbFloat 20s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}

.shop-page::before {
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, #6366f1, transparent 70%);
    top: -100px;
    right: -100px;
    animation-delay: 0s;
}

.shop-page::after {
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, #10b981, transparent 70%);
    bottom: -150px;
    left: -150px;
    animation-delay: -10s;
}

@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(50px, -30px) scale(1.1); }
    66% { transform: translate(-30px, 50px) scale(0.95); }
}

.shop-content {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
}

/* Points Balance Hero Card */
.balance-hero {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 28px;
    padding: 40px;
    margin-bottom: 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 24px;
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.balance-left h1 {
    font-size: 2.5rem;
    font-weight: 900;
    color: #ffffff;
    margin: 0;
    line-height: 1.1;
    letter-spacing: -1px;
}

.balance-left h1 span {
    background: linear-gradient(135deg, #6366f1, #a78bfa, #c084fc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.balance-left p {
    color: rgba(255, 255, 255, 0.6);
    margin: 12px 0 0;
    font-size: 1rem;
    font-weight: 500;
}

.balance-right {
    text-align: center;
}

.balance-points {
    font-size: 4rem;
    font-weight: 900;
    background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}

.balance-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: rgba(255, 255, 255, 0.5);
    margin-top: 8px;
    font-weight: 700;
}

/* Section Headers */
.section-header {
    color: #ffffff;
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-header::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(255,255,255,0.2), transparent);
}

/* Shop Grid */
.shop-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 28px;
    margin-bottom: 56px;
}

/* Shop Card — Glassmorphism */
.shop-card {
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    animation: slideUp 0.6s ease-out both;
}

.shop-card:nth-child(1) { animation-delay: 0.1s; }
.shop-card:nth-child(2) { animation-delay: 0.2s; }
.shop-card:nth-child(3) { animation-delay: 0.3s; }

.shop-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 24px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent 50%, rgba(255,255,255,0.05));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

.shop-card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow:
        0 20px 60px rgba(0, 0, 0, 0.4),
        0 0 40px rgba(99, 102, 241, 0.15);
}

.shop-card.out-of-stock {
    opacity: 0.5;
    pointer-events: none;
}

.shop-card.cannot-afford {
    opacity: 0.7;
}

.shop-card.cannot-afford:hover {
    transform: translateY(-4px);
}

/* Card Icon Area */
.card-icon-area {
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.card-icon-area::before {
    content: '';
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.25;
}

.card-icon-area .icon-emoji {
    font-size: 5rem;
    z-index: 1;
    transition: transform 0.4s ease;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
}

.shop-card:hover .icon-emoji {
    transform: scale(1.15) rotate(-5deg);
}

/* Type Badge */
.type-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #ffffff;
    z-index: 2;
}

/* Cost Badge */
.cost-badge {
    position: absolute;
    bottom: 16px;
    left: 16px;
    right: 16px;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.cost-amount {
    font-size: 1.3rem;
    font-weight: 900;
    color: #fbbf24;
}

.cost-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255, 255, 255, 0.5);
    font-weight: 700;
}

/* Card Body */
.card-body-shop {
    padding: 24px;
}

.card-body-shop h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 10px;
    line-height: 1.3;
}

.card-body-shop p {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.6;
    margin: 0 0 20px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.stock-info {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.4);
    margin-bottom: 16px;
    font-weight: 600;
}

.stock-info.available {
    color: #10b981;
}

.stock-info.low {
    color: #f59e0b;
}

/* Redeem Button */
.redeem-btn {
    width: 100%;
    padding: 14px 24px;
    border: none;
    border-radius: 16px;
    font-size: 0.95rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.redeem-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    opacity: 0;
    transition: opacity 0.3s;
}

.redeem-btn:hover::before {
    opacity: 1;
}

.redeem-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.redeem-btn:active {
    transform: translateY(0);
}

.redeem-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.redeem-btn:disabled::before {
    display: none;
}

/* Redemption History */
.history-section {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 24px;
    padding: 32px;
    margin-top: 40px;
}

.history-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    margin-bottom: 12px;
    transition: all 0.2s;
}

.history-item:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.1);
}

.history-item:last-child {
    margin-bottom: 0;
}

.history-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.05);
    flex-shrink: 0;
}

.history-details {
    flex: 1;
}

.history-details h4 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #ffffff;
}

.history-details small {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.75rem;
}

.history-points {
    font-size: 1rem;
    font-weight: 900;
    color: #f59e0b;
    white-space: nowrap;
}

.status-granted {
    color: #10b981;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.status-pending {
    color: #f59e0b;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255, 255, 255, 0.3);
}

.empty-state .empty-icon {
    font-size: 4rem;
    margin-bottom: 16px;
    display: block;
}

.empty-state p {
    font-size: 1rem;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .balance-hero {
        flex-direction: column;
        text-align: center;
        padding: 28px 20px;
    }

    .balance-left h1 {
        font-size: 1.8rem;
    }

    .balance-points {
        font-size: 3rem;
    }

    .shop-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="shop-page">
    <div class="shop-content">

        <!-- Points Balance Hero -->
        <div class="balance-hero">
            <div class="balance-left">
                <h1>🛍️ <span>Rewards Shop</span></h1>
                <p>เปลี่ยน Points ที่สะสมมาเป็นรางวัลสุด Exclusive —ธีม, เหรียญตรา, และสิทธิ์พิเศษ</p>
            </div>
            <div class="balance-right">
                <div class="balance-points"><?= number_format($userPoints) ?></div>
                <div class="balance-label">Available Points</div>
            </div>
        </div>

        <!-- Shop Items Grid -->
        <div class="section-header">🔥 สินค้าAvailable</div>

        <?php if (empty($shopItems)): ?>
            <div class="empty-state">
                <span class="empty-icon">🏪</span>
                <p>ยังไม่มีสินค้าในร้าน — โปรดติดตามไอเทมใหม่เร็วๆ นี้</p>
            </div>
        <?php else: ?>
            <div class="shop-grid">
                <?php foreach ($shopItems as $item): ?>
                    <?php
                        $canAfford = $userPoints >= (int) $item['points_cost'];
                        $stock = (int) $item['stock'];
                        $isUnlimited = $stock === -1;
                        $isOutOfStock = !$isUnlimited && $stock <= 0;
                        $isLowStock = !$isUnlimited && $stock > 0 && $stock <= 5;
                        $colors = $typeColors[$item['type']] ?? ['#6366f1', '#8b5cf6'];
                        $gradient = "linear-gradient(135deg, {$colors[0]}, {$colors[1]})";
                    ?>
                    <div class="shop-card <?= !$canAfford ? 'cannot-afford' : '' ?> <?= $isOutOfStock ? 'out-of-stock' : '' ?>">
                        <!-- Icon Area -->
                        <div class="card-icon-area">
                            <div style="position:absolute; width:200px; height:200px; border-radius:50%; background: <?= $gradient ?>; filter:blur(60px); opacity:0.2;"></div>
                            <span class="icon-emoji"><?= e($item['image_icon'] ?? '🎁') ?></span>

                            <!-- Type Badge -->
                            <span class="type-badge" style="background: <?= $gradient ?>;">
                                <?= e($typeLabels[$item['type']] ?? $item['type']) ?>
                            </span>

                            <!-- Cost -->
                            <div class="cost-badge">
                                <span class="cost-amount"><?= number_format($item['points_cost']) ?></span>
                                <span class="cost-label">Points</span>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body-shop">
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= e($item['description']) ?></p>

                            <!-- Stock -->
                            <?php if ($isOutOfStock): ?>
                                <div class="stock-info">❌ หมดชั่วคราว</div>
                            <?php elseif ($isLowStock): ?>
                                <div class="stock-info">⚡ เหลือเพียง <?= $stock ?> ชิ้น — รีบมือ!</div>
                            <?php elseif ($isUnlimited): ?>
                                <div class="stock-info available">✅ มีไม่จำกัด</div>
                            <?php else: ?>
                                <div class="stock-info available">✅ เหลือ <?= number_format($stock) ?></div>
                            <?php endif; ?>

                            <!-- Redeem Form -->
                            <form method="POST" action="<?= url('shop/redeem/' . $item['id']) ?>"
                                  onsubmit="var b=this.querySelector('button[type=submit]');if(b){b.disabled=true;b.innerText='⏳ กำลังดำเนินการ...';}">
                                <?= CSRF::field() ?>
                                <button type="submit"
                                        class="redeem-btn"
                                        style="background: <?= $gradient ?>;"
                                        <?= (!$canAfford || $isOutOfStock) ? 'disabled' : '' ?>>
                                    <?php if ($isOutOfStock): ?>
                                        🔒 หมดสต็อก
                                    <?php elseif (!$canAfford): ?>
                                        🔒 Points ไม่เพียงพอ
                                    <?php else: ?>
                                        ⚡ แกล้ว! แลกเลย
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Redemption History -->
        <div class="history-section">
            <div class="section-header">📜 ประวัติการแลก</div>

            <?php if (empty($myRedemptions)): ?>
                <div class="empty-state" style="padding: 40px 20px;">
                    <span class="empty-icon">📭</span>
                    <p>ยังไม่มีประวัติการแลก — เริ่มแลกไอเทมแรกของคุณได้เลย!</p>
                </div>
            <?php else: ?>
                <?php foreach ($myRedemptions as $redemption): ?>
                    <div class="history-item">
                        <div class="history-icon">
                            <?= e($redemption['image_icon'] ?? '🎁') ?>
                        </div>
                        <div class="history-details">
                            <h4><?= e($redemption['reward_name']) ?></h4>
                            <small><?= e($redemption['redeemed_at']) ?></small>
                            <span class="status-<?= $redemption['status'] ?>">
                                <?= $redemption['status'] === 'granted' ? '✅Granted' : '⏳ Pending' ?>
                            </span>
                        </div>
                        <div class="history-points">-<?= number_format($redemption['points_spent']) ?> pts</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>
