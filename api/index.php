<?php

// ============================================================
// PASO 1: Crear directorios en /tmp ANTES de que Laravel arranque
// El filesystem de Vercel es read-only excepto /tmp
// ============================================================
$tmpDirs = [
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
// PASO 2: Establecer variables de entorno ANTES del autoloader
// LARAVEL_STORAGE_PATH es leído nativamente por Laravel >= 11
// SESSION_DRIVER y CACHE_STORE deben apuntar a drivers sin filesystem
// ============================================================
$vercelEnvOverrides = [
    'LARAVEL_STORAGE_PATH' => '/tmp',
    'LOG_CHANNEL'          => 'stderr',
    'LOG_STACK'            => 'stderr',
    'CACHE_STORE'          => 'array',
    'SESSION_DRIVER'       => 'array',   // array = en memoria, sin filesystem
    'QUEUE_CONNECTION'     => 'sync',
];

foreach ($vercelEnvOverrides as $key => $value) {
    // Solo sobreescribir si NO está definido por Vercel ya
    if (empty($_ENV[$key]) && empty($_SERVER[$key])) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Forzar siempre LARAVEL_STORAGE_PATH a /tmp (crítico)
putenv('LARAVEL_STORAGE_PATH=/tmp');
$_ENV['LARAVEL_STORAGE_PATH'] = '/tmp';
$_SERVER['LARAVEL_STORAGE_PATH'] = '/tmp';

// ============================================================
// PASO 3: Ejecutar la aplicación Laravel
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
