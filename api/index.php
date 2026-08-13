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
// PASO 2: Copiar bootstrap/cache/*.php generados en build a /tmp
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
// PASO 3: Definir todas las variables de entorno de producción
// Inyectamos las variables directamente antes de cargar Laravel
// ============================================================
$envOverrides = [
    'APP_NAME'                => 'CMMS Leon Plast',
    'APP_ENV'                 => 'production',
    'APP_KEY'                 => 'base64:S3JDpvAcpIdSpJp1nyJQGi6lEvdFImge7j5pi5eUAUU=',
    'APP_DEBUG'               => 'true',
    'APP_URL'                 => 'https://cmms-leonplast-5he3-ochre.vercel.app',
    'APP_LOCALE'              => 'es',
    'APP_FALLBACK_LOCALE'     => 'es',
    'APP_TIMEZONE'            => 'America/Lima',
    'APP_MAINTENANCE_DRIVER'  => 'file',
    'APP_MAINTENANCE_STORE'   => 'file',
    'DB_CONNECTION'           => 'mysql',
    'DB_HOST'                 => 'gateway01.us-west-2.prod.aws.tidbcloud.com',
    'DB_PORT'                 => '4000',
    'DB_DATABASE'             => 'cmms_leonplast',
    'DB_USERNAME'             => '3S6n8bNRbZXLUhm.root',
    'DB_PASSWORD'             => 'VNOAbPXLCQ1iBBqr',
    'SESSION_DRIVER'          => 'array',
    'SESSION_LIFETIME'        => '120',
    'CACHE_STORE'             => 'array',
    'QUEUE_CONNECTION'        => 'sync',
    'MAIL_MAILER'             => 'log',
    'LOG_CHANNEL'             => 'stderr',
    'LOG_STACK'               => 'stderr',
    'FILESYSTEM_DISK'         => 'local',
    'BCRYPT_ROUNDS'           => '12',
    'LARAVEL_STORAGE_PATH'    => '/tmp',
    'APP_PACKAGES_CACHE'      => '/tmp/bootstrap/cache/packages.php',
    'APP_SERVICES_CACHE'      => '/tmp/bootstrap/cache/services.php',
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
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain');
    }
    echo "ERROR FATAL EN SERVERLESS VERCEL:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
