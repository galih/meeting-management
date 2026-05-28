<?php
declare(strict_types=1);

class ProfileController
{
    /** GET /profile */
    public static function index(): void
    {
        Auth::requireAuth();
        $user      = Database::queryOne("SELECT id, name, email, role, department_id FROM users WHERE id = ?", [Auth::id()]);
        $pageTitle = 'Profil Saya';
        View::layout('profile/index', compact('user', 'pageTitle'));
    }

    /** POST /profile/update */
    public static function update(): void
    {
        Auth::requireAuth();

        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['flash_error'] = 'Nama dan email tidak boleh kosong.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Format email tidak valid.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }

        // Cek email duplikat (selain milik sendiri)
        $existing = Database::queryOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, Auth::id()]
        );
        if ($existing) {
            $_SESSION['flash_error'] = 'Email sudah digunakan oleh akun lain.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }

        Database::getInstance()->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        )->execute([$name, $email, Auth::id()]);

        // Perbarui session
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['email'] = $email;

        $_SESSION['flash_success'] = 'Profil berhasil diperbarui.';
        header('Location: ' . BASE_URL . '/profile'); exit;
    }

    /** POST /profile/change-password */
    public static function changePassword(): void
    {
        Auth::requireAuth();

        $current  = $_POST['current_password']  ?? '';
        $new      = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $_SESSION['flash_error'] = 'Semua kolom password wajib diisi.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }
        if (strlen($new) < 6) {
            $_SESSION['flash_error'] = 'Password baru minimal 6 karakter.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }
        if ($new !== $confirm) {
            $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }

        $user = Database::queryOne("SELECT password FROM users WHERE id = ?", [Auth::id()]);
        if (!$user || !password_verify($current, $user['password'])) {
            $_SESSION['flash_error'] = 'Password saat ini salah.';
            header('Location: ' . BASE_URL . '/profile'); exit;
        }

        Database::getInstance()->prepare(
            "UPDATE users SET password = ? WHERE id = ?"
        )->execute([password_hash($new, PASSWORD_DEFAULT), Auth::id()]);

        $_SESSION['flash_success'] = 'Password berhasil diubah.';
        header('Location: ' . BASE_URL . '/profile'); exit;
    }
}
