<?php

// ============================================================
// PASO 1: Crear directorios en /tmp ANTES de que Laravel arranque
// El filesystem de Vercel es read-only excepto /tmp
// ============================================================
$tmpDirs = [
    '/tmp/bootstrap',
    '/tmp/bootstrap/cache',
    '/tmp/logs',
    '/tmp/framework',
    '/tmp/framework/views',
    '/tmp/framework/sessions',
    '/tmp/framework/cache',
    '/tmp/framework/cache/data',
    '/tmp/app',
    '/tmp/app/public',
];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================================
// PASO 2: Copiar bootstrap/cache/*.php generados por el build a /tmp
// Durante el build de Vercel: composer install --no-dev +
//   php artisan package:discover -> genera packages.php y services.php
//   SIN dev-packages (pail, sail, collision, etc.)
// En runtime el dir es read-only, por eso copiamos a /tmp donde sí escribe
// ============================================================
$srcCacheDir = __DIR__ . '/../bootstrap/cache';
$runtimeCacheDir = '/tmp/bootstrap/cache';
foreach (['packages.php', 'services.php'] as $file) {
    $src = $srcCacheDir . '/' . $file;
    $dst = $runtimeCacheDir . '/' . $file;
    if (file_exists($src) && !file_exists($dst)) {
        @copy($src, $dst);
    }
}

// ============================================================
// PASO 3: Establecer variables de entorno ANTES del autoloader
// LARAVEL_STORAGE_PATH  → /tmp  (leído nativamente por Laravel >= 11)
// APP_PACKAGES_CACHE    → /tmp/bootstrap/cache/packages.php (writable)
// APP_SERVICES_CACHE    → /tmp/bootstrap/cache/services.php (writable)
// LOG_CHANNEL           → stderr (sin escritura a disco)
// CACHE_STORE           → array  (en memoria)
// SESSION_DRIVER        → array  (en memoria, sin filesystem)
// ============================================================
$envOverrides = [
    'LARAVEL_STORAGE_PATH' => '/tmp',
    'APP_PACKAGES_CACHE'   => '/tmp/bootstrap/cache/packages.php',
    'APP_SERVICES_CACHE'   => '/tmp/bootstrap/cache/services.php',
    'LOG_CHANNEL'          => 'stderr',
    'LOG_STACK'            => 'stderr',
    'CACHE_STORE'          => 'array',
    'SESSION_DRIVER'       => 'array',
    'QUEUE_CONNECTION'     => 'sync',
];
foreach ($envOverrides as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// ============================================================
// PASO 4: Ejecutar la aplicación Laravel
// ============================================================
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "ERROR FATAL EN SERVERLESS VERCEL:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
