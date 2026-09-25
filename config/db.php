<?php

use craft\config\DbConfig;
use craft\helpers\App;

return DbConfig::create()
    ->driver(App::env('CRAFT_DB_DRIVER') ?: 'mysql')
    ->server(App::env('CRAFT_DB_SERVER'))
    ->port((int)App::env('CRAFT_DB_PORT'))
    ->database(App::env('CRAFT_DB_DATABASE'))
    ->user(App::env('CRAFT_DB_USER'))
    ->password(App::env('CRAFT_DB_PASSWORD'))
    ->schema(App::env('CRAFT_DB_SCHEMA') ?: 'public')
    ->tablePrefix(App::env('CRAFT_DB_TABLE_PREFIX') ?: '');
