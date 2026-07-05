<?php
declare(strict_types=1);

class Auth
{
    private const CSRF_EXEMPT = [
        '/login',
        '/forgot-password',
        '/reset-password',
    ];

    // ── Permissions cache key di session ────────────────────────────
    private const PERM_SESSION_KEY = '_user_permissions';

    // ── Auth checks ──────────────────────────────────────────────────

    public static function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            $uri    = $_SERVER['REQUEST_URI'] ?? '/';
            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
            $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
                      || (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json');
            $isApi  = preg_match('#^(/[^/]+)?/api/#', $uri)
                      || str_contains($uri, '/notification')
                      || str_contains($uri, '/notes')
                      || str_contains($uri, '/status')
                      || str_contains($uri, '/delete')
                      || str_contains($uri, '/store')
                      || str_contains($uri, '/upload');

            if ($method === 'GET' && !$isAjax && !$isApi) {
                $parsed = parse_url($uri);
                if (empty($parsed['host']) || $parsed['host'] === ($_SERVER['HTTP_HOST'] ?? '')) {
                    $_SESSION['redirect_after_login'] = $uri;
                }
            }

            if ($isAjax || $isApi) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesi berakhir. Silakan login kembali.']);
                exit;
            }

            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireLogin(): void
    {
        self::requireAuth();
    }

    /**
     * Wajib role tertentu — 403 jika tidak sesuai.
     * Backward-compatible: Auth::requireRole('admin','sekretaris')
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
            self::abort403();
        }
    }

    public static function hasRole(string ...$roles): bool
    {
        return in_array($_SESSION['user']['role'] ?? '', $roles, true);
    }

    // ── Permission checks (RBAC) ─────────────────────────────────────

    /**
     * Cek apakah user memiliki permission tertentu.
     * Admin selalu true. Permission di-cache di session per login.
     *
     * Contoh: Auth::can('notulen.edit')
     *         Auth::can('meeting.create', 'meeting.edit')  → OR logic
     */
    public static function can(string ...$permissions): bool
    {
        self::requireAuth();
        // Admin bypass semua permission
        if (self::isAdmin()) return true;

        $userPerms = self::getPermissions();
        foreach ($permissions as $p) {
            if (in_array($p, $userPerms, true)) return true;
        }
        return false;
    }

    /**
     * Wajib memiliki permission — abort 403 jika tidak.
     */
    public static function requirePermission(string ...$permissions): void
    {
        self::requireAuth();
        if (!self::can(...$permissions)) {
            self::abort403();
        }
    }

    /**
     * Ambil daftar permission slug milik user saat ini.
     * Di-cache di $_SESSION agar tidak query DB terus.
     * Cache di-invalidate saat role user berubah (lihat invalidatePermissions()).
     */
    public static function getPermissions(): array
    {
        if (isset($_SESSION[self::PERM_SESSION_KEY])) {
            return $_SESSION[self::PERM_SESSION_KEY];
        }
        return self::loadPermissions();
    }

    /**
     * Load permissions dari DB dan simpan ke session.
     * Dipanggil saat login atau setelah role diubah.
     */
    public static function loadPermissions(): array
    {
        $role = self::role();
        if (!$role) {
            $_SESSION[self::PERM_SESSION_KEY] = [];
            return [];
        }
        try {
            $rows = Database::query(
                "SELECT p.name
                 FROM permissions p
                 JOIN role_permissions rp ON rp.perm_id = p.id
                 JOIN roles r ON r.id = rp.role_id
                 WHERE r.name = ?",
                [$role]
            );
            $perms = array_column($rows, 'name');
        } catch (\Throwable) {
            // Tabel belum ada (migrasi belum dijalankan) → fallback ke array kosong
            $perms = [];
        }
        $_SESSION[self::PERM_SESSION_KEY] = $perms;
        return $perms;
    }

    /**
     * Hapus cache permission di session.
     * Panggil ini setelah admin mengubah role/permission user.
     */
    public static function invalidatePermissions(): void
    {
        unset($_SESSION[self::PERM_SESSION_KEY]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

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

    private static function abort403(): never
    {
        http_response_code(403);
        $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
        } else {
            $file = APP_PATH . '/views/errors/403.php';
            if (file_exists($file)) include $file;
            else echo '<h1>403 — Akses Ditolak</h1>';
        }
        exit;
    }

    // ── CSRF Token ───────────────────────────────────────────────────

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken()) . '">';
    }

    public static function csrfMeta(): string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::csrfToken()) . '">';
    }

    public static function verifyCsrf(): bool
    {
        $token   = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        return $session !== '' && hash_equals($session, $token);
    }

    public static function requireCsrf(): void
    {
        if (!self::verifyCsrf()) {
            http_response_code(403);
            $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
                      || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.']);
            } else {
                echo '<h1>403 — Token keamanan tidak valid.</h1><p>Silakan <a href="javascript:history.back()">kembali</a> dan coba lagi.</p>';
            }
            exit;
        }
    }

    public static function csrfExemptPrefixes(): array
    {
        return self::CSRF_EXEMPT;
    }

    // ── Remember-me ──────────────────────────────────────────────────

    public static function checkRememberToken(): void
    {
        if (!empty($_SESSION['user'])) return;
        $raw = $_COOKIE['remember_token'] ?? '';
        if (!$raw) return;

        $hashed = hash('sha256', $raw);

        try {
            $row = Database::queryOne(
                "SELECT u.* FROM users u
                 JOIN user_tokens t ON t.user_id = u.id
                 WHERE t.token_hash = ? AND t.expires_at > NOW() AND u.is_active = 1",
                [$hashed]
            );
            if ($row) {
                $_SESSION['user'] = [
                    'id'       => $row['id'],
                    'username' => $row['username'],
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'role'     => strtolower(trim($row['role'] ?? '')),
                    'avatar'   => $row['avatar'] ?? null,
                ];
                // Load permissions segar setelah login via remember-me
                self::loadPermissions();
                session_regenerate_id(true);
            }
        } catch (\Throwable $e) {
            // silent
        }
    }
}
