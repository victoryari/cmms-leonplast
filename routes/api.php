<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiWorkOrderController;
use App\Http\Controllers\Api\ApiAssetController;
use App\Http\Controllers\Api\ApiPreventivePlanController;
use App\Http\Controllers\Api\ApiSparePartController;
use App\Http\Controllers\Api\ApiReportController;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\Api\ApiConfigController;
use App\Http\Controllers\Api\ApiNotificationController;

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
        Route::post('/ordenes-trabajo/{id}/pausar', [ApiWorkOrderController::class, 'pause']);
        Route::post('/ordenes-trabajo/{id}/reanudar', [ApiWorkOrderController::class, 'resume']);
        Route::post('/ordenes-trabajo/{id}/repuestos', [ApiWorkOrderController::class, 'addSparePart']);
        Route::post('/ordenes-trabajo/{id}/fotos', [ApiWorkOrderController::class, 'uploadPhoto']);
        Route::post('/ordenes-trabajo/{id}/completar', [ApiWorkOrderController::class, 'complete']);

        // Endpoints de Mantenimiento Preventivo para Flutter
        Route::get('/planes-preventivos', [ApiPreventivePlanController::class, 'index']);
        Route::get('/planes-preventivos/{id}', [ApiPreventivePlanController::class, 'show']);
        Route::post('/planes-preventivos/{id}/ejecutar', [ApiPreventivePlanController::class, 'executeNow']);

        // Endpoints de Inventario de Repuestos para Flutter
        Route::get('/repuestos', [ApiSparePartController::class, 'index']);
        Route::get('/repuestos/alertas', [ApiSparePartController::class, 'alerts']);
        Route::get('/repuestos/{id}', [ApiSparePartController::class, 'show']);
        Route::post('/repuestos/{id}/movimiento', [ApiSparePartController::class, 'registerMovement']);

        // Endpoints de Reportes KPI & Analítica para Flutter
        Route::get('/reportes/kpis', [ApiReportController::class, 'kpis']);
        Route::get('/reportes/pareto', [ApiReportController::class, 'pareto']);
        Route::get('/reportes/activos', [ApiReportController::class, 'assets']);

        // Endpoints de Configuración & Catálogos para Flutter
        Route::get('/config/catalogos', [ApiConfigController::class, 'catalogs']);

        // Endpoints de Notificaciones Push & Avisos para Flutter
        Route::get('/notificaciones', [ApiNotificationController::class, 'index']);
        Route::post('/notificaciones/{id}/marcar-leida', [ApiNotificationController::class, 'markAsRead']);
        Route::post('/usuarios/fcm-token', [ApiNotificationController::class, 'updateFcmToken']);

        // Endpoints de Gestión de Usuarios para Flutter
        Route::get('/usuarios', [ApiUserController::class, 'index']);
        Route::get('/usuarios/perfil', [ApiUserController::class, 'profile']);
        Route::put('/usuarios/perfil', [ApiUserController::class, 'updateProfile']);
    });
});
