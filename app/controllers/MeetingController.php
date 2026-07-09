<?php
class MeetingController
{
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

    /** Cek apakah user boleh edit detail kegiatan */
    private static function canEdit(array $meeting): bool
    {
        if (Auth::hasRole('admin')) return true;
        return (int)($meeting['created_by'] ?? 0) === (int)Auth::id();
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

        $departments = Database::query("SELECT id, name, level, parent_id FROM departments WHERE is_active=1 ORDER BY name");

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

    /**
     * GET /meetings/create
     * Tampilkan form buat kegiatan baru.
     * Route ini HARUS didaftarkan SEBELUM /meetings/{id} agar
     * string "create" tidak ditangkap sebagai $id.
     */
    public static function create(): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $departments = Database::query(
            "SELECT id, name, level, parent_id FROM departments WHERE is_active=1 ORDER BY name"
        );
        $allUsers = Database::query(
            "SELECT id, name FROM users WHERE is_active=1 ORDER BY name"
        );

        View::layout('meetings/create', [
            'pageTitle'   => 'Buat Kegiatan Baru',
            'departments' => $departments,
            'allUsers'    => $allUsers,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        self::verifyCsrf();

        $title     = trim($_POST['title']          ?? '');
        $desc      = trim($_POST['description']    ?? '');
        $location  = trim($_POST['location']       ?? '');
        $startDt   = trim($_POST['start_datetime'] ?? '');
        $endDt     = trim($_POST['end_datetime']   ?? '');
        $color     = trim($_POST['color']          ?? '#f76707');
        $deptId    = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

        $errors = [];
        if ($title === '') $errors[] = 'Judul meeting tidak boleh kosong.';
        if ($startDt === '' || $endDt === '') {
            $errors[] = 'Tanggal mulai dan selesai wajib diisi.';
        } elseif (strtotime($endDt) <= strtotime($startDt)) {
            $errors[] = 'Tanggal selesai harus lebih besar dari tanggal mulai.';
        }
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) $color = '#f76707';

        if (!empty($errors)) {
            $_SESSION['flash_error']        = implode(' ', $errors);
            $_SESSION['flash_reopen_modal'] = true;
            $_SESSION['flash_post_title']   = $title;
            header('Location: ' . BASE_URL . '/meetings'); exit;
        }

        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO meetings
             (title, description, location, start_datetime, end_datetime,
              color, department_id, created_by)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([
            $title, $desc, $location, $startDt, $endDt, $color, $deptId, Auth::id(),
        ]);
        $meetingId = (int)$db->lastInsertId();

        $participants = array_filter(array_map('intval', (array)($_POST['participants'] ?? [])));
        foreach ($participants as $uid) {
            $db->prepare(
                "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (?,?)"
            )->execute([$meetingId, $uid]);
        }
        if (!empty($participants)) {
            Notification::sendBulk(
                $participants, 'meeting_invite',
                "Anda diundang ke meeting: {$title}",
                BASE_URL . "/meetings/{$meetingId}"
            );
        }

        ActivityLog::record(
            'meeting.create',
            "Membuat kegiatan: {$title}",
            'meeting', $meetingId
        );

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
            'canEdit'          => self::canEdit($meeting),
        ]);
    }

    public static function edit(int $id): void
    {
        Auth::requireAuth();
        $meeting = Database::queryOne(
            "SELECT m.*, d.name AS dept_name
             FROM meetings m
             LEFT JOIN departments d ON d.id = m.department_id
             WHERE m.id=?",
            [$id]
        );
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        if (!self::canEdit($meeting)) {
            http_response_code(403);
            include APP_PATH . '/views/errors/403.php';
            exit;
        }

        $departments  = Database::query("SELECT id, name, level, parent_id FROM departments WHERE is_active=1 ORDER BY name");
        $allUsers     = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        $participants = Database::query(
            "SELECT user_id FROM meeting_participants WHERE meeting_id=?", [$id]
        );
        $participantIds = array_column($participants, 'user_id');

        View::layout('meetings/edit', [
            'pageTitle'      => 'Edit Kegiatan: ' . $meeting['title'],
            'meeting'        => $meeting,
            'departments'    => $departments,
            'allUsers'       => $allUsers,
            'participantIds' => $participantIds,
        ]);
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        self::verifyCsrf();

        $meeting = Database::queryOne("SELECT * FROM meetings WHERE id=?", [$id]);
        if (!$meeting) { http_response_code(404); echo 'Meeting tidak ditemukan.'; exit; }

        if (!self::canEdit($meeting)) {
            http_response_code(403);
            include APP_PATH . '/views/errors/403.php';
            exit;
        }

        $title    = trim($_POST['title']          ?? '');
        $desc     = trim($_POST['description']    ?? '');
        $location = trim($_POST['location']       ?? '');
        $startDt  = trim($_POST['start_datetime'] ?? '');
        $endDt    = trim($_POST['end_datetime']   ?? '');
        $color    = trim($_POST['color']          ?? '#f76707');
        $deptId   = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

        $errors = [];
        if ($title === '') $errors[] = 'Judul kegiatan tidak boleh kosong.';
        if ($startDt === '' || $endDt === '') {
            $errors[] = 'Tanggal mulai dan selesai wajib diisi.';
        } elseif (strtotime($endDt) <= strtotime($startDt)) {
            $errors[] = 'Tanggal selesai harus lebih besar dari tanggal mulai.';
        }
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) $color = $meeting['color'];

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . "/meetings/{$id}/edit"); exit;
        }

        Database::getInstance()->prepare(
            "UPDATE meetings
             SET title=?, description=?, location=?, start_datetime=?, end_datetime=?,
                 color=?, department_id=?
             WHERE id=?"
        )->execute([$title, $desc, $location, $startDt, $endDt, $color, $deptId, $id]);

        // Ambil peserta lama sebelum di-delete untuk diff notifikasi
        $oldRows = Database::query(
            "SELECT user_id FROM meeting_participants WHERE meeting_id=?", [$id]
        );
        $oldParticipantIds = array_map('intval', array_column($oldRows, 'user_id'));

        // Sync peserta
        $newParticipants = array_filter(array_map('intval', (array)($_POST['participants'] ?? [])));
        $db = Database::getInstance();
        $db->prepare("DELETE FROM meeting_participants WHERE meeting_id=?")->execute([$id]);
        foreach ($newParticipants as $uid) {
            $db->prepare(
                "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (?,?)"
            )->execute([$id, $uid]);
        }

        // Kirim notifikasi hanya ke peserta yang BARU ditambahkan
        $addedParticipants = array_values(array_diff($newParticipants, $oldParticipantIds));
        if (!empty($addedParticipants)) {
            Notification::sendBulk(
                $addedParticipants,
                'meeting_invite',
                "Anda diundang ke kegiatan: {$title}",
                BASE_URL . "/meetings/{$id}"
            );
        }

        ActivityLog::record(
            'meeting.update',
            "Mengubah detail kegiatan: {$title}",
            'meeting', $id
        );

        $_SESSION['flash_success'] = 'Detail kegiatan berhasil diperbarui.';
        header('Location: ' . BASE_URL . "/meetings/{$id}"); exit;
    }

    public static function updateStatus(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        self::verifyCsrf();

        $status  = $_POST['status'] ?? '';
        $allowed = ['scheduled', 'ongoing', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']); exit;
        }
        Database::getInstance()->prepare(
            "UPDATE meetings SET status=? WHERE id=?"
        )->execute([$status, $id]);

        $meeting = Database::queryOne("SELECT title FROM meetings WHERE id=?", [$id]);
        ActivityLog::record(
            'meeting.status',
            "Ubah status kegiatan \"" . ($meeting['title'] ?? $id) . "\" menjadi: {$status}",
            'meeting', $id
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin');
        self::verifyCsrf(); // fix: tambah CSRF check untuk konsistensi dengan store() dan update()

        $meeting = Database::queryOne("SELECT title FROM meetings WHERE id=?", [$id]);
        $title   = $meeting['title'] ?? "ID #{$id}";

        $db = Database::getInstance();

        $attachments = Database::query(
            "SELECT stored_name FROM meeting_attachments WHERE meeting_id=?", [$id]
        );
        $uploadDir = defined('UPLOAD_PATH') ? UPLOAD_PATH : (ROOT_PATH . '/uploads');
        foreach ($attachments as $att) {
            $path = rtrim($uploadDir, '/') . '/' . $att['stored_name'];
            if (file_exists($path)) @unlink($path);
        }

        $relatedTables = [
            "DELETE FROM notulen_exports      WHERE meeting_id=?",
            "DELETE FROM notulen_history      WHERE meeting_id=?",
            "DELETE FROM notulen_comments     WHERE meeting_id=?",
            "DELETE FROM meeting_attachments  WHERE meeting_id=?",
            "DELETE FROM meeting_attendances  WHERE meeting_id=?",
            "DELETE FROM tindak_lanjut        WHERE meeting_id=?",
            "DELETE FROM meeting_participants WHERE meeting_id=?",
            "DELETE FROM email_queue          WHERE meeting_id=?",
            "DELETE FROM notifications        WHERE url LIKE CONCAT('%/meetings/',?,'%')",
            "DELETE FROM notulen              WHERE meeting_id=?",
            "DELETE FROM activity_log         WHERE object_type='meeting' AND object_id=?",
        ];
        foreach ($relatedTables as $sql) {
            $db->prepare($sql)->execute([$id]);
        }
        $db->prepare("DELETE FROM meetings WHERE id=?")->execute([$id]);

        ActivityLog::record(
            'meeting.delete',
            "Menghapus kegiatan: {$title}",
            'meeting', null
        );

        $_SESSION['flash_success'] = 'Kegiatan berhasil dihapus.';
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
                    m.color, m.status, m.location
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
            'extendedProps' => [
                'status'   => $m['status'],
                'location' => $m['location'] ?? '',
            ],
        ], $meetings);

        header('Content-Type: application/json');
        echo json_encode($events); exit;
    }

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
}
