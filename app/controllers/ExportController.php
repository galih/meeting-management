<?php
class ExportController
{
    /**
     * GET /notulen/{id}/export-pdf
     */
    public static function exportPdf(int $meetingId): void
    {
        Auth::requireAuth();

        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m
             LEFT JOIN users u ON u.id = m.created_by
             WHERE m.id = ?",
            [$meetingId]
        );
        if (!$meeting) { http_response_code(404); die('Meeting tidak ditemukan.'); }

        // queryOne() bisa return false jika belum ada notulen — fallback ke array kosong
        $notulenRaw = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id = ?", [$meetingId]
        );
        $notulen = is_array($notulenRaw) ? $notulenRaw : ['content' => null, 'meeting_id' => $meetingId];

        $participants = Database::query(
            "SELECT u.id, u.name, mp.status
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id = ?",
            [$meetingId]
        );

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?
             ORDER BY tl.priority DESC, tl.due_date ASC",
            [$meetingId]
        );

        $user = Auth::user();

        try {
            Database::getInstance()->prepare(
                "INSERT INTO notulen_exports (meeting_id, exported_by, format)
                 VALUES (?, ?, 'pdf')"
            )->execute([$meetingId, $user['id']]);
        } catch (\PDOException) {
            // tabel notulen_exports belum ada, lanjut
        }

        $html = PdfExporter::export($meeting, $notulen, $participants, $tindakLanjutList, $user);

        if (str_starts_with($html, '/exports/')) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="notulen-' . $meetingId . '.pdf"');
            readfile(ROOT_PATH . '/public' . $html);
        } else {
            echo $html;
        }
        exit;
    }
}
