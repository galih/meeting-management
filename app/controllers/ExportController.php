<?php
declare(strict_types=1);

class ExportController
{
    // -------------------------------------------------------------------------
    // Route handler — dipanggil Router dengan {id} dari URL
    // GET /notulen/{id}/export-docx
    // -------------------------------------------------------------------------

    public function exportDocx(int $id): void
    {
        Auth::requireLogin();
        [$meeting, $notulen, $participants, $tindakLanjut] = $this->fetchMeetingData($id);
        $user = Auth::user();
        DocxExporter::export($meeting, $notulen, $participants, $tindakLanjut, $user);
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
