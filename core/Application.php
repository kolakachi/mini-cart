<?php

namespace app\core;

class Application {

    public Database $database;
    public Router $route;
    public Request $request;
    public Response $response;
    public static string $ROOT_DIR;

    public static Application $app;

    public function __construct($rootPath, array $config) {

        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->database = new Database($config['db']);
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Router($this->request, $this->response);

    }

    public function run() {
        echo $this->route->resolve();
    }
}