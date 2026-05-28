<?php
class DepartmentController
{
    // ---------------------------------------------------------------
    // Pastikan kolom hierarki sudah ada; jalankan DDL jika belum
    // ---------------------------------------------------------------
    private static function ensureColumns(): void
    {
        $db = Database::getInstance();

        // Cek apakah kolom 'level' sudah ada
        $cols = $db->query("SHOW COLUMNS FROM departments LIKE 'level'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE departments
                ADD COLUMN parent_id INT DEFAULT NULL AFTER id,
                ADD COLUMN level TINYINT UNSIGNED NOT NULL DEFAULT 1
                    COMMENT '1=Unit Kerja, 2=Bidang/Bagian, 3=Sub Bidang/Sub Bagian' AFTER code");
            // FK boleh gagal kalau sudah ada, pakai try-catch
            try {
                $db->exec("ALTER TABLE departments
                    ADD CONSTRAINT fk_dept_parent
                    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL");
            } catch (\Throwable $e) { /* ignore jika sudah ada */ }
        }
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

        // Bangun pohon di PHP
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
