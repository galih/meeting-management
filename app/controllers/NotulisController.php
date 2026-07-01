<?php
declare(strict_types=1);

class NotulisController
{
    /* ── Baca limit riwayat dari setting, default 10 ── */
    private static function historyLimit(): int
    {
        try {
            $row = Database::queryOne("SELECT value FROM app_settings WHERE `key`='notulen_history_limit'");
            $v   = (int)(($row['value'] ?? '') ?: 10);
            return max(1, min(100, $v));
        } catch (\Throwable $e) {
            return 10;
        }
    }

    /* ── Hapus baris lama yang melebihi limit untuk meeting tertentu ── */
    private static function pruneHistory(int $meetingId): void
    {
        $limit = self::historyLimit();
        try {
            /* Hitung total baris saat ini */
            $cnt = (int)(Database::queryOne(
                "SELECT COUNT(*) AS c FROM notulen_history WHERE meeting_id=?",
                [$meetingId]
            )['c'] ?? 0);

            if ($cnt > $limit) {
                $excess = $cnt - $limit;
                /* Hapus baris terlama (id terkecil) sejumlah $excess */
                Database::getInstance()->prepare(
                    "DELETE FROM notulen_history
                     WHERE meeting_id=?
                     ORDER BY id ASC
                     LIMIT " . $excess
                )->execute([$meetingId]);
            }
        } catch (\Throwable $e) { /* tabel belum ada, abaikan */ }
    }

    /* ------------------------------------------------------------------ */
    /*  EDITOR NOTULEN  (GET /notulen/{id})                                */
    /* ------------------------------------------------------------------ */
    public static function editor(int $meetingId): void
    {
        Auth::requireLogin();
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $notulen = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);

        $historyCount = 0;
        try {
            $historyCount = (int)(Database::queryOne(
                "SELECT COUNT(*) AS cnt FROM notulen_history WHERE meeting_id=?",
                [$meetingId]
            )['cnt'] ?? 0);
        } catch (\Throwable $e) { /* tabel belum ada */ }

