<?php
class TindakLanjutController
{
    private const PER_PAGE = 20;

    /**
     * Hitung summary dalam 1 query GROUP BY + 1 query overdue.
     * userId hanya dipakai jika isAdminLike = true (tidak boleh dikontrol dari luar oleh non-admin).
     */
    private static function getSummary(bool $isAdminLike, int $userId = 0): array
    {
        if (!$isAdminLike) {
            $baseWhere  = 'assigned_to = ?';
            $baseParams = [Auth::id()];
        } elseif ($userId > 0) {
            $baseWhere  = 'assigned_to = ?';
            $baseParams = [$userId];
        } else {
            $baseWhere  = '1=1';
            $baseParams = [];
        }

        // Satu query GROUP BY menggantikan 4 query terpisah
        $rows = Database::query(
            "SELECT status, COUNT(*) AS cnt FROM tindak_lanjut WHERE {$baseWhere} GROUP BY status",
            $baseParams
        );

        $counts = ['pending' => 0, 'in_progress' => 0, 'done' => 0, 'cancelled' => 0];
        $total  = 0;
        foreach ($rows as $r) {
            $s = $r['status'];
            if (isset($counts[$s])) $counts[$s] = (int)$r['cnt'];
            $total += (int)$r['cnt'];
        }

        $overdue = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut WHERE {$baseWhere} AND due_date < CURDATE() AND status NOT IN ('done','cancelled')",
            $baseParams
        )['c'] ?? 0);

        return [
            'total'       => $total,
            'pending'     => $counts['pending'],
            'in_progress' => $counts['in_progress'],
            'done'        => $counts['done'],
            'overdue'     => $overdue,
        ];
    }

    public static function index(): void
    {
        Auth::requireAuth();
        $status   = $_GET['status']   ?? '';
        $priority = $_GET['priority'] ?? '';
        $search   = trim($_GET['q']   ?? '');
        $userId   = (int)($_GET['user_id'] ?? 0);
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $offset   = ($page - 1) * self::PER_PAGE;

        $isAdminLike = Auth::hasRole('admin', 'sekretaris');

        $where  = ['1=1'];
        $params = [];

        if (!$isAdminLike) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = Auth::id();
        } elseif ($userId) {
            $where[]  = 'tl.assigned_to = ?';
            $params[] = $userId;
        }

        if ($status)   { $where[] = 'tl.status = ?';         $params[] = $status; }
        if ($priority) { $where[] = 'tl.priority = ?';       $params[] = $priority; }
        if ($search)   { $where[] = 'tl.description LIKE ?'; $params[] = "%{$search}%"; }

        $whereStr = implode(' AND ', $where);

        $totalRows = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut tl WHERE {$whereStr}",
            $params
        )['c'] ?? 0);
        $totalPages = max(1, (int)ceil($totalRows / self::PER_PAGE));

        $tindakLanjutList = Database::query(
            "SELECT tl.*, m.title AS meeting_title,
                    u.name AS assignee_name,
                    (SELECT COUNT(*) FROM tindak_lanjut_notes n WHERE n.tindak_lanjut_id = tl.id) AS note_count
             FROM tindak_lanjut tl
             JOIN meetings m ON m.id = tl.meeting_id
             LEFT JOIN users u ON u.id = tl.assigned_to
             WHERE {$whereStr}
             ORDER BY tl.due_date ASC, tl.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [self::PER_PAGE, $offset])
        );

        $summary  = self::getSummary($isAdminLike, $userId);
        $allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

        View::layout('tindak-lanjut/index', [
            'pageTitle'        => 'Tindak Lanjut',
            'tindakLanjutList' => $tindakLanjutList,
            'summary'          => $summary,
            'allUsers'         => $allUsers,
            'status'           => $status,
            'priority'         => $priority,
            'search'           => $search,
            'user_id'          => $userId,
            'page'             => $page,
            'totalPages'       => $totalPages,
            'totalRows'        => $totalRows,
            'perPage'          => self::PER_PAGE,
        ]);
    }

    public static function show(int $id): void
    {
        Auth::requireAuth();

        $tl = Database::queryOne(
            "SELECT tl.*,
                    m.title  AS meeting_title,
                    m.id     AS meeting_id,
                    u.name   AS assignee_name,
                    u.email  AS assignee_email,
                    c.name   AS creator_name
             FROM tindak_lanjut tl
             JOIN meetings m ON m.id = tl.meeting_id
             LEFT JOIN users u ON u.id = tl.assigned_to
             LEFT JOIN users c ON c.id = tl.created_by
             WHERE tl.id = ?",
            [$id]
        );

        if (!$tl) {
            http_response_code(404);
            $pageTitle = '404 - Tindak Lanjut Tidak Ditemukan';
            include APP_PATH . '/views/errors/404.php';
            return;
        }

        // Hanya assignee, admin, atau sekretaris yang boleh lihat
        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            $pageTitle = '403 - Akses Ditolak';
            include APP_PATH . '/views/errors/403.php';
            return;
        }

        $notes = Database::query(
            "SELECT n.id, n.note, n.created_at, n.user_id, u.name AS author_name
             FROM tindak_lanjut_notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.tindak_lanjut_id = ?
             ORDER BY n.created_at ASC",
            [$id]
        );

        $isAdmin = Auth::hasRole('admin');
        $myId    = Auth::id();
        foreach ($notes as &$n) {
            $n['can_delete'] = $isAdmin || ((int)$n['user_id'] === $myId);
        }
        unset($n);

        $canEdit = Auth::hasRole('admin', 'sekretaris') || ((int)$tl['assigned_to'] === $myId);

        View::layout('tindak-lanjut/show', [
            'pageTitle' => 'Detail Tindak Lanjut',
            'tl'        => $tl,
            'notes'     => $notes,
            'canEdit'   => $canEdit,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            // Verifikasi CSRF dari header untuk JSON request
            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit;
            }
        } else {
            Auth::requireCsrf(); // fix: ganti CSRF::verify() → Auth::requireCsrf()
            $d = $_POST;
        }

        $meetingId  = (int)($d['meeting_id']  ?? 0);
        $desc       = trim($d['description']  ?? '');
        $assignedTo = (int)($d['assigned_to'] ?? 0);
        $dueDate    = ($d['due_date']  ?? '') ?: null;
        $priority   = in_array($d['priority'] ?? '', ['high','medium','low']) ? $d['priority'] : 'medium';

        if (!$meetingId || !$desc) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'meeting_id dan description wajib']); exit;
        }

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut
             (meeting_id, description, assigned_to, due_date, priority, created_by)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            $meetingId, $desc, $assignedTo ?: null, $dueDate, $priority, Auth::id(),
        ]);

        if ($assignedTo) {
            Notification::send($assignedTo, 'tindak_lanjut',
                "Anda mendapat tugas tindak lanjut baru: {$desc}",
                '/tindak-lanjut'
            );
        }

        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]); exit;
        }

        $_SESSION['flash_success'] = 'Tindak lanjut berhasil ditambahkan.';
        header('Location: ' . BASE_URL . '/tindak-lanjut'); exit;
    }

    public static function updateStatus(int $id): void
    {
        Auth::requireAuth();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit;
            }
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            Auth::requireCsrf(); // fix: ganti CSRF::verify() → Auth::requireCsrf()
            $input = $_POST;
        }

        $status = $input['status'] ?? '';

        // userId untuk scope summary: hanya boleh dipakai jika admin/sekretaris
        $isAdminLike = Auth::hasRole('admin', 'sekretaris');
        $userId = 0;
        if ($isAdminLike) {
            $userId = (int)($input['user_id'] ?? 0);
            if ($userId > 0) {
                $exists = Database::queryOne("SELECT id FROM users WHERE id=? AND is_active=1", [$userId]);
                if (!$exists) $userId = 0;
            }
        }

        $allowed = ['pending', 'in_progress', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']); exit;
        }

        $tl = Database::queryOne("SELECT * FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']); exit;
        }

        if (!$isAdminLike && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']); exit;
        }

        $completedAt = $status === 'done' ? date('Y-m-d H:i:s') : null;
        Database::getInstance()->prepare(
            "UPDATE tindak_lanjut SET status=?, completed_at=? WHERE id=?"
        )->execute([$status, $completedAt, $id]);

        $summary = self::getSummary($isAdminLike, $userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'summary' => $summary, 'new_status' => $status]); exit;
    }

    // ── Progress Notes ────────────────────────────────────────────────

    public static function getNotes(int $id): void
    {
        Auth::requireAuth();
        $tl = Database::queryOne("SELECT assigned_to FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { http_response_code(404); echo json_encode([]); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403); echo json_encode([]); exit;
        }

        $isAdmin = Auth::hasRole('admin');
        $myId    = Auth::id();

        $notes = Database::query(
            "SELECT n.id, n.note, n.created_at, n.user_id, u.name AS author_name,
                    DATE_FORMAT(n.created_at, '%d %b %Y %H:%i') AS created_at_human
             FROM tindak_lanjut_notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.tindak_lanjut_id = ?
             ORDER BY n.created_at ASC",
            [$id]
        );

        foreach ($notes as &$n) {
            $n['can_delete'] = $isAdmin || ((int)$n['user_id'] === $myId);
            unset($n['user_id']);
        }
        unset($n);

        header('Content-Type: application/json');
        echo json_encode($notes); exit;
    }

    public static function addNote(int $id): void
    {
        Auth::requireAuth();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit;
            }
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            Auth::requireCsrf(); // fix: ganti CSRF::verify() → Auth::requireCsrf()
            $input = $_POST;
        }

        $tl = Database::queryOne("SELECT assigned_to, description FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { http_response_code(404); echo json_encode(['success'=>false]); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
        }

        $note = trim($input['note'] ?? '');
        if (!$note) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Note tidak boleh kosong']); exit;
        }

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut_notes (tindak_lanjut_id, user_id, note) VALUES (?,?,?)"
        )->execute([$id, Auth::id(), $note]);

        self::processMentions($note, $id, $tl['description']);

        $newNote = Database::queryOne(
            "SELECT n.id, n.note, n.created_at,
                    DATE_FORMAT(n.created_at, '%d %b %Y %H:%i') AS created_at_human,
                    n.user_id, u.name AS author_name
             FROM tindak_lanjut_notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.tindak_lanjut_id = ? ORDER BY n.id DESC LIMIT 1",
            [$id]
        );

        $newNote['can_delete'] = Auth::hasRole('admin') || ((int)$newNote['user_id'] === Auth::id());
        unset($newNote['user_id']);

        $noteCount = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut_notes WHERE tindak_lanjut_id=?", [$id]
        )['c'] ?? 0);

        header('Content-Type: application/json');
        echo json_encode(['success'=>true, 'note'=>$newNote, 'note_count'=>$noteCount]); exit;
    }

    private static function processMentions(string $note, int $tlId, string $tlDesc): void
    {
        preg_match_all('/@([\p{L}\p{N}][\p{L}\p{N}\s\-]*[\p{L}\p{N}]|[\p{L}\p{N}]+)/u', $note, $matches);
        if (empty($matches[1])) return;

        $myId      = Auth::id();
        $mentioned = [];

        foreach ($matches[1] as $namePart) {
            $namePart = trim($namePart);
            if (!$namePart) continue;

            $user = Database::queryOne(
                "SELECT id FROM users WHERE is_active=1 AND LOWER(name) = LOWER(?) LIMIT 1",
                [$namePart]
            );
            if (!$user) continue;
            $uid = (int)$user['id'];
            if ($uid === $myId || in_array($uid, $mentioned)) continue;

            $mentioned[] = $uid;
            Notification::send(
                $uid,
                'mention',
                'Anda di-mention dalam progress note: "' . mb_strimwidth($tlDesc, 0, 60, '...') . '"',
                '/tindak-lanjut'
            );
        }
    }

    public static function deleteNote(int $tlId, int $noteId): void
    {
        Auth::requireAuth();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit;
            }
        } else {
            Auth::requireCsrf(); // fix: ganti CSRF::verify() → Auth::requireCsrf()
        }

        $n = Database::queryOne("SELECT * FROM tindak_lanjut_notes WHERE id=?", [$noteId]);
        if (!$n) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false]); exit;
        }

        if (!Auth::hasRole('admin') && $n['user_id'] != Auth::id()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
        }

        Database::getInstance()->prepare("DELETE FROM tindak_lanjut_notes WHERE id=?")->execute([$noteId]);

        $noteCount = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut_notes WHERE tindak_lanjut_id=?", [$tlId]
        )['c'] ?? 0);

        header('Content-Type: application/json');
        echo json_encode(['success'=>true, 'note_count'=>$noteCount]); exit;
    }

    // ── Lampiran Progress ─────────────────────────────────────────────

    public static function getAttachments(int $id): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');

        $tl = Database::queryOne("SELECT assigned_to FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { http_response_code(404); echo json_encode(['success'=>false,'data'=>[]]); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403); echo json_encode(['success'=>false,'data'=>[]]); exit;
        }

        $rows = Database::query(
            "SELECT a.id, a.original_name, a.file_path, a.mime_type, a.file_size,
                    a.uploaded_by, a.created_at,
                    DATE_FORMAT(a.created_at, '%d %b %Y %H:%i') AS created_at_human,
                    u.name AS uploaded_by_name
             FROM tindak_lanjut_attachments a
             LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.tindak_lanjut_id = ?
             ORDER BY a.created_at DESC",
            [$id]
        );

        $isAdmin = Auth::hasRole('admin');
        $myId    = Auth::id();

        foreach ($rows as &$row) {
            $row['icon']       = self::iconForMime($row['mime_type'] ?? '');
            $row['size_fmt']   = self::formatSize((int)($row['file_size'] ?? 0));
            $row['can_delete'] = $isAdmin || ((int)$row['uploaded_by'] === $myId);
            $row['url']        = BASE_URL . $row['file_path'];
            unset($row['uploaded_by']);
        }
        unset($row);

        $attachCount = count($rows);
        echo json_encode(['success'=>true,'data'=>$rows,'attach_count'=>$attachCount]);
        exit;
    }

    public static function uploadAttachment(int $id): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');

        $tl = Database::queryOne("SELECT assigned_to FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan']); exit; }

        if (!Auth::hasRole('admin', 'sekretaris') && $tl['assigned_to'] != Auth::id()) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $codes = [
                1=>'Ukuran melebihi php.ini',
                2=>'Ukuran melebihi MAX_FILE_SIZE',
                3=>'Upload tidak lengkap',
                4=>'Tidak ada file',
                6=>'Tidak ada folder tmp',
                7=>'Gagal tulis ke disk',
                8=>'Upload dihentikan ekstensi',
            ];
            $msg = $codes[$file['error'] ?? 0] ?? 'Upload gagal';
            echo json_encode(['success'=>false,'message'=>$msg]); exit;
        }

        $allowedMimes = [
            'application/pdf','application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg','image/png','image/gif','image/webp',
            'text/plain','text/csv',
            'application/zip','application/x-zip-compressed',
        ];
        $maxSize = 10 * 1024 * 1024;
        $mime    = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan. Gunakan PDF, Word, Excel, PowerPoint, gambar, atau ZIP.']); exit;
        }
        if ($file['size'] > $maxSize) {
            echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 10 MB.']); exit;
        }

        $ext      = self::extensionForMime($mime);
        $filename = 'tl_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir      = ROOT_PATH . '/assets/uploads/tindak-lanjut/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit;
        }

        $original = self::safeFileName($file['name']);
        $filePath = '/assets/uploads/tindak-lanjut/' . $filename;

        Database::getInstance()->prepare(
            "INSERT INTO tindak_lanjut_attachments
             (tindak_lanjut_id, uploaded_by, original_name, file_path, mime_type, file_size)
             VALUES (?,?,?,?,?,?)"
        )->execute([$id, Auth::id(), $original, $filePath, $mime, $file['size']]);

        ActivityLog::record('tindak_lanjut.attachment.create', 'Upload lampiran progress: ' . $original, 'tindak_lanjut', $id);

        $newId = (int)Database::getInstance()->lastInsertId();
        $attachCount = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut_attachments WHERE tindak_lanjut_id=?", [$id]
        )['c'] ?? 0);

        echo json_encode([
            'success'      => true,
            'message'      => 'File berhasil diupload',
            'attach_count' => $attachCount,
            'attachment'   => [
                'id'               => $newId,
                'original_name'    => $original,
                'file_path'        => $filePath,
                'url'              => BASE_URL . $filePath,
                'mime_type'        => $mime,
                'file_size'        => $file['size'],
                'size_fmt'         => self::formatSize($file['size']),
                'icon'             => self::iconForMime($mime),
                'uploaded_by_name' => Database::queryOne("SELECT name FROM users WHERE id=?", [Auth::id()])['name'] ?? '',
                'can_delete'       => true,
            ],
        ]);
        exit;
    }

    public static function deleteAttachment(int $tlId, int $attachId): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');

        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
            echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit;
        }

        $att = Database::queryOne(
            "SELECT * FROM tindak_lanjut_attachments WHERE id=? AND tindak_lanjut_id=?",
            [$attachId, $tlId]
        );
        if (!$att) { echo json_encode(['success'=>false,'message'=>'Lampiran tidak ditemukan']); exit; }

        if (!Auth::hasRole('admin') && $att['uploaded_by'] != Auth::id()) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Anda tidak berhak menghapus file ini']); exit;
        }

        $path = ROOT_PATH . $att['file_path'];
        if (file_exists($path)) @unlink($path);

        Database::getInstance()->prepare(
            "DELETE FROM tindak_lanjut_attachments WHERE id=?"
        )->execute([$attachId]);

        ActivityLog::record('tindak_lanjut.attachment.delete', 'Hapus lampiran progress: ' . $att['original_name'], 'tindak_lanjut', $tlId);

        $attachCount = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM tindak_lanjut_attachments WHERE tindak_lanjut_id=?", [$tlId]
        )['c'] ?? 0);

        echo json_encode(['success'=>true,'message'=>'Lampiran berhasil dihapus','attach_count'=>$attachCount]);
        exit;
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private static function iconForMime(string $mime): string
    {
        if (strpos($mime, 'pdf') !== false)          return '📄';
        if (strpos($mime, 'word') !== false)         return '📝';
        if (strpos($mime, 'sheet') !== false)        return '📊';
        if (strpos($mime, 'excel') !== false)        return '📊';
        if (strpos($mime, 'presentation') !== false) return '📋';
        if (strpos($mime, 'image') !== false)        return '🖼️';
        return '📎';
    }

    private static function formatSize(int $bytes): string
    {
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    private static function safeFileName(string $name): string
    {
        $name = trim(str_replace(["\r", "\n", "\0"], '', $name));
        return $name === '' ? 'file' : $name;
    }

    private static function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/zip', 'application/x-zip-compressed' => 'zip',
            default => 'bin',
        };
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit;
            }
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            Auth::requireCsrf(); // fix: ganti CSRF::verify() → Auth::requireCsrf()
            $input = $_POST;
        }

        $tl = Database::queryOne("SELECT id FROM tindak_lanjut WHERE id=?", [$id]);
        if (!$tl) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']); exit;
        }

        // Hapus file fisik lampiran sebelum hapus record
        $attachments = Database::query("SELECT file_path FROM tindak_lanjut_attachments WHERE tindak_lanjut_id=?", [$id]);
        foreach ($attachments as $att) {
            $path = ROOT_PATH . $att['file_path'];
            if (file_exists($path)) @unlink($path);
        }

        Database::getInstance()->prepare("DELETE FROM tindak_lanjut WHERE id=?")->execute([$id]);

        $isAdminLike = Auth::hasRole('admin', 'sekretaris');
        $userId      = 0;
        if ($isAdminLike) {
            $userId = (int)($input['user_id'] ?? 0);
            if ($userId > 0) {
                $exists = Database::queryOne("SELECT id FROM users WHERE id=? AND is_active=1", [$userId]);
                if (!$exists) $userId = 0;
            }
        }
        $summary = self::getSummary($isAdminLike, $userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'summary' => $summary]); exit;
    }
}
