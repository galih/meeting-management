<?php
class DepartmentController
{
    // ---------------------------------------------------------------
    // Pastikan kolom hierarki sudah ada; jalankan DDL jika belum
    // ---------------------------------------------------------------
    private static function ensureColumns(): void
    {
        $db = Database::getInstance();

        $cols = $db->query("SHOW COLUMNS FROM departments LIKE 'level'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE departments
                ADD COLUMN parent_id INT DEFAULT NULL AFTER id,
                ADD COLUMN level TINYINT UNSIGNED NOT NULL DEFAULT 1
                    COMMENT '1=Unit Kerja, 2=Bidang/Bagian, 3=Sub Bidang/Sub Bagian' AFTER code");
            try {
                $db->exec("ALTER TABLE departments
                    ADD CONSTRAINT fk_dept_parent
                    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL");
            } catch (\Throwable $e) { /* ignore */ }
        }
    }

    // ---------------------------------------------------------------
    // Pastikan tabel log tersedia (auto-migrate ringan)
    // ---------------------------------------------------------------
    private static function ensureLogTable(): void
    {
        Database::getInstance()->exec("
            CREATE TABLE IF NOT EXISTS department_member_log (
                id            INT PRIMARY KEY AUTO_INCREMENT,
                department_id INT          NOT NULL,
                user_id       INT          NOT NULL,
                action        ENUM('assign','remove') NOT NULL,
                from_dept_id  INT          DEFAULT NULL,
                actor_id      INT          DEFAULT NULL,
                actor_name    VARCHAR(100) DEFAULT NULL,
                note          VARCHAR(255) DEFAULT NULL,
                created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_dml_dept    (department_id),
                INDEX idx_dml_user    (user_id),
                INDEX idx_dml_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // ---------------------------------------------------------------
    // Helper: catat log perpindahan anggota
    // ---------------------------------------------------------------
    private static function logMemberAction(int $deptId, int $userId, string $action, ?int $fromDeptId = null, ?string $note = null): void
    {
        self::ensureLogTable();
        $actorId   = $_SESSION['user_id']   ?? null;
        $actorName = $_SESSION['user_name'] ?? null;
        Database::getInstance()->prepare(
            "INSERT INTO department_member_log
                (department_id, user_id, action, from_dept_id, actor_id, actor_name, note)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([$deptId, $userId, $action, $fromDeptId, $actorId, $actorName, $note]);
    }

    // ---------------------------------------------------------------
    // Halaman manajemen Unit Kerja
    // ---------------------------------------------------------------
    public static function index(): void
    {
        Auth::requireRole('admin');
        self::ensureColumns();

        $rows = Database::query(
            "SELECT d.*,
                    u.name AS head_name,
                    p.name AS parent_name,
                    (SELECT COUNT(*) FROM users
                     WHERE department_id = d.id AND is_active = 1) AS total_users
             FROM departments d
             LEFT JOIN users u ON u.id = d.head_id
             LEFT JOIN departments p ON p.id = d.parent_id
             WHERE d.is_active = 1
             ORDER BY d.level ASC, ISNULL(d.parent_id) DESC, d.parent_id ASC, d.name ASC"
        );

        $tree  = [];
        $index = [];
        foreach ($rows as &$r) {
            $r['children'] = [];
            $index[$r['id']] = &$r;
        }
        unset($r);
        foreach ($rows as &$r) {
            if (!empty($r['parent_id']) && isset($index[$r['parent_id']])) {
                $index[$r['parent_id']]['children'][] = &$r;
            } else {
                $tree[] = &$r;
            }
        }
        unset($r);

        $allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        $parents  = Database::query(
            "SELECT id, name, level FROM departments WHERE is_active=1 AND level < 3 ORDER BY level ASC, name ASC"
        );

        View::layout('departments/index', [
            'pageTitle' => 'Unit Kerja',
            'tree'      => $tree,
            'allUsers'  => $allUsers,
            'parents'   => $parents,
        ]);
    }

    // ---------------------------------------------------------------
    // Tambah unit baru
    // ---------------------------------------------------------------
    public static function store(): void
    {
        Auth::requireRole('admin');
        self::ensureColumns();

        $name     = trim($_POST['name']        ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $desc     = trim($_POST['description'] ?? '');
        $headId   = !empty($_POST['head_id'])   ? (int)$_POST['head_id']   : null;
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Nama unit kerja wajib diisi.';
            header('Location: ' . BASE_URL . '/departments'); exit;
        }

        $level = 1;
        if ($parentId) {
            $parent = Database::queryOne("SELECT level FROM departments WHERE id=?", [$parentId]);
            $level  = $parent ? min((int)$parent['level'] + 1, 3) : 1;
        }

        Database::getInstance()->prepare(
            "INSERT INTO departments (parent_id, name, code, description, head_id, level)
             VALUES (?,?,?,?,?,?)"
        )->execute([$parentId, $name, $code ?: null, $desc, $headId, $level]);

        $_SESSION['flash_success'] = "Unit kerja {$name} berhasil ditambahkan.";
        header('Location: ' . BASE_URL . '/departments'); exit;
    }

    // ---------------------------------------------------------------
    // Update unit
    // ---------------------------------------------------------------
    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        self::ensureColumns();

        $name     = trim($_POST['name']        ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $desc     = trim($_POST['description'] ?? '');
        $headId   = !empty($_POST['head_id'])   ? (int)$_POST['head_id']   : null;
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        $level = 1;
        if ($parentId) {
            $parent = Database::queryOne("SELECT level FROM departments WHERE id=?", [$parentId]);
            $level  = $parent ? min((int)$parent['level'] + 1, 3) : 1;
        }

        Database::getInstance()->prepare(
            "UPDATE departments SET parent_id=?, name=?, code=?, description=?, head_id=?, level=? WHERE id=?"
        )->execute([$parentId, $name, $code ?: null, $desc, $headId, $level, $id]);

        $_SESSION['flash_success'] = 'Unit kerja berhasil diupdate.';
        header('Location: ' . BASE_URL . '/departments'); exit;
    }

    // ---------------------------------------------------------------
    // Soft-delete beserta semua turunan
    // ---------------------------------------------------------------
    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        Database::getInstance()->prepare(
            "UPDATE departments SET is_active=0
             WHERE id=? OR parent_id=?
                OR parent_id IN (SELECT id FROM (SELECT id FROM departments WHERE parent_id=?) AS sub)"
        )->execute([$id, $id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    // ---------------------------------------------------------------
    // Halaman kelola anggota unit kerja
    // GET /departments/{id}/members
    // ---------------------------------------------------------------
    public static function members(int $id): void
    {
        Auth::requireRole('admin');

        $dept = Database::queryOne(
            "SELECT d.*, u.name AS head_name
             FROM departments d
             LEFT JOIN users u ON u.id = d.head_id
             WHERE d.id = ? AND d.is_active = 1",
            [$id]
        );

        if (!$dept) {
            $_SESSION['flash_error'] = 'Unit kerja tidak ditemukan.';
            header('Location: ' . BASE_URL . '/departments'); exit;
        }

        $members = Database::query(
            "SELECT id, name, email FROM users
             WHERE department_id = ? AND is_active = 1
             ORDER BY name ASC",
            [$id]
        );

        $others = Database::query(
            "SELECT id, name, email,
                    COALESCE((SELECT d2.name FROM departments d2 WHERE d2.id = users.department_id), '—') AS dept_name,
                    users.department_id AS current_dept_id
             FROM users
             WHERE (department_id IS NULL OR department_id != ?) AND is_active = 1
             ORDER BY name ASC",
            [$id]
        );

        View::layout('departments/members', [
            'pageTitle' => 'Anggota — ' . $dept['name'],
            'dept'      => $dept,
            'members'   => $members,
            'others'    => $others,
        ]);
    }

    // ---------------------------------------------------------------
    // Assign / pindahkan user ke department ini
    // POST /departments/{id}/assign-member
    // ---------------------------------------------------------------
    public static function assignMember(int $deptId): void
    {
        Auth::requireRole('admin');

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['flash_error'] = 'Pengguna tidak valid.';
            header('Location: ' . BASE_URL . '/departments/' . $deptId . '/members'); exit;
        }

        $dept = Database::queryOne("SELECT id, name FROM departments WHERE id=? AND is_active=1", [$deptId]);
        if (!$dept) {
            $_SESSION['flash_error'] = 'Unit kerja tidak ditemukan.';
            header('Location: ' . BASE_URL . '/departments'); exit;
        }

        // Simpan dept asal sebelum diupdate (untuk log)
        $userRow    = Database::queryOne("SELECT department_id FROM users WHERE id=?", [$userId]);
        $fromDeptId = $userRow ? $userRow['department_id'] : null;

        Database::getInstance()->prepare(
            "UPDATE users SET department_id = ? WHERE id = ?"
        )->execute([$deptId, $userId]);

        // Catat log
        self::logMemberAction($deptId, $userId, 'assign', $fromDeptId);

        $_SESSION['flash_success'] = 'Anggota berhasil ditambahkan ke ' . $dept['name'] . '.';
        header('Location: ' . BASE_URL . '/departments/' . $deptId . '/members'); exit;
    }

    // ---------------------------------------------------------------
    // Lepas user dari department (set department_id = NULL)
    // POST /departments/{id}/remove-member
    // ---------------------------------------------------------------
    public static function removeMember(int $deptId): void
    {
        Auth::requireRole('admin');

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['flash_error'] = 'Pengguna tidak valid.';
            header('Location: ' . BASE_URL . '/departments/' . $deptId . '/members'); exit;
        }

        Database::getInstance()->prepare(
            "UPDATE users SET department_id = NULL WHERE id = ? AND department_id = ?"
        )->execute([$userId, $deptId]);

        // Catat log
        self::logMemberAction($deptId, $userId, 'remove', $deptId);

        $_SESSION['flash_success'] = 'Anggota berhasil dilepas dari unit kerja.';
        header('Location: ' . BASE_URL . '/departments/' . $deptId . '/members'); exit;
    }

    // ---------------------------------------------------------------
    // Riwayat perpindahan anggota
    // GET /departments/{id}/log
    // ---------------------------------------------------------------
    public static function memberLog(int $id): void
    {
        Auth::requireRole('admin');
        self::ensureLogTable();

        $dept = Database::queryOne(
            "SELECT d.*, u.name AS head_name
             FROM departments d
             LEFT JOIN users u ON u.id = d.head_id
             WHERE d.id = ? AND d.is_active = 1",
            [$id]
        );

        if (!$dept) {
            $_SESSION['flash_error'] = 'Unit kerja tidak ditemukan.';
            header('Location: ' . BASE_URL . '/departments'); exit;
        }

        $logs = Database::query(
            "SELECT l.*,
                    u.name  AS user_name,
                    u.email AS user_email,
                    fd.name AS from_dept_name
             FROM department_member_log l
             LEFT JOIN users       u  ON u.id  = l.user_id
             LEFT JOIN departments fd ON fd.id = l.from_dept_id
             WHERE l.department_id = ?
             ORDER BY l.created_at DESC
             LIMIT 200",
            [$id]
        );

        // Statistik rapat unit ini
        $meetingStats = Database::queryOne(
            "SELECT
                COUNT(*) AS total_meetings,
                SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) AS done_meetings,
                SUM(CASE WHEN status='scheduled' OR status='ongoing' THEN 1 ELSE 0 END) AS upcoming_meetings
             FROM meetings
             WHERE department_id = ?",
            [$id]
        );

        View::layout('departments/history', [
            'pageTitle'    => 'Riwayat Anggota — ' . $dept['name'],
            'dept'         => $dept,
            'logs'         => $logs,
            'meetingStats' => $meetingStats,
        ]);
    }

    // ---------------------------------------------------------------
    // API: statistik rapat per unit
    // GET /api/departments/{id}/stats
    // ---------------------------------------------------------------
    public static function apiStats(int $id): void
    {
        Auth::requireAuth();

        $dept = Database::queryOne(
            "SELECT id, name, level FROM departments WHERE id=? AND is_active=1", [$id]
        );
        if (!$dept) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']); exit;
        }

        $stats = Database::queryOne(
            "SELECT
                COUNT(*)  AS total_meetings,
                SUM(CASE WHEN status='done'      THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN status='scheduled' THEN 1 ELSE 0 END) AS scheduled,
                SUM(CASE WHEN status='ongoing'   THEN 1 ELSE 0 END) AS ongoing,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled
             FROM meetings WHERE department_id = ?",
            [$id]
        );

        $memberCount = Database::queryOne(
            "SELECT COUNT(*) AS total FROM users WHERE department_id=? AND is_active=1", [$id]
        );

        header('Content-Type: application/json');
        echo json_encode([
            'department'   => $dept,
            'meetings'     => $stats,
            'member_count' => (int)($memberCount['total'] ?? 0),
        ]);
        exit;
    }

    // ---------------------------------------------------------------
    // API flat list (dropdown di meeting/user)
    // ---------------------------------------------------------------
    public static function apiList(): void
    {
        Auth::requireAuth();
        self::ensureColumns();
        $list = Database::query(
            "SELECT id, name, code, level, parent_id
             FROM departments
             WHERE is_active=1
             ORDER BY level ASC, parent_id ASC, name ASC"
        );
        header('Content-Type: application/json');
        echo json_encode($list); exit;
    }

    // ---------------------------------------------------------------
    // API children cascade
    // GET /api/departments/children?parent_id=X
    // ---------------------------------------------------------------
    public static function apiChildren(): void
    {
        Auth::requireAuth();
        $parentId = (int)($_GET['parent_id'] ?? 0);
        $list = Database::query(
            "SELECT id, name, level FROM departments
             WHERE parent_id=? AND is_active=1
             ORDER BY name ASC",
            [$parentId]
        );
        header('Content-Type: application/json');
        echo json_encode($list); exit;
    }
}
