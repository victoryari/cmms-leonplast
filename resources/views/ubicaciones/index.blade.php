@extends('layouts.app')

@section('title', 'Ubicaciones & Sedes')

@section('content')
<div class="space-y-6">

    <!-- Header & Dropdown Selector (Estilo Imagen Referencia) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3" x-data="{ open: false }">
            
            <!-- Dropdown Main Button (📍 Ubicaciones ˅) -->
            <div class="relative w-full sm:w-64">
                <button @click="open = !open" type="button" 
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition">
                    <div class="flex items-center space-x-2">
                        <span>📍 Ubicaciones</span>
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
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-bold text-blue-400 bg-blue-600/20 border border-blue-500/30 transition">
                        <span>📍 Ubicaciones & Sedes</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Equipo']) }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>⚙️ Equipos</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Herramienta']) }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>🔧 Herramientas</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Repuesto_Suministro']) }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <span>🏬 Repuestos y Suministros</span>
                    </a>

                    <a href="{{ route('activos.index', ['tipo_clasificacion' => 'Digital']) }}" 
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

        <a href="{{ route('ubicaciones.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Ubicación / Sede</span>
        </a>
    </div>

    <!-- Sub-Barra: Toggle Lista vs Árbol & Breadcrumb (Exacto a la Imagen de Referencia) -->
    <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center space-x-3">
            <!-- Toggle Buttons (Lista vs Árbol) -->
            <div class="flex items-center p-1 rounded-xl bg-slate-950 border border-slate-800">
                <a href="{{ route('ubicaciones.index') }}" 
                   class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ !request('vista') ? 'bg-slate-800 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Lista</span>
                </a>

                <a href="{{ route('activos.index', ['vista' => 'arbol']) }}" 
                   class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request('vista') == 'arbol' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 10-3 0m3 0h10m0 0a1.5 1.5 0 103 0m-3 0v-6a1.5 1.5 0 10-3 0m3 6v6a1.5 1.5 0 11-3 0"></path></svg>
                    <span>Árbol</span>
                </a>
            </div>

            <!-- Breadcrumb -->
            <div class="flex items-center space-x-2 text-xs text-slate-400 font-medium">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Inicio</span>
                <span>/</span>
                <span class="text-white font-bold uppercase tracking-wider">LEÓN PLAST / SEDES & UBICACIONES</span>
            </div>
        </div>

        <!-- Mini Filtro de Búsqueda -->
        <form method="GET" action="{{ route('ubicaciones.index') }}" class="flex items-center space-x-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Filtrar ubicaciones..."
                   class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 w-full sm:w-64">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 border border-slate-700">
                🔍
            </button>
        </form>
    </div>

    <!-- Ubicaciones Data Table (Estructura Fiel a la Imagen de Referencia) -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300 border-collapse">
                <thead class="bg-slate-950/90 text-slate-400 font-semibold border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-3 px-3 w-8 text-center">
                            <input type="checkbox" class="rounded bg-slate-950 border-slate-800 text-blue-600 focus:ring-0">
                        </th>
                        <th class="py-3 px-4 text-center">Habilitado</th>
                        <th class="py-3 px-4 text-center">Fuera de servicio</th>
                        <th class="py-3 px-4">Descripción</th>
                        <th class="py-3 px-4">Nombre</th>
                        <th class="py-3 px-4">Dirección</th>
                        <th class="py-3 px-4">Ciudad</th>
                        <th class="py-3 px-4">Código Área</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($ubicaciones as $u)
                    <tr class="hover:bg-slate-800/40 transition group">
                        
                        <!-- Checkbox + Plus Expand Icon (Igual a la imagen) -->
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center space-x-1.5 justify-center">
                                <input type="checkbox" class="rounded bg-slate-950 border-slate-800 text-blue-600 focus:ring-0">
                                <span class="text-slate-500 font-bold text-xs cursor-pointer hover:text-white">+</span>
                            </div>
                        </td>

                        <!-- Habilitado Badge (Si / No) -->
                        <td class="py-3 px-4 text-center">
                            @if($u->activo)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 inline-block">
                                Si
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800/80 text-slate-400 border border-slate-700 inline-block">
                                No
                            </span>
                            @endif
                        </td>

                        <!-- Fuera de Servicio Badge (Si / No) -->
                        <td class="py-3 px-4 text-center">
                            @if(!$u->activo)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 inline-block">
                                Si
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800/80 text-slate-400 border border-slate-700 inline-block">
                                No
                            </span>
                            @endif
                        </td>

                        <!-- Descripción -->
                        <td class="py-3 px-4 text-slate-300 max-w-xs truncate">
                            {{ $u->notas ?? ($u->nombre . ' ' . $u->direccion . ' ' . $u->ciudad) }}
                        </td>

                        <!-- Nombre de la Ubicación -->
                        <td class="py-3 px-4">
                            <a href="{{ route('ubicaciones.show', $u->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                {{ $u->nombre }}
                            </a>
                            @if($u->empresa_subsidiaria)
                            <span class="block text-[10px] text-slate-500">{{ $u->empresa_subsidiaria }}</span>
                            @endif
                        </td>

                        <!-- Dirección -->
                        <td class="py-3 px-4 text-slate-300">
                            {{ $u->direccion ?? '-' }}
                        </td>

                        <!-- Ciudad -->
                        <td class="py-3 px-4 text-slate-200 font-semibold">
                            {{ $u->ciudad }}
                        </td>

                        <!-- Código Área / Postal -->
                        <td class="py-3 px-4 font-mono text-[11px] text-cyan-400 font-bold">
                            {{ $u->codigo_postal ?? $u->codigo_ubicacion }}
                        </td>

                        <!-- Acciones: Editar y Eliminar Lógicamente -->
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                
                                <!-- Ver Detalle -->
                                <a href="{{ route('ubicaciones.show', $u->id) }}" 
                                   class="p-1.5 rounded-lg bg-blue-600/20 text-blue-400 hover:bg-blue-600 hover:text-white border border-blue-500/30 transition" title="Ver Ficha">
                                    👁️
                                </a>

                                <!-- Editar -->
                                <a href="{{ route('ubicaciones.edit', $u->id) }}" 
                                   class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700 transition" title="Editar Ubicación">
                                    ✏️
                                </a>

                                <!-- Eliminar Lógicamente (Inactivar / Habilitar Toggle) -->
                                <form action="{{ route('ubicaciones.destroy', $u->id) }}" method="POST" 
                                      onsubmit="return confirm('¿Está seguro de {{ $u->activo ? 'inactivar / eliminar lógicamente' : 'habilitar' }} la ubicación {{ $u->nombre }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg {{ $u->activo ? 'bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white border border-rose-500/30' : 'bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600 hover:text-white border border-emerald-500/30' }} transition" 
                                            title="{{ $u->activo ? 'Eliminar Lógicamente (Inactivar)' : 'Habilitar Ubicación' }}">
                                        {{ $u->activo ? '🗑️' : '✓' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500 text-xs">
                            No se encontraron ubicaciones o sedes registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ubicaciones->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            {{ $ubicaciones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
