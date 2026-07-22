<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\PreventivePlanController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicRequestController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas públicas de Solicitud Rápida por Código QR (Sin necesidad de login)
Route::get('/solicitud-rapida/{codigo_qr}', [PublicRequestController::class, 'create'])->name('public.create');
Route::post('/solicitud-rapida/{codigo_qr}', [PublicRequestController::class, 'store'])->name('public.store');
Route::get('/solicitud-rapida/rastreo/{codigo_ot}', [PublicRequestController::class, 'track'])->name('public.track');

// Rutas de inicio de sesión
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas por sesión Web
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Auto-gestión de Perfil
    Route::get('/perfil', [ProfileController::class, 'index'])->name('perfil.index');
    Route::post('/perfil', [ProfileController::class, 'update'])->name('perfil.update');

    // Módulo de Gestión de Activos Industriales
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor,Tecnico')->group(function () {
        Route::get('/activos', [AssetController::class, 'index'])->name('activos.index');
        Route::get('/activos/crear', [AssetController::class, 'create'])->name('activos.create');
        Route::post('/activos', [AssetController::class, 'store'])->name('activos.store');
        Route::get('/activos/{id}', [AssetController::class, 'show'])->name('activos.show');
        Route::get('/activos/{id}/editar', [AssetController::class, 'edit'])->name('activos.edit');
        Route::put('/activos/{id}', [AssetController::class, 'update'])->name('activos.update');
        Route::delete('/activos/{id}', [AssetController::class, 'destroy'])->name('activos.destroy');
        Route::get('/activos/{id}/imprimir-qr', [AssetController::class, 'printQr'])->name('activos.print-qr');
    });

    // Módulo de Órdenes de Trabajo (OTs)
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor,Tecnico,Solicitante')->group(function () {
        Route::get('/ordenes-trabajo', [WorkOrderController::class, 'index'])->name('ordenes.index');
        Route::get('/ordenes-trabajo/crear', [WorkOrderController::class, 'create'])->name('ordenes.create');
        Route::post('/ordenes-trabajo', [WorkOrderController::class, 'store'])->name('ordenes.store');
        Route::get('/ordenes-trabajo/{id}', [WorkOrderController::class, 'show'])->name('ordenes.show');
        Route::post('/ordenes-trabajo/{id}/asignar', [WorkOrderController::class, 'assign'])->name('ordenes.assign');
        Route::post('/ordenes-trabajo/{id}/estado', [WorkOrderController::class, 'updateStatus'])->name('ordenes.update-status');
        Route::post('/ordenes-trabajo/{id}/repuestos', [WorkOrderController::class, 'addSparePart'])->name('ordenes.add-spare-part');
        Route::post('/ordenes-trabajo/{id}/fotos', [WorkOrderController::class, 'uploadPhoto'])->name('ordenes.upload-photo');
        Route::post('/ordenes-trabajo/{id}/calificar', [WorkOrderController::class, 'rate'])->name('ordenes.rate');
    });

    // Mantenimiento Preventivo & Rutinas Programadas
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor,Tecnico')->group(function () {
        Route::get('/planes-preventivos', [PreventivePlanController::class, 'index'])->name('planes.index');
        Route::get('/planes-preventivos/crear', [PreventivePlanController::class, 'create'])->name('planes.create');
        Route::post('/planes-preventivos', [PreventivePlanController::class, 'store'])->name('planes.store');
        Route::get('/planes-preventivos/{id}', [PreventivePlanController::class, 'show'])->name('planes.show');
        Route::post('/planes-preventivos/{id}/ejecutar', [PreventivePlanController::class, 'executeNow'])->name('planes.execute-now');
        Route::post('/planes-preventivos/{id}/toggle-status', [PreventivePlanController::class, 'toggleStatus'])->name('planes.toggle-status');
    });

    // Módulo de Gestión de Inventario de Repuestos & Almacén
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor,Tecnico')->group(function () {
        Route::get('/repuestos', [SparePartController::class, 'index'])->name('repuestos.index');
        Route::get('/repuestos/crear', [SparePartController::class, 'create'])->name('repuestos.create');
        Route::post('/repuestos', [SparePartController::class, 'store'])->name('repuestos.store');
        Route::get('/repuestos/{id}', [SparePartController::class, 'show'])->name('repuestos.show');
        Route::get('/repuestos/{id}/editar', [SparePartController::class, 'edit'])->name('repuestos.edit');
        Route::put('/repuestos/{id}', [SparePartController::class, 'update'])->name('repuestos.update');
        Route::post('/repuestos/{id}/movimiento', [SparePartController::class, 'registerMovement'])->name('repuestos.movimiento');
    });

    // Módulo de Reportes KPI & Analítica de Planta
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor')->group(function () {
        Route::get('/reportes-kpi', [ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes-kpi/exportar-csv', [ReportController::class, 'exportCsv'])->name('reportes.export-csv');
    });

    // Módulo de Gestión de Usuarios & Personal de Planta (Solo Administrador)
    Route::middleware('role:Administrador')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.show');
        Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::post('/usuarios/{id}/restablecer-clave', [UserController::class, 'resetPassword'])->name('usuarios.reset-password');
    });
});
