<?php
declare(strict_types=1);

class NotifikasiController {

    /**
     * GET /api/notifications
     * JSON untuk polling sidebar — wrapper {data, unread_count}
     */
    public function index(): void {
        Auth::requireAuth();
        header('Content-Type: application/json');
        $uid    = Auth::id();
        $notifs = Notification::getUnread($uid, 20);  // sudah include created_at_human & int is_read
        $unread = Notification::countUnread($uid);
        echo json_encode([
            'data'         => $notifs,
            'unread_count' => $unread,
        ]);
    }

    /**
     * POST /api/notifications/read
     * Body: {all:true} atau {id:123}
     */
    public function markRead(): void {
        Auth::requireAuth();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $uid   = Auth::id();

        if (!empty($input['all'])) {
            Notification::markAllRead($uid);
        } elseif (!empty($input['id'])) {
            Notification::markRead((int)$input['id'], $uid);
        }

        echo json_encode(['success' => true, 'unread' => Notification::countUnread($uid)]);
    }

    /**
     * POST /api/notifications/delete-all
     */
    public function deleteAll(): void {
        Auth::requireAuth();
        header('Content-Type: application/json');
        Notification::deleteAll((int)Auth::id());
        echo json_encode(['success' => true]);
    }

    /**
     * GET /notifications
     * Halaman full notifikasi.
     * Support filter: ?filter=unread
     */
    public function page(): void {
        Auth::requireAuth();
        $uid    = Auth::id();
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $filter = $_GET['filter'] ?? 'all';   // 'all' | 'unread'
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        if ($filter === 'unread') {
            $where = 'WHERE user_id = ? AND is_read = 0';
        } else {
            $where = 'WHERE user_id = ?';
        }

        $notifs = Database::query(
            "SELECT * FROM notifications {$where}
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$uid, $limit, $offset]
        );

        // fix is_read cast + tambahkan human time di server-side juga
        foreach ($notifs as &$n) {
            $n['is_read']          = (int)$n['is_read'];
            $n['created_at_human'] = Notification::timeAgo($n['created_at'] ?? '');
        }
        unset($n);

        $total     = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM notifications {$where}", [$uid]
        )['c'] ?? 0);
        $totalPage   = (int)ceil($total / $limit) ?: 1;
        $unreadTotal = Notification::countUnread($uid);

        View::layout('notifications/index', [
            'pageTitle'    => 'Notifikasi',
            'notifs'       => $notifs,
            'total'        => $total,
            'totalPage'    => $totalPage,
            'page'         => $page,
            'filter'       => $filter,
            'unreadTotal'  => $unreadTotal,
        ]);
    }
}
