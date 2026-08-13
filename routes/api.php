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
    Route::post('/auth/login', [ApiAuthController::class, 'login'])
        ->middleware('throttle:api-login');

    // Endpoints protegidos por Sanctum Token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [ApiAuthController::class, 'me']);
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

        // Endpoints de Activos Industriales para Flutter
        Route::get('/activos', [ApiAssetController::class, 'index'])
            ->middleware('api-permission:activos:ver');
        Route::get('/activos/qr/{codigo}', [ApiAssetController::class, 'findByQr'])
            ->middleware('api-permission:activos:ver');
        Route::get('/activos/{id}', [ApiAssetController::class, 'show'])
            ->middleware('api-permission:activos:ver');
        Route::post('/activos/{id}/estado', [ApiAssetController::class, 'updateStatus'])
            ->middleware('api-permission:activos:editar');

        // Endpoints de Órdenes de Trabajo para Flutter
        Route::get('/ordenes-trabajo', [ApiWorkOrderController::class, 'index'])
            ->middleware('api-permission:ordenes:ver');
        Route::get('/ordenes-trabajo/sync', [ApiWorkOrderController::class, 'sync'])
            ->middleware('api-permission:ordenes:ver');
        Route::get('/ordenes-trabajo/{id}', [ApiWorkOrderController::class, 'show'])
            ->middleware('api-permission:ordenes:ver');
        Route::get('/ordenes-trabajo/{id}/historial', [ApiWorkOrderController::class, 'history'])
            ->middleware('api-permission:ordenes:ver');
        Route::post('/ordenes-trabajo/solicitar', [ApiWorkOrderController::class, 'store'])
            ->middleware('api-permission:ordenes:crear');
        Route::post('/ordenes-trabajo/{id}/cambiar-estado', [ApiWorkOrderController::class, 'updateStatus'])
            ->middleware('api-permission:ordenes:ejecutar');
        Route::post('/ordenes-trabajo/{id}/pausar', [ApiWorkOrderController::class, 'pause'])
            ->middleware('api-permission:ordenes:ejecutar');
        Route::post('/ordenes-trabajo/{id}/reanudar', [ApiWorkOrderController::class, 'resume'])
            ->middleware('api-permission:ordenes:ejecutar');
        Route::post('/ordenes-trabajo/{id}/repuestos', [ApiWorkOrderController::class, 'addSparePart'])
            ->middleware('api-permission:ordenes:ejecutar');
        Route::post('/ordenes-trabajo/{id}/fotos', [ApiWorkOrderController::class, 'uploadPhoto'])
            ->middleware('api-permission:ordenes:ejecutar');
        Route::post('/ordenes-trabajo/{id}/completar', [ApiWorkOrderController::class, 'complete'])
            ->middleware('api-permission:ordenes:ejecutar');

        // Endpoints de Mantenimiento Preventivo para Flutter
        Route::get('/planes-preventivos', [ApiPreventivePlanController::class, 'index'])
            ->middleware('api-permission:planes:ver');
        Route::get('/planes-preventivos/{id}', [ApiPreventivePlanController::class, 'show'])
            ->middleware('api-permission:planes:ver');
        Route::post('/planes-preventivos/{id}/ejecutar', [ApiPreventivePlanController::class, 'executeNow'])
            ->middleware('api-permission:planes:ejecutar');

        // Endpoints de Inventario de Repuestos para Flutter
        Route::get('/repuestos', [ApiSparePartController::class, 'index'])
            ->middleware('api-permission:repuestos:ver');
        Route::get('/repuestos/alertas', [ApiSparePartController::class, 'alerts'])
            ->middleware('api-permission:repuestos:ver');
        Route::get('/repuestos/{id}', [ApiSparePartController::class, 'show'])
            ->middleware('api-permission:repuestos:ver');
        Route::post('/repuestos/{id}/movimiento', [ApiSparePartController::class, 'registerMovement'])
            ->middleware('api-permission:repuestos:movimientos');

        // Endpoints de Reportes KPI & Analítica para Flutter
        Route::get('/reportes/kpis', [ApiReportController::class, 'kpis'])
            ->middleware('api-permission:reportes:ver');
        Route::get('/reportes/pareto', [ApiReportController::class, 'pareto'])
            ->middleware('api-permission:reportes:ver');
        Route::get('/reportes/activos', [ApiReportController::class, 'assets'])
            ->middleware('api-permission:reportes:ver');

        // Endpoints de Configuración & Catálogos para Flutter (referencia abierta a roles operativos)
        Route::get('/config/catalogos', [ApiConfigController::class, 'catalogs'])
            ->middleware('api-permission:activos:ver,ordenes:ver,repuestos:ver,planes:ver');

        // Endpoints de Notificaciones Push & Avisos para Flutter (datos propios del usuario)
        Route::get('/notificaciones', [ApiNotificationController::class, 'index']);
        Route::post('/notificaciones/{id}/marcar-leida', [ApiNotificationController::class, 'markAsRead']);
        Route::post('/usuarios/fcm-token', [ApiNotificationController::class, 'updateFcmToken']);

        // Endpoints de Gestión de Usuarios para Flutter
        Route::get('/usuarios', [ApiUserController::class, 'index'])
            ->middleware('api-permission:usuarios_roles:ver');
        Route::get('/usuarios/perfil', [ApiUserController::class, 'profile']);
        Route::put('/usuarios/perfil', [ApiUserController::class, 'updateProfile']);
    });
});
