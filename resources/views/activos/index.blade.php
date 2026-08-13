@extends('layouts.app')

@section('title', 'Gestión de Activos Industriales')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs font-bold font-mono">
                    📂 CATÁLOGOS
                </span>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Activos Industriales</h2>
            </div>
            <p class="text-xs text-slate-400 mt-1">Clasificación de planta por Ubicaciones, Equipos, Herramientas, Repuestos/Suministros y Digitales</p>
        </div>
        <a href="{{ route('activos.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Nuevo Activo</span>
        </a>
    </div>

    <!-- Dropdown Selector & Navigation Filter Bar (Estilo Referencia Imagen) -->
    <div class="p-4 rounded-3xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center space-x-3 w-full md:w-auto" x-data="{ open: false }">
            
            <!-- Dropdown Main Button -->
            <div class="relative w-full sm:w-64">
                <button @click="open = !open" type="button" 
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition">
                    <div class="flex items-center space-x-2">
                        @if(request('tipo_clasificacion') == 'Ubicacion')
                            <span>📍 Ubicaciones</span>
                        @elseif(request('tipo_clasificacion') == 'Equipo')
                            <span>⚙️ Equipos</span>
                        @elseif(request('tipo_clasificacion') == 'Herramienta')
                            <span>🔧 Herramientas</span>
                        @elseif(request('tipo_clasificacion') == 'Repuesto_Suministro')
                            <span>🏬 Repuestos y Suministros</span>
                        @elseif(request('tipo_clasificacion') == 'Digital')
                            <span>💻 Digitales</span>
                        @elseif(request('vista') == 'arbol')
                            <span>🗺️ Vista Árbol Jerárquico</span>
                        @else
                            <span>📚 Todos los Activos</span>
                        @endif
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <!-- Dropdown Menu Items (Estilo Imagen Referencia) -->
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute left-0 mt-2 w-72 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl z-50 p-2 space-y-1">
                    
                    <a href="{{ route('activos.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ !request('tipo_clasificacion') && !request('vista') ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
                        <span>📚 Todos los Activos</span>
                    </a>

                    <a href="{{ route('ubicaciones.index') }}" 
                       class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ request()->routeIs('ubicaciones.*') || request('tipo_clasificacion') == 'Ubicacion' ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
                        <span>📍 Ubicaciones & Sedes</span>
                        <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-1.5 py-0.5 rounded font-mono">Gestión</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Equipo']) }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ request('tipo_clasificacion') == 'Equipo' ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
                        <span>⚙️ Equipos</span>
                    </a>

                    <a href="{{ route('activos.herramientas') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ request()->routeIs('activos.herramientas') ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
                        <span>🔧 Herramientas</span>
                    </a>

                    <a href="{{ route('activos.repuestos-suministros') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ request()->routeIs('activos.repuestos-suministros') ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
                        <span>🏬 Repuestos y Suministros</span>
                    </a>

                    <a href="{{ route('activos.digitales') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 transition {{ request()->routeIs('activos.digitales') ? 'bg-blue-600/20 text-blue-400 font-bold border border-blue-500/30' : 'text-slate-300' }}">
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

        <!-- Metric Badges by Classification -->
        <div class="flex items-center gap-2 overflow-x-auto text-[11px] font-semibold">
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                📍 Ubicaciones: <strong class="text-white">{{ $metrics['ubicaciones'] }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                ⚙️ Equipos: <strong class="text-blue-400">{{ $metrics['equipos'] }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                🔧 Herramientas: <strong class="text-amber-400">{{ $metrics['herramientas'] }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                💻 Digitales: <strong class="text-cyan-400">{{ $metrics['digitales'] }}</strong>
            </span>
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

    @if(request('vista') === 'arbol')
    <!-- Vista Árbol Jerárquico (Tree View de la Planta - Referencia Imagen) -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-cyan-400 flex items-center space-x-2">
                <span>🗺️ Vista Árbol Jerárquico de la Planta</span>
            </h3>
            <span class="text-xs text-slate-400">Estructura Naves ➔ Equipos ➔ Componentes</span>
        </div>

        <div class="space-y-3 font-sans">
            @forelse($arbolActivos as $nodoRaiz)
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="p-2 rounded-xl bg-cyan-600/20 text-cyan-400 border border-cyan-500/30 text-sm font-bold">
                            📍 {{ $nodoRaiz->tipo_clasificacion }}
                        </span>
                        <div>
                            <a href="{{ route('activos.show', $nodoRaiz->id) }}" class="font-extrabold text-sm text-white hover:text-cyan-400 transition">
                                {{ $nodoRaiz->nombre }}
                            </a>
                            <span class="font-mono text-[11px] text-slate-400 block">{{ $nodoRaiz->codigo_activo }} ({{ $nodoRaiz->ubicacion ?? 'Planta General' }})</span>
                        </div>
                    </div>

                    <a href="{{ route('activos.show', $nodoRaiz->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700">
                        Ver Detalle ➔
                    </a>
                </div>

                <!-- Sub-nodos / Equipos Hijos -->
                @if($nodoRaiz->children->count() > 0)
                <div class="ml-6 pl-4 border-l-2 border-slate-800 space-y-2 pt-2">
                    @foreach($nodoRaiz->children as $hijo)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition">
                        <div class="flex items-center space-x-2.5">
                            <span class="text-xs">
                                @if($hijo->tipo_clasificacion == 'Equipo') ⚙️ 
                                @elseif($hijo->tipo_clasificacion == 'Herramienta') 🔧 
                                @elseif($hijo->tipo_clasificacion == 'Digital') 💻 
                                @else 📦 @endif
                            </span>
                            <div>
                                <a href="{{ route('activos.show', $hijo->id) }}" class="font-bold text-xs text-slate-200 hover:text-blue-400 transition">
                                    {{ $hijo->nombre }}
                                </a>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $hijo->codigo_activo }} - {{ $hijo->categoria }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $hijo->estado_operativo == 'Operativo' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border-amber-500/30' }}">
                            ● {{ str_replace('_', ' ', $hijo->estado_operativo) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="py-8 text-center text-slate-500 text-xs">
                No hay activos jerárquicos registrados como raíz.
            </div>
            @endforelse
        </div>
    </div>
    @else

    <!-- Assets Grid Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800 text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Código / Equipo</th>
                        <th class="py-3.5 px-4">Clasificación</th>
                        <th class="py-3.5 px-4">Categoría</th>
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
                                @if($activo->imagen_principal_url)
                                <img src="{{ $activo->imagen_principal_url }}" alt="{{ $activo->nombre }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-500/20 flex items-center justify-center font-mono font-bold text-[10px] shrink-0">
                                    {{ substr($activo->codigo_activo, -3) }}
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('activos.show', $activo->id) }}" class="font-bold text-white group-hover:text-blue-400 transition block text-sm">
                                        {{ $activo->nombre }}
                                    </a>
                                    <span class="font-mono text-[11px] text-slate-400">{{ $activo->codigo_activo }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border 
                                @if($activo->tipo_clasificacion == 'Ubicacion') bg-cyan-500/10 text-cyan-300 border-cyan-500/30
                                @elseif($activo->tipo_clasificacion == 'Equipo') bg-blue-500/10 text-blue-300 border-blue-500/30
                                @elseif($activo->tipo_clasificacion == 'Herramienta') bg-amber-500/10 text-amber-300 border-amber-500/30
                                @elseif($activo->tipo_clasificacion == 'Digital') bg-purple-500/10 text-purple-300 border-purple-500/30
                                @else bg-slate-800 text-slate-300 border-slate-700 @endif">
                                {{ str_replace('_', ' ', $activo->tipo_clasificacion) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-slate-300 font-medium block">{{ $activo->categoria }}</span>
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
    @endif

</div>
@endsection
