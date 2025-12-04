<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| FORCE DEBUG (for Render 500 error)
|--------------------------------------------------------------------------
| This completely bypasses .env and forces Laravel to display all errors.
| Remove this block after fixing the error!
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Force Laravel debug ON
$app->make('config')->set('app.debug', true);

// Force APP_ENV = local (optional but helps)
$app->make('config')->set('app.env', 'local');

$app->handleRequest(Request::capture());