        $participants = Database::query(
            "SELECT u.id, u.name, u.avatar, u.role
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id = ?",
            [$meetingId]
        ) ?: [];

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?
             ORDER BY tl.due_date ASC",
            [$meetingId]
        ) ?: [];

        $users = Database::query(
            "SELECT id, name FROM users WHERE is_active=1 ORDER BY name"
        ) ?: [];

        View::layout('notulen/editor', [
            'pageTitle'        => 'Editor Notulen \u2014 ' . $meeting['title'],
            'meeting'          => $meeting,
            'notulen'          => $notulen,
            'historyCount'     => $historyCount,
            'participants'     => $participants,
            'tindakLanjutList' => $tindakLanjutList,
            'users'            => $users,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  RIWAYAT NOTULEN  (GET /notulen/{id}/history)                       */
    /* ------------------------------------------------------------------ */
    public static function history(int $meetingId): void
    {
        Auth::requireLogin();
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $limit     = self::historyLimit();
        $histories = [];
        try {
            $histories = Database::query(
                "SELECT nh.*, u.name AS editor_name, u.avatar AS editor_avatar
                 FROM notulen_history nh
                 LEFT JOIN users u ON u.id = nh.edited_by
                 WHERE nh.meeting_id = ?
                 ORDER BY nh.created_at DESC
                 LIMIT " . $limit,
                [$meetingId]
            ) ?: [];
        } catch (\Throwable $e) { /* tabel belum ada */ }

        $notulen = Database::queryOne(
            "SELECT n.*, u.name AS editor_name, u.avatar AS editor_avatar
             FROM notulen n
             LEFT JOIN users u ON u.id = n.created_by
             WHERE n.meeting_id = ?",
            [$meetingId]
        );

        View::layout('notulen/history', [
            'pageTitle'     => 'Riwayat Notulen \u2014 ' . $meeting['title'],
            'meeting'       => $meeting,
            'notulen'       => $notulen,
            'histories'     => $histories,
            'historyLimit'  => $limit,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SHOW  (alias lama)                                                  */
    /* ------------------------------------------------------------------ */
    public static function show(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $notulen = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
        View::layout('notulen/show', [
            'pageTitle' => 'Notulen \u2014 ' . $meeting['title'],
            'meeting'   => $meeting,
            'notulen'   => $notulen,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SAVE NOTULEN (POST /api/notulen/save)                              */
    /* ------------------------------------------------------------------ */
    public static function save(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');

        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!$body) { echo json_encode(['success'=>false,'message'=>'Payload tidak valid']); exit; }

        $meetingId   = (int)($body['meeting_id'] ?? 0);
        $htmlContent = $body['content'] ?? '';

        if (!$meetingId) { echo json_encode(['success'=>false,'message'=>'meeting_id diperlukan']); exit; }
        $meeting = Database::queryOne("SELECT id FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { echo json_encode(['success'=>false,'message'=>'Meeting tidak ditemukan']); exit; }

        $db       = Database::getInstance();
        $existing = Database::queryOne("SELECT id, version FROM notulen WHERE meeting_id=?", [$meetingId]);

        if ($existing) {
            try {
                $old = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
                if ($old) {
                    $db->prepare(
                        "INSERT INTO notulen_history (meeting_id, content, version, edited_by)
                         VALUES (?, ?, ?, ?)"
                    )->execute([$meetingId, $old['content'], (int)($old['version'] ?? 1), Auth::id()]);
                }
                /* Auto-prune setelah insert agar DB tidak membengkak */
                self::pruneHistory($meetingId);
            } catch (\Throwable $e) { /* notulen_history belum ada */ }

            $newVersion = (int)($existing['version'] ?? 1) + 1;
            $db->prepare(
                "UPDATE notulen
                 SET content=?, version=?, updated_by=?, updated_at=NOW()
                 WHERE meeting_id=?"
            )->execute([$htmlContent, $newVersion, Auth::id(), $meetingId]);
        } else {
            $newVersion = 1;
            $db->prepare(
                "INSERT INTO notulen (meeting_id, content, version, created_by) VALUES (?,?,?,?)"
            )->execute([$meetingId, $htmlContent, $newVersion, Auth::id()]);
        }

        ActivityLog::record('notulen.update', 'Simpan notulen untuk meeting ID '.$meetingId, 'notulen', $meetingId);
        echo json_encode(['success'=>true,'message'=>'Notulen berhasil disimpan.','version'=>$newVersion]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  RENDER HTML (untuk export)                                          */
    /* ------------------------------------------------------------------ */
    public static function renderHtml(string $htmlContent): string
    {
        return $htmlContent;
    }

    /* ------------------------------------------------------------------ */
    /*  GET NOTULEN (API)                                                   */
    /* ------------------------------------------------------------------ */
    public static function get(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { echo json_encode(['success'=>false,'message'=>'meeting_id diperlukan']); exit; }

        $notulen = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
        if (!$notulen) {
            echo json_encode(['success'=>true,'content'=>'','html'=>'']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'content' => $notulen['content'] ?? '',
            'html'    => $notulen['content'] ?? '',
            'version' => (int)($notulen['version'] ?? 1),
        ]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  SYNC (API)                                                          */
    /* ------------------------------------------------------------------ */
    public static function sync(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $meetingId     = (int)($_GET['meeting_id'] ?? 0);
        $clientVersion = (int)($_GET['version']    ?? 0);
        if (!$meetingId) { echo json_encode(['success'=>false]); exit; }

        $notulen = Database::queryOne(
            "SELECT n.*, u.name AS editor_name, n.updated_by AS editor_id
             FROM notulen n
             LEFT JOIN users u ON u.id = n.updated_by
             WHERE n.meeting_id = ?",
            [$meetingId]
        );

        if (!$notulen) {
            echo json_encode(['success'=>true,'version'=>0,'updated_at'=>null]);
            exit;
        }

        $serverVersion = (int)($notulen['version'] ?? 1);

        echo json_encode([
            'success'     => true,
            'updated_at'  => $notulen['updated_at'] ?? null,
            'version'     => $serverVersion,
            'content'     => ($serverVersion > $clientVersion) ? ($notulen['content'] ?? '') : null,
            'editor_name' => $notulen['editor_name'] ?? null,
            'editor_id'   => $notulen['editor_id']   ?? null,
        ]);
        exit;
    }
}
