<?php
class EmailController
{
    // ── Fix #2: CSRF helper ─────────────────────────────────────────────
    private static function verifyCsrf(): void
    {
        $token   = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        if (!$session || !hash_equals($session, $token)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => '403 CSRF token tidak valid.']);
            exit;
        }
    }

    /**
     * POST /meetings/{id}/send-invitations
     */
    public static function sendInvitations(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');
        self::verifyCsrf();

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
            $subject = '\xF0\x9F\x93\x85 Undangan Meeting: ' . $meeting['title'];
            $body    = EmailTemplate::invitation($meeting, $p);
            Mailer::queue($p['email'], $p['name'], $subject, $body, 'invitation');
            $queued++;
        }

        $result = Mailer::processQueue(50);

        $meetingUrl = rtrim(BASE_URL, '/') . '/meetings/' . $meetingId;
        foreach ($participants as $p) {
            Notification::send(
                (int)$p['id'],
                'meeting_invitation',
                'Anda diundang ke meeting: ' . $meeting['title'],
                $meetingUrl
            );
        }

        self::jsonResponse(true, "Undangan dikirim ke {$queued} peserta.", $result);
    }

    /**
     * POST /meetings/{id}/send-summary
     */
    public static function sendSummary(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');
        self::verifyCsrf();

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
            $subject = '\xF0\x9F\x93\x8B Ringkasan Meeting: ' . $meeting['title'];
            $body    = EmailTemplate::meetingSummary($meeting, $notulen, $tindakLanjutList, $p);
            Mailer::queue($p['email'], $p['name'], $subject, $body, 'summary');
            $queued++;
        }

        $result = Mailer::processQueue(50);
        self::jsonResponse(true, "Ringkasan dikirim ke {$queued} peserta.", $result);
    }

    /**
     * GET /api/email/send-reminders
     */
    public static function sendDeadlineReminders(): void
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $tasks = Database::query(
            "SELECT tl.*, u.name AS assigned_name, u.email AS assigned_email,
                    m.title AS meeting_title
             FROM tindak_lanjut tl
             JOIN users u ON u.id = tl.assigned_to
             JOIN meetings m ON m.id = tl.meeting_id
             WHERE tl.due_date = ?
               AND tl.status NOT IN ('done','cancelled')
               AND u.email IS NOT NULL",
            [$tomorrow]
        );

        $queued = 0;
        foreach ($tasks as $tl) {
            $subject = '\xE2\x8F\xB0 Reminder Deadline: ' . $tl['description'];
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
