<?php
declare(strict_types=1);

class ProfileController
{
    public static function show(): void
    {
        Auth::requireAuth();
        $user = Database::queryOne(
            "SELECT u.*, d.name AS dept_name
             FROM users u
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE u.id = ?",
            [Auth::id()]
        );
        $departments = Database::query("SELECT id, name FROM departments WHERE is_active=1 ORDER BY name");
        View::layout('users/profile', [
            'pageTitle'   => 'Profil Saya',
            'profileUser' => $user,
            'departments' => $departments,
        ]);
    }

    public static function update(): void
    {
        Auth::requireAuth();
        $d  = $_POST;
        $id = Auth::id();

        // Cek email duplikat
        $exist = Database::queryOne(
            "SELECT id FROM users WHERE email=? AND id!=?", [trim($d['email'] ?? ''), $id]
        );
        if ($exist) {
            $_SESSION['flash_error'] = 'Email sudah digunakan akun lain.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }

        $db = Database::getInstance();
        if (!empty($d['password'])) {
            if (strlen($d['password']) < 6) {
                $_SESSION['flash_error'] = 'Password minimal 6 karakter.';
                header('Location: ' . BASE_URL . '/profile'); exit;
            }
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare(
                "UPDATE users SET name=?, email=?, password=? WHERE id=?"
            )->execute([trim($d['name'] ?? ''), trim($d['email'] ?? ''), $hash, $id]);
        } else {
            $db->prepare(
                "UPDATE users SET name=?, email=? WHERE id=?"
            )->execute([trim($d['name'] ?? ''), trim($d['email'] ?? ''), $id]);
        }

        // Refresh session
        $fresh = Database::queryOne("SELECT * FROM users WHERE id=?", [$id]);
        $_SESSION['user'] = $fresh;

        $_SESSION['flash_success'] = 'Profil berhasil diperbarui.';
        header('Location: ' . BASE_URL . '/profile'); exit;
    }
}
