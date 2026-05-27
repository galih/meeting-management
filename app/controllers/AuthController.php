<?php
declare(strict_types=1);

class AuthController
{
    /**
     * Cek remember token saat app boot
     */
    public static function checkRememberToken(): void
    {
        if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user  = Database::queryOne(
                "SELECT * FROM users WHERE remember_token=? AND is_active=1", [$token]
            );
            if ($user) self::setSession($user);
        }
    }

    /**
     * GET /login
     */
    public static function loginForm(): void
    {
        if (Auth::check()) { header('Location: ' . BASE_URL . '/'); exit; }
        View::layout('auth/login', ['title' => 'Login']);
    }

    /**
     * POST /login
     */
    public static function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']       ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = 'Username dan password wajib diisi.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $user = Database::queryOne(
            "SELECT * FROM users WHERE username=? AND is_active=1", [$username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash_error'] = 'Username atau password salah.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        self::setSession($user);

        // Remember me — 30 hari
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            Database::getInstance()->prepare(
                "UPDATE users SET remember_token=? WHERE id=?"
            )->execute([$token, $user['id']]);
            setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', false, true);
        }

        // Update last login
        Database::getInstance()->prepare(
            "UPDATE users SET last_login=NOW() WHERE id=?"
        )->execute([$user['id']]);

        $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect); exit;
    }

    /**
     * GET /logout
     */
    public static function logout(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            Database::getInstance()->prepare(
                "UPDATE users SET remember_token=NULL WHERE id=?"
            )->execute([Auth::id()]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/login'); exit;
    }

    /**
     * GET|POST /forgot-password
     */
    public static function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $user     = Database::queryOne("SELECT * FROM users WHERE username=?", [$username]);
            if ($user) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                Database::getInstance()->prepare(
                    "UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?"
                )->execute([$token, $expires, $user['id']]);
                try {
                    Mailer::send($user['email'], 'Reset Password — ' . APP_NAME,
                        "Halo {$user['name']},\n\nKlik link berikut untuk reset password:\n"
                        . APP_URL . "/reset-password?token={$token}\n\nLink berlaku 1 jam."
                    );
                } catch (\Throwable) {}
            }
            $_SESSION['flash_success'] = 'Jika username terdaftar, link reset akan dikirim ke email Anda.';
            header('Location: ' . BASE_URL . '/forgot-password'); exit;
        }
        View::layout('auth/forgot_password', ['title' => 'Lupa Password']);
    }

    /**
     * GET|POST /reset-password
     */
    public static function resetPassword(): void
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $user  = Database::queryOne(
            "SELECT * FROM users WHERE reset_token=? AND reset_token_expires > NOW()", [$token]
        );

        if (!$user) {
            $_SESSION['flash_error'] = 'Link reset tidak valid atau sudah kadaluarsa.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pass    = $_POST['password']         ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            if (strlen($pass) < 8) {
                $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
                header('Location: ' . BASE_URL . '/reset-password?token=' . $token); exit;
            }
            if ($pass !== $confirm) {
                $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
                header('Location: ' . BASE_URL . '/reset-password?token=' . $token); exit;
            }
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::getInstance()->prepare(
                "UPDATE users SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?"
            )->execute([$hash, $user['id']]);
            $_SESSION['flash_success'] = 'Password berhasil direset. Silakan login.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        View::layout('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    // ── Helper ──────────────────────────────────────────────────
    private static function setSession(array $user): void
    {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
    }
}
