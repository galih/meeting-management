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
            $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
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
        $d = $_POST;
        $exist = Database::queryOne("SELECT id FROM users WHERE email=?", [trim($d['email'])]);
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
            "SELECT id FROM users WHERE email=? AND id!=?", [trim($d['email']), $id]
        );
        if ($exist) {
            $_SESSION['flash_error'] = 'Email sudah digunakan.';
            header('Location: ' . BASE_URL . '/users'); exit;
        }
        $db        = Database::getInstance();
        $isActive  = isset($d['is_active']) ? 1 : 0;
        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare(
                "UPDATE users SET name=?, email=?, password=?, role=?, department_id=?, is_active=? WHERE id=?"
            )->execute([
                trim($d['name']), trim($d['email']), $hash, $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $isActive, $id,
            ]);
        } else {
            $db->prepare(
                "UPDATE users SET name=?, email=?, role=?, department_id=?, is_active=? WHERE id=?"
            )->execute([
                trim($d['name']), trim($d['email']), $d['role'],
                !empty($d['department_id']) ? (int)$d['department_id'] : null, $isActive, $id,
            ]);
        }
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
        Database::getInstance()->prepare(
            "UPDATE users SET is_active=0 WHERE id=?"
        )->execute([$id]);
        echo json_encode(['success' => true]); exit;
    }
}
