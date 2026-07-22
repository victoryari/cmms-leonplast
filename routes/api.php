<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiWorkOrderController;

Route::prefix('v1')->group(function () {
    // Autenticación API (App móvil Flutter)
    Route::post('/auth/login', [ApiAuthController::class, 'login']);

    // Endpoints protegidos por Sanctum Token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [ApiAuthController::class, 'me']);
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

        // Endpoints de Órdenes de Trabajo
        Route::get('/ordenes-trabajo', [ApiWorkOrderController::class, 'index']);

        // Escaneo de QR de Activos
        Route::get('/activos/qr/{codigo}', [ApiWorkOrderController::class, 'findAssetByQr']);
    });
});
