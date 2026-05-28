<?php
declare(strict_types=1);

class NotulisController
{
    public static function normalizeContent(?string $raw): string
    {
        if (empty($raw)) return '';
        $decoded = json_decode($raw, true);
        if ($decoded === null || !isset($decoded['blocks'])) return $raw;
        $html = '';
        foreach ($decoded['blocks'] as $block) {
            $text = $block['data']['text'] ?? '';
            $html .= match($block['type']) {
                'header'    => '<h' . ($block['data']['level'] ?? 2) . '>' . $text . '</h' . ($block['data']['level'] ?? 2) . '>',
                'paragraph' => '<p>' . $text . '</p>',
                'list'      => self::blocksListToHtml($block['data']),
                'checklist' => self::blocksChecklistToHtml($block['data']),
                'quote'     => '<blockquote>' . $text . '</blockquote>',
                default     => '<p>' . $text . '</p>',
            };
        }
        return $html;
    }

    private static function blocksListToHtml(array $data): string
    {
        $tag   = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = array_map(fn($i) => '<li>' . (is_array($i) ? ($i['content'] ?? '') : $i) . '</li>', $data['items'] ?? []);
        return "<{$tag}>" . implode('', $items) . "</{$tag}>";
    }

    private static function blocksChecklistToHtml(array $data): string
    {
        $html = '<ul style="list-style:none;padding-left:0;">';
        foreach ($data['items'] ?? [] as $item) {
            $check = ($item['checked'] ?? false) ? '&#x2705;' : '&#x2B1C;';
            $html .= '<li>' . $check . ' ' . ($item['text'] ?? '') . '</li>';
        }
        return $html . '</ul>';
    }

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

        $baseUrl = rtrim(BASE_URL, '/');
        $saveUrl = $baseUrl . '/api/notulen/save';
        $syncUrl = $baseUrl . '/api/notulen/sync';

        $initialContent = self::normalizeContent($notulen['content'] ?? '');

        $headScripts = '
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
';

        $allUsersJson = json_encode(array_values(array_map(
            fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']],
            $users
        )));

        // URUTAN PENTING:
        // 1. Quill library
        // 2. Global JS variables (termasuk MEETING_ID)
        // 3. notulen-editor.js
        // 4. notulen-comments.js
        // 5. meeting-attachments.js  ← harus SETELAH MEETING_ID terdefinisi
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
<script src="' . $baseUrl . '/assets/js/notulen-editor.js"></script>
<script src="' . $baseUrl . '/assets/js/notulen-comments.js"></script>
<script src="' . $baseUrl . '/assets/js/meeting-attachments.js"></script>
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
        ob_start();
        try {
            if (!Auth::hasRole('admin', 'sekretaris')) {
                ob_end_clean();
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
                exit;
            }
            $raw       = file_get_contents('php://input');
            $body      = json_decode($raw, true);
            $meetingId = (int)($body['meeting_id'] ?? 0);
            $content   = $body['content'] ?? null;
            if (!$meetingId || $content === null) {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                exit;
            }
            $contentHtml = is_string($content) ? $content : '';
            $userId      = Auth::id();
            $existing    = Database::queryOne("SELECT id, version FROM notulen WHERE meeting_id=?", [$meetingId]);
            if ($existing) {
                $newVersion = ($existing['version'] ?? 0) + 1;
                Database::getInstance()->prepare(
                    "UPDATE notulen SET content=?, version=?, updated_by=?, updated_at=NOW() WHERE meeting_id=?"
                )->execute([$contentHtml, $newVersion, $userId, $meetingId]);
            } else {
                $newVersion = 1;
                Database::getInstance()->prepare(
                    "INSERT INTO notulen (meeting_id, content, version, updated_by, updated_at) VALUES (?,?,?,?,NOW())"
                )->execute([$meetingId, $contentHtml, $newVersion, $userId]);
            }
            try {
                Database::getInstance()->prepare(
                    "INSERT INTO notulen_history (meeting_id, content, version, edited_by) VALUES (?,?,?,?)"
                )->execute([$meetingId, $contentHtml, $newVersion, $userId]);
            } catch (\Throwable $e) {
                error_log('notulen_history insert error: ' . $e->getMessage());
            }
            ob_end_clean();
            echo json_encode(['success' => true, 'version' => $newVersion]);
            exit;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log('NotulisController::save error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
            exit;
        }
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
             FROM notulen n LEFT JOIN users u ON u.id=n.updated_by
             WHERE n.meeting_id=?",
            [$meetingId]
        );
        if (!$notulen) { echo json_encode(['status' => 'no_notulen']); exit; }
        if ((int)$notulen['version'] > $clientVersion) {
            $notulen['content'] = self::normalizeContent($notulen['content'] ?? '');
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
