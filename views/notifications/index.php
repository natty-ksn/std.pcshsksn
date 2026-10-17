<?php $pageTitle = 'การแจ้งเตือน'; ?>
<div style="max-width:700px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="mb-0">🔔 การแจ้งเตือน</h2>
        <?php if (!empty($notifications) && $unreadCount > 0): ?>
            <form method="POST" action="<?= url('notifications/mark-all-read') ?>" style="margin:0;">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn-sm"
                    style="background:var(--gray-100);color:var(--gray-700);border:none;padding:8px 16px;border-radius:8px;font-size:0.85rem;cursor:pointer;font-weight:600;">
                    ✅ อ่านทั้งหมด
                </button>
            </form>
        <?php endif; ?>
    </div>
    <div class="mt-3"></div>

    <?php if (empty($notifications)): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">🔔</div>
                <h3>ไม่มีการแจ้งเตือน</h3>
            </div>
        </div>
    <?php else: ?>
        <?php 
        $cleanText = function(string $text): string {
            if (strpos($text, 'Ó') !== false || strpos($text, 'fif') !== false) {
                if (strpos($text, 'ได้รับตรา') !== false) {
                    return preg_replace('/🏅 ได้รับตรา.*/u', '🏅 ได้รับตรา 🌟 ผลงานแรก', $text);
                }
                if (strpos($text, 'ยินดีด้วย') !== false) {
                    return 'ยินดีด้วย! คุณได้รับตรา "ผลงานแรก" เพิ่มผลงานแรกเข้าสู่ระบบ — ทุกการเดินทางเริ่มจากก้าวแรก';
                }
            }
            return $text;
        };
        foreach ($notifications as $n): 
            $title = $cleanText($n['title'] ?? '');
            $message = $cleanText($n['message'] ?? '');
        ?>
            <a href="<?= url('notifications/' . $n['id'] . '/read') ?>" class="card mb-2"
                style="display:block;text-decoration:none;<?= $n['is_read'] ? 'opacity:0.6;' : '' ?>">
                <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:16px;">
                    <div
                        style="width:40px;height:40px;border-radius:50%;background:<?= $n['is_read'] ? 'var(--gray-100)' : 'var(--primary-50)' ?>;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                        <?= $n['type'] === 'comment' ? '💬' : ($n['type'] === 'badge' ? '🏅' : '🔔') ?>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:600;color:var(--gray-800);font-size:0.9rem;">
                            <?= e($title) ?>
                        </div>
                        <div style="font-size:0.82rem;color:var(--gray-500);">
                            <?= e($message) ?>
                        </div>
                        <div style="font-size:0.75rem;color:var(--gray-400);margin-top:4px;">
                            <?= timeAgo($n['created_at']) ?>
                        </div>
                    </div>
                    <?php if (!$n['is_read']): ?><span
                            style="width:8px;height:8px;border-radius:50%;background:var(--primary-500);"></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>