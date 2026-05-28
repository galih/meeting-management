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
            )->execute([$id, '{}']);
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

        // EditorJS CDN di-load di <head> agar sudah siap sebelum body scripts jalan
        $headScripts = '
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.28.2/dist/editorjs.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.1/dist/header.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/nested-list@1.4.2/dist/nested-list.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@1.6.0/dist/checklist.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@2.3.0/dist/table.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/underline@1.1.0/dist/underline.umd.min.js"></script>
';

        // Semua konstanta JS + kedua file JS di-inject di akhir body
        $allUsersJson = json_encode(array_values(array_map(
            fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']],
            $users
        )));

        $scripts = '
<script>
const MEETING_ID      = ' . $id . ';
const CURRENT_USER_ID = ' . (int)($user['id'] ?? 0) . ';
const IS_EDITOR       = ' . ($canEdit ? 'true' : 'false') . ';
const SAVE_URL        = ' . json_encode($saveUrl) . ';
const SYNC_URL        = ' . json_encode($syncUrl) . ';
const INITIAL_CONTENT = ' . json_encode($notulen['content'] ?? '{}') . ';
const ALL_USERS       = ' . $allUsersJson . ';
</script>
<script src="' . BASE_URL . '/assets/js/notulen-realtime.js"></script>
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
        $content   = $body['content'] ?? null;

        if (!$meetingId || $content === null) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']); exit;
        }

        $contentJson = is_string($content) ? $content : json_encode($content);
        $userId      = Auth::id();

        $existing = Database::queryOne("SELECT id, version FROM notulen WHERE meeting_id=?", [$meetingId]);
        if ($existing) {
            $newVersion = ($existing['version'] ?? 0) + 1;
            Database::getInstance()->prepare(
                "UPDATE notulen SET content=?, version=?, last_edited_by=?, updated_at=NOW()
                 WHERE meeting_id=?"
            )->execute([$contentJson, $newVersion, $userId, $meetingId]);
        } else {
            $newVersion = 1;
            Database::getInstance()->prepare(
                "INSERT INTO notulen (meeting_id, content, version, last_edited_by, updated_at)
                 VALUES (?,?,?,?,NOW())"
            )->execute([$meetingId, $contentJson, $newVersion, $userId]);
        }

        Database::getInstance()->prepare(
            "INSERT INTO notulen_history (meeting_id, content, version, edited_by) VALUES (?,?,?,?)"
        )->execute([$meetingId, $contentJson, $newVersion, $userId]);

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
