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

        View::layout('notulen/editor', [
            'title'        => 'Notulen — ' . $meeting['title'],
            'meeting'      => $meeting,
            'notulen'      => $notulen,
            'participants' => $participants,
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
        $meetingId = (int)($_POST['meeting_id'] ?? 0);
        $content   = $_POST['content'] ?? '';
        $version   = (int)($_POST['version'] ?? 1);

        if (!$meetingId) {
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
