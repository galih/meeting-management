<?php
declare(strict_types=1);

class View {

    /**
     * Render view menjadi string (tanpa layout)
     */
    public static function render(string $view, array $data = []): string {
        extract($data);
        ob_start();
        $viewPath = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View tidak ditemukan: {$view}");
        }
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Render view dengan layout base.php (wajib sudah login)
     * Jika $data['scripts'] berisi string HTML <script>, akan di-inject
     * setelah CDN scripts di base.php (lewat <?= $scripts ?? '' ?>).
     */
    public static function layout(string $view, array $data = []): void {
        // Render content dulu, pisahkan $scripts agar tidak ikut di-extract ke $content
        $scripts = $data['scripts'] ?? '';
        $data['content'] = self::render($view, $data);
        $data['scripts'] = $scripts;
        extract($data);
        include APP_PATH . '/views/layouts/base.php';
    }

    /**
     * Render view standalone TANPA layout base.php
     * Dipakai untuk halaman auth (login, forgot-password, reset-password)
     */
    public static function standalone(string $view, array $data = []): void {
        extract($data);
        $viewPath = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View tidak ditemukan: {$view}");
        }
        include $viewPath;
    }
}
