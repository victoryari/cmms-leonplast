@extends('layouts.app')

@section('title', 'Gestión de Activos Industriales')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Activos Industriales</h2>
            <p class="text-xs text-slate-400 mt-1">Inventario de máquinas de planta, inyectoras, auxiliares e historial técnico</p>
        </div>
        <a href="{{ route('activos.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Nuevo Activo</span>
        </a>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center font-bold text-sm">
                ⚙️
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Total Activos</span>
                <span class="text-lg font-extrabold text-white font-mono">{{ $metrics['total'] }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">
                ✓
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Operativos</span>
                <span class="text-lg font-extrabold text-emerald-400 font-mono">{{ $metrics['operativos'] }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
                🛠️
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">En Mantenimiento</span>
                <span class="text-lg font-extrabold text-amber-400 font-mono">{{ $metrics['mantenimiento'] }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-sm">
                🔧
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">En Reparación</span>
                <span class="text-lg font-extrabold text-purple-400 font-mono">{{ $metrics['reparacion'] }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-3.5 col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-sm">
                🚨
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Fuera de Servicio</span>
                <span class="text-lg font-extrabold text-rose-400 font-mono">{{ $metrics['fuera_servicio'] }}</span>
            </div>
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
                    @php $catNombre = is_object($cat) ? $cat->nombre : $cat; @endphp
                    <option value="{{ $catNombre }}" {{ request('categoria') == $catNombre ? 'selected' : '' }}>{{ $catNombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="estado_operativo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Todos los Estados</option>
                    @foreach($estadosOperativos as $est)
                    <option value="{{ $est }}" {{ request('estado_operativo') == $est ? 'selected' : '' }}>{{ str_replace('_', ' ', $est) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar Activos
                </button>
                @if(request()->hasAny(['search', 'categoria', 'estado_operativo', 'area']))
                <a href="{{ route('activos.index') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-xl border border-slate-700" title="Limpiar filtros">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Grid Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800 text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Código / Equipo</th>
                        <th class="py-3.5 px-4">Categoría</th>
                        <th class="py-3.5 px-4">Marca / Modelo</th>
                        <th class="py-3.5 px-4">Ubicación / Área</th>
                        <th class="py-3.5 px-4 text-center">Estado Operativo</th>
                        <th class="py-3.5 px-4 text-center">Condición</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($activos as $activo)
                    <tr class="hover:bg-slate-800/40 transition group">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-500/20 flex items-center justify-center font-mono font-bold text-[10px] shrink-0">
                                    {{ substr($activo->codigo_activo, -3) }}
                                </div>
                                <div>
                                    <a href="{{ route('activos.show', $activo->id) }}" class="font-bold text-white group-hover:text-blue-400 transition block text-sm">
                                        {{ $activo->nombre }}
                                    </a>
                                    <span class="font-mono text-[10px] text-slate-400 block">{{ $activo->codigo_activo }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $activo->categoria }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-slate-200 block font-semibold">{{ $activo->marca ?? '-' }}</span>
                            <span class="text-slate-400 text-[10px] block">{{ $activo->modelo ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-slate-300 block font-medium">{{ $activo->area ?? 'Planta General' }}</span>
                            <span class="text-slate-500 text-[10px] block">{{ $activo->ubicacion ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold border inline-block
                                @if($activo->estado_operativo == 'Operativo') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @elseif($activo->estado_operativo == 'Mantenimiento') bg-amber-500/10 text-amber-400 border-amber-500/30
                                @elseif($activo->estado_operativo == 'Reparacion') bg-purple-500/10 text-purple-400 border-purple-500/30
                                @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                                {{ str_replace('_', ' ', $activo->estado_operativo) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-[11px] font-semibold text-slate-300">
                                {{ $activo->estado_condicion ?? 'Bueno' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="{{ route('activos.show', $activo->id) }}" class="px-2.5 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white text-[11px] font-bold transition">
                                Ver Ficha
                            </a>
                            <a href="{{ route('activos.print-qr', $activo->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold border border-slate-700 transition" title="Imprimir Etiqueta QR">
                                📷 QR
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                            No se encontraron activos registrados con los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activos->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            {{ $activos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
