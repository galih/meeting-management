<?php
declare(strict_types=1);

class NotifikasiController {

    // GET /api/notifications  — JSON untuk polling navbar
    public function index(): void {
        Auth::requireAuth();
        header('Content-Type: application/json');
        $uid    = Auth::id();
        $notifs = Notification::getUnread($uid, 20);
        echo json_encode($notifs);
    }

    // POST /api/notifications/read  — tandai dibaca
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

    // GET /notifications  — halaman full notifikasi
    public function page(): void {
        Auth::requireAuth();
        $uid    = Auth::id();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $notifs = Database::query(
            "SELECT * FROM notifications WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$uid, $limit, $offset]
        );
        $total     = (int)(Database::queryOne("SELECT COUNT(*) c FROM notifications WHERE user_id=?", [$uid])['c'] ?? 0);
        $totalPage = (int)ceil($total / $limit);

        // Tandai semua sebagai dibaca ketika halaman ini dibuka
        Notification::markAllRead($uid);

        $pageTitle = 'Semua Notifikasi';
        View::layout('notifications/index', compact('notifs', 'total', 'totalPage', 'page'));
    }
}
