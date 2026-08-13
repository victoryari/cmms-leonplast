@extends('layouts.app')

@section('title', 'Ficha de Empresa: ' . $tercero->razon_social)

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('terceros.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                ←
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        RUC: {{ $tercero->ruc_documento }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $tercero->activo ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/10 text-slate-400 border-slate-500/30' }}">
                        ● {{ $tercero->activo ? 'Habilitado' : 'Inactivo' }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $tercero->razon_social }}</h2>
                @if($tercero->nombre_comercial)
                <p class="text-xs text-slate-400">Nombre Comercial: {{ $tercero->nombre_comercial }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('terceros.edit', $tercero->id) }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                ✏️ Editar Datos
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Commercial Details & Products/Services -->
        <div class="lg:col-span-2 space-y-6">

            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400">Datos Principales & Contacto</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Tipo de Servicio</span>
                        <strong class="text-white block mt-0.5">{{ $tercero->tipo_label }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Evaluación de Servicio</span>
                        <div class="flex items-center space-x-1 text-amber-400 text-sm mt-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= $tercero->calificacion ? '★' : '☆' }}</span>
                            @endfor
                            <span class="text-xs text-slate-400 font-bold ml-1">({{ $tercero->calificacion }}/5)</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Contacto Principal</span>
                        <strong class="text-white block mt-0.5">{{ $tercero->contacto_nombre ?? 'No especificado' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Teléfono / WhatsApp</span>
                        <strong class="text-white block mt-0.5">{{ $tercero->telefono ?? 'Sin registrar' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 sm:col-span-2">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Correo Electrónico</span>
                        <strong class="text-blue-400 block mt-0.5">{{ $tercero->email ?? 'Sin registrar' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 sm:col-span-2">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Dirección Fiscal / Sede</span>
                        <strong class="text-slate-200 block mt-0.5">{{ $tercero->direccion ?? 'N/A' }} ({{ $tercero->ciudad }})</strong>
                    </div>
                </div>

                @if($tercero->observaciones)
                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800/80 text-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Notas / Observaciones Homologación:</span>
                    <p class="text-slate-300 leading-relaxed">{{ $tercero->observaciones }}</p>
                </div>
                @endif
            </div>

            <!-- Activos de Planta Asociados a este Proveedor -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-cyan-400 flex items-center justify-between">
                    <span>📦 Activos Suministrados por esta Empresa ({{ $tercero->activos->count() }})</span>
                </h3>

                <div class="space-y-2">
                    @forelse($tercero->activos as $act)
                    <a href="{{ route('activos.show', $act->id) }}" class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800/80 hover:border-slate-700 flex items-center justify-between transition">
                        <div class="flex items-center space-x-3">
                            <span class="font-mono text-xs font-bold text-blue-400 px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20">
                                {{ $act->codigo_activo }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $act->nombre }}</h4>
                                <span class="text-[10px] text-slate-400">{{ $act->categoria }} - {{ $act->marca }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-400">Ver Ficha ➔</span>
                    </a>
                    @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No hay activos asignados a este proveedor en el inventario actual.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: Summary Statistics -->
        <div class="space-y-6">
            <div class="p-6 rounded-3xl bg-slate-900 border border-indigo-500/30 text-center space-y-4 shadow-xl">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-3xl mx-auto">
                    🏢
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">{{ $tercero->nombre_comercial ?? $tercero->razon_social }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">RUC: {{ $tercero->ruc_documento }}</p>
                </div>

                <div class="pt-3 border-t border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-800">
                        <span class="text-slate-400">Activos Suministrados:</span>
                        <strong class="text-white font-bold">{{ $tercero->activos->count() }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Repuestos en Catálogo:</span>
                        <strong class="text-amber-400 font-bold">{{ $tercero->repuestos->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
