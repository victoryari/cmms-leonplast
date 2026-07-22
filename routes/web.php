<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\PreventivePlanController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de inicio de sesión
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas por sesión Web
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
});
