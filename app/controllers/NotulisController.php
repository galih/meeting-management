<?php
declare(strict_types=1);

class NotulisController
{
    public static function editor(int $id): void
    {
        Auth::requireAuth();

        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m LEFT JOIN users u ON u.id=m.created_by
             WHERE m.id=?", [$id]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan'; exit; }

        $notulen = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$id]);
        if (!$notulen) {
            Database::getInstance()->prepare(
                "INSERT INTO notulen (meeting_id, content, version) VALUES (?,?,0)"
            )->execute([$id, '']);
            $notulen = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$id]);
        }

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl LEFT JOIN users u ON u.id=tl.assigned_to
             WHERE tl.meeting_id=? ORDER BY tl.created_at DESC", [$id]
        );
        $users   = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        $user    = Auth::user();
        $canEdit = Auth::hasRole('admin', 'sekretaris');

        $saveUrl = rtrim(BASE_URL, '/') . '/api/notulen/save';
        $syncUrl = rtrim(BASE_URL, '/') . '/api/notulen/sync';

        // Quill CSS di <head>
        $headScripts = '
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
';

        $allUsersJson = json_encode(array_values(array_map(
            fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']],
            $users
        )));

        // Konten Quill: jika kosong/null simpan string kosong, bukan JSON EditorJS
        $rawContent = $notulen['content'] ?? '';
        // Jika konten lama masih format EditorJS JSON ({"blocks":[...]}), reset ke kosong
        $initialContent = '';
        if (!empty($rawContent)) {
            $decoded = json_decode($rawContent, true);
            // Jika bukan EditorJS format (tidak punya key 'blocks'), anggap HTML biasa
            if ($decoded === null || !isset($decoded['blocks'])) {
                $initialContent = $rawContent;
            }
            // Jika EditorJS format lama → biarkan kosong (tidak bisa dikonversi)
        }

        $scripts = '
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
const MEETING_ID      = ' . $id . ';
const CURRENT_USER_ID = ' . (int)($user['id'] ?? 0) . ';
const IS_EDITOR       = ' . ($canEdit ? 'true' : 'false') . ';
const SAVE_URL        = ' . json_encode($saveUrl) . ';
const SYNC_URL        = ' . json_encode($syncUrl) . ';
const INITIAL_CONTENT = ' . json_encode($initialContent) . ';
const ALL_USERS       = ' . $allUsersJson . ';
</script>
<script src="' . BASE_URL . '/assets/js/notulen-editor.js"></script>
<script src="' . BASE_URL . '/assets/js/notulen-comments.js"></script>
';

        View::layout('notulen/editor', [
            'pageTitle'        => 'Notulen: ' . $meeting['title'],
            'meeting'          => $meeting,
            'notulen'          => $notulen,
            'tindakLanjutList' => $tindakLanjutList,
            'users'            => $users,
            'user'             => $user,
            'headScripts'      => $headScripts,
            'scripts'          => $scripts,
        ]);
    }

    public static function save(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');

        if (!Auth::hasRole('admin', 'sekretaris')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $raw       = file_get_contents('php://input');
        $body      = json_decode($raw, true);
        $meetingId = (int)($body['meeting_id'] ?? 0);
        $content   = $body['content'] ?? null; // HTML string dari Quill

        if (!$meetingId || $content === null) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']); exit;
        }

        // Quill mengirim HTML — simpan langsung
        $contentHtml = is_string($content) ? $content : '';
        $userId      = Auth::id();

        $existing = Database::queryOne("SELECT id, version FROM notulen WHERE meeting_id=?", [$meetingId]);
        if ($existing) {
            $newVersion = ($existing['version'] ?? 0) + 1;
            Database::getInstance()->prepare(
                "UPDATE notulen SET content=?, version=?, last_edited_by=?, updated_at=NOW()
                 WHERE meeting_id=?"
            )->execute([$contentHtml, $newVersion, $userId, $meetingId]);
        } else {
            $newVersion = 1;
            Database::getInstance()->prepare(
                "INSERT INTO notulen (meeting_id, content, version, last_edited_by, updated_at)
                 VALUES (?,?,?,?,NOW())"
            )->execute([$meetingId, $contentHtml, $newVersion, $userId]);
        }

        Database::getInstance()->prepare(
            "INSERT INTO notulen_history (meeting_id, content, version, edited_by) VALUES (?,?,?,?)"
        )->execute([$meetingId, $contentHtml, $newVersion, $userId]);

        echo json_encode(['success' => true, 'version' => $newVersion]);
        exit;
    }

    public static function sync(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');

        $meetingId     = (int)($_GET['meeting_id'] ?? 0);
        $clientVersion = (int)($_GET['version']    ?? 0);

        if (!$meetingId) {
            echo json_encode(['status' => 'error', 'message' => 'meeting_id wajib']); exit;
        }

        $notulen = Database::queryOne(
            "SELECT n.*, u.name AS editor_name, u.id AS last_edited_by_id
             FROM notulen n LEFT JOIN users u ON u.id=n.last_edited_by
             WHERE n.meeting_id=?",
            [$meetingId]
        );

        if (!$notulen) { echo json_encode(['status' => 'no_notulen']); exit; }

        if ((int)$notulen['version'] > $clientVersion) {
            echo json_encode(['status' => 'updated', 'data' => $notulen]); exit;
        }

        echo json_encode(['status' => 'current']); exit;
    }

    public static function history(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$id]);
        if (!$meeting) { http_response_code(404); exit; }

        $histories = Database::query(
            "SELECT nh.*, u.name AS editor_name
             FROM notulen_history nh LEFT JOIN users u ON u.id=nh.edited_by
             WHERE nh.meeting_id=? ORDER BY nh.created_at DESC",
            [$id]
        );

        View::layout('notulen/history', [
            'pageTitle' => 'Riwayat Notulen',
            'meeting'   => $meeting,
            'histories' => $histories,
        ]);
    }
}
