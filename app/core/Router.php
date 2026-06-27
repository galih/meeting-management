<?php
declare(strict_types=1);

class Router {
    private array $routes = [];

    /**
     * @param string         $path
     * @param callable|array $handler
     */
    public function get(string $path, $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * @param string         $path
     * @param callable|array $handler
     */
    public function post(string $path, $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void {
        $uri = strtok($uri, '?') ?: '/';

        foreach ($this->routes[$method] ?? [] as $path => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $path);
            if (preg_match("#^{$pattern}$#", $uri, $matches)) {
                array_shift($matches);

                $params = array_map(
                    fn($v) => ctype_digit($v) ? (int)$v : $v,
                    $matches
                );

                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    (new $class)->$action(...$params);
                } else {
                    $handler(...$params);
                }
                return;
            }
        }

        // 404
        http_response_code(404);
        $pageTitle = '404 - Halaman Tidak Ditemukan';
        include APP_PATH . '/views/errors/404.php';
    }
}
