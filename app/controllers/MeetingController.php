<?php
class MeetingController
{
    /**
     * Admin: semua meeting.
     * Sekretaris & Peserta: hanya meeting di mana user adalah creator atau peserta.
     */
    private static function accessScope(): array
    {
        if (Auth::hasRole('admin')) {
            return ['where' => '1=1', 'params' => []];
        }
        $uid = Auth::id();
        return [
            'where'  => '(m.created_by = ? OR EXISTS (
                            SELECT 1 FROM meeting_participants mp
                            WHERE mp.meeting_id = m.id AND mp.user_id = ?
                         ))',
            'params' => [$uid, $uid],
        ];
    }

    public static function index(): void
    {
        Auth::requireAuth();
        $search   = trim($_GET['search']  ?? '');
        $status   = $_GET['status']       ?? '';
        $dept     = $_GET['dept']         ?? '';
        $dateFrom = $_GET['date_from']    ?? '';
        $dateTo   = $_GET['date_to']      ?? '';

        $scope  = self::accessScope();
        $where  = [$scope['where']];
        $params = $scope['params'];

        if ($search) {
            $where[]  = '(m.title LIKE ? OR m.location LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status)   { $where[] = 'm.status = ?';                $params[] = $status; }
        if ($dept)     { $where[] = 'm.department_id = ?';         $params[] = (int)$dept; }
        if ($dateFrom) { $where[] = 'DATE(m.start_datetime) >= ?'; $params[] = $dateFrom; }
        if ($dateTo)   { $where[] = 'DATE(m.start_datetime) <= ?'; $params[] = $dateTo; }

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

        View::layout('meetings/index', [
            'pageTitle'   => 'Daftar Meeting',
            'meetings'    => $meetings,
            'departments' => $departments,
            'search'      => $search,
            'status'      => $status,
            'dept'        => $dept,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $d  = $_POST;
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
            $d['color']          ?? '#f76707',
            !empty($d['department_id']) ? (int)$d['department_id'] : null,
            Auth::id(),
        ]);
        $meetingId = (int)$db->lastInsertId();

        // Cast semua participant ID ke int sebelum dikirim ke DB & notifikasi
        $participants = array_map('intval', (array)($d['participants'] ?? []));
        $participants = array_filter($participants); // hapus 0

        foreach ($participants as $uid) {
            $db->prepare(
                "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (?,?)"
            )->execute([$meetingId, $uid]);
        }

        if (!empty($participants)) {
            Notification::sendBulk(
                $participants,
                'meeting_invite',
                "Anda diundang ke meeting: {$d['title']}",
                BASE_URL . "/meetings/{$meetingId}"
            );
        }

        $_SESSION['flash_success'] = 'Meeting berhasil dibuat.';
        header('Location: ' . BASE_URL . "/meetings/{$meetingId}"); exit;
    }

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

        // Non-admin hanya boleh lihat jika terlibat
        if (!Auth::hasRole('admin')) {
            $uid      = Auth::id();
            $terlibat = $meeting['created_by'] == $uid
                || Database::queryOne(
                    "SELECT 1 FROM meeting_participants WHERE meeting_id=? AND user_id=?",
                    [$id, $uid]
                );
            if (!$terlibat) {
                http_response_code(403);
                include APP_PATH . '/views/errors/403.php';
                exit;
            }
        }

        $participants = Database::query(
            "SELECT u.id, u.name, u.email, u.avatar,
                    COALESCE(mp.status, 'invited') AS status
             FROM meeting_participants mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.meeting_id=?",
            [$id]
        );

        $tindakLanjutList = Database::query(
            "SELECT tl.*, u.name AS assigned_name
             FROM tindak_lanjut tl
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE tl.meeting_id = ?
             ORDER BY tl.created_at DESC",
            [$id]
        );

        $users = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        View::layout('meetings/show', [
            'pageTitle'        => $meeting['title'],
            'meeting'          => $meeting,
            'participants'     => $participants,
            'tindakLanjutList' => $tindakLanjutList,
            'users'            => $users,
        ]);
    }

    public static function updateStatus(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $status  = $_POST['status'] ?? '';
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

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin');
        Database::getInstance()->prepare("DELETE FROM meetings WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Meeting berhasil dihapus.';
        header('Location: ' . BASE_URL . '/meetings'); exit;
    }

    public static function calendarApi(): void
    {
        Auth::requireAuth();
        $start = $_GET['start'] ?? date('Y-m-01');
        $end   = $_GET['end']   ?? date('Y-m-t');

        $scope  = self::accessScope();
        $params = array_merge($scope['params'], [$start, $end]);

        $meetings = Database::query(
            "SELECT m.id, m.title, m.start_datetime AS start, m.end_datetime AS `end`,
                    m.color, m.status
             FROM meetings m
             WHERE {$scope['where']}
               AND m.start_datetime BETWEEN ? AND ?
             ORDER BY m.start_datetime",
            $params
        );

        $events = array_map(fn($m) => [
            'id'    => $m['id'],
            'title' => $m['title'],
            'start' => $m['start'],
            'end'   => $m['end'],
            'color' => $m['color'],
            'url'   => BASE_URL . '/meetings/' . $m['id'],
            'extendedProps' => ['status' => $m['status']],
        ], $meetings);

        header('Content-Type: application/json');
        echo json_encode($events); exit;
    }
}
