<?php

// Create required /tmp directories BEFORE Laravel boots
$tmpDirs = [
    '/tmp/logs',
    '/tmp/framework',
    '/tmp/framework/views',
    '/tmp/framework/sessions',
    '/tmp/framework/cache',
    '/tmp/app',
    '/tmp/app/public',
];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Set LARAVEL_STORAGE_PATH BEFORE any autoloading
// Laravel reads this env var natively at framework level
putenv('LARAVEL_STORAGE_PATH=/tmp');
$_ENV['LARAVEL_STORAGE_PATH'] = '/tmp';
$_SERVER['LARAVEL_STORAGE_PATH'] = '/tmp';

// Also set the view/cache/log paths expected by Laravel
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=database');

$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['SESSION_DRIVER'] = 'database';

// Forward to Laravel's public/index.php
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "ERROR EN SERVERLESS VERCEL:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
