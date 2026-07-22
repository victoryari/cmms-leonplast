@extends('layouts.app')

@section('title', 'Dashboard Principal')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner with Active User Information -->
    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-blue-900/60 via-indigo-900/40 to-slate-900 border border-blue-500/20 shadow-2xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <span>Sesión Activa - Leon Plast S.A.C.</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-white">¡Bienvenido, {{ $user->nombre_completo }}!</h2>
                <p class="text-slate-300 text-sm mt-1 max-w-xl">
                    Has ingresado como <strong class="text-blue-400">{{ $user->role?->nombre }}</strong>. 
                    @if($user->especialidad)
                    Especialidad: <span class="text-slate-400 font-medium">{{ $user->especialidad }}</span>.
                    @endif
                </p>
            </div>

            <div class="flex items-center space-x-3 bg-slate-950/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-md">
                <div class="w-12 h-12 rounded-xl bg-blue-600/30 border border-blue-500/40 flex items-center justify-center text-blue-400 font-bold text-lg">
                    {{ substr($user->nombres, 0, 1) }}
                </div>
                <div class="text-xs">
                    <p class="text-slate-400">Código Empleado</p>
                    <p class="font-mono text-white font-bold text-sm">{{ $user->codigo_empleado ?? 'EMP-001' }}</p>
                    <p class="text-emerald-400 text-[11px] font-semibold mt-0.5">● Estado: Activo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Total Activos -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Activos de Planta</p>
                <h3 class="text-3xl font-extrabold text-white mt-1">{{ $metrics['total_activos'] }}</h3>
                <p class="text-[11px] text-emerald-400 mt-1 font-medium">Inyectoras, grúas, etc.</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>

        <!-- OTs Pendientes -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">OTs Pendientes</p>
                <h3 class="text-3xl font-extrabold text-amber-400 mt-1">{{ $metrics['ots_pendientes'] }}</h3>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">Por aprobar / asignar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- OTs En Progreso -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">En Ejecución</p>
                <h3 class="text-3xl font-extrabold text-indigo-400 mt-1">{{ $metrics['ots_en_progreso'] }}</h3>
                <p class="text-[11px] text-indigo-300 mt-1 font-medium">Técnicos interviniendo</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>

        <!-- OTs Completadas -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completadas</p>
                <h3 class="text-3xl font-extrabold text-emerald-400 mt-1">{{ $metrics['ots_completadas'] }}</h3>
                <p class="text-[11px] text-emerald-400 mt-1 font-medium">Mantenimiento cerrado</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- API Flutter REST Notification Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-cyan-500/30 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-white flex items-center gap-2">
                    <span>Endpoints API REST (Laravel Sanctum) para App Móvil Flutter</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 font-mono">POST /api/v1/auth/login</span>
                </h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                    La estructura backend está lista para consumir desde tu App Flutter en Dart. Permite login por Token Bearer, consulta de Órdenes de Trabajo y escaneo de códigos QR de activos en planta.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
