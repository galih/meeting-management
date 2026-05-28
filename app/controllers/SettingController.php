<?php
declare(strict_types=1);

class SettingController
{
    private static function getSetting(string $key, string $default = ''): string
    {
        $row = Database::queryOne("SELECT value FROM app_settings WHERE `key`=?", [$key]);
        return $row ? $row['value'] : $default;
    }

    private static function setSetting(string $key, string $value): void
    {
        $exists = Database::queryOne("SELECT id FROM app_settings WHERE `key`=?", [$key]);
        if ($exists) {
            Database::getInstance()->prepare("UPDATE app_settings SET value=?, updated_at=NOW() WHERE `key`=?")
                ->execute([$value, $key]);
        } else {
            Database::getInstance()->prepare("INSERT INTO app_settings (`key`, value) VALUES (?,?)")
                ->execute([$key, $value]);
        }
    }

    public static function index(): void
    {
        Auth::requireRole('admin');
        $settings = [
            'app_logo'            => self::getSetting('app_logo'),
            'login_bg'            => self::getSetting('login_bg'),
            'app_name_custom'     => self::getSetting('app_name_custom', APP_NAME),
        ];
        View::layout('settings/index', [
            'pageTitle' => 'Pengaturan Aplikasi',
            'settings'  => $settings,
        ]);
    }

    public static function uploadLogo(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $file = $_FILES['logo'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File tidak valid']); exit;
        }
        $result = self::handleUpload($file, 'logo');
        if (!$result['success']) { echo json_encode($result); exit; }

        // Hapus logo lama
        $old = self::getSetting('app_logo');
        self::deleteOldFile($old);
        self::setSetting('app_logo', $result['path']);
        echo json_encode(['success' => true, 'path' => $result['path']]); exit;
    }

    public static function uploadLoginBg(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $file = $_FILES['login_bg'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File tidak valid']); exit;
        }
        $result = self::handleUpload($file, 'login-bg');
        if (!$result['success']) { echo json_encode($result); exit; }

        $old = self::getSetting('login_bg');
        self::deleteOldFile($old);
        self::setSetting('login_bg', $result['path']);
        echo json_encode(['success' => true, 'path' => $result['path']]); exit;
    }

    public static function removeLogo(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $old = self::getSetting('app_logo');
        self::deleteOldFile($old);
        self::setSetting('app_logo', '');
        echo json_encode(['success' => true]); exit;
    }

    public static function removeLoginBg(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $old = self::getSetting('login_bg');
        self::deleteOldFile($old);
        self::setSetting('login_bg', '');
        echo json_encode(['success' => true]); exit;
    }

    // ── Helper ───────────────────────────────────────────────────────────
    private static function handleUpload(array $file, string $prefix): array
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $mime    = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, WEBP, atau SVG.'];
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Ukuran file maksimal 2 MB.'];
        }
        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png';
        $name = $prefix . '-' . time() . '.' . strtolower($ext);
        $dir  = ROOT_PATH . '/assets/uploads/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['success' => false, 'message' => 'Gagal menyimpan file.'];
        }
        return ['success' => true, 'path' => BASE_URL . '/assets/uploads/' . $name];
    }

    private static function deleteOldFile(string $url): void
    {
        if (empty($url)) return;
        // Ubah URL ke path fisik
        $baseUrl  = rtrim(BASE_URL, '/');
        $relative = ltrim(str_replace($baseUrl, '', $url), '/');
        $path     = ROOT_PATH . '/' . $relative;
        if (file_exists($path) && strpos($path, '/assets/uploads/') !== false) {
            @unlink($path);
        }
    }

    /**
     * Helper statis untuk dipakai di views/layout — ambil setting dari DB.
     */
    public static function get(string $key, string $default = ''): string
    {
        return self::getSetting($key, $default);
    }
}
