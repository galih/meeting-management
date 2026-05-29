<?php
class UserController
{
    private const PER_PAGE = 10;

    public static function index(): void
    {
        Auth::requireRole('admin');
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $where  = ['u.is_active IN (0,1)'];
        $params = [];
        if ($search) {
            $where[]  = '(u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        $whereStr = implode(' AND ', $where);

        $total = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM users u WHERE {$whereStr}", $params
        )['c'] ?? 0);

        $users = Database::query(
            "SELECT u.*, d.name AS dept_name
             FROM users u
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE {$whereStr}
             ORDER BY u.name
             LIMIT ? OFFSET ?",
            array_merge($params, [self::PER_PAGE, $offset])
        );

        $departments = Database::query("SELECT id, name FROM departments WHERE is_active=1 ORDER BY name");
        $totalPage   = (int)ceil($total / self::PER_PAGE);

        View::layout('users/index', [
            'pageTitle'   => 'Manajemen Pengguna',
            'users'       => $users,
            'departments' => $departments,
            'search'      => $search,
            'page'        => $page,
            'total'       => $total,
            'totalPage'   => $totalPage,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin');
        $d        = $_POST;
        $username = trim($d['username'] ?? '');
        $email    = trim($d['email'] ?? '');

        if (!$username) {
            $_SESSION['flash_error'] = 'Username wajib diisi.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        if (Database::queryOne("SELECT id FROM users WHERE username=?", [$username])) {
            $_SESSION['flash_error'] = 'Username sudah digunakan.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        if (Database::queryOne("SELECT id FROM users WHERE email=?", [$email])) {
            $_SESSION['flash_error'] = 'Email sudah digunakan.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }

        $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $db   = Database::getInstance();
        $db->prepare(
            "INSERT INTO users (username, name, email, password, role, department_id, is_active)
             VALUES (?,?,?,?,?,?,1)"
        )->execute([
            $username, trim($d['name']), $email, $hash,
            $d['role'] ?? 'peserta',
            !empty($d['department_id']) ? (int)$d['department_id'] : null,
        ]);
        $newId = (int)$db->lastInsertId();

        ActivityLog::record(
            'user.create',
            "Menambahkan user baru: {$d['name']} ({$username}) — role: " . ($d['role'] ?? 'peserta'),
            'user', $newId
        );

        $_SESSION['flash_success'] = 'Pengguna berhasil ditambahkan.';
        header('Location: ' . BASE_URL . '/users'); exit;
    }

    public static function update(int $id): void
    {
        Auth::requireRole('admin');
        $d        = $_POST;
        $username = trim($d['username'] ?? '');
        $email    = trim($d['email']    ?? '');

        if (!$username) {
            $_SESSION['flash_error'] = 'Username wajib diisi.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        if (Database::queryOne("SELECT id FROM users WHERE username=? AND id!=?", [$username, $id])) {
            $_SESSION['flash_error'] = 'Username sudah digunakan akun lain.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        if (Database::queryOne("SELECT id FROM users WHERE email=? AND id!=?", [$email, $id])) {
            $_SESSION['flash_error'] = 'Email sudah digunakan akun lain.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }

        $db       = Database::getInstance();
        $isActive = isset($d['is_active']) ? 1 : 0;

        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare(
                "UPDATE users SET username=?, name=?, email=?, password=?, role=?, department_id=?, is_active=? WHERE id=?"
            )->execute([
                $username, trim($d['name']), $email, $hash, $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $isActive, $id,
            ]);
        } else {
            $db->prepare(
                "UPDATE users SET username=?, name=?, email=?, role=?, department_id=?, is_active=? WHERE id=?"
            )->execute([
                $username, trim($d['name']), $email, $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $isActive, $id,
            ]);
        }

        ActivityLog::record(
            'user.update',
            "Mengubah data user: {$d['name']} ({$username}) — role: {$d['role']}",
            'user', $id
        );

        $_SESSION['flash_success'] = 'Data pengguna berhasil diupdate.';
        header('Location: ' . BASE_URL . '/users'); exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        if ($id === Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'Tidak bisa menonaktifkan akun sendiri']); exit;
        }
        $user = Database::queryOne("SELECT name, username FROM users WHERE id=?", [$id]);
        Database::getInstance()->prepare(
            "UPDATE users SET is_active=0 WHERE id=?"
        )->execute([$id]);

        if ($user) {
            ActivityLog::record(
                'user.update',
                "Menonaktifkan akun: {$user['name']} ({$user['username']})",
                'user', $id
            );
        }

        echo json_encode(['success' => true]); exit;
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        if ($id === Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri']); exit;
        }

        $user = Database::queryOne("SELECT id, name, username FROM users WHERE id=?", [$id]);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']); exit;
        }

        try {
            Database::getInstance()->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            ActivityLog::record(
                'user.delete',
                "Menghapus user permanen: {$user['name']} ({$user['username']})",
                'user', $id
            );
            echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
        } catch (\PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak dapat dihapus karena user memiliki data terkait. Nonaktifkan saja.'
            ]);
        }
        exit;
    }
}
