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

        // Dibutuhkan oleh view: daftar user untuk modal TL
        $users = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        // Daftar tindak lanjut untuk sidebar
        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?
             ORDER BY tl.created_at DESC",
            [$meetingId]
        );

        View::layout('notulen/editor', [
            'title'            => 'Notulen — ' . $meeting['title'],
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
        // Terima JSON (dari fetch) atau POST form biasa
        $input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $meetingId = (int)($input['meeting_id'] ?? 0);
        $content   = $input['content'] ?? '';

        if (!$meetingId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'meeting_id wajib']); exit;
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
        } else {
            $db->prepare(
                "INSERT INTO notulen (meeting_id, content, version, created_by, updated_by)
                 VALUES (?,?,1,?,?)"
            )->execute([$meetingId, $content, Auth::id(), Auth::id()]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Notulen disimpan.']); exit;
    }

    public static function sync(): void
    {
        Auth::requireAuth();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        $notulen   = Database::queryOne(
            "SELECT content, version, updated_at FROM notulen WHERE meeting_id=?", [$meetingId]
        );
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'content' => $notulen['content'] ?? '',
            'version' => $notulen['version'] ?? 0,
        ]); exit;
    }
}
