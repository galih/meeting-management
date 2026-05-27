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
            header('Location: /login');
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
}
