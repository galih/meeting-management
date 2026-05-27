<?php
class UserController
{
    public static function index(): void
    {
        Auth::requireRole('admin');
        $users = Database::query(
            "SELECT u.*, d.name AS dept_name
             FROM users u
             LEFT JOIN departments d ON d.id = u.department_id
             ORDER BY u.name"
        );
        $departments = Database::query("SELECT id, name FROM departments WHERE is_active=1 ORDER BY name");
        View::layout('users/index', [
            'title'       => 'Manajemen Pengguna',
            'users'       => $users,
            'departments' => $departments,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin');
        $d = $_POST;
        $exist = Database::queryOne("SELECT id FROM users WHERE email=?", [$d['email']]);
        if ($exist) {
            $_SESSION['flash_error'] = 'Email sudah digunakan.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        Database::getInstance()->prepare(
            "INSERT INTO users (name, email, password, role, department_id, is_active)
             VALUES (?,?,?,?,?,1)"
        )->execute([
            trim($d['name']),
            trim($d['email']),
            $hash,
            $d['role'] ?? 'peserta',
            !empty($d['department_id']) ? (int)$d['department_id'] : null,
        ]);
        $_SESSION['flash_success'] = 'Pengguna berhasil ditambahkan.';
        header('Location: ' . BASE_URL . '/users'); exit;
    }

    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        $d = $_POST;
        $exist = Database::queryOne(
            "SELECT id FROM users WHERE email=? AND id!=?", [$d['email'], $id]
        );
        if ($exist) {
            $_SESSION['flash_error'] = 'Email sudah digunakan.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        $db = Database::getInstance();
        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare(
                "UPDATE users SET name=?, email=?, password=?, role=?, department_id=? WHERE id=?"
            )->execute([
                trim($d['name']), trim($d['email']), $hash, $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $id,
            ]);
        } else {
            $db->prepare(
                "UPDATE users SET name=?, email=?, role=?, department_id=? WHERE id=?"
            )->execute([
                trim($d['name']), trim($d['email']), $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $id,
            ]);
        }
        $_SESSION['flash_success'] = 'Data pengguna berhasil diupdate.';
        header('Location: ' . BASE_URL . '/users'); exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        if ($id === Auth::id()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri']); exit;
        }
        Database::getInstance()->prepare(
            "UPDATE users SET is_active=0 WHERE id=?"
        )->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }
}
