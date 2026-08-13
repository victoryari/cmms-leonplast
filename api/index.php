<?php

define('LARAVEL_START', microtime(true));

// Ensure writable directories for Vercel Serverless environment (/tmp)
$tmpDirs = [
    '/tmp/logs',
    '/tmp/framework/views',
    '/tmp/framework/sessions',
    '/tmp/framework/cache',
    '/tmp/app/public'
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
