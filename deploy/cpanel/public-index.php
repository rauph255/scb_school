<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Keep the Laravel application outside public_html at ~/scb_app.
$applicationRoot = dirname(__DIR__).'/scb_app';

if (! is_file($applicationRoot.'/vendor/autoload.php') || ! is_file($applicationRoot.'/bootstrap/app.php')) {
    http_response_code(503);
    exit('The application release is not installed correctly.');
}

if (file_exists($maintenance = $applicationRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $applicationRoot.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $applicationRoot.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
