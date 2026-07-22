@extends('layouts.app')

@section('title', "Ficha Técnica: {$activo->codigo_activo}")

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('activos.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        {{ $activo->codigo_activo }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border
                        @if($activo->estado_operativo == 'Operativo') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @elseif($activo->estado_operativo == 'Mantenimiento') bg-amber-500/10 text-amber-400 border-amber-500/30
                        @elseif($activo->estado_operativo == 'Reparacion') bg-rose-500/10 text-rose-400 border-rose-500/30
                        @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                        ● Estado: {{ str_replace('_', ' ', $activo->estado_operativo) }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $activo->nombre }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('activos.print-qr', $activo->id) }}" target="_blank" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-cyan-600/20 hover:bg-cyan-600 text-cyan-300 hover:text-white border border-cyan-500/30 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2a2 2 0 002-2v-5a2 2 0 00-2-2H4a2 2 0 00-2 2v5a2 2 0 002 2h2m4 0h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Imprimir Etiqueta QR</span>
            </a>

            @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento']))
            <a href="{{ route('activos.edit', $activo->id) }}" 
               class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Editar Ficha</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Main Grid: Technical Specs & QR Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Specs -->
        <div class="lg:col-span-2 space-y-6">

            <!-- KPIs Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase">MTBF (Fiabilidad)</p>
                    <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ number_format($activo->mtbf_horas ?? 350, 1) }}h</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Tiempo medio entre fallas</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase">MTTR (Mantenibilidad)</p>
                    <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ number_format($activo->mttr_horas ?? 3.5, 1) }}h</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Tiempo medio de reparación</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase">Disponibilidad</p>
                    <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $activo->disponibilidad_porcentaje ?? '99.0' }}%</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Eficiencia operativa de planta</p>
                </div>
            </div>

            <!-- Datos Generales Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Datos Generales del Equipo</span>
                </h3>

                <p class="text-xs text-slate-300 leading-relaxed">{{ $activo->descripcion }}</p>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-2 text-xs">
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Categoría</span>
                        <span class="text-white font-medium">{{ $activo->categoria }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Marca</span>
                        <span class="text-white font-medium">{{ $activo->marca ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Modelo</span>
                        <span class="text-white font-medium">{{ $activo->modelo ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Número de Serie</span>
                        <span class="text-mono text-slate-300 font-medium">{{ $activo->numero_serie ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Ubicación en Planta</span>
                        <span class="text-white font-medium">{{ $activo->ubicacion ?? 'Nave Principal' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-semibold text-[10px] uppercase">Área Operativa</span>
                        <span class="text-white font-medium">{{ $activo->area ?? 'Producción' }}</span>
                    </div>
                </div>
            </div>

            <!-- Especificaciones Técnicas Card -->
            @if(!empty($activo->especificaciones_tecnicas))
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    <span>Especificaciones Técnicas del Fabricante</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($activo->especificaciones_tecnicas as $clave => $valor)
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                        <span class="text-[10px] text-slate-500 block uppercase font-semibold">{{ $clave }}</span>
                        <strong class="text-xs text-slate-200 font-semibold">{{ $valor }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Right Col: QR Tag Card & Commercial Data -->
        <div class="space-y-6">

            <!-- QR Tag Interactive Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-cyan-500/30 text-center space-y-4 shadow-xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>Escaneo Móvil Flutter</span>
                </div>

                <div class="bg-white p-4 rounded-2xl inline-block shadow-2xl">
                    <img src="{{ $activo->qr_image_url }}" alt="QR {{ $activo->codigo_activo }}" class="w-44 h-44 mx-auto">
                </div>

                <div>
                    <p class="font-mono text-sm font-bold text-white">{{ $activo->codigo_activo }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Escaneable en planta con la cámara del celular para consultar ficha e historial.</p>
                </div>

                <a href="{{ route('activos.print-qr', $activo->id) }}" target="_blank" 
                   class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition shadow-lg shadow-cyan-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Imprimir Etiqueta Industrial</span>
                </a>
            </div>

            <!-- Adquisición y Valor Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-3 text-xs">
                <h4 class="font-bold text-white uppercase text-[11px] text-slate-400">Datos Comercial y Vida Útil</h4>
                
                <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                    <span class="text-slate-500">Costo Adquisición:</span>
                    <strong class="text-emerald-400 font-mono">USD ${{ number_format($activo->costo_adquisicion ?? 0, 2) }}</strong>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                    <span class="text-slate-500">Fecha Adquisición:</span>
                    <span class="text-slate-200">{{ $activo->fecha_adquisicion ? $activo->fecha_adquisicion->format('d/m/Y') : 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-500">Vida Útil Estimada:</span>
                    <span class="text-slate-200">{{ $activo->vida_util_estimada ?? 15 }} Años</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
