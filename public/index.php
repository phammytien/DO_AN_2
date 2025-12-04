<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');

// Nếu Laravel crash trước khi render view → in lỗi ra log ngay
set_exception_handler(function ($e) {
    error_log("UNCAUGHT EXCEPTION: " . $e->getMessage());
    error_log($e->getTraceAsString());
    http_response_code(500);
    echo "Server Error";
});

set_error_handler(function ($severity, $message, $file, $line) {
    error_log("PHP ERROR: [$severity] $message in $file on line $line");
    return false;
});

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoload
require __DIR__.'/../vendor/autoload.php';

// Bootstrap App
$app = require_once __DIR__.'/../bootstrap/app.php';

// Force debug ON only to print errors
$app->make('config')->set('app.debug', true);
$app->make('config')->set('app.env', 'local');

// Handle request
$app->handleRequest(Request::capture());
