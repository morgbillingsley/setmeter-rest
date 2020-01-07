<?php

class App {

    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();
        $controllerName = $this->parseControllerName($url[0]);

        if(file_exists('../app/Controllers/' . $controllerName . '.php')) {
            $this->controller = $controllerName;
            unset($url[0]);
        } elseif (!empty($url[0])) {
            $this->method = 'notFound';
            unset($url[0]);
        }

        require_once '../app/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->method = 'notFound';
            }
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (isset($path)) {
            $url = trim($path, '/');
        } else {
            $url = '';
        }
        return $url = explode('/', $url);
    }

    public function parseControllerName(string $name)
    {
        if (preg_match('/^.*-.+$/', $name)) {
            $split = str_replace('-', ' ', $name);
            $capital = ucwords($split);
            
            return str_replace(' ', '', $capital);
        }

        return ucwords($name);
    }
}

?>