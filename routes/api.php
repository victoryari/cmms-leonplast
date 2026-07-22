<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiWorkOrderController;
use App\Http\Controllers\Api\ApiAssetController;

Route::prefix('v1')->group(function () {
    // Autenticación API (App móvil Flutter)
    Route::post('/auth/login', [ApiAuthController::class, 'login']);

    // Endpoints protegidos por Sanctum Token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [ApiAuthController::class, 'me']);
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

        // Endpoints de Activos Industriales para Flutter
        Route::get('/activos', [ApiAssetController::class, 'index']);
        Route::get('/activos/qr/{codigo}', [ApiAssetController::class, 'findByQr']);
        Route::get('/activos/{id}', [ApiAssetController::class, 'show']);
        Route::post('/activos/{id}/estado', [ApiAssetController::class, 'updateStatus']);

        // Endpoints de Órdenes de Trabajo para Flutter
        Route::get('/ordenes-trabajo', [ApiWorkOrderController::class, 'index']);
        Route::get('/ordenes-trabajo/sync', [ApiWorkOrderController::class, 'sync']);
        Route::get('/ordenes-trabajo/{id}', [ApiWorkOrderController::class, 'show']);
        Route::get('/ordenes-trabajo/{id}/historial', [ApiWorkOrderController::class, 'history']);
        Route::post('/ordenes-trabajo/solicitar', [ApiWorkOrderController::class, 'store']);
        Route::post('/ordenes-trabajo/{id}/cambiar-estado', [ApiWorkOrderController::class, 'updateStatus']);
        Route::post('/ordenes-trabajo/{id}/repuestos', [ApiWorkOrderController::class, 'addSparePart']);
        Route::post('/ordenes-trabajo/{id}/fotos', [ApiWorkOrderController::class, 'uploadPhoto']);
        Route::post('/ordenes-trabajo/{id}/completar', [ApiWorkOrderController::class, 'complete']);
    });
});
