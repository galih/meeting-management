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

    public static function layout(string $view, array $data = []): void {
        $data['content'] = self::render($view, $data);
        extract($data);
        include APP_PATH . '/views/layouts/base.php';
    }
}
