<?php
declare(strict_types=1);

class View {

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
     * Render view dengan layout base.php.
     * $data['scripts']     => HTML string di-inject sebelum </body>
     * $data['headScripts'] => HTML string di-inject di dalam <head> (untuk CDN seperti EditorJS)
     */
    public static function layout(string $view, array $data = []): void {
        $scripts     = $data['scripts']     ?? '';
        $headScripts = $data['headScripts'] ?? '';
        $data['content'] = self::render($view, $data);
        $data['scripts']     = $scripts;
        $data['headScripts'] = $headScripts;
        extract($data);
        include APP_PATH . '/views/layouts/base.php';
    }

    public static function standalone(string $view, array $data = []): void {
        extract($data);
        $viewPath = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View tidak ditemukan: {$view}");
        }
        include $viewPath;
    }
}
