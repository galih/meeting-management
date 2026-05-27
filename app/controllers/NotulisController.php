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

        $notulen = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]
        );

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

        View::layout('notulen/editor', [
            'pageTitle'        => 'Notulen — ' . $meeting['title'],
            'meeting'          => $meeting,
            'notulen'          => $notulen,
            'participants'     => $participants,
            'users'            => $users,
            'tindakLanjutList' => $tindakLanjutList,
            'user'             => Auth::user(),
        ]);
    }

    public static function history(int $meetingId): void
    {
        Auth::requireAuth();
        $history = Database::query(
            "SELECT nh.*, u.name AS editor_name
             FROM notulen_history nh
             LEFT JOIN users u ON u.id=nh.edited_by
             WHERE nh.meeting_id=?
             ORDER BY nh.created_at DESC",
            [$meetingId]
        );
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'history' => $history]); exit;
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

        // Normalise: jika content berupa array (EditorJS output), encode ke string JSON
        if (is_array($content)) {
            $content = json_encode($content);
        }

        $existing = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]
        );

        $db = Database::getInstance();
        if ($existing) {
            $db->prepare(
                "INSERT INTO notulen_history (meeting_id, content, version, edited_by)
                 VALUES (?,?,?,?)"
            )->execute([$meetingId, $existing['content'], $existing['version'], Auth::id()]);
            $db->prepare(
                "UPDATE notulen SET content=?, version=version+1, updated_by=?, updated_at=NOW() WHERE meeting_id=?"
            )->execute([$content, Auth::id(), $meetingId]);
            $version = ($existing['version'] ?? 0) + 1;
        } else {
            $db->prepare(
                "INSERT INTO notulen (meeting_id, content, version, created_by, updated_by)
                 VALUES (?,?,1,?,?)"
            )->execute([$meetingId, $content, Auth::id(), Auth::id()]);
            $version = 1;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Notulen disimpan.', 'version' => $version]); exit;
    }

    public static function sync(): void
    {
        Auth::requireAuth();
        $meetingId      = (int)($_GET['meeting_id'] ?? 0);
        $clientVersion  = (int)($_GET['version']    ?? 0);

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
