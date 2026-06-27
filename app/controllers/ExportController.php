<?php
declare(strict_types=1);

class ExportController
{
    // -------------------------------------------------------------------------
    // Route handlers — dipanggil Router dengan {id} dari URL
    // GET /notulen/{id}/export-docx
    // GET /notulen/{id}/export-pdf
    // -------------------------------------------------------------------------

    public function exportDocx(int $id): void
    {
        Auth::requireLogin();
        [$meeting, $notulen, $participants, $tindakLanjut] = $this->fetchMeetingData($id);
        $user = Auth::user();
        DocxExporter::export($meeting, $notulen, $participants, $tindakLanjut, $user);
    }

    public function exportPdf(int $id): void
    {
        Auth::requireLogin();
        [$meeting, $notulen, $participants, $tindakLanjut] = $this->fetchMeetingData($id);
        $user   = Auth::user();
        $result = PdfExporter::export($meeting, $notulen, $participants, $tindakLanjut, $user);

        // PdfExporter::export() mengembalikan path file (/exports/...) jika mPDF
        // tersedia, atau string HTML jika fallback. Tangani keduanya.
        if (strncmp((string)$result, '/exports/', 9) === 0) {
            // File tersimpan — redirect ke serve route
            $baseUrl = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
            header('Location: ' . $baseUrl . $result);
            exit;
        }

        // Fallback: tampilkan HTML langsung di browser agar bisa dicetak
        header('Content-Type: text/html; charset=UTF-8');
        echo $result;
        exit;
    }

    // -------------------------------------------------------------------------
    // Serve static export file yang sudah tersimpan di /exports/
    // GET /exports?file=/exports/{filename}
    // -------------------------------------------------------------------------
    public static function serveFile(): void
    {
        Auth::requireLogin();
        $file = $_GET['file'] ?? '';

        if (strncmp($file, '/exports/', 9) !== 0) {
            http_response_code(400); echo 'Path tidak valid.'; exit;
        }

        $path = ROOT_PATH . $file;
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

    // -------------------------------------------------------------------------
    // Helper — ambil semua data yang dibutuhkan exporter
    // -------------------------------------------------------------------------
    private function fetchMeetingData(int $meetingId): array
    {
        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS created_by_name, d.name AS dept_name
             FROM meetings m
             LEFT JOIN users u ON u.id = m.created_by
             LEFT JOIN departments d ON d.id = m.department_id
             WHERE m.id = ?",
            [$meetingId]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $notulen = Database::queryOne(
            "SELECT n.*, u.name AS editor_name
             FROM notulen n
             LEFT JOIN users u ON u.id = n.updated_by
             WHERE n.meeting_id = ?",
            [$meetingId]
        ) ?? [];

        $participants = Database::query(
            "SELECT u.name, u.email, mp.status, d.name AS dept_name
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE mp.meeting_id = ? ORDER BY u.name",
            [$meetingId]
        );

        $tindakLanjut = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ? ORDER BY tl.created_at",
            [$meetingId]
        );

        return [$meeting, $notulen, $participants, $tindakLanjut];
    }
}
