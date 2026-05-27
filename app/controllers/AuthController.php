<?php
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
        if (Auth::check()) { header('Location: /'); exit; }
        View::render('layouts/auth', 'auth/login', ['title' => 'Login']);
    }

    /**
     * POST /login
     */
    public static function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Email dan password wajib diisi.';
            header('Location: /login'); exit;
        }

        $user = Database::queryOne(
            "SELECT * FROM users WHERE email=? AND is_active=1", [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            header('Location: /login'); exit;
        }

        // Set session
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

        // Redirect ke halaman sebelumnya jika ada
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
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
        header('Location: /login'); exit;
    }

    /**
     * GET|POST /forgot-password
     */
    public static function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $user  = Database::queryOne("SELECT * FROM users WHERE email=?", [$email]);
            if ($user) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                Database::getInstance()->prepare(
                    "UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?"
                )->execute([$token, $expires, $user['id']]);
                try {
                    Mailer::send($email, 'Reset Password — ' . APP_NAME,
                        "Klik link berikut untuk reset password:\n" . APP_URL . "/reset-password?token={$token}\n\nLink berlaku 1 jam."
                    );
                } catch (\Throwable) {}
            }
            $_SESSION['flash_success'] = 'Jika email terdaftar, link reset akan dikirim ke inbox Anda.';
            header('Location: /forgot-password'); exit;
        }
        View::render('layouts/auth', 'auth/forgot_password', ['title' => 'Lupa Password']);
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
            header('Location: /login'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pass    = $_POST['password']         ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            if (strlen($pass) < 8) {
                $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
                header('Location: /reset-password?token=' . $token); exit;
            }
            if ($pass !== $confirm) {
                $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
                header('Location: /reset-password?token=' . $token); exit;
            }
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::getInstance()->prepare(
                "UPDATE users SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?"
            )->execute([$hash, $user['id']]);
            $_SESSION['flash_success'] = 'Password berhasil direset. Silakan login.';
            header('Location: /login'); exit;
        }

        View::render('layouts/auth', 'auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    // ── Helper ──────────────────────────────────────────────────
    private static function setSession(array $user): void
    {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
    }
}