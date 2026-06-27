<?php
declare(strict_types=1);

class ExportController
{
    // -------------------------------------------------------------------------
    // Route handlers — dipanggil dari Router dengan {id} sebagai parameter
    // GET /notulen/{id}/export-docx
    // -------------------------------------------------------------------------
    public function exportDocx(int $id): void
    {
        Auth::requireLogin();
        $this->handleDocx($id);
    }

    // GET /notulen/{id}/export-pdf
    public function exportPdf(int $id): void
    {
        Auth::requireLogin();
        $this->handlePdf($id);
    }

    // -------------------------------------------------------------------------
    // Legacy static handlers — dipanggil via ?meeting_id=
    // Dipertahankan untuk backward compatibility
    // -------------------------------------------------------------------------
    public static function downloadPdf(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }
        (new self())->handlePdf($meetingId);
    }

    public static function downloadDocx(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo 'Meeting ID diperlukan.'; exit; }
        (new self())->handleDocx($meetingId);
    }

    // -------------------------------------------------------------------------
    // Core logic
    // -------------------------------------------------------------------------
    private function handlePdf(int $meetingId): void
    {
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

    private function handleDocx(int $meetingId): void
    {
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

    // -------------------------------------------------------------------------
    // Serve static export file yang sudah tersimpan di /exports/
    // GET /exports/{filename}
    // -------------------------------------------------------------------------
    public static function serveFile(): void
    {
        Auth::requireLogin();
        $html = $_GET['file'] ?? '';

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
