<?php

namespace Core;

class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $path = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';

            if (file_exists($path)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        require_once __DIR__ . '/../app/Controllers/' . $this->controller . '.php';
        $class = "App\\Controllers\\" . $this->controller;
        $this->controller = new $class;

        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([
            $this->controller,
            $this->method
        ], $this->params);
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(
                rtrim($_GET['url'], '/'),
                FILTER_SANITIZE_URL
            ));
        }

        // Fallback for PHP built-in server where .htaccess is ignored
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');
        if (!empty($path) && strpos($path, 'index.php') === false) {
            return explode('/', filter_var($path, FILTER_SANITIZE_URL));
        }

        return [];
    }
}
