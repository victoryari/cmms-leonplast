@extends('layouts.app')

@section('title', 'Catálogo de Herramientas')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Main Dropdown Selector -->
        <div class="flex items-center space-x-3" x-data="{ open: false }">
            <div class="relative w-full sm:w-64">
                <button @click="open = !open" type="button" 
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition">
                    <div class="flex items-center space-x-2">
                        <span>🔧 Herramientas</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <!-- Dropdown Menu Items -->
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute left-0 mt-2 w-72 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl z-50 p-2 space-y-1">
                    
                    <a href="{{ route('activos.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>📚 Todos los Activos</span>
                    </a>

                    <a href="{{ route('ubicaciones.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>📍 Ubicaciones & Sedes</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Equipo']) }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>⚙️ Equipos</span>
                    </a>

                    <a href="{{ route('activos.herramientas') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-bold text-blue-400 bg-blue-600/20 border border-blue-500/30 transition">
                        <span>🔧 Herramientas</span>
                    </a>

                    <a href="{{ route('activos.repuestos-suministros') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>🏬 Repuestos y Suministros</span>
                    </a>

                    <a href="{{ route('activos.digitales') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>💻 Digitales</span>
                    </a>

                    <div class="border-t border-slate-800 pt-1">
                        <a href="{{ route('activos.index', ['vista' => 'arbol']) }}" 
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-bold text-cyan-400 hover:bg-cyan-500/10 transition">
                            <span>🗺️ Vista Árbol Jerárquico</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('activos.create', ['tipo_clasificacion' => 'Herramienta']) }}" 
           class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Nueva Herramienta</span>
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Herramientas</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Operativas</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['operativas'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">En Mantenimiento</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['mantenimiento'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-rose-400 uppercase">Criticidad Alta</p>
            <p class="text-2xl font-extrabold text-rose-400 mt-1">{{ $metrics['criticas'] }}</p>
        </div>
    </div>

    <!-- Sub-Barra & Filtro de Búsqueda -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-2 text-xs text-slate-400 font-medium">
            <span>Catálogo especializado de herramientas de taller, instrumentos de medición y calibración</span>
        </div>

        <form method="GET" action="{{ route('activos.herramientas') }}" class="flex items-center space-x-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, código o marca..."
                   class="bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 w-full sm:w-64">
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 border border-slate-700">
                🔍
            </button>
        </form>
    </div>

    <!-- Tools Data Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300 border-collapse">
                <thead class="bg-slate-950/90 text-slate-400 font-semibold border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Código / SKU</th>
                        <th class="py-3 px-4">Nombre de la Herramienta</th>
                        <th class="py-3 px-4">Marca / Modelo</th>
                        <th class="py-3 px-4">Ubicación Interna</th>
                        <th class="py-3 px-4 text-center">Estado Operativo</th>
                        <th class="py-3 px-4 text-center">Criticidad</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($activos as $a)
                    <tr class="hover:bg-slate-800/40 transition group">
                        <td class="py-3 px-4 font-mono text-[11px] text-blue-400 font-bold">
                            {{ $a->codigo_activo }}
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('activos.show', $a->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                {{ $a->nombre }}
                            </a>
                            <span class="block text-[10px] text-slate-500">{{ $a->categoria }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            {{ $a->marca ?? '-' }} {{ $a->modelo ? '/ ' . $a->modelo : '' }}
                        </td>
                        <td class="py-3 px-4 text-slate-400">
                            {{ $a->ubicacion ?? 'Taller Central' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border 
                                {{ $a->estado_operativo == 'Operativo' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border-amber-500/30' }}">
                                {{ str_replace('_', ' ', $a->estado_operativo) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border 
                                {{ ($a->estado_condicion == 'Critico' || $a->estado_condicion == 'Malo') ? 'bg-rose-500/10 text-rose-400 border-rose-500/30' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                {{ $a->estado_condicion ?? 'Bueno' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('activos.show', $a->id) }}" class="p-1.5 rounded-lg bg-blue-600/20 text-blue-400 hover:bg-blue-600 hover:text-white border border-blue-500/30 transition" title="Ver Ficha">👁️</a>
                                <a href="{{ route('activos.edit', $a->id) }}" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700 transition" title="Editar">✏️</a>
                                <a href="{{ route('activos.print-qr', $a->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-cyan-400 hover:bg-slate-700 border border-slate-700 transition" title="Imprimir QR">QR</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                            No se encontraron herramientas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activos->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            {{ $activos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
