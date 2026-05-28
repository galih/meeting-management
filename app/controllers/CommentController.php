<?php
class CommentController
{
    /**
     * GET /api/notulen/{meetingId}/comments
     */
    public static function index(int $meetingId): void
    {
        Auth::requireAuth();
        $comments = Database::query(
            "SELECT nc.*, u.name AS user_name, u.role,
                    (SELECT COUNT(*) FROM notulen_comments r WHERE r.parent_id = nc.id) AS reply_count
             FROM notulen_comments nc
             JOIN users u ON u.id = nc.user_id
             WHERE nc.meeting_id = ? AND nc.parent_id IS NULL
             ORDER BY nc.created_at ASC",
            [$meetingId]
        );
        foreach ($comments as &$c) {
            $c['replies'] = Database::query(
                "SELECT nc.*, u.name AS user_name
                 FROM notulen_comments nc
                 JOIN users u ON u.id = nc.user_id
                 WHERE nc.parent_id = ?
                 ORDER BY nc.created_at ASC",
                [$c['id']]
            );
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'comments' => $comments]); exit;
    }

    /**
     * POST /api/notulen/{meetingId}/comments
     */
    public static function store(int $meetingId): void
    {
        Auth::requireAuth();
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $content  = trim($body['content']  ?? '');
        $blockId  = $body['block_id']      ?? null;
        $parentId = !empty($body['parent_id']) ? (int)$body['parent_id'] : null;
        $mentions = (array)($body['mentions'] ?? []);

        if (empty($content)) {
            self::json(false, 'Komentar tidak boleh kosong'); return;
        }

        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO notulen_comments (meeting_id, block_id, parent_id, user_id, content)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([$meetingId, $blockId, $parentId, Auth::id(), $content]);
        $commentId = (int)$db->lastInsertId();

        $baseUrl  = rtrim(BASE_URL, '/') . '/notulen/' . $meetingId;
        $userName = Auth::user()['name'] ?? 'Seseorang';

        // Simpan mentions & kirim notifikasi
        foreach ($mentions as $uid) {
            $uid = (int)$uid;
            $db->prepare(
                "INSERT IGNORE INTO comment_mentions (comment_id, user_id) VALUES (?,?)"
            )->execute([$commentId, $uid]);
            Notification::send(
                $uid,
                'comment_mention',
                "{$userName} menyebut Anda di notulen.",
                $baseUrl
            );
        }

        // Notifikasi ke peserta meeting jika komentar baru (bukan reply)
        if (!$parentId) {
            $participants = Database::query(
                "SELECT user_id FROM meeting_participants WHERE meeting_id=? AND user_id != ?",
                [$meetingId, Auth::id()]
            );
            foreach ($participants as $p) {
                Notification::send(
                    (int)$p['user_id'],
                    'notulen_comment',
                    "{$userName} menambahkan komentar di notulen.",
                    $baseUrl
                );
            }
        }

        $comment = Database::queryOne(
            "SELECT nc.*, u.name AS user_name FROM notulen_comments nc
             JOIN users u ON u.id = nc.user_id WHERE nc.id = ?",
            [$commentId]
        );
        self::json(true, 'Komentar ditambahkan', ['comment' => $comment]);
    }

    /**
     * POST /api/comments/{id}/resolve
     */
    public static function resolve(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $c = Database::queryOne("SELECT * FROM notulen_comments WHERE id=?", [$id]);
        if (!$c) { self::json(false, 'Tidak ditemukan'); return; }
        $new = $c['is_resolved'] ? 0 : 1;
        Database::getInstance()->prepare(
            "UPDATE notulen_comments SET is_resolved=? WHERE id=?"
        )->execute([$new, $id]);
        self::json(true, $new ? 'Thread diselesaikan' : 'Thread dibuka kembali', ['is_resolved' => $new]);
    }

    /**
     * POST /api/comments/{id}/delete
     */
    public static function delete(int $id): void
    {
        Auth::requireAuth();
        $c = Database::queryOne("SELECT * FROM notulen_comments WHERE id=?", [$id]);
        if (!$c) { self::json(false, 'Tidak ditemukan'); return; }
        if ((int)$c['user_id'] !== Auth::id() && !Auth::hasRole('admin')) {
            self::json(false, 'Akses ditolak'); return;
        }
        Database::getInstance()->prepare("DELETE FROM notulen_comments WHERE id=?")->execute([$id]);
        self::json(true, 'Komentar dihapus');
    }

    private static function json(bool $success, string $message, array $extra = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(compact('success', 'message'), $extra)); exit;
    }
}
