<?php
declare(strict_types=1);

class AuthController
{
    /** @deprecated Gunakan Auth::checkRememberToken() di Auth.php */
    public static function checkRememberToken(): void
    {
        Auth::checkRememberToken();
    }

    public static function loginForm(): void
    {
        if (Auth::check()) { header('Location: ' . BASE_URL . '/'); exit; }
        View::standalone('auth/login', ['title' => 'Login']);
    }

    public static function login(): void
    {
        // ── Rate limiting: maks 10 percobaan per 15 menit per IP ──
        $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimKey = 'login_attempts_' . md5($ip);
        $attempts   = $_SESSION[$rateLimKey] ?? ['count' => 0, 'until' => 0];

        if ($attempts['count'] >= 10 && time() < $attempts['until']) {
            $wait = ceil(($attempts['until'] - time()) / 60);
            $_SESSION['login_error'] = "Terlalu banyak percobaan login. Coba lagi dalam {$wait} menit.";
            header('Location: ' . BASE_URL . '/login'); exit;
        }
        if (time() >= ($attempts['until'] ?? 0)) {
            $attempts = ['count' => 0, 'until' => 0];
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $user = Database::queryOne(
            "SELECT * FROM users WHERE username=? AND is_active=1", [$username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts['count']++;
            if ($attempts['count'] >= 10) {
                $attempts['until'] = time() + 15 * 60;
            }
            $_SESSION[$rateLimKey] = $attempts;

            ActivityLog::record(
                'auth.failed',
                "Login gagal untuk username: {$username} dari IP: {$ip}",
                'auth'
            );
            $_SESSION['login_error'] = 'Username atau password salah.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        unset($_SESSION[$rateLimKey]);

        session_regenerate_id(true);
        self::setSession($user);

        if ($remember) {
            $raw    = bin2hex(random_bytes(32));
            $hashed = hash('sha256', $raw);
            $expiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

            Database::getInstance()->prepare(
                "INSERT INTO user_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash), expires_at=VALUES(expires_at)"
            )->execute([$user['id'], $hashed, $expiry]);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                       || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
            setcookie('remember_token', $raw, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        Database::getInstance()->prepare(
            "UPDATE users SET last_login=NOW() WHERE id=?"
        )->execute([$user['id']]);

        ActivityLog::record(
            'auth.login',
            "Login berhasil: {$user['name']} ({$user['username']}) dari IP: {$ip}",
            'auth',
            (int)$user['id']
        );

        $redirect = $_SESSION['redirect_after_login'] ?? '';
        unset($_SESSION['redirect_after_login']);
        if (!$redirect || !self::isSafeRedirect($redirect)) {
            $redirect = BASE_URL . '/';
        }
        header('Location: ' . $redirect); exit;
    }

    public static function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::record(
                'auth.logout',
                "Logout: {$user['name']} ({$user['username']})",
                'auth',
                (int)$user['id']
            );
        }

        if (isset($_COOKIE['remember_token'])) {
            $hashed = hash('sha256', $_COOKIE['remember_token']);
            Database::getInstance()->prepare(
                "DELETE FROM user_tokens WHERE user_id=? AND token_hash=?"
            )->execute([Auth::id(), $hashed]);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie('remember_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/login'); exit;
    }

    public static function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim(strtolower($_POST['email'] ?? ''));

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = Database::queryOne(
                    "SELECT * FROM users WHERE LOWER(email)=? AND is_active=1",
                    [$email]
                );
                if ($user) {
                    $token   = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    Database::getInstance()->prepare(
                        "UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?"
                    )->execute([$token, $expires, $user['id']]);
                    try {
                        Mailer::send(
                            $user['email'],
                            'Reset Password — ' . APP_NAME,
                            "Halo {$user['name']},\n\nKlik link berikut untuk reset password:\n"
                            . BASE_URL . "/reset-password?token={$token}\n\nLink berlaku 1 jam."
                        );
                    } catch (\Throwable $e) {
                        // silent — email gagal tidak menghentikan flow
                    }
                }
            }

            // Pesan generik agar tidak mengungkap apakah email terdaftar
            $_SESSION['flash_success'] = 'Jika email terdaftar, link reset akan dikirim ke inbox Anda.';
            header('Location: ' . BASE_URL . '/forgot-password'); exit;
        }
        View::standalone('auth/forgot-password', ['title' => 'Lupa Password']);
    }

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
                header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token)); exit;
            }
            if ($pass !== $confirm) {
                $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
                header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token)); exit;
            }
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::getInstance()->prepare(
                "UPDATE users SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?"
            )->execute([$hash, $user['id']]);
            // Invalidate semua remember-me token setelah reset password
            Database::getInstance()->prepare(
                "DELETE FROM user_tokens WHERE user_id=?"
            )->execute([$user['id']]);
            $_SESSION['flash_success'] = 'Password berhasil direset. Silakan login.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        View::standalone('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
            'reset' => $user,
        ]);
    }

    private static function setSession(array $user): void
    {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'role'     => strtolower(trim($user['role'] ?? '')),
        ];
    }

    private static function isSafeRedirect(string $url): bool
    {
        $parsed = parse_url($url);
        if (isset($parsed['host'])) {
            return $parsed['host'] === ($_SERVER['HTTP_HOST'] ?? '');
        }
        return isset($parsed['path']) && str_starts_with($parsed['path'], '/');
    }
}
