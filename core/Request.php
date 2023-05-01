<?php

namespace app\core;

class Request {

    private array $routeParams = [];

    public function getPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getBody() {
        $body = [];
        if ($this->method() === 'get' && $_GET != null) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->method() !== 'get') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }

            if (!$_POST) {
                $_POST = json_decode(file_get_contents('php://input'), true);
                if($_POST != null) {
                    foreach ($_POST as $key => $value) {
                        $body[$key] = $value;
                    }
                }
            }
        }

        return $body;
    }

    public function setRouteParams($routeParams) {
        $this->routeParams = $routeParams;
    }

    public function getRouteParams() {
        return $this->routeParams;
    }

    public function getRouteParam($param, $default = null) {
        return $this->routeParams[$param] ?? $default;
    }
}