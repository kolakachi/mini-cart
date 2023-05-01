<?php

require_once __DIR__.'/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use app\core\Application;
use app\database\Config;

$config = [
    'db' => Config::init()
];

$app = new Application(dirname(__DIR__), $config);

$app->database->runMigrations();