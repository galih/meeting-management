<?php
declare(strict_types=1);

class Auth
{
    /**
     * Wajib login — redirect ke /login jika belum
     */
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /** Alias untuk kompatibilitas */
    public static function requireLogin(): void
    {
        self::requireAuth();
    }

    /**
     * Wajib role tertentu — 403 jika tidak sesuai
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
            http_response_code(403);
            $file = APP_PATH . '/views/errors/403.php';
            if (file_exists($file)) include $file;
            else echo '<h1>403 — Akses Ditolak</h1>';
            exit;
        }
    }

    /**
     * Cek apakah user punya salah satu role (tanpa throw)
     */
    public static function hasRole(string ...$roles): bool
    {
        return in_array($_SESSION['user']['role'] ?? '', $roles, true);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /** Alias untuk hasRole */
    public static function can(string ...$roles): bool
    {
        return self::hasRole(...$roles);
    }

    // ── CSRF Token helpers ─────────────────────────────────────────────

    /**
     * Generate dan simpan CSRF token ke session.
     * Panggil sekali saat awal request (misal di layout).
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Render hidden input CSRF untuk form HTML.
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken()) . '">';
    }

    /**
     * Verifikasi CSRF token dari POST atau header X-CSRF-Token.
     * Lempar exception / return false jika tidak valid.
     */
    public static function verifyCsrf(): bool
    {
        $token   = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        return $session !== '' && hash_equals($session, $token);
    }

    /**
     * Remember-me token check (dipanggil di index.php sebelum routing)
     */
    public static function checkRememberToken(): void
    {
        if (!empty($_SESSION['user'])) return;
        $token = $_COOKIE['remember_token'] ?? '';
        if (!$token) return;
        try {
            $row = Database::queryOne(
                "SELECT u.* FROM users u
                 JOIN user_tokens t ON t.user_id = u.id
                 WHERE t.token = ? AND t.expires_at > NOW() AND u.is_active = 1",
                [$token]
            );
            if ($row) {
                $_SESSION['user'] = [
                    'id'     => $row['id'],
                    'name'   => $row['name'],
                    'email'  => $row['email'],
                    'role'   => $row['role'],
                    'avatar' => $row['avatar'] ?? null,
                ];
            }
        } catch (\Throwable) {}
    }
}
