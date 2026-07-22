@extends('layouts.app')

@section('title', 'Planes de Mantenimiento Preventivo')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Planes de Mantenimiento Preventivo</h2>
            <p class="text-xs text-slate-400 mt-1">Programación automática de rutinas periódicas por calendario o lecturas de horómetro</p>
        </div>

        @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
        <a href="{{ route('planes.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Configurar Nuevo Plan</span>
        </a>
        @endif
    </div>

    <!-- Metrics Cards Header -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Rutinas</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Planes Activos</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['activos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">Vencidos / Pendientes</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['vencidos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Pausados</p>
            <p class="text-2xl font-extrabold text-slate-400 mt-1">{{ $metrics['pausados'] }}</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('planes.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por plan o máquina..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <select name="tipo_plan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none">
                    <option value="">Todos los Tipos</option>
                    <option value="Por_Calendario" {{ request('tipo_plan') == 'Por_Calendario' ? 'selected' : '' }}>Por Calendario (Días/Meses)</option>
                    <option value="Por_Medidor" {{ request('tipo_plan') == 'Por_Medidor' ? 'selected' : '' }}>Por Medidor (Horómetro)</option>
                </select>
            </div>

            <div>
                <select name="estado" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none">
                    <option value="">Todos los Estados</option>
                    <option value="Activo" {{ request('estado') == 'Activo' ? 'selected' : '' }}>Activos</option>
                    <option value="Pausado" {{ request('estado') == 'Pausado' ? 'selected' : '' }}>Pausados</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar Planes
                </button>
            </div>
        </form>
    </div>

    <!-- Preventive Plans Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($planes as $plan)
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 hover:border-slate-700 transition space-y-4 flex flex-col justify-between shadow-xl">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                        @if($plan->tipo_plan == 'Por_Calendario') bg-blue-500/10 text-blue-400 border-blue-500/30
                        @else bg-purple-500/10 text-purple-400 border-purple-500/30 @endif">
                        {{ $plan->tipo_plan == 'Por_Calendario' ? '📅 Calendario' : '⏱ Horómetro' }}
                    </span>

                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                        @if($plan->estado == 'Activo') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                        ● {{ $plan->estado }}
                    </span>
                </div>

                <a href="{{ route('planes.show', $plan->id) }}" class="block font-bold text-base text-white hover:text-emerald-400 transition leading-snug">
                    {{ $plan->nombre_plan }}
                </a>

                <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800/80 text-xs space-y-1">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Activo de Planta:</span>
                    <strong class="text-blue-400 font-mono font-bold">[{{ $plan->activo?->codigo_activo }}]</strong>
                    <span class="text-slate-300 font-medium ml-1">{{ $plan->activo?->nombre }}</span>
                </div>

                <div class="text-xs text-slate-400 space-y-1 pt-1">
                    <div class="flex justify-between">
                        <span>Frecuencia:</span>
                        <strong class="text-white">{{ $plan->frecuencia_texto }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Última Ejecución:</span>
                        <span class="text-slate-300">{{ $plan->ultima_ejecucion ? $plan->ultima_ejecucion->format('d/m/Y') : 'Sin registros' }}</span>
                    </div>
                    <div class="flex justify-between font-semibold pt-1 border-t border-slate-800/60">
                        <span>Próxima OT:</span>
                        <span class="{{ $plan->proxima_ejecucion && $plan->proxima_ejecucion <= now() ? 'text-amber-400 font-bold animate-pulse' : 'text-emerald-400' }}">
                            {{ $plan->proxima_ejecucion ? $plan->proxima_ejecucion->format('d/m/Y') : 'Inmediata' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between space-x-2">
                <a href="{{ route('planes.show', $plan->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                    Ver Detalles
                </a>

                <form method="POST" action="{{ route('planes.execute-now', $plan->id) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('¿Deseas generar inmediatamente la OT preventiva para este plan?')"
                            class="px-3 py-1.5 rounded-xl bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600 hover:text-white border border-emerald-500/30 text-xs font-bold transition flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>
                        <span>Ejecutar OT Ahora</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-500 bg-slate-900 border border-slate-800 rounded-3xl">
            No hay planes preventivos registrados que coincidan con la búsqueda.
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $planes->links() }}
    </div>

</div>
@endsection
