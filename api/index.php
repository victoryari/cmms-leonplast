<?php

// ============================================================
// PASO 1: Crear directorios en /tmp ANTES de que Laravel arranque
// El filesystem de Vercel es read-only excepto /tmp
// ============================================================
$tmpBootstrap = '/tmp/bootstrap/cache';
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
// PASO 2: Copiar archivos de cache de bootstrap/cache a /tmp/bootstrap/cache
// Los archivos se despliegan en /var/task/user/bootstrap/cache/ (read-only)
// PackageManifest necesita poder escribirlos, por eso los copiamos a /tmp
// ============================================================
$srcCacheDir = __DIR__ . '/../bootstrap/cache';
$cacheFiles = ['packages.php', 'services.php', 'config.php', 'routes-v7.php', 'events.php'];
foreach ($cacheFiles as $file) {
    $src = $srcCacheDir . '/' . $file;
    $dst = $tmpBootstrap . '/' . $file;
    if (file_exists($src) && !file_exists($dst)) {
        @copy($src, $dst);
    }
}

// ============================================================
// PASO 3: Establecer variables de entorno ANTES del autoloader
// ============================================================
$vercelEnvOverrides = [
    'LARAVEL_STORAGE_PATH'  => '/tmp',
    'APP_BOOTSTRAP_PATH'    => '/tmp/bootstrap',
    'APP_PACKAGES_CACHE'    => '/tmp/bootstrap/cache/packages.php',
    'APP_SERVICES_CACHE'    => '/tmp/bootstrap/cache/services.php',
    'APP_CONFIG_CACHE'      => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE'      => '/tmp/bootstrap/cache/routes-v7.php',
    'APP_EVENTS_CACHE'      => '/tmp/bootstrap/cache/events.php',
    'LOG_CHANNEL'           => 'stderr',
    'LOG_STACK'             => 'stderr',
    'CACHE_STORE'           => 'array',
    'SESSION_DRIVER'        => 'array',
    'QUEUE_CONNECTION'      => 'sync',
];

foreach ($vercelEnvOverrides as $key => $value) {
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
