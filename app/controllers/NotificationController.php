<?php
/**
 * NotificationController
 */
class NotificationController extends Controller
{
    public function index(): void
    {
        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT 50",
            [Auth::id()]
        );
        $unreadCount = Database::fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [Auth::id()]
        )['count'] ?? 0;
        $this->view('notifications.index', ['notifications' => $notifications, 'unreadCount' => $unreadCount]);
    }

    public function markRead(string $id): void
    {
        Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [(int) $id, Auth::id()]);
        $notif = Database::fetch("SELECT link FROM notifications WHERE id = ?", [(int) $id]);
        App::redirect($notif['link'] ?? 'notifications');
    }

    public function markAllRead(): void
    {
        Database::execute("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [Auth::id()]);
        App::redirect('notifications');
    }
}
