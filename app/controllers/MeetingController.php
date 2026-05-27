<?php
class MeetingController
{
    /**
     * GET /meetings
     */
    public static function index(): void
    {
        Auth::requireAuth();
        $search    = trim($_GET['search']  ?? '');
        $status    = $_GET['status']       ?? '';
        $dept      = $_GET['dept']         ?? '';
        $dateFrom  = $_GET['date_from']    ?? '';
        $dateTo    = $_GET['date_to']      ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = '(m.title LIKE ? OR m.location LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where[]  = 'm.status = ?';
            $params[] = $status;
        }
        if ($dept) {
            $where[]  = 'm.department_id = ?';
            $params[] = (int)$dept;
        }
        if ($dateFrom) {
            $where[]  = 'DATE(m.start_datetime) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[]  = 'DATE(m.start_datetime) <= ?';
            $params[] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);
        $meetings = Database::query(
            "SELECT m.*, u.name AS creator_name, d.name AS dept_name,
                    (SELECT COUNT(*) FROM meeting_participants mp WHERE mp.meeting_id=m.id) AS total_peserta
             FROM meetings m
             LEFT JOIN users u ON u.id = m.created_by
             LEFT JOIN departments d ON d.id = m.department_id
             WHERE {$whereStr}
             ORDER BY m.start_datetime DESC",
            $params
        );

        $departments = Database::query("SELECT id, name FROM departments WHERE is_active=1 ORDER BY name");

        View::render('layouts/base', 'meetings/index', [
            'title'       => 'Daftar Meeting',
            'meetings'    => $meetings,
            'departments' => $departments,
            'search'      => $search,
            'status'      => $status,
            'dept'        => $dept,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]);
    }

    /**
     * POST /meetings
     */
    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $d = $_POST;

        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO meetings
             (title, description, location, start_datetime, end_datetime,
              color, department_id, created_by)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([
            trim($d['title']),
            trim($d['description'] ?? ''),
            trim($d['location']    ?? ''),
            $d['start_datetime'],
            $d['end_datetime'],
            $d['color']          ?? '#206bc4',
            !empty($d['department_id']) ? (int)$d['department_id'] : null,
            Auth::id(),
        ]);
        $meetingId = (int)$db->lastInsertId();

        // Simpan peserta
        $participants = (array)($d['participants'] ?? []);
        foreach ($participants as $uid) {
            $db->prepare(
                "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (?,?)"
            )->execute([$meetingId, (int)$uid]);
        }

        // Notifikasi peserta
        foreach ($participants as $uid) {
            Notification::send((int)$uid, 'meeting_invite',
                "Anda diundang ke meeting: {$d['title']}",
                "/meetings/{$meetingId}"
            );
        }

        $_SESSION['flash_success'] = 'Meeting berhasil dibuat.';
        header("Location: /meetings/{$meetingId}"); exit;
    }

    /**
     * GET /meetings/{id}
     */
    public static function show(int $id): void
    {
        Auth::requireAuth();
        $meeting = Database::queryOne(
            "SELECT m.*, u.name AS creator_name, d.name AS dept_name
             FROM meetings m
             LEFT JOIN users u ON u.id = m.created_by
             LEFT JOIN departments d ON d.id = m.department_id
             WHERE m.id=?",
            [$id]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        $participants = Database::query(
            "SELECT u.id, u.name, u.email, u.avatar,
                    COALESCE(ma.status, 'pending') AS attendance_status
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             LEFT JOIN meeting_attendances ma ON ma.meeting_id=mp.meeting_id AND ma.user_id=mp.user_id
             WHERE mp.meeting_id=?",
            [$id]
        );

        $users = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        View::render('layouts/base', 'meetings/show', [
            'title'        => $meeting['title'],
            'meeting'      => $meeting,
            'participants' => $participants,
            'users'        => $users,
        ]);
    }

    /**
     * POST /meetings/{id}/status
     */
    public static function updateStatus(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $status = $_POST['status'] ?? '';
        $allowed = ['scheduled', 'ongoing', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']); exit;
        }
        Database::getInstance()->prepare(
            "UPDATE meetings SET status=? WHERE id=?"
        )->execute([$status, $id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    /**
     * POST /meetings/{id}/delete
     */
    public static function destroy(int $id): void
    {
        Auth::requireRole('admin');
        Database::getInstance()->prepare("DELETE FROM meetings WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Meeting berhasil dihapus.';
        header('Location: /meetings'); exit;
    }

    /**
     * GET /api/meetings/calendar
     * Data JSON untuk FullCalendar
     */
    public static function calendarApi(): void
    {
        Auth::requireAuth();
        $start = $_GET['start'] ?? date('Y-m-01');
        $end   = $_GET['end']   ?? date('Y-m-t');

        $meetings = Database::query(
            "SELECT id, title, start_datetime AS start, end_datetime AS `end`, color, status
             FROM meetings
             WHERE start_datetime BETWEEN ? AND ?
             ORDER BY start_datetime",
            [$start, $end]
        );

        $events = array_map(fn($m) => [
            'id'    => $m['id'],
            'title' => $m['title'],
            'start' => $m['start'],
            'end'   => $m['end'],
            'color' => $m['color'],
            'url'   => '/meetings/' . $m['id'],
            'extendedProps' => ['status' => $m['status']],
        ], $meetings);

        header('Content-Type: application/json');
        echo json_encode($events); exit;
    }
}
