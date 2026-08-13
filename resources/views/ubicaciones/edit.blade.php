@extends('layouts.app')

@section('title', 'Editar Ubicación - ' . $ubicacion->nombre)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

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
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('ubicaciones.show', $ubicacion->id) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                ←
            </a>
            <div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Editar Ubicación / Sede</h2>
                <p class="text-xs text-slate-400 mt-0.5">Actualización de coordenadas, presupuestos y jerarquía</p>
            </div>
        </div>
        <a href="{{ route('ubicaciones.show', $ubicacion->id) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            Volver a Ficha
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-6">
        <form action="{{ route('ubicaciones.update', $ubicacion->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Top Grid: QR + Parent + Name + Code + Active -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <div class="md:col-span-2 p-3 bg-slate-950 border border-slate-800 rounded-2xl flex flex-col items-center justify-center text-center space-y-2">
                    <img id="qr-preview" src="{{ $ubicacion->qr_image_url }}" alt="QR" class="w-28 h-28 rounded-lg bg-white p-1 shadow-md">
                    <span class="text-[10px] font-mono text-cyan-400 font-bold">QR SECTOR</span>
                </div>

                <div class="md:col-span-10 space-y-4">
                    <div>
                        <label for="parent_id" class="block text-xs font-semibold text-slate-400 mb-1">Localización Padre / Sede Central</label>
                        <select id="parent_id" name="parent_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                            <option value="">Sin Padre (Sede Principal / Corporativo Raíz)</option>
                            @foreach($padres as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id', $ubicacion->parent_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }} ({{ $p->codigo_ubicacion }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre de la Ubicación *</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $ubicacion->nombre) }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="codigo_ubicacion" class="block text-xs font-semibold text-slate-300 mb-1">Código Único *</label>
                            <input type="text" id="codigo_ubicacion" name="codigo_ubicacion" value="{{ old('codigo_ubicacion', $ubicacion->codigo_ubicacion) }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-mono text-cyan-400 font-bold focus:outline-none">
                        </div>

                        <div>
                            <label for="activo" class="block text-xs font-semibold text-slate-300 mb-1">Estado *</label>
                            <select id="activo" name="activo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white">
                                <option value="1" {{ $ubicacion->activo ? 'selected' : '' }}>Habilitado</option>
                                <option value="0" {{ !$ubicacion->activo ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address and Interactive Map -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-4 border-t border-slate-800">
                <div class="lg:col-span-5 space-y-3.5">
                    <h3 class="text-xs font-extrabold text-blue-400 uppercase tracking-wider">Dirección & Región</h3>

                    <div>
                        <label for="empresa_subsidiaria" class="block text-xs font-semibold text-slate-400 mb-1">Empresa Subsidiaria</label>
                        <input type="text" id="empresa_subsidiaria" name="empresa_subsidiaria" value="{{ old('empresa_subsidiaria', $ubicacion->empresa_subsidiaria) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                    </div>

                    <div>
                        <label for="direccion" class="block text-xs font-semibold text-slate-400 mb-1">Dirección Fiscal / Referencia</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $ubicacion->direccion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ciudad" class="block text-xs font-semibold text-slate-400 mb-1">Ciudad *</label>
                            <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $ubicacion->ciudad) }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label for="departamento" class="block text-xs font-semibold text-slate-400 mb-1">Departamento *</label>
                            <input type="text" id="departamento" name="departamento" value="{{ old('departamento', $ubicacion->departamento) }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="pais" class="block text-xs font-semibold text-slate-400 mb-1">País *</label>
                            <input type="text" id="pais" name="pais" value="{{ old('pais', $ubicacion->pais) }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label for="codigo_postal" class="block text-xs font-semibold text-slate-400 mb-1">Código Postal</label>
                            <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal', $ubicacion->codigo_postal) }}"
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-2">
                    <h3 class="text-xs font-extrabold text-cyan-400 uppercase tracking-wider">Ubicación Geográfica en Mapa</h3>
                    <div id="map" class="w-full h-72 rounded-2xl border border-slate-800 z-10"></div>
                </div>
            </div>

            <!-- Lower Classification Grid -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="latitud" class="block text-xs font-semibold text-slate-400 mb-1">Latitud</label>
                        <input type="text" id="latitud" name="latitud" value="{{ old('latitud', $ubicacion->latitud) }}" readonly
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs font-mono text-cyan-400 font-bold">
                    </div>

                    <div>
                        <label for="longitud" class="block text-xs font-semibold text-slate-400 mb-1">Longitud</label>
                        <input type="text" id="longitud" name="longitud" value="{{ old('longitud', $ubicacion->longitud) }}" readonly
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs font-mono text-cyan-400 font-bold">
                    </div>

                    <div>
                        <label for="tipo" class="block text-xs font-semibold text-slate-400 mb-1">Tipo de Ubicación *</label>
                        <select id="tipo" name="tipo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="Planta_Industrial" {{ old('tipo', $ubicacion->tipo) == 'Planta_Industrial' ? 'selected' : '' }}>⚙️ Planta Industrial</option>
                            <option value="Sede_Principal" {{ old('tipo', $ubicacion->tipo) == 'Sede_Principal' ? 'selected' : '' }}>🏭 Sede Principal / Central</option>
                            <option value="Almacen_Deposito" {{ old('tipo', $ubicacion->tipo) == 'Almacen_Deposito' ? 'selected' : '' }}>🏬 Almacén / Depósito Central</option>
                            <option value="Oficina_Regional" {{ old('tipo', $ubicacion->tipo) == 'Oficina_Regional' ? 'selected' : '' }}>🏢 Oficina Regional</option>
                            <option value="Empresa_Subsidiaria" {{ old('tipo', $ubicacion->tipo) == 'Empresa_Subsidiaria' ? 'selected' : '' }}>🌐 Empresa Subsidiaria</option>
                            <option value="Area_Planta" {{ old('tipo', $ubicacion->tipo) == 'Area_Planta' ? 'selected' : '' }}>📍 Área de Planta</option>
                        </select>
                    </div>

                    <div>
                        <label for="prioridad" class="block text-xs font-semibold text-slate-400 mb-1">Prioridad Operativa *</label>
                        <select id="prioridad" name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-amber-400 font-bold">
                            <option value="Alta" {{ old('prioridad', $ubicacion->prioridad) == 'Alta' ? 'selected' : '' }}>🔴 Alta (Crítico)</option>
                            <option value="Media" {{ old('prioridad', $ubicacion->prioridad) == 'Media' ? 'selected' : '' }}>🟡 Media</option>
                            <option value="Baja" {{ old('prioridad', $ubicacion->prioridad) == 'Baja' ? 'selected' : '' }}>🟢 Baja</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="codigo_barras_nfc" class="block text-xs font-semibold text-slate-400 mb-1">Código de Barras / NFC</label>
                        <input type="text" id="codigo_barras_nfc" name="codigo_barras_nfc" value="{{ old('codigo_barras_nfc', $ubicacion->codigo_barras_nfc) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                    </div>

                    <div>
                        <label for="centro_costo" class="block text-xs font-semibold text-slate-400 mb-1">Centro de Costo</label>
                        <input type="text" id="centro_costo" name="centro_costo" value="{{ old('centro_costo', $ubicacion->centro_costo) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">
                    </div>

                    <div>
                        <label for="presupuesto_anual" class="block text-xs font-semibold text-slate-400 mb-1">Presupuesto Anual (S/.)</label>
                        <input type="number" step="0.01" id="presupuesto_anual" name="presupuesto_anual" value="{{ old('presupuesto_anual', $ubicacion->presupuesto_anual) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-emerald-400 font-bold">
                    </div>
                </div>

                <div>
                    <label for="notas" class="block text-xs font-semibold text-slate-400 mb-1">Notas & Observaciones</label>
                    <textarea id="notas" name="notas" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white">{{ old('notas', $ubicacion->notas) }}</textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('ubicaciones.show', $ubicacion->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Guardar Cambios
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = {{ $ubicacion->latitud ?? -12.046374 }};
        const defaultLng = {{ $ubicacion->longitud ?? -76.953500 }};

        const map = L.map('map').setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function updateCoords(lat, lng) {
            document.getElementById('latitud').value = lat.toFixed(7);
            document.getElementById('longitud').value = lng.toFixed(7);
        }

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            marker.setLatLng([lat, lng]);
            updateCoords(lat, lng);
        });

        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoords(position.lat, position.lng);
        });
    });
</script>
@endsection
