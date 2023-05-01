<?php

namespace app\core;

class Controller
{
    public function render($view, $params = []) {
        return Application::$app->route->renderView($view, $params);
    }

    public function json($payload, $code) {
        return Application::$app->route->jsonResponse($payload, $code);
    }
}