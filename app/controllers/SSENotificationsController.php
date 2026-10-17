<?php
/**
 * SSENotificationsController — Server-Sent Events Stream for Real-Time Notifications
 *
 * Streams notification events to the browser using SSE (Server-Sent Events).
 * The client connects via EventSource and receives JSON payloads whenever
 * new notifications or badges arrive — no page refresh needed.
 *
 * SSE Protocol:
 *   - Each message: "data: {json}\n\n"
 *   - Events auto-identify with a numeric ID (notification/badge primary key)
 *   - Client auto-reconnects; sends Last-Event-ID header on reconnect
 *   - Loop kills itself gracefully if connection_aborted()
 *
 * @package  PCSHSKSN e-Portfolio
 * @category Controllers
 */
class SSENotificationsController
{
    /**
     * How often to poll the database for new events (seconds).
     */
    private const POLL_INTERVAL = 5;

    /**
     * Maximum execution time for the SSE stream (short-lived 15s window to prevent zombie workers on shared hosting).
     */
    private const MAX_EXEC_SECONDS = 15;

    /**
     * Number of badge events to check per poll window.
     */
    private const BADGE_LIMIT = 5;

    /**
     * Number of notification events to check per poll window.
     */
    private const NOTIF_LIMIT = 10;

    /**
     * Notification stream / lightweight poll endpoint.
     *
     * Supports two modes:
     *   1. ?mode=poll (recommended for high-concurrency shared hosts):
     *      Performs a single fast query (<10ms), closes DB immediately, and returns JSON.
     *   2. Default SSE stream:
     *      Short-lived (max 15s) stream that sends a heartbeat comment on every iteration
     *      so PHP immediately detects client disconnect via connection_aborted().
     */
    public function stream(): void
    {
        // Validate authentication — no session lock during the stream
        if (!Session::has('user_id')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = (int) Session::get('user_id');

        // Close the session file ASAP so other requests aren't blocked
        session_write_close();

        $lastId = 0;
        if (!empty($_SERVER['HTTP_LAST_EVENT_ID'])) {
            $lastId = (int) $_SERVER['HTTP_LAST_EVENT_ID'];
        } elseif (!empty($_GET['last_id'])) {
            $lastId = (int) $_GET['last_id'];
        }

        // ========================================
        // Fast Single-Pass JSON Poll Mode (Zero Zombie Processes)
        // ========================================
        $isPollMode = (($_GET['mode'] ?? '') === 'poll')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if ($isPollMode) {
            $events = $this->fetchPendingEvents($userId, $lastId);
            Database::close();
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            echo json_encode([
                'events' => $events['items'],
                'last_id' => $events['last_id'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // ========================================
        // SSE HTTP Headers (Short-lived Stream)
        // ========================================
        ignore_user_abort(false);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');       // Disable Nginx buffering
        header('Content-Encoding: none');       // No compression for streaming

        // Instruct browser EventSource to wait 15s before reconnecting
        echo "retry: 15000\n";
        echo ": connected\n\n";
        $this->flush();

        $startTime = time();

        // ========================================
        // SSE Main Loop (Max 15s, sends heartbeat every cycle)
        // ========================================
        while (true) {
            if (connection_aborted()) {
                Database::close();
                break;
            }

            if ((time() - $startTime) >= self::MAX_EXEC_SECONDS) {
                echo ": max-exec-reached\n\n";
                $this->flush();
                Database::close();
                break;
            }

            $events = $this->fetchPendingEvents($userId, $lastId);
            $lastId = $events['last_id'];

            foreach ($events['items'] as $item) {
                $this->sendEvent((int) $item['id'], $item);
            }

            // Always send a heartbeat byte so PHP detects closed client sockets immediately!
            echo ": hb\n\n";
            $this->flush();

            // Close DB connection before sleeping to free MySQL connection slot
            Database::close();

            if (connection_aborted()) {
                break;
            }

            sleep(self::POLL_INTERVAL);
        }
    }

    /**
     * Fetch new unpushed notifications and recently earned badges in a single fast pass.
     */
    private function fetchPendingEvents(int $userId, int $lastId): array
    {
        $items = [];
        try {
            $notifications = Database::fetchAll(
                "SELECT id, type, title, message, link, created_at
                 FROM notifications
                 WHERE user_id = ?
                   AND is_pushed = 0
                   AND id > ?
                 ORDER BY id ASC
                 LIMIT ?",
                [$userId, $lastId < 100000 ? $lastId : 0, self::NOTIF_LIMIT]
            );

            $pushedIds = [];
            foreach ($notifications as $notif) {
                $nid = (int) $notif['id'];
                $items[] = [
                    'source' => 'notification',
                    'id' => $nid,
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'] ?? '',
                    'link' => $notif['link'] ?? '',
                    'created_at' => $notif['created_at'],
                ];
                $lastId = max($lastId, $nid);
                $pushedIds[] = $nid;
            }

            if (!empty($pushedIds)) {
                $placeholders = implode(',', array_fill(0, count($pushedIds), '?'));
                Database::execute(
                    "UPDATE notifications SET is_pushed = 1 WHERE id IN ({$placeholders})",
                    $pushedIds
                );
            }

            $badges = Database::fetchAll(
                "SELECT ub.id, ub.earned_at, bm.name, bm.icon, bm.description
                 FROM user_badges ub
                 JOIN badges_master bm ON ub.badge_code = bm.code
                 WHERE ub.user_id = ?
                   AND ub.earned_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
                   AND ub.id > ?
                 ORDER BY ub.id ASC
                 LIMIT ?",
                [$userId, max(0, $lastId - 100000), self::BADGE_LIMIT]
            );

            foreach ($badges as $badge) {
                $eventId = (int) $badge['id'] + 100000;
                $items[] = [
                    'source' => 'badge',
                    'id' => $eventId,
                    'type' => 'badge.awarded',
                    'title' => "🏅 ได้รับตรา {$badge['icon']} {$badge['name']}",
                    'message' => $badge['description'] ?? '',
                    'link' => '/rewards/badges',
                    'created_at' => $badge['earned_at'],
                    'badge_name' => $badge['name'],
                    'badge_icon' => $badge['icon'],
                ];
                $lastId = max($lastId, $eventId);
            }
        } catch (\Throwable $e) {
            // Silently ignore transient DB errors during background poll
        }

        return ['items' => $items, 'last_id' => $lastId];
    }

    /**
     * Send a single SSE event to the client.
     *
     * Format:
     *   id: {event_id}
     *   data: {json_payload}
     *
     * @param int   $eventId  Unique, monotonically increasing event ID
     * @param array $payload  Data to serialize as JSON
     */
    private function sendEvent(int $eventId, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "id: {$eventId}\n";
        echo "data: {$json}\n\n";
        $this->flush();
    }

    /**
     * Flush output buffers and push data to the client immediately.
     *
     * This is critical for SSE — without flushing, PHP buffers output
     * and the client sees nothing until the buffer fills or the script ends.
     */
    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
