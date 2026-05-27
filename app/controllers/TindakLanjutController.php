<?php
class TindakLanjutController
{
    public static function index(): void
    {
        Auth::requireAuth();
        $status = $_GET['status'] ?? '';
        $userId = (int)($_GET['user_id'] ?? 0);

        $where  = ['1=1'];
        $params = [];

        if (!Auth::hasRole('admin', 'sekretaris')) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = Auth::id();
        } elseif ($userId) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = $userId;
        }

        if ($status) { $where[] = 'tl.status = ?'; $params[] = $status; }

        $whereStr = implode(' AND ', $where);
        $items    = Database::query(
            "SELECT tl.*, m.title AS meeting_title,
                    u.name AS assignee_name
             FROM tindak_lanjut tl
             JOIN meetings m ON m.id = tl.meeting_id
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE {$whereStr}
             ORDER BY tl.due_date ASC, tl.created_at DESC",
            $params
        );

        $users = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        View::layout('tindak_lanjut/index', [
            'title'   => 'Tindak Lanjut',
            'items'   => $items,
            'users'   => $users,
            'status'  => $status,
            'user_id' => $userId,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $d = $_POST;

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut
             (meeting_id, description, assigned_to, due_date, priority, created_by)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            (int)$d['meeting_id'],
            trim($d['description']),
            (int)$d['assigned_to'],
            $d['due_date'],
            $d['priority'] ?? 'medium',
            Auth::id(),
        ]);

        Notification::send((int)$d['assigned_to'], 'tindak_lanjut',
            "Anda mendapat tugas tindak lanjut baru: {$d['description']}",
            '/tindak-lanjut'
        );

        $_SESSION['flash_success'] = 'Tindak lanjut berhasil ditambahkan.';
        header('Location: ' . BASE_URL . '/tindak-lanjut'); exit;
    }

    public static function updateStatus(int $id): void
    {
        Auth::requireAuth();
        $status  = $_POST['status'] ?? '';
        $allowed = ['pending', 'in_progress', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']); exit;
        }
        $tl = Database::queryOne("SELECT * FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { echo json_encode(['success' => false]); exit; }
        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403); echo json_encode(['success' => false, 'message' => 'Akses ditolak']); exit;
        }
        $completedAt = $status === 'done' ? date('Y-m-d H:i:s') : null;
        Database::getInstance()->prepare(
            "UPDATE tindak_lanjut SET status=?, completed_at=? WHERE id=?"
        )->execute([$status, $completedAt, $id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        Database::getInstance()->prepare("DELETE FROM tindak_lanjut WHERE id=?")->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }
}
