<?php

use craft\helpers\App;
use craft\helpers\Db;

return [
    'dsn' => App::env('CRAFT_DB_DSN') ?: Db::url(),
    'driver' => App::env('CRAFT_DB_DRIVER'),
    'server' => App::env('CRAFT_DB_SERVER'),
    'port' => App::env('CRAFT_DB_PORT'),
    'database' => App::env('CRAFT_DB_DATABASE'),
    'user' => App::env('CRAFT_DB_USER'),
    'password' => App::env('CRAFT_DB_PASSWORD'),
    'schema' => App::env('CRAFT_DB_SCHEMA') ?: 'public',
    'tablePrefix' => App::env('CRAFT_DB_TABLE_PREFIX'),
];
