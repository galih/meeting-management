<?php
class EmailController
{
    /**
     * POST /meetings/{id}/send-invitations
     * Kirim undangan ke semua peserta meeting
     */
    public static function sendInvitations(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m LEFT JOIN users u ON u.id = m.created_by
             WHERE m.id = ?",
            [$meetingId]
        );
        if (!$meeting) { self::jsonResponse(false, 'Meeting tidak ditemukan'); return; }

        $participants = Database::query(
            "SELECT u.id, u.name, u.email
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id = ?",
            [$meetingId]
        );

        $queued = 0;
        foreach ($participants as $p) {
            if (empty($p['email'])) continue;
            $subject = '📅 Undangan Meeting: ' . $meeting['title'];
            $body    = EmailTemplate::invitation($meeting, $p);
            Mailer::queue($p['email'], $p['name'], $subject, $body, 'invitation');
            $queued++;
        }

        // Proses langsung (tanpa cron)
        $result = Mailer::processQueue(50);

        // Notifikasi in-app
        foreach ($participants as $p) {
            Notification::send(
                $p['id'],
                'meeting_invitation',
                'Undangan Meeting',
                'Anda diundang ke meeting: ' . $meeting['title'],
                ['meeting_id' => $meetingId]
            );
        }

        self::jsonResponse(true, "Undangan dikirim ke {$queued} peserta.", $result);
    }

    /**
     * POST /meetings/{id}/send-summary
     * Kirim ringkasan notulen ke semua peserta
     */
    public static function sendSummary(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name
             FROM meetings m LEFT JOIN users u ON u.id = m.created_by
             WHERE m.id = ?",
            [$meetingId]
        );
        if (!$meeting) { self::jsonResponse(false, 'Meeting tidak ditemukan'); return; }

        $notulen = Database::queryOne(
            "SELECT * FROM notulen WHERE meeting_id = ?", [$meetingId]
        ) ?? [];

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?",
            [$meetingId]
        );

        $participants = Database::query(
            "SELECT u.id, u.name, u.email
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id = ?",
            [$meetingId]
        );

        $queued = 0;
        foreach ($participants as $p) {
            if (empty($p['email'])) continue;
            $subject = '📋 Ringkasan Meeting: ' . $meeting['title'];
            $body    = EmailTemplate::meetingSummary($meeting, $notulen, $tindakLanjutList, $p);
            Mailer::queue($p['email'], $p['name'], $subject, $body, 'summary');
            $queued++;
        }

        $result = Mailer::processQueue(50);
        self::jsonResponse(true, "Ringkasan dikirim ke {$queued} peserta.", $result);
    }

    /**
     * POST /api/email/send-reminders
     * Kirim reminder deadline tindak lanjut H-1
     * Dipanggil manual atau via cron: curl https://domain.com/api/email/send-reminders
     */
    public static function sendDeadlineReminders(): void
    {
        // Bisa dipanggil via cron tanpa auth, atau oleh admin
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $tasks = Database::query(
            "SELECT tl.*, u.name AS assigned_name, u.email AS assigned_email,
                    m.title AS meeting_title
             FROM tindak_lanjut tl
             JOIN users u ON u.id = tl.assigned_to
             JOIN meetings m ON m.id = tl.meeting_id
             WHERE tl.deadline = ?
               AND tl.status NOT IN ('done','cancelled')
               AND u.email IS NOT NULL",
            [$tomorrow]
        );

        $queued = 0;
        foreach ($tasks as $tl) {
            $subject = '⏰ Reminder Deadline: ' . $tl['deskripsi'];
            $user    = ['name' => $tl['assigned_name'], 'email' => $tl['assigned_email']];
            $body    = EmailTemplate::deadlineReminder($tl, $user);
            Mailer::queue($tl['assigned_email'], $tl['assigned_name'], $subject, $body, 'reminder');
            $queued++;
        }

        $result = Mailer::processQueue(100);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'queued' => $queued, 'result' => $result]);
        exit;
    }

    private static function jsonResponse(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(compact('success', 'message') + $data);
        exit;
    }
}
