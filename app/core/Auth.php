<?php
declare(strict_types=1);

class Auth {
    public static function requireLogin(): void {
        if (empty($_SESSION['user'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            http_response_code(403);
            include APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    public static function role(): ?string {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function check(): bool {
        return !empty($_SESSION['user']);
    }

    public static function isAdmin(): bool {
        return self::role() === 'admin';
    }

    public static function can(string ...$roles): bool {
        return in_array(self::role(), $roles, true);
    }
}
