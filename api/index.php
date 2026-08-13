<?php

define('LARAVEL_START', microtime(true));

// Ensure writable directories for Vercel Serverless environment (/tmp)
$tmpDirs = ['/tmp/views', '/tmp/sessions', '/tmp/cache'];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_EVENTS_CACHE=/tmp/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');
putenv('VIEW_COMPILED_PATH=/tmp/views');
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');

$_ENV['APP_CONFIG_CACHE'] = '/tmp/config.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['CACHE_STORE'] = 'array';

// Register autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle request
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(
    Illuminate\Http\Request::capture()
);

