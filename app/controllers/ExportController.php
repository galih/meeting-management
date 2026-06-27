<?php
declare(strict_types=1);

class ExportController
{
    public static function downloadPdf(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $notulen      = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
        $participants = Database::query(
            "SELECT u.name, u.email, mp.status
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id=? ORDER BY u.name",
            [$meetingId]
        );
        $tindakLanjut = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id=? ORDER BY tl.created_at",
            [$meetingId]
        );

        $exporter = new PdfExporter();
        $exporter->download($meeting, $notulen, $participants, $tindakLanjut);
    }

    public static function downloadDocx(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $notulen      = Database::queryOne("SELECT * FROM notulen WHERE meeting_id=?", [$meetingId]);
        $participants = Database::query(
            "SELECT u.name, u.email, mp.status
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id=? ORDER BY u.name",
            [$meetingId]
        );
        $tindakLanjut = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id=? ORDER BY tl.created_at",
            [$meetingId]
        );

        $exporter = new DocxExporter();
        $exporter->download($meeting, $notulen, $participants, $tindakLanjut);
    }

    /**
     * Serve static export file yang sudah tersimpan di /exports/
     * Diakses via route /exports/{filename}
     */
    public static function serveFile(): void
    {
        Auth::requireLogin();
        $html = $_GET['file'] ?? '';

        // PHP 7.4 compat: ganti str_starts_with dengan strncmp
        if (strncmp($html, '/exports/', 9) !== 0) {
            http_response_code(400); echo 'Path tidak valid.'; exit;
        }

        $path = ROOT_PATH . $html;
        if (!file_exists($path)) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }

        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
