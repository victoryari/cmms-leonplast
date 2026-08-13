@extends('layouts.app')

@section('title', 'Ficha de Ubicación: ' . $ubicacion->nombre)

@section('content')
<div class="space-y-6">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="{{ asset('vendor/css/leaflet.css') }}" />
    <script src="{{ asset('vendor/js/leaflet.js') }}"></script>

    <!-- Estilos Personalizados para integrar Popups de Leaflet con CartoDB Dark Matter -->
    <style>
        .leaflet-popup-content-wrapper {
            background-color: #0f172a !important; /* slate-900 */
            color: #f8fafc !important; /* slate-50 */
            border: 1px solid #334155 !important; /* slate-700 */
            border-radius: 16px !important;
            font-family: inherit;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
            padding: 4px;
        }
        .leaflet-popup-tip {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
        }
        .leaflet-container a.leaflet-popup-close-button {
            color: #94a3b8 !important;
            font-size: 14px !important;
            top: 6px !important;
            right: 6px !important;
        }
        .leaflet-container a.leaflet-popup-close-button:hover {
            color: #ffffff !important;
        }
    </style>

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('ubicaciones.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                ←
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        {{ $ubicacion->codigo_ubicacion }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $ubicacion->activo ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/10 text-slate-400 border-slate-500/30' }}">
                        ● {{ $ubicacion->activo ? 'Habilitado' : 'Inactivo' }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $ubicacion->nombre }}</h2>
                @if($ubicacion->empresa_subsidiaria)
                <p class="text-xs text-slate-400">Empresa Subsidiaria: {{ $ubicacion->empresa_subsidiaria }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('ubicaciones.edit', $ubicacion->id) }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                ✏️ Editar Ubicación
            </a>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Activos In Location -->
        <div class="lg:col-span-2 space-y-6">

            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400">Detalles de la Sede / Planta</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-850">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Tipo de Ubicación</span>
                        <strong class="text-white block mt-0.5">{{ $ubicacion->tipo_label }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-850">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Prioridad Operativa</span>
                        <strong class="text-amber-400 block mt-0.5">{{ $ubicacion->prioridad }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-850">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Centro de Costos</span>
                        <strong class="font-mono text-cyan-400 block mt-0.5">{{ $ubicacion->centro_costo ?? 'N/A' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-850">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Presupuesto Anual</span>
                        <strong class="text-emerald-400 block mt-0.5">S/. {{ number_format($ubicacion->presupuesto_anual, 2) }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-850 sm:col-span-2">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Dirección Geográfica</span>
                        <strong class="text-slate-200 block mt-0.5">{{ $ubicacion->direccion ?? 'N/A' }} ({{ $ubicacion->ciudad }} - {{ $ubicacion->departamento }}, {{ $ubicacion->pais }})</strong>
                    </div>
                </div>

                @if($ubicacion->latitud && $ubicacion->longitud)
                <div class="space-y-2 pt-2">
                    <span class="text-xs font-bold text-cyan-400 uppercase">Mapa de Ubicación Exacta</span>
                    <div id="show-map" class="w-full h-56 rounded-2xl border border-slate-800 z-10"></div>
                </div>
                @endif
            </div>

            <!-- Activos en esta Ubicación -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-cyan-400 flex items-center justify-between">
                    <span>📦 Activos Instalados en esta Sede ({{ $ubicacion->activos->count() }})</span>
                </h3>

                <div class="space-y-2">
                    @forelse($ubicacion->activos as $act)
                    <a href="{{ route('activos.show', $act->id) }}" class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800/80 hover:border-slate-700 flex items-center justify-between transition">
                        <div class="flex items-center space-x-3">
                            <span class="font-mono text-xs font-bold text-blue-400 px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20">
                                {{ $act->codigo_activo }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $act->nombre }}</h4>
                                <span class="text-[10px] text-slate-400">{{ $act->categoria }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-400">Ver Activo ➔</span>
                    </a>
                    @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No hay activos asignados directamente a esta ubicación en el inventario actual.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: QR Code & Summary -->
        <div class="space-y-6">

            <!-- QR Code Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center space-y-4 shadow-xl">
                <h3 class="text-xs font-bold text-slate-400 uppercase">Etiqueta QR de Identificación</h3>
                <img src="{{ $ubicacion->qr_image_url }}" alt="QR Code" class="w-40 h-40 mx-auto rounded-xl bg-white p-1.5 shadow-md">
                <p class="font-mono text-xs font-bold text-cyan-400">{{ $ubicacion->codigo_ubicacion }}</p>
            </div>

        </div>

    </div>

</div>

@if($ubicacion->latitud && $ubicacion->longitud)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('show-map').setView([{{ $ubicacion->latitud }}, {{ $ubicacion->longitud }}], 14);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(map);

        L.marker([{{ $ubicacion->latitud }}, {{ $ubicacion->longitud }}])
            .addTo(map)
            .bindPopup("<div class='p-1 text-slate-200 text-xs font-semibold'>🏢 {{ $ubicacion->nombre }}<br><span class='text-[10px] text-slate-400 font-mono'>{{ $ubicacion->codigo_ubicacion }}</span></div>")
            .openPopup();
    });
</script>
@endif
@endsection
