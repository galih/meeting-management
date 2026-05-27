<?php
class NotulisController
{
    public static function editor(int $meetingId): void
    {
        Auth::requireAuth();
        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m LEFT JOIN users u ON u.id=m.created_by
             WHERE m.id=?",
            [$meetingId]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        // Auto-create record notulen jika belum ada
        $notulen = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]
        );
        if (!$notulen) {
            $emptyContent = json_encode(['time' => time() * 1000, 'blocks' => [], 'version' => '2.28.0']);
            Database::getInstance()->prepare(
                "INSERT INTO notulen (meeting_id, content, version, created_by, updated_by) VALUES (?,?,1,?,?)"
            )->execute([$meetingId, $emptyContent, Auth::id(), Auth::id()]);
            $notulen = Database::queryOne(
                "SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]
            );
        }

        if (empty($notulen['content'])) {
            $notulen['content'] = json_encode(['time' => time() * 1000, 'blocks' => [], 'version' => '2.28.0']);
        }

        $participants = Database::query(
            "SELECT u.id, u.name FROM meeting_participants mp
             JOIN users u ON u.id=mp.user_id WHERE mp.meeting_id=?",
            [$meetingId]
        );

        $users = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?
             ORDER BY tl.created_at DESC",
            [$meetingId]
        );

        $baseUrl   = rtrim(BASE_URL, '/');
        $isEditor  = Auth::hasRole('admin', 'sekretaris') ? 'true' : 'false';
        $meetingIdInt = (int)$meeting['id'];
        $userId    = (int)Auth::id();
        $usersJson = json_encode(array_map(fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']], $users));
        $saveUrl   = json_encode($baseUrl . '/api/notulen/save');
        $syncUrl   = json_encode($baseUrl . '/api/notulen/sync');
        $tlUrl     = json_encode($baseUrl . '/tindak-lanjut');

        // Decode content untuk EditorJS
        $contentDecoded = json_decode($notulen['content'] ?? '{}');
        if (!$contentDecoded || !isset($contentDecoded->blocks)) {
            $contentDecoded = (object)['time' => time() * 1000, 'blocks' => [], 'version' => '2.28.0'];
        }
        $contentJson = json_encode($contentDecoded);

        // Scripts di-inject SETELAH CDN EditorJS via $scripts
        $scripts = <<<HTML
<script>
const MEETING_ID      = {$meetingIdInt};
const CURRENT_USER_ID = {$userId};
const IS_EDITOR       = {$isEditor};
const INITIAL_CONTENT = {$contentJson};
const ALL_USERS       = {$usersJson};
const SAVE_URL        = {$saveUrl};
const SYNC_URL        = {$syncUrl};
</script>
<script src="{$baseUrl}/assets/js/notulen-realtime.js"></script>
<script src="{$baseUrl}/assets/js/notulen-comments.js"></script>
<script>
document.getElementById('btn-tl2-save')?.addEventListener('click', async () => {
  const desk = document.getElementById('tl2-desk').value.trim();
  if (!desk) { alert('Deskripsi wajib diisi!'); return; }
  const res = await fetch({$tlUrl}, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      meeting_id:  MEETING_ID,
      description: desk,
      assigned_to: document.getElementById('tl2-assign').value,
      due_date:    document.getElementById('tl2-deadline').value,
      priority:    document.getElementById('tl2-priority').value,
    })
  });
  const d = await res.json();
  if (d.success) {
    bootstrap.Modal.getInstance(document.getElementById('modalTL')).hide();
    location.reload();
  } else {
    alert(d.message || 'Gagal menyimpan');
  }
});
</script>
HTML;

        View::layout('notulen/editor', [
            'pageTitle'        => 'Notulen — ' . $meeting['title'],
            'meeting'          => $meeting,
            'notulen'          => $notulen,
            'participants'     => $participants,
            'users'            => $users,
            'tindakLanjutList' => $tindakLanjutList,
            'user'             => Auth::user(),
            'scripts'          => $scripts,
        ]);
    }

    public static function history(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m LEFT JOIN users u ON u.id=m.created_by
             WHERE m.id=?",
            [$meetingId]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $history = Database::query(
            "SELECT nh.*, u.name AS editor_name
             FROM notulen_history nh
             LEFT JOIN users u ON u.id=nh.edited_by
             WHERE nh.meeting_id=?
             ORDER BY nh.created_at DESC",
            [$meetingId]
        );

        View::layout('notulen/history', [
            'pageTitle' => 'Riwayat Notulen — ' . $meeting['title'],
            'meeting'   => $meeting,
            'history'   => $history,
        ]);
    }

    public static function save(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $meetingId = (int)($input['meeting_id'] ?? 0);
        $content   = $input['content'] ?? '';

        if (!$meetingId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'meeting_id wajib']); exit;
        }

        if (is_array($content)) {
            $content = json_encode($content);
        }

        $existing = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]
        );

        $db = Database::getInstance();
        if ($existing) {
            $db->prepare(
                "INSERT INTO notulen_history (meeting_id, content, version, edited_by) VALUES (?,?,?,?)"
            )->execute([$meetingId, $existing['content'], $existing['version'], Auth::id()]);
            $db->prepare(
                "UPDATE notulen SET content=?, version=version+1, updated_by=?, updated_at=NOW() WHERE meeting_id=?"
            )->execute([$content, Auth::id(), $meetingId]);
            $version = ($existing['version'] ?? 0) + 1;
        } else {
            $db->prepare(
                "INSERT INTO notulen (meeting_id, content, version, created_by, updated_by) VALUES (?,?,1,?,?)"
            )->execute([$meetingId, $content, Auth::id(), Auth::id()]);
            $version = 1;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Notulen disimpan.', 'version' => $version]); exit;
    }

    public static function sync(): void
    {
        Auth::requireAuth();
        $meetingId     = (int)($_GET['meeting_id'] ?? 0);
        $clientVersion = (int)($_GET['version']    ?? 0);

        $notulen = Database::queryOne(
            "SELECT n.content, n.version, n.updated_at, u.name AS editor_name, u.id AS last_edited_by_id
             FROM notulen n
             LEFT JOIN users u ON u.id = n.updated_by
             WHERE n.meeting_id=?",
            [$meetingId]
        );

        if (!$notulen || $notulen['version'] <= $clientVersion) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok', 'version' => $notulen['version'] ?? 0]); exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'updated',
            'data'   => [
                'content'           => $notulen['content'],
                'version'           => $notulen['version'],
                'editor_name'       => $notulen['editor_name'] ?? '',
                'last_edited_by_id' => $notulen['last_edited_by_id'],
            ],
        ]); exit;
    }
}
