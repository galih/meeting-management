<?php
class CommentController
{
    private static function ensureTable(): void
    {
        $db = Database::getInstance();
        $db->exec("
            CREATE TABLE IF NOT EXISTS notulen_comments (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                meeting_id  INT NOT NULL,
                parent_id   INT DEFAULT NULL,
                user_id     INT NOT NULL,
                content     TEXT NOT NULL,
                is_resolved TINYINT(1) NOT NULL DEFAULT 0,
                created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        try {
            $db->exec("ALTER TABLE notulen_comments ADD COLUMN is_resolved TINYINT(1) NOT NULL DEFAULT 0");
        } catch (\Throwable $e) { /* sudah ada */ }

        $db->exec("
            CREATE TABLE IF NOT EXISTS comment_mentions (
                comment_id INT NOT NULL,
                user_id    INT NOT NULL,
                PRIMARY KEY (comment_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /** GET /api/notulen/{meetingId}/comments */
    public static function index(int $meetingId): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            Auth::requireAuth();
            self::ensureTable();
            $comments = Database::query(
                "SELECT nc.*, u.name AS user_name, u.role
                 FROM notulen_comments nc
                 JOIN users u ON u.id = nc.user_id
                 WHERE nc.meeting_id = ? AND nc.parent_id IS NULL
                 ORDER BY nc.created_at ASC",
                [$meetingId]
            );
            foreach ($comments as &$c) {
                $c['is_resolved'] = (bool)$c['is_resolved'];
                $c['replies'] = Database::query(
                    "SELECT nc.*, u.name AS user_name
                     FROM notulen_comments nc
                     JOIN users u ON u.id = nc.user_id
                     WHERE nc.parent_id = ?
                     ORDER BY nc.created_at ASC",
                    [$c['id']]
                );
                foreach ($c['replies'] as &$r) {
                    $r['is_resolved'] = (bool)($r['is_resolved'] ?? false);
                }
                unset($r);
            }
            unset($c);
            echo json_encode(['success' => true, 'comments' => $comments]);
        } catch (\Throwable $e) {
            error_log('CommentController::index: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /** POST /api/notulen/{meetingId}/comments */
    public static function store(int $meetingId): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            Auth::requireAuth();
            self::ensureTable();

            $body     = json_decode(file_get_contents('php://input'), true) ?? [];
            $content  = trim($body['content']  ?? '');
            $parentId = !empty($body['parent_id']) ? (int)$body['parent_id'] : null;
            $mentions = (array)($body['mentions'] ?? []);

            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Komentar tidak boleh kosong']);
                exit;
            }

            $db = Database::getInstance();
            // INSERT tanpa block_id — kolom itu tidak ada di tabel
            $db->prepare(
                "INSERT INTO notulen_comments (meeting_id, parent_id, user_id, content)
                 VALUES (?, ?, ?, ?)"
            )->execute([$meetingId, $parentId, Auth::id(), $content]);
            $commentId = (int)$db->lastInsertId();

            $baseUrl  = rtrim(BASE_URL, '/') . '/notulen/' . $meetingId;
            $userName = Auth::user()['name'] ?? 'Seseorang';

            foreach ($mentions as $uid) {
                $uid = (int)$uid;
                $db->prepare(
                    "INSERT IGNORE INTO comment_mentions (comment_id, user_id) VALUES (?,?)"
                )->execute([$commentId, $uid]);
                try {
                    Notification::send($uid, 'comment_mention', "{$userName} menyebut Anda di notulen.", $baseUrl);
                } catch (\Throwable $e) {}
            }

            if (!$parentId) {
                $participants = Database::query(
                    "SELECT user_id FROM meeting_participants WHERE meeting_id=? AND user_id != ?",
                    [$meetingId, Auth::id()]
                );
                foreach ($participants as $p) {
                    try {
                        Notification::send((int)$p['user_id'], 'notulen_comment', "{$userName} menambahkan komentar di notulen.", $baseUrl);
                    } catch (\Throwable $e) {}
                }
            }

            $comment = Database::queryOne(
                "SELECT nc.*, u.name AS user_name
                 FROM notulen_comments nc
                 JOIN users u ON u.id = nc.user_id
                 WHERE nc.id = ?",
                [$commentId]
            );
            echo json_encode(['success' => true, 'message' => 'Komentar ditambahkan', 'comment' => $comment]);
        } catch (\Throwable $e) {
            error_log('CommentController::store: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /** POST /api/comments/{id}/resolve */
    public static function resolve(int $id): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            Auth::requireRole('admin', 'sekretaris');
            $c = Database::queryOne("SELECT * FROM notulen_comments WHERE id=?", [$id]);
            if (!$c) { echo json_encode(['success' => false, 'message' => 'Tidak ditemukan']); exit; }
            $new = $c['is_resolved'] ? 0 : 1;
            Database::getInstance()->prepare(
                "UPDATE notulen_comments SET is_resolved=? WHERE id=?"
            )->execute([$new, $id]);
            echo json_encode(['success' => true, 'is_resolved' => (bool)$new]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /** POST /api/comments/{id}/delete */
    public static function delete(int $id): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            Auth::requireAuth();
            $c = Database::queryOne("SELECT * FROM notulen_comments WHERE id=?", [$id]);
            if (!$c) { echo json_encode(['success' => false, 'message' => 'Tidak ditemukan']); exit; }
            if ((int)$c['user_id'] !== Auth::id() && !Auth::hasRole('admin')) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak']); exit;
            }
            Database::getInstance()->prepare("DELETE FROM notulen_comments WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Komentar dihapus']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
