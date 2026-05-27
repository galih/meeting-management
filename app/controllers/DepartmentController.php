<?php
class DepartmentController
{
    public static function index(): void
    {
        Auth::requireRole('admin');
        $departments = Database::query(
            "SELECT d.*, u.name AS head_name,
                    (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active=1) AS total_users
             FROM departments d
             LEFT JOIN users u ON u.id = d.head_id
             WHERE d.is_active = 1
             ORDER BY d.name ASC"
        );
        $allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        View::render('layouts/base', 'departments/index', [
            'title'       => 'Manajemen Departemen',
            'departments' => $departments,
            'allUsers'    => $allUsers,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin');
        $name    = trim($_POST['name'] ?? '');
        $code    = strtoupper(trim($_POST['code'] ?? ''));
        $desc    = trim($_POST['description'] ?? '');
        $headId  = !empty($_POST['head_id']) ? (int)$_POST['head_id'] : null;
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Nama departemen wajib diisi.';
            header('Location: /departments'); exit;
        }
        Database::getInstance()->prepare(
            "INSERT INTO departments (name, code, description, head_id) VALUES (?,?,?,?)"
        )->execute([$name, $code ?: null, $desc, $headId]);
        $_SESSION['flash_success'] = "Departemen {$name} berhasil ditambahkan.";
        header('Location: /departments'); exit;
    }

    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        $name   = trim($_POST['name'] ?? '');
        $code   = strtoupper(trim($_POST['code'] ?? ''));
        $desc   = trim($_POST['description'] ?? '');
        $headId = !empty($_POST['head_id']) ? (int)$_POST['head_id'] : null;
        Database::getInstance()->prepare(
            "UPDATE departments SET name=?, code=?, description=?, head_id=? WHERE id=?"
        )->execute([$name, $code ?: null, $desc, $headId, $id]);
        $_SESSION['flash_success'] = 'Departemen berhasil diupdate.';
        header('Location: /departments'); exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        Database::getInstance()->prepare(
            "UPDATE departments SET is_active=0 WHERE id=?"
        )->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    /** API: daftar departemen untuk dropdown */
    public static function apiList(): void
    {
        Auth::requireAuth();
        $list = Database::query("SELECT id, name, code FROM departments WHERE is_active=1 ORDER BY name");
        header('Content-Type: application/json');
        echo json_encode($list); exit;
    }
}
