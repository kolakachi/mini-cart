<?php

namespace app\core;


use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

class Database
{
    public function __construct ($config) {
        $capsule = new Capsule;

        $capsule->addConnection($config);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function runMigrations() {
        $migration = new \app\database\migrations\mini_00001_initial;
        $migration->up();
    }

    public function runSeeds() {
        $seeder = new \app\database\seeds\Seeder;
        $seeder->up();
    }
}