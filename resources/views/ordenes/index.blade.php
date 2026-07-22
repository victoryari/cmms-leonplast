@extends('layouts.app')

@section('title', 'Órdenes de Trabajo (OTs)')

@section('content')
<div class="space-y-6" x-data="{ assignModalOpen: false, selectedOtId: null, selectedOtCode: '' }">

    <!-- Header Title & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Órdenes de Trabajo</h2>
            <p class="text-xs text-slate-400 mt-1">Control operativo de mantenimientos preventivos, correctivos e intervenciones de planta</p>
        </div>

        <a href="{{ route('ordenes.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Solicitar Mantenimiento</span>
        </a>
    </div>

    <!-- Status Tabs / Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('ordenes.index', ['estado' => 'Pendiente']) }}" 
           class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">⏳ OTs Pendientes</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['pendientes'] }}</p>
        </a>

        <a href="{{ route('ordenes.index', ['estado' => 'Aprobada']) }}" 
           class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-blue-500/50 transition">
            <p class="text-[11px] font-semibold text-blue-400 uppercase">📋 OTs Aprobadas</p>
            <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $metrics['aprobadas'] }}</p>
        </a>

        <a href="{{ route('ordenes.index', ['estado' => 'En_Progreso']) }}" 
           class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 transition">
            <p class="text-[11px] font-semibold text-indigo-400 uppercase">⚡ En Ejecución</p>
            <p class="text-2xl font-extrabold text-indigo-400 mt-1">{{ $metrics['en_progreso'] }}</p>
        </a>

        <a href="{{ route('ordenes.index', ['estado' => 'Completada']) }}" 
           class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 transition">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">✅ Completadas</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['completadas'] }}</p>
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('ordenes.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por OT, título o activo..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <select name="estado" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Aprobada" {{ request('estado') == 'Aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="En_Progreso" {{ request('estado') == 'En_Progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="En_Revision" {{ request('estado') == 'En_Revision' ? 'selected' : '' }}>En Revisión</option>
                    <option value="Completada" {{ request('estado') == 'Completada' ? 'selected' : '' }}>Completada</option>
                </select>
            </div>

            <div>
                <select name="prioridad" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Todas las Prioridades</option>
                    <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                    <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>Media</option>
                    <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                    <option value="Crítica" {{ request('prioridad') == 'Crítica' ? 'selected' : '' }}>Crítica</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar OTs
                </button>
                @if(request()->hasAny(['search', 'estado', 'prioridad', 'tipo_ot']))
                <a href="{{ route('ordenes.index') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-xl border border-slate-700" title="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Work Orders List Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-4 px-6">Código / Tipo</th>
                        <th class="py-4 px-6">Título de la Orden & Activo</th>
                        <th class="py-4 px-6">Solicitante</th>
                        <th class="py-4 px-6">Técnico Asignado</th>
                        <th class="py-4 px-6">Prioridad</th>
                        <th class="py-4 px-6">Estado</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($ordenes as $ot)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400">
                            <div>{{ $ot->codigo_ot }}</div>
                            <span class="inline-block text-[10px] text-slate-500 font-sans font-normal mt-0.5">{{ $ot->tipo_ot }}</span>
                        </td>

                        <td class="py-4 px-6 max-w-xs">
                            <a href="{{ route('ordenes.show', $ot->id) }}" class="font-bold text-white hover:text-blue-400 transition block truncate">
                                {{ $ot->titulo }}
                            </a>
                            <p class="text-[11px] text-slate-400 mt-0.5 truncate flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span>{{ $ot->activo?->nombre ?? 'Activo N/A' }}</span>
                            </p>
                        </td>

                        <td class="py-4 px-6">
                            <span class="text-slate-200 font-medium block">{{ $ot->solicitante?->nombre_completo ?? 'N/A' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $ot->fecha_solicitud?->diffForHumans() }}</span>
                        </td>

                        <td class="py-4 px-6">
                            @if($ot->tecnico)
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[10px]">
                                    {{ substr($ot->tecnico->nombres, 0, 1) }}
                                </div>
                                <span class="text-slate-300 font-medium">{{ $ot->tecnico->nombre_completo }}</span>
                            </div>
                            @else
                            <span class="text-slate-500 italic text-[11px]">Sin asignar</span>
                            @endif
                        </td>

                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($ot->prioridad == 'Crítica') bg-rose-500/10 text-rose-400 border-rose-500/30
                                @elseif($ot->prioridad == 'Alta') bg-amber-500/10 text-amber-400 border-amber-500/30
                                @elseif($ot->prioridad == 'Media') bg-blue-500/10 text-blue-400 border-blue-500/30
                                @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                                {{ $ot->prioridad }}
                            </span>
                        </td>

                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($ot->estado == 'Completada') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @elseif($ot->estado == 'En_Progreso') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                                @elseif($ot->estado == 'Aprobada') bg-blue-500/10 text-blue-400 border-blue-500/30
                                @elseif($ot->estado == 'Pendiente') bg-amber-500/10 text-amber-400 border-amber-500/30
                                @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                                ● {{ str_replace('_', ' ', $ot->estado) }}
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right space-x-2">
                            @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']) && !$ot->tecnico_id)
                            <button @click="assignModalOpen = true; selectedOtId = {{ $ot->id }}; selectedOtCode = '{{ $ot->codigo_ot }}'" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500 hover:text-white border border-amber-500/30 font-semibold text-[11px] transition">
                                Asignar
                            </button>
                            @endif

                            <a href="{{ route('ordenes.show', $ot->id) }}" class="px-3 py-1 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 font-semibold text-[11px] transition">
                                Detalles
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No hay órdenes de trabajo que coincidan con el filtro seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $ordenes->links() }}
    </div>

    <!-- Quick Assign Technician Modal (Supervisors/Admins) -->
    <div x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Asignar Técnico a <span class="text-blue-400 font-mono" x-text="selectedOtCode"></span></h3>
            
            <form :action="'/ordenes-trabajo/' + selectedOtId + '/asignar'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Seleccionar Técnico Responsable *</label>
                    <select name="tecnico_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="">Seleccione Técnico</option>
                        @foreach($tecnicos as $tec)
                        <option value="{{ $tec->id }}">{{ $tec->nombre_completo }} ({{ $tec->especialidad ?? 'Mantenimiento' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Prioridad *</label>
                    <select name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="Baja">Baja</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Crítica">Crítica</option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30">Aprobar y Asignar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
