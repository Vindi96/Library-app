<?php
/**
 * Craft web bootstrap file
 */

// Load Composer's autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Load dotenv
craft\helpers\App::bootstrapDotenv(dirname(__DIR__));

// Load and run Craft
$app = craft\helpers\App::createWebApplication();
$app->run();
