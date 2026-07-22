<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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

    // Módulos restringidos para gestión de planta
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor')->group(function () {
        Route::get('/activos', function () {
            return view('placeholder', ['title' => 'Gestión de Activos Industriales']);
        })->name('activos.index');

        Route::get('/planes-preventivos', function () {
            return view('placeholder', ['title' => 'Planes de Mantenimiento Preventivo']);
        })->name('planes.index');
    });

    // Módulos accesibles por todos los roles autenticados
    Route::middleware('role:Administrador,Gerente_Mantenimiento,Supervisor,Tecnico,Solicitante')->group(function () {
        Route::get('/ordenes-trabajo', function () {
            return view('placeholder', ['title' => 'Órdenes de Trabajo (OTs)']);
        })->name('ordenes.index');
    });
});
