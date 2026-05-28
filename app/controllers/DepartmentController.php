<?php
class DepartmentController
{
    // ----------------------------------------------------------------
    // Halaman manajemen Unit Kerja
    // ----------------------------------------------------------------
    public static function index(): void
    {
        Auth::requireRole('admin');

        // Ambil semua node aktif sekaligus, susun pohon di PHP
        $rows = Database::query(
            "SELECT d.*,
                    u.name  AS head_name,
                    p.name  AS parent_name,
                    (SELECT COUNT(*) FROM users
                     WHERE department_id = d.id AND is_active = 1) AS total_users
             FROM departments d
             LEFT JOIN users u ON u.id = d.head_id
             LEFT JOIN departments p ON p.id = d.parent_id
             WHERE d.is_active = 1
             ORDER BY d.level ASC, d.parent_id ASC, d.name ASC"
        );

        // Bangun pohon: [level1 => [..., children => [level2 => [..., children => [level3]]]]]
        $tree  = [];
        $index = [];
        foreach ($rows as &$r) {
            $r['children'] = [];
            $index[$r['id']] = &$r;
        }
        unset($r);
        foreach ($rows as &$r) {
            if ($r['parent_id'] && isset($index[$r['parent_id']])) {
                $index[$r['parent_id']]['children'][] = &$r;
            } else {
                $tree[] = &$r;
            }
        }
        unset($r);

        $allUsers  = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        // Untuk dropdown parent: hanya level 1 & 2
        $parents   = Database::query(
            "SELECT id, name, level FROM departments WHERE is_active=1 AND level < 3 ORDER BY level, name"
        );

        View::layout('departments/index', [
            'pageTitle'  => 'Unit Kerja',
            'tree'       => $tree,
            'allUsers'   => $allUsers,
            'parents'    => $parents,
        ]);
    }

    // ----------------------------------------------------------------
    // Tambah unit baru
    // ----------------------------------------------------------------
    public static function store(): void
    {
        Auth::requireRole('admin');
        $name     = trim($_POST['name']     ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $desc     = trim($_POST['description'] ?? '');
        $headId   = !empty($_POST['head_id'])   ? (int)$_POST['head_id']   : null;
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Nama unit kerja wajib diisi.';
            header('Location: ' . BASE_URL . '/departments'); exit;
        }

        // Tentukan level dari parent
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

    // ----------------------------------------------------------------
    // Update unit
    // ----------------------------------------------------------------
    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        $name     = trim($_POST['name']     ?? '');
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

    // ----------------------------------------------------------------
    // Soft-delete (nonaktifkan beserta semua turunannya)
    // ----------------------------------------------------------------
    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        // Nonaktifkan node ini dan semua anaknya (hingga 2 level)
        Database::getInstance()->prepare(
            "UPDATE departments SET is_active=0
             WHERE id=? OR parent_id=?
                OR parent_id IN (SELECT id FROM (SELECT id FROM departments WHERE parent_id=?) AS sub)"
        )->execute([$id, $id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    // ----------------------------------------------------------------
    // API list (untuk dropdown di form meeting/user dsb.)
    // Mengembalikan flat list dengan indikasi level untuk grouping
    // ----------------------------------------------------------------
    public static function apiList(): void
    {
        Auth::requireAuth();
        $list = Database::query(
            "SELECT id, name, code, level, parent_id
             FROM departments
             WHERE is_active=1
             ORDER BY level ASC, parent_id ASC, name ASC"
        );
        header('Content-Type: application/json');
        echo json_encode($list); exit;
    }

    // ----------------------------------------------------------------
    // API: ambil sub-unit berdasarkan parent (untuk dropdown cascade)
    // GET /api/departments/children?parent_id=X
    // ----------------------------------------------------------------
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
