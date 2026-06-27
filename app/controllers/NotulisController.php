<?php
declare(strict_types=1);

class NotulisController
{
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

        // Ambil versi historis untuk counter badge
        $historyCount = Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM notulen_history WHERE meeting_id=?",
            [$meetingId]
        )['cnt'] ?? 0;

        // Peserta rapat
        $participants = Database::query(
            "SELECT u.name, u.avatar, u.jabatan
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id = ?",
            [$meetingId]
        );

        // Tindak lanjut terkait
        $tindakLanjut = Database::query(
            "SELECT tl.*, u.name AS pic_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.pic_id
             WHERE tl.meeting_id = ?
             ORDER BY tl.due_date ASC",
            [$meetingId]
        );

        View::layout('notulen/editor', [
            'pageTitle'    => 'Editor Notulen — ' . $meeting['title'],
            'meeting'      => $meeting,
            'notulen'      => $notulen,
            'historyCount' => (int)$historyCount,
            'participants' => $participants ?: [],
            'tindakLanjut' => $tindakLanjut ?: [],
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

        // Ambil semua versi dari tabel notulen_history (jika ada) + versi aktif
        $historyRows = [];
        try {
            $historyRows = Database::query(
                "SELECT nh.*, u.name AS editor_name, u.avatar AS editor_avatar
                 FROM notulen_history nh
                 LEFT JOIN users u ON u.id = nh.created_by
                 WHERE nh.meeting_id = ?
                 ORDER BY nh.created_at DESC",
                [$meetingId]
            ) ?: [];
        } catch (\Throwable $e) {
            // Tabel notulen_history belum ada — tampil halaman kosong
            $historyRows = [];
        }

        // Versi aktif (dari tabel notulen)
        $notulen = Database::queryOne(
            "SELECT n.*, u.name AS editor_name, u.avatar AS editor_avatar
             FROM notulen n
             LEFT JOIN users u ON u.id = n.created_by
             WHERE n.meeting_id = ?",
            [$meetingId]
        );

        View::layout('notulen/history', [
            'pageTitle'   => 'Riwayat Notulen — ' . $meeting['title'],
            'meeting'     => $meeting,
            'notulen'     => $notulen,
            'historyRows' => $historyRows,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SHOW / EDITOR NOTULEN  (alias lama — tetap dipertahankan)          */
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
            'pageTitle' => 'Notulen — ' . $meeting['title'],
            'meeting'   => $meeting,
            'notulen'   => $notulen,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SAVE NOTULEN (POST JSON)                                            */
    /* ------------------------------------------------------------------ */
    public static function save(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');

        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!$body) { echo json_encode(['success'=>false,'message'=>'Payload tidak valid']); exit; }

        $meetingId = (int)($body['meeting_id'] ?? 0);
        $blocks    = $body['blocks'] ?? [];

        if (!$meetingId) { echo json_encode(['success'=>false,'message'=>'meeting_id diperlukan']); exit; }
        $meeting = Database::queryOne("SELECT id FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { echo json_encode(['success'=>false,'message'=>'Meeting tidak ditemukan']); exit; }

        // Build HTML dari blok editor
        $html = '';
        foreach ($blocks as $block) {
            $type    = $block['type']    ?? 'paragraph';
            $content = $block['content'] ?? '';
            if ($type === 'heading') {
                $html .= '<h3>' . htmlspecialchars($content) . '</h3>';
            } elseif ($type === 'paragraph') {
                $html .= '<p>'  . htmlspecialchars($content) . '</p>';
            } elseif ($type === 'bullet') {
                $html .= '<li>' . htmlspecialchars($content) . '</li>';
            } else {
                $html .= '<p>'  . htmlspecialchars($content) . '</p>';
            }
        }

        $db      = Database::getInstance();
        $existing = Database::queryOne("SELECT id FROM notulen WHERE meeting_id=?", [$meetingId]);
        if ($existing) {
            // Simpan snapshot ke history sebelum overwrite
            try {
                $old = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
                if ($old) {
                    $db->prepare(
                        "INSERT INTO notulen_history (meeting_id, content, blocks, created_by)
                         VALUES (?, ?, ?, ?)"
                    )->execute([$meetingId, $old['content'], $old['blocks'], Auth::id()]);
                }
            } catch (\Throwable $e) {
                // notulen_history belum ada — skip saja
            }
            $db->prepare("UPDATE notulen SET content=?, blocks=?, updated_at=NOW() WHERE meeting_id=?")
               ->execute([json_encode($blocks), $html, $meetingId]);
        } else {
            $db->prepare("INSERT INTO notulen (meeting_id, content, blocks, created_by) VALUES (?,?,?,?)")
               ->execute([$meetingId, json_encode($blocks), $html, Auth::id()]);
        }

        ActivityLog::record('notulen.update', 'Simpan notulen untuk meeting ID '.$meetingId, 'notulen', $meetingId);
        echo json_encode(['success'=>true,'message'=>'Notulen berhasil disimpan.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  RENDER HTML NOTULEN (untuk preview / PDF)                          */
    /* ------------------------------------------------------------------ */
    public static function renderHtml(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $block) {
            $type    = $block['type']    ?? 'paragraph';
            $content = $block['content'] ?? '';
            if ($type === 'heading') {
                $html .= '<h3>' . htmlspecialchars($content) . '</h3>';
            } elseif ($type === 'paragraph') {
                $html .= '<p>'  . htmlspecialchars($content) . '</p>';
            } elseif ($type === 'bullet') {
                $html .= '<li>' . htmlspecialchars($content) . '</li>';
            } else {
                $html .= '<p>'  . htmlspecialchars($content) . '</p>';
            }
        }
        return $html;
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
            echo json_encode(['success'=>true,'blocks'=>[],'html'=>'']);
            exit;
        }

        $blocks = json_decode($notulen['content'] ?? '[]', true) ?: [];
        echo json_encode(['success'=>true,'blocks'=>$blocks,'html'=>$notulen['blocks'] ?? '']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  SYNC NOTULEN (GET /api/notulen/sync)                               */
    /* ------------------------------------------------------------------ */
    public static function sync(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { echo json_encode(['success'=>false]); exit; }

        $notulen = Database::queryOne("SELECT updated_at FROM notulen WHERE meeting_id=?", [$meetingId]);
        echo json_encode(['success'=>true,'updated_at'=>$notulen['updated_at'] ?? null]);
        exit;
    }
}
