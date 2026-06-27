<?php
class TindakLanjutController
{
    private const PER_PAGE = 20;

    // ── Helper: hitung summary untuk user/filter tertentu ─────────────────────
    private static function getSummary(bool $isAdmin, int $userId): array
    {
        $baseWhere  = !$isAdmin ? 'assigned_to = ?' : ($userId ? 'assigned_to = ?' : '1=1');
        $baseParams = !$isAdmin ? [Auth::id()] : ($userId ? [$userId] : []);
        return [
            'total'       => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere}", $baseParams)['c'] ?? 0),
            'pending'     => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere} AND status='pending'", $baseParams)['c'] ?? 0),
            'in_progress' => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere} AND status='in_progress'", $baseParams)['c'] ?? 0),
            'done'        => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere} AND status='done'", $baseParams)['c'] ?? 0),
            'overdue'     => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere} AND due_date < CURDATE() AND status NOT IN ('done','cancelled')", $baseParams)['c'] ?? 0),
        ];
    }

    public static function index(): void
    {
        Auth::requireAuth();
        $status   = $_GET['status']   ?? '';
        $priority = $_GET['priority'] ?? '';
        $search   = trim($_GET['q']   ?? '');
        $userId   = (int)($_GET['user_id'] ?? 0);
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $offset   = ($page - 1) * self::PER_PAGE;

        $where  = ['1=1'];
        $params = [];

        if (!Auth::hasRole('admin')) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = Auth::id();
        } elseif ($userId) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = $userId;
        }

        if ($status)   { $where[] = 'tl.status = ?';         $params[] = $status; }
        if ($priority) { $where[] = 'tl.priority = ?';       $params[] = $priority; }
        if ($search)   { $where[] = 'tl.description LIKE ?'; $params[] = "%{$search}%"; }

        $whereStr = implode(' AND ', $where);

        // Total rows for pagination
        $totalRows = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut tl WHERE {$whereStr}",
            $params
        )['c'] ?? 0);
        $totalPages = max(1, (int)ceil($totalRows / self::PER_PAGE));

        $tindakLanjutList = Database::query(
            "SELECT tl.*, m.title AS meeting_title,
                    u.name AS assignee_name
             FROM tindak_lanjut tl
             JOIN meetings m ON m.id = tl.meeting_id
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE {$whereStr}
             ORDER BY tl.due_date ASC, tl.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [self::PER_PAGE, $offset])
        );

        $summary = self::getSummary(Auth::hasRole('admin'), $userId);

        $users = Auth::hasRole('admin')
            ? Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name")
            : [];

        View::layout('tindak-lanjut/index', [
            'pageTitle'        => 'Tindak Lanjut',
            'tindakLanjutList' => $tindakLanjutList,
            'summary'          => $summary,
            'users'            => $users,
            'status'           => $status,
            'priority'         => $priority,
            'search'           => $search,
            'user_id'          => $userId,
            'page'             => $page,
            'totalPages'       => $totalPages,
            'totalRows'        => $totalRows,
            'perPage'          => self::PER_PAGE,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $d = $_POST;
        }

        $meetingId  = (int)($d['meeting_id']  ?? 0);
        $desc       = trim($d['description']  ?? '');
        $assignedTo = (int)($d['assigned_to'] ?? 0);
        $dueDate    = ($d['due_date']  ?? '') ?: null;
        $priority   = $d['priority']   ?? 'medium';

        if (!$meetingId || !$desc) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'meeting_id dan description wajib']); exit;
        }

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut
             (meeting_id, description, assigned_to, due_date, priority, created_by)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            $meetingId,
            $desc,
            $assignedTo ?: null,
            $dueDate,
            $priority,
            Auth::id(),
        ]);

        if ($assignedTo) {
            Notification::send($assignedTo, 'tindak_lanjut',
                "Anda mendapat tugas tindak lanjut baru: {$desc}",
                '/tindak-lanjut'
            );
        }

        if (str_contains($contentType, 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]); exit;
        }

        $_SESSION['flash_success'] = 'Tindak lanjut berhasil ditambahkan.';
        header('Location: ' . BASE_URL . '/tindak-lanjut'); exit;
    }

    public static function updateStatus(int $id): void
    {
        Auth::requireAuth();
        $input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $status  = $input['status'] ?? '';
        $allowed = ['pending', 'in_progress', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']); exit;
        }
        $tl = Database::queryOne("SELECT * FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { header('Content-Type: application/json'); echo json_encode(['success' => false]); exit; }
        if (!Auth::hasRole('admin') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']); exit;
        }
        $completedAt = $status === 'done' ? date('Y-m-d H:i:s') : null;
        Database::getInstance()->prepare(
            "UPDATE tindak_lanjut SET status=?, completed_at=? WHERE id=?"
        )->execute([$status, $completedAt, $id]);

        // Kembalikan summary terbaru agar stat cards bisa diupdate tanpa reload
        $userId = Auth::hasRole('admin') ? 0 : Auth::id();
        $summary = self::getSummary(Auth::hasRole('admin'), $userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'summary' => $summary]); exit;
    }

    // ── Progress Notes ────────────────────────────────────────────────────────

    /**
     * GET /tindak-lanjut/{id}/notes  — ambil semua note (JSON)
     */
    public static function getNotes(int $id): void
    {
        Auth::requireAuth();
        $tl = Database::queryOne("SELECT assigned_to FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { http_response_code(404); echo json_encode([]); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403); echo json_encode([]); exit;
        }

        $isAdmin = Auth::hasRole('admin');
        $myId    = Auth::id();

        $notes = Database::query(
            "SELECT n.id, n.note, n.created_at, n.user_id, u.name AS author_name
             FROM tindak_lanjut_notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.tindak_lanjut_id = ?
             ORDER BY n.created_at ASC",
            [$id]
        );

        // Tambah flag can_delete per note
        foreach ($notes as &$n) {
            $n['can_delete'] = $isAdmin || ((int)$n['user_id'] === $myId);
            unset($n['user_id']); // tidak perlu dikirim ke klien
        }
        unset($n);

        header('Content-Type: application/json');
        echo json_encode($notes); exit;
    }

    /**
     * POST /tindak-lanjut/{id}/notes  — tambah note baru
     */
    public static function addNote(int $id): void
    {
        Auth::requireAuth();
        $tl = Database::queryOne("SELECT assigned_to FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { http_response_code(404); echo json_encode(['success'=>false]); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $note  = trim($input['note'] ?? '');
        if (!$note) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Note tidak boleh kosong']); exit;
        }

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut_notes (tindak_lanjut_id, user_id, note) VALUES (?,?,?)"
        )->execute([$id, Auth::id(), $note]);

        $newNote = Database::queryOne(
            "SELECT n.id, n.note, n.created_at, n.user_id, u.name AS author_name
             FROM tindak_lanjut_notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.tindak_lanjut_id = ? ORDER BY n.id DESC LIMIT 1",
            [$id]
        );

        // Tambah can_delete untuk note baru
        $newNote['can_delete'] = true; // penulis selalu bisa hapus note miliknya
        unset($newNote['user_id']);

        header('Content-Type: application/json');
        echo json_encode(['success'=>true, 'note'=>$newNote]); exit;
    }

    /**
     * POST /tindak-lanjut/notes/{noteId}/delete  — hapus note
     */
    public static function deleteNote(int $noteId): void
    {
        Auth::requireAuth();
        $n = Database::queryOne("SELECT * FROM tindak_lanjut_notes WHERE id=?", [$noteId]);
        if (!$n) { header('Content-Type: application/json'); echo json_encode(['success'=>false]); exit; }

        // Hanya penulis atau admin yang boleh hapus
        if (!Auth::hasRole('admin') && $n['user_id'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
        }

        Database::getInstance()->prepare("DELETE FROM tindak_lanjut_notes WHERE id=?")->execute([$noteId]);
        header('Content-Type: application/json');
        echo json_encode(['success'=>true]); exit;
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        Database::getInstance()->prepare("DELETE FROM tindak_lanjut WHERE id=?")->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }
}
