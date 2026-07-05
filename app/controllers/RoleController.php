<?php
declare(strict_types=1);

class RoleController
{
    // ── GET /roles ──────────────────────────────────────────────────
    public static function index(): void
    {
        Auth::requireRole('admin');

        $roles = Database::query(
            "SELECT r.*,
                    COUNT(DISTINCT u.id)  AS user_count,
                    COUNT(DISTINCT rp.perm_id) AS perm_count
             FROM roles r
             LEFT JOIN users u  ON u.role = r.name AND u.is_active = 1
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             GROUP BY r.id
             ORDER BY r.id"
        );

        $permissions = Database::query(
            "SELECT * FROM permissions ORDER BY module, name"
        );

        // Buat map: role_id => [perm_name, ...]
        $rolePerms = [];
        $pivotRows = Database::query(
            "SELECT rp.role_id, p.name AS perm_name
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.perm_id"
        );
        foreach ($pivotRows as $row) {
            $rolePerms[$row['role_id']][] = $row['perm_name'];
        }

        // Group permissions by module
        $modules = [];
        foreach ($permissions as $p) {
            $modules[$p['module']][] = $p;
        }

        View::layout('roles/index', [
            'pageTitle'   => 'Manajemen Role & Permission',
            'roles'       => $roles,
            'permissions' => $permissions,
            'modules'     => $modules,
            'rolePerms'   => $rolePerms,
        ]);
    }

    // ── POST /roles ─────────────────────────────────────────────────
    public static function store(): void
    {
        Auth::requireRole('admin');
        $name  = strtolower(trim(preg_replace('/[^a-z0-9_]/i', '', $_POST['name'] ?? '')));
        $label = trim($_POST['label'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');

        if (!$name || !$label) {
            $_SESSION['flash_error'] = 'Nama dan label role wajib diisi.';
            header('Location: ' . BASE_URL . '/roles'); exit;
        }
        if (strlen($name) > 50) {
            $_SESSION['flash_error'] = 'Nama role maksimal 50 karakter.';
            header('Location: ' . BASE_URL . '/roles'); exit;
        }
        if (Database::queryOne("SELECT id FROM roles WHERE name=?", [$name])) {
            $_SESSION['flash_error'] = "Role '{$name}' sudah ada.";
            header('Location: ' . BASE_URL . '/roles'); exit;
        }

        Database::getInstance()->prepare(
            "INSERT INTO roles (name, label, color, is_system) VALUES (?,?,?,0)"
        )->execute([$name, $label, $color]);

        ActivityLog::record('role.create', "Membuat role baru: {$name} ({$label})", 'role');

        $_SESSION['flash_success'] = "Role '{$label}' berhasil dibuat.";
        header('Location: ' . BASE_URL . '/roles'); exit;
    }

    // ── POST /roles/{id}/update ──────────────────────────────────────
    public static function update(int $id): void
    {
        Auth::requireRole('admin');

        $role = Database::queryOne("SELECT * FROM roles WHERE id=?", [$id]);
        if (!$role) {
            $_SESSION['flash_error'] = 'Role tidak ditemukan.';
            header('Location: ' . BASE_URL . '/roles'); exit;
        }
        if ($role['is_system']) {
            $_SESSION['flash_error'] = 'Role system tidak dapat diubah nama/slugnya.';
            header('Location: ' . BASE_URL . '/roles'); exit;
        }

        $label = trim($_POST['label'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');
        if (!$label) {
            $_SESSION['flash_error'] = 'Label role wajib diisi.';
            header('Location: ' . BASE_URL . '/roles'); exit;
        }

        Database::getInstance()->prepare(
            "UPDATE roles SET label=?, color=? WHERE id=?"
        )->execute([$label, $color, $id]);

        ActivityLog::record('role.update', "Mengubah role ID {$id}: label={$label}", 'role', $id);
        $_SESSION['flash_success'] = 'Role berhasil diupdate.';
        header('Location: ' . BASE_URL . '/roles'); exit;
    }

    // ── POST /roles/{id}/delete ──────────────────────────────────────
    public static function delete(int $id): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $role = Database::queryOne("SELECT * FROM roles WHERE id=?", [$id]);
        if (!$role) { echo json_encode(['success'=>false,'message'=>'Role tidak ditemukan.']); exit; }
        if ($role['is_system']) { echo json_encode(['success'=>false,'message'=>'Role system tidak dapat dihapus.']); exit; }

        // Pastikan tidak ada user yang memakai role ini
        $count = (int)(Database::queryOne(
            "SELECT COUNT(*) c FROM users WHERE role=? AND is_active=1", [$role['name']]
        )['c'] ?? 0);
        if ($count > 0) {
            echo json_encode(['success'=>false,'message'=>"Masih ada {$count} pengguna aktif dengan role ini. Pindahkan dulu sebelum menghapus."]);
            exit;
        }

        Database::getInstance()->prepare("DELETE FROM roles WHERE id=?")->execute([$id]);
        ActivityLog::record('role.delete', "Menghapus role: {$role['name']} ({$role['label']})", 'role', $id);
        echo json_encode(['success'=>true]);
        exit;
    }

    // ── POST /api/roles/{id}/permissions ────────────────────────────
    // Body: { permissions: ['meeting.view','notulen.edit', ...] }
    public static function syncPermissions(int $id): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $role = Database::queryOne("SELECT * FROM roles WHERE id=?", [$id]);
        if (!$role) { echo json_encode(['success'=>false,'message'=>'Role tidak ditemukan.']); exit; }

        // Terima JSON body atau POST form
        $input = json_decode(file_get_contents('php://input'), true);
        $permNames = $input['permissions'] ?? $_POST['permissions'] ?? [];
        if (!is_array($permNames)) $permNames = [];

        // Admin role: tidak bisa di-restrict
        if ($role['is_system'] && $role['name'] === 'admin') {
            echo json_encode(['success'=>false,'message'=>'Permission role admin tidak dapat diubah.']);
            exit;
        }

        $db = Database::getInstance();

        // Hapus semua permission lama untuk role ini
        $db->prepare("DELETE FROM role_permissions WHERE role_id=?")->execute([$id]);

        // Insert permission baru
        $inserted = 0;
        if (!empty($permNames)) {
            $placeholders = implode(',', array_fill(0, count($permNames), '?'));
            $perms = Database::query(
                "SELECT id, name FROM permissions WHERE name IN ({$placeholders})",
                $permNames
            );
            $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, perm_id) VALUES (?,?)");
            foreach ($perms as $p) {
                $stmt->execute([$id, $p['id']]);
                $inserted++;
            }
        }

        // Invalidate permission cache semua user yang punya role ini
        // (session server-side; jika pakai file session, tidak bisa batch-invalidate.
        //  Solusi: simpan role_version di tabel roles, bandingkan di Auth::getPermissions())
        // Untuk sekarang: update versi role agar cache session di-miss
        $db->prepare("UPDATE roles SET updated_at=NOW() WHERE id=?")->execute([$id]);

        ActivityLog::record(
            'role.permissions',
            "Sync permission role '{$role['name']}': {$inserted} permission aktif",
            'role', $id
        );

        echo json_encode(['success'=>true,'message'=>"Permission berhasil disimpan ({$inserted} aktif)."]);
        exit;
    }

    // ── GET /api/roles ───────────────────────────────────────────────
    public static function apiList(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $roles = Database::query("SELECT id, name, label, color FROM roles ORDER BY id");
        echo json_encode(['success'=>true,'roles'=>$roles]);
        exit;
    }
}
