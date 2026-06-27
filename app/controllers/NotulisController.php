<?php
declare(strict_types=1);

class NotulisController
{
    /* ------------------------------------------------------------------ */
    /*  SHOW / EDITOR NOTULEN                                               */
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

        // Build HTML dari blok editor — PHP 7.4 compat: ganti match() dengan if-elseif
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
            // PHP 7.4 compat: if-elseif chain
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
}
