<?php

namespace app\core;

class Router {

    protected array $routes = [];
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;

    }

    public function get($path, $callback) {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['post'][$path] = $callback;
    }

    public function delete($path, $callback) {
        $this->routes['delete'][$path] = $callback;
    }

    public function getCallback() {
        $path = $this->request->getPath();
        $method = $this->request->method();

        $url = trim($path, '/');
        $routes = $this->routes[$method] ?? [];

        $routesParams = false;

        foreach ($routes as $route => $callback) {
            $route = trim($route, '/');
            $routeNames = [];

            if(!$route) {
                continue;
            }

            if (preg_match_all('/\:(\w+)/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            $routeRegex = "@^" . preg_replace_callback('/\:(\w+)/', fn($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";

            if (preg_match_all($routeRegex, $url, $valueMatches)) {

                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $routeParams = array_combine($routeNames, $values);

                $this->request->setRouteParams($routeParams);
                return $callback;
            }
        }

        return false;
    }

    public function resolve() {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback =  $this->routes[$method][$path] ?? false;

        if(!$callback) {
            $callback = $this->getCallback();
            $this->response->setStatusCode(404);
            if ($callback === false) {
                return 'Not found';
            }

        }

        if (is_string($callback)){
            return $this->renderView($callback);
        }

        return call_user_func($callback, $this->request);

    }

    public function renderView($view, $params = []) {
        $layoutContent = $this->layoutContent();
        $viewContent = $this->renderOnlyView($view, $params);
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent() {
        ob_start();
        include_once Application::$ROOT_DIR."/views/layouts/master.php";
        return ob_get_clean();
    }

    protected function renderOnlyView($view, $params) {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        include_once Application::$ROOT_DIR."/views/$view.php";
        return ob_get_clean();
    }

    public function jsonResponse($payload, $code) {
        header('Content-Type: application/json');
        $this->response->setStatusCode($code);
        return json_encode($payload);
    }
}