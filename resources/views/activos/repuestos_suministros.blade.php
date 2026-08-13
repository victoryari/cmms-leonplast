@extends('layouts.app')

@section('title', 'Catálogo de Registro de Repuestos & Suministros')

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
                        <span>🏬 Repuestos y Suministros</span>
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
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>🔧 Herramientas</span>
                    </a>

                    <a href="{{ route('activos.repuestos-suministros') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-bold text-blue-400 bg-blue-600/20 border border-blue-500/30 transition">
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

        <div class="flex items-center space-x-3">
            <a href="{{ route('repuestos.create') }}" 
               class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Registrar Ficha de Repuesto / Artículo</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Artículos Registrados</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_articulos'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-cyan-400 uppercase">Categorías</p>
            <p class="text-2xl font-extrabold text-cyan-400 mt-1">{{ $metrics['categorias'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-indigo-400 uppercase">Marcas Fabricantes</p>
            <p class="text-2xl font-extrabold text-indigo-400 mt-1">{{ $metrics['marcas'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">Alertas en Almacén</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['criticos'] }}</p>
        </div>
    </div>

    <!-- Sub-Barra & Filtro de Búsqueda -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-2 text-xs text-slate-400 font-medium">
            <span>Catálogo oficial de fichas técnicas de repuestos, consumibles e insumos industriales</span>
        </div>

        <form method="GET" action="{{ route('activos.repuestos-suministros') }}" class="flex items-center space-x-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU, nombre o marca..."
                   class="bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 w-full sm:w-64">
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 border border-slate-700">
                🔍
            </button>
        </form>
    </div>

    <!-- Spare Parts Catalogue Data Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300 border-collapse">
                <thead class="bg-slate-950/90 text-slate-400 font-semibold border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Código SKU / Parte</th>
                        <th class="py-3 px-4">Nombre del Repuesto / Suministro</th>
                        <th class="py-3 px-4">Categoría</th>
                        <th class="py-3 px-4">Marca / Fabricante</th>
                        <th class="py-3 px-4">Unidad de Medida</th>
                        <th class="py-3 px-4">Ubicación en Almacén</th>
                        <th class="py-3 px-4 text-right">Costo Unit. (S/.)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($repuestos as $r)
                    <tr class="hover:bg-slate-800/40 transition group">
                        <td class="py-3 px-4 font-mono text-[11px] text-cyan-400 font-bold">
                            {{ $r->codigo_sku }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-white block">{{ $r->nombre }}</span>
                            @if($r->descripcion)
                            <span class="text-[10px] text-slate-500 block truncate max-w-xs">{{ $r->descripcion }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            {{ $r->categoria }}
                        </td>
                        <td class="py-3 px-4 text-slate-400">
                            {{ $r->marca ?? 'Genérico' }}
                        </td>
                        <td class="py-3 px-4 text-slate-300 font-mono text-[11px]">
                            {{ $r->unidad_medida ?? 'Unidades' }}
                        </td>
                        <td class="py-3 px-4 text-slate-400">
                            {{ $r->ubicacion_almacen ?? 'Almacén Central' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-400 font-bold">
                            S/. {{ number_format($r->costo_unitario, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                            No se encontraron repuestos o suministros registrados en el catálogo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($repuestos->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            {{ $repuestos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
