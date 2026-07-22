@extends('layouts.app')

@section('title', 'Gestión de Activos Industriales')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Registro Maestro de Activos</h2>
            <p class="text-xs text-slate-400 mt-1">Gestión técnica de Inyectoras, Grúas, Compresores y Maquinaria de Planta</p>
        </div>

        @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
        <a href="{{ route('activos.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Nuevo Activo</span>
        </a>
        @endif
    </div>

    <!-- Metrics Header Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Activos</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-emerald-500/20">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">● Operativos</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['operativos'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-amber-500/20">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">● Mantenimiento</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['mantenimiento'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-rose-500/20">
            <p class="text-[11px] font-semibold text-rose-400 uppercase">● Reparación</p>
            <p class="text-2xl font-extrabold text-rose-400 mt-1">{{ $metrics['reparacion'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-purple-500/20">
            <p class="text-[11px] font-semibold text-purple-400 uppercase">● Fuera de Servicio</p>
            <p class="text-2xl font-extrabold text-purple-400 mt-1">{{ $metrics['fuera_servicio'] }}</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('activos.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código, nombre, marca..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <select name="categoria" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->nombre }}" {{ request('categoria') == $cat->nombre ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="estado_operativo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Todos los Estados</option>
                    <option value="Operativo" {{ request('estado_operativo') == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                    <option value="Mantenimiento" {{ request('estado_operativo') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="Reparacion" {{ request('estado_operativo') == 'Reparacion' ? 'selected' : '' }}>Reparación</option>
                    <option value="Fuera_de_servicio" {{ request('estado_operativo') == 'Fuera_de_servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar Activos
                </button>
                @if(request()->hasAny(['search', 'categoria', 'estado_operativo', 'area']))
                <a href="{{ route('activos.index') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-xl border border-slate-700" title="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Grid View -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activos as $activo)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition duration-200 flex flex-col justify-between shadow-xl">
            <div>
                <!-- Top Row: Badge & QR Link -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-800 text-blue-400 border border-slate-700">
                            {{ $activo->codigo_activo }}
                        </span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border
                            @if($activo->estado_operativo == 'Operativo') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                            @elseif($activo->estado_operativo == 'Mantenimiento') bg-amber-500/10 text-amber-400 border-amber-500/30
                            @elseif($activo->estado_operativo == 'Reparacion') bg-rose-500/10 text-rose-400 border-rose-500/30
                            @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                            ● {{ str_replace('_', ' ', $activo->estado_operativo) }}
                        </span>
                    </div>

                    <a href="{{ route('activos.print-qr', $activo->id) }}" target="_blank" class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800" title="Imprimir Etiqueta QR">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2a2 2 0 002-2v-5a2 2 0 00-2-2H4a2 2 0 00-2 2v5a2 2 0 002 2h2m4 0h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </a>
                </div>

                <!-- Asset Title & Info -->
                <h3 class="text-base font-bold text-white leading-snug hover:text-blue-400 transition">
                    <a href="{{ route('activos.show', $activo->id) }}">{{ $activo->nombre }}</a>
                </h3>
                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $activo->descripcion }}</p>

                <!-- Specs Breakdown -->
                <div class="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase font-semibold">Marca / Modelo</span>
                        <span class="text-slate-200 font-medium truncate block">{{ $activo->marca ?? 'N/A' }} {{ $activo->modelo }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase font-semibold">Ubicación Planta</span>
                        <span class="text-slate-200 font-medium truncate block">{{ $activo->ubicacion ?? 'Planta General' }}</span>
                    </div>
                </div>

                <!-- KPIs bar -->
                <div class="mt-3 p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/60 flex items-center justify-between text-[11px]">
                    <div>
                        <span class="text-slate-500">MTBF:</span>
                        <strong class="text-blue-400">{{ $activo->mtbf_horas ? number_format($activo->mtbf_horas, 1) . 'h' : 'N/A' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500">MTTR:</span>
                        <strong class="text-amber-400">{{ $activo->mttr_horas ? number_format($activo->mttr_horas, 1) . 'h' : 'N/A' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500">Disp:</span>
                        <strong class="text-emerald-400">{{ $activo->disponibilidad_porcentaje ? $activo->disponibilidad_porcentaje . '%' : '99.0%' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-medium">{{ $activo->categoria }}</span>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('activos.show', $activo->id) }}" class="px-3 py-1.5 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 text-xs font-semibold transition">
                        Ver Ficha
                    </a>

                    @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento']))
                    <a href="{{ route('activos.edit', $activo->id) }}" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 text-center bg-slate-900 border border-slate-800 rounded-3xl">
            <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h3 class="text-base font-bold text-white">No se encontraron activos registrados</h3>
            <p class="text-xs text-slate-400 mt-1">Prueba cambiando los criterios de búsqueda o registra un nuevo equipo de planta.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $activos->links() }}
    </div>

</div>
@endsection
