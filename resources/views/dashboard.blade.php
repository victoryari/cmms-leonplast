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

    <!-- Quick Metrics Cards (6 Modules KPI Grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Total Activos -->
        <a href="{{ route('activos.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Activos</p>
            <h3 class="text-2xl font-extrabold text-white">{{ $metrics['total_activos'] }}</h3>
            <p class="text-[10px] text-emerald-400 font-medium">Equipos de Planta ➔</p>
        </a>

        <!-- OTs Pendientes -->
        <a href="{{ route('ordenes.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-slate-400 uppercase">OTs Pendientes</p>
            <h3 class="text-2xl font-extrabold text-amber-400">{{ $metrics['ots_pendientes'] }}</h3>
            <p class="text-[10px] text-slate-400 font-medium">Por Aprobación ➔</p>
        </a>

        <!-- OTs En Progreso -->
        <a href="{{ route('ordenes.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-slate-400 uppercase">En Ejecución</p>
            <h3 class="text-2xl font-extrabold text-indigo-400">{{ $metrics['ots_en_progreso'] }}</h3>
            <p class="text-[10px] text-indigo-300 font-medium">En Intervención ➔</p>
        </a>

        <!-- Mantenimiento Preventivo -->
        <a href="{{ route('planes.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Preventivos</p>
            <h3 class="text-2xl font-extrabold text-blue-400">{{ $metrics['total_planes'] }}</h3>
            <p class="text-[10px] text-blue-300 font-medium">Rutinas Activas ➔</p>
        </a>

        <!-- Inventario & Almacén -->
        <a href="{{ route('repuestos.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Almacén</p>
            <h3 class="text-2xl font-extrabold {{ $metrics['alertas_repuestos'] > 0 ? 'text-amber-400' : 'text-white' }}">{{ $metrics['total_repuestos'] }}</h3>
            <p class="text-[10px] text-amber-400 font-medium">{{ $metrics['alertas_repuestos'] }} alertas stock ➔</p>
        </a>

        <!-- Analítica KPI -->
        @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
        <a href="{{ route('reportes.index') }}" class="p-4 rounded-2xl bg-slate-900 hover:bg-slate-800/80 border border-slate-800 shadow-xl transition space-y-1 block">
            <p class="text-[11px] font-bold text-purple-400 uppercase">Reportes KPI</p>
            <h3 class="text-2xl font-extrabold text-purple-400">99.9%</h3>
            <p class="text-[10px] text-purple-300 font-medium">Disponibilidad ➔</p>
        </a>
        @endif
    </div>

    <!-- Main Navigation Modules Section -->
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Módulos del Sistema CMMS</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Módulo 1: Activos -->
            <a href="{{ route('activos.index') }}" class="p-6 rounded-3xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition group shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-blue-400 transition">1. Activos Industriales</h4>
                        <p class="text-xs text-slate-400 mt-1">Fichas técnicas, códigos QR, árboles jerárquicos y medidores de horómetro.</p>
                    </div>
                </div>
            </a>

            <!-- Módulo 2: OTs -->
            <a href="{{ route('ordenes.index') }}" class="p-6 rounded-3xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition group shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-indigo-400 transition">2. Órdenes de Trabajo</h4>
                        <p class="text-xs text-slate-400 mt-1">Flujo operativo completo: Solicitud, Aprobación, Asignación y Cierre con fotos antes/después.</p>
                    </div>
                </div>
            </a>

            <!-- Módulo 3: Preventivo -->
            <a href="{{ route('planes.index') }}" class="p-6 rounded-3xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition group shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-emerald-400 transition">3. Rutinas Preventivas</h4>
                        <p class="text-xs text-slate-400 mt-1">Planes periódicos por fecha u horómetro y generación automática de OTs por Cron Job.</p>
                    </div>
                </div>
            </a>

            <!-- Módulo 4: Almacén & Repuestos -->
            <a href="{{ route('repuestos.index') }}" class="p-6 rounded-3xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition group shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-600/20 text-amber-400 border border-amber-500/30 flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-amber-400 transition">4. Inventario & Almacén</h4>
                        <p class="text-xs text-slate-400 mt-1">Kárdex imborrable de Entradas/Salidas, ubicación en estantes y alertas de stock mínimo.</p>
                    </div>
                </div>
            </a>

            <!-- Módulo 5: Reportes KPI & Pareto -->
            @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
            <a href="{{ route('reportes.index') }}" class="p-6 rounded-3xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition group shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-600/20 text-purple-400 border border-purple-500/30 flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white group-hover:text-purple-400 transition">5. Reportes KPI & Pareto</h4>
                        <p class="text-xs text-slate-400 mt-1">Indicadores MTBF, MTTR, Disponibilidad %, Ley de Pareto 80/20 y exportación CSV.</p>
                    </div>
                </div>
            </a>
            @endif

            <!-- Módulo 6: Mobile API -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-cyan-500/30 shadow-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-600/20 text-cyan-400 border border-cyan-500/30 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white">6. API REST Flutter</h4>
                        <p class="text-xs text-slate-400 mt-1">Endpoints Sanctum activos para sincronización y recepción de fotos desde el smartphone del técnico.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
