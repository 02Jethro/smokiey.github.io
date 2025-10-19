<?php
class Router {
    private $routes = [];

    public function add($route, $callback) {
        $this->routes[$route] = $callback;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace(BASE_URL, '', $uri);

        foreach ($this->routes as $route => $callback) {
            if ($route === $uri) {
                call_user_func($callback);
                return;
            }
        }

        // 404 - Page not found
        http_response_code(404);
        echo "Page not found";
    }
}
?>