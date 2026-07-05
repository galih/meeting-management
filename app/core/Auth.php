<?php
declare(strict_types=1);

class Auth
{
    /**
     * Daftar POST route yang dikecualikan dari verifikasi CSRF otomatis.
     * - /login          : form login (token belum ada di session)
     * - /d/{token}      : public link (no session)
     * - /api/email/...  : cron/webhook tanpa form
     */
    private const CSRF_EXEMPT = [
        '/login',
        '/forgot-password',
        '/reset-password',
    ];

    // ── Auth checks ──────────────────────────────────────────────────

    /**
     * Wajib login — redirect ke /login jika belum.
     */
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
                // Validasi URL sebelum disimpan ke session (cegah open redirect)
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

    public static function can(string ...$roles): bool
    {
        return self::hasRole(...$roles);
    }

    // ── CSRF Token ───────────────────────────────────────────────────

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Hidden input field untuk form HTML */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken()) . '">';
    }

    /** Meta tag untuk dipakai JS fetch (X-CSRF-Token header) */
    public static function csrfMeta(): string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::csrfToken()) . '">';
    }

    /**
     * Verifikasi CSRF — return true/false.
     * Cek header X-CSRF-Token (AJAX) atau field _csrf (form POST).
     */
    public static function verifyCsrf(): bool
    {
        $token   = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        return $session !== '' && hash_equals($session, $token);
    }

    /**
     * Verifikasi CSRF dan abort 403 jika gagal.
     * Gunakan ini di controller yang butuh proteksi eksplisit.
     */
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

    /**
     * Daftar prefix URI yang dikecualikan dari CSRF otomatis di Router.
     */
    public static function csrfExemptPrefixes(): array
    {
        return self::CSRF_EXEMPT;
    }

    // ── Remember-me ──────────────────────────────────────────────────

    /**
     * Remember-me token check.
     * Token di cookie dibandingkan dengan HASH SHA-256 yang tersimpan di tabel user_tokens.
     * JANGAN simpan token plaintext — selalu hash sebelum INSERT/SELECT.
     */
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
                // Rotate session ID setelah login via remember-me
                session_regenerate_id(true);
            }
        } catch (\Throwable $e) {
            // silent — jangan crash hanya karena remember-me gagal
        }
    }
}
