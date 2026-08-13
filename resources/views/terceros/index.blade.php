@extends('layouts.app')

@section('title', 'Terceros - Proveedores & Contratistas')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs font-bold font-mono">
                    📂 CATÁLOGOS
                </span>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Terceros: Proveedores & Contratistas</h2>
            </div>
            <p class="text-xs text-slate-400 mt-1">Directorio de empresas proveedoras de repuestos y contratistas de servicios técnicos de mantenimiento</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('terceros.create') }}" 
               class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Registrar Nuevo Tercero</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Terceros</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">Proveedores Repuestos</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['proveedores'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-cyan-400 uppercase">Contratistas Servicios</p>
            <p class="text-2xl font-extrabold text-cyan-400 mt-1">{{ $metrics['contratistas'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Habilitados / Activos</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['activos'] }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('terceros.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por RUC, Razón Social o Contacto..."
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="tipo" onchange="this.form.submit()" class="w-full sm:w-56 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none">
                <option value="">Todos los Tipos</option>
                <option value="Proveedor_Repuestos" {{ request('tipo') == 'Proveedor_Repuestos' ? 'selected' : '' }}>🏬 Proveedores de Repuestos</option>
                <option value="Contratista_Servicios" {{ request('tipo') == 'Contratista_Servicios' ? 'selected' : '' }}>🛠️ Contratistas de Servicios</option>
                <option value="Ambos" {{ request('tipo') == 'Ambos' ? 'selected' : '' }}>🌐 Proveedor y Contratista</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700">
                Filtrar
            </button>

            @if(request()->anyFilled(['search', 'tipo']))
            <a href="{{ route('terceros.index') }}" class="text-xs text-slate-400 hover:text-white underline">
                Limpiar Filtros
            </a>
            @endif
        </form>
    </div>

    <!-- Terceros List Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">RUC / Empresa</th>
                        <th class="py-3.5 px-4">Tipo de Servicio</th>
                        <th class="py-3.5 px-4">Contacto Principal</th>
                        <th class="py-3.5 px-4">Teléfono / Email</th>
                        <th class="py-3.5 px-4 text-center">Calificación</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs">
                    @forelse($terceros as $t)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400 font-bold text-xs shrink-0">
                                    🏢
                                </div>
                                <div>
                                    <a href="{{ route('terceros.show', $t->id) }}" class="font-bold text-white hover:text-blue-400 transition block">
                                        {{ $t->razon_social }}
                                    </a>
                                    <span class="font-mono text-[10px] text-slate-400">RUC: {{ $t->ruc_documento }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold border 
                                @if($t->tipo == 'Proveedor_Repuestos') bg-amber-500/10 text-amber-300 border-amber-500/30
                                @elseif($t->tipo == 'Contratista_Servicios') bg-cyan-500/10 text-cyan-300 border-cyan-500/30
                                @else bg-indigo-500/10 text-indigo-300 border-indigo-500/30 @endif">
                                {{ $t->tipo_label }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-slate-200">
                            {{ $t->contacto_nombre ?? 'Sin contacto' }}
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="text-slate-200">{{ $t->telefono ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $t->email ?? '-' }}</p>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center space-x-0.5 text-amber-400 text-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $t->calificacion ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $t->activo ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/10 text-slate-400 border-slate-500/30' }}">
                                {{ $t->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('terceros.show', $t->id) }}" class="px-2.5 py-1 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 text-[11px] font-bold">
                                    Ver Ficha
                                </a>
                                <a href="{{ route('terceros.edit', $t->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700 text-[11px] font-bold">
                                    Editar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            No se encontraron empresas o terceros registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($terceros->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $terceros->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
