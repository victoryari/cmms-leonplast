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
// PASO 2: Copiar packages.php y services.php a /tmp/bootstrap/cache
// Estos archivos se despliegan en read-only; PackageManifest necesita
// poder escribirlos. Los copiamos a /tmp donde sí puede escribir.
// NOTA: NO copiamos config.php ni routes.php porque contienen rutas
// locales de Windows hardcodeadas. Laravel los regenerará con rutas correctas.
// ============================================================
$srcCacheDir = __DIR__ . '/../bootstrap/cache';
$cacheFilesToCopy = ['packages.php', 'services.php'];
foreach ($cacheFilesToCopy as $file) {
    $src = $srcCacheDir . '/' . $file;
    $dst = '/tmp/bootstrap/cache/' . $file;
    if (file_exists($src) && !file_exists($dst)) {
        @copy($src, $dst);
    }
}

// ============================================================
// PASO 3: Establecer variables de entorno ANTES del autoloader
// LARAVEL_STORAGE_PATH es leído nativamente por Laravel >= 11
// APP_PACKAGES_CACHE y APP_SERVICES_CACHE apuntan a /tmp (writable)
// LOG_CHANNEL=stderr para no intentar escribir en el filesystem
// SESSION_DRIVER=array y CACHE_STORE=array para evitar I/O al disco
// ============================================================
$envOverrides = [
    'LARAVEL_STORAGE_PATH' => '/tmp',
    'APP_PACKAGES_CACHE'   => '/tmp/bootstrap/cache/packages.php',
    'APP_SERVICES_CACHE'   => '/tmp/bootstrap/cache/services.php',
    // NO definir APP_CONFIG_CACHE ni APP_ROUTES_CACHE:
    // Laravel los leerá dinámicamente desde /config/ con storage_path()=/tmp correcto
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
