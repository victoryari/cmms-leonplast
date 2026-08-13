@extends('layouts.app')

@section('title', 'Registrar Nueva Ubicación / Sede')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Leaflet CSS & Dark Map Tile Styling -->
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
            <a href="{{ route('ubicaciones.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                ←
            </a>
            <div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Registrar Ubicación de Planta / Sede</h2>
                <p class="text-xs text-slate-400 mt-0.5">Sedes provinciales, plantas industriales, depósitos y empresas subsidiarias</p>
            </div>
        </div>
        <a href="{{ route('ubicaciones.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            Ver Listado de Sedes
        </a>
    </div>

    <!-- Main Registration Container (Diseño Fiel a Imagen de Referencia) -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-6">
        <form action="{{ route('ubicaciones.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Bloque Superior: Código QR + Localización Padre + Nombre + Código -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                
                <!-- QR Code Box (Esquina Superior Izquierda según Imagen) -->
                <div class="md:col-span-2 p-3 bg-slate-950 border border-slate-800 rounded-2xl flex flex-col items-center justify-center text-center space-y-2">
                    <img id="qr-preview" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $codigoSugerido }}" 
                         alt="QR Preview" class="w-28 h-28 rounded-lg bg-white p-1 shadow-md">
                    <span class="text-[10px] font-mono text-cyan-400 font-bold tracking-wider">CÓDIGO QR SECTOR</span>
                </div>

                <!-- Campos Principales -->
                <div class="md:col-span-10 space-y-4">
                    
                    <!-- Localización Padre (Dropdown) -->
                    <div>
                        <label for="parent_id" class="block text-xs font-semibold text-slate-400 mb-1">Localización Padre / Sede Central</label>
                        <select id="parent_id" name="parent_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                            <option value="">Sin Padre (Sede Principal / Corporativo Raíz)</option>
                            @foreach($padres as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }} ({{ $p->codigo_ubicacion }}) - {{ $p->ciudad }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Grid Nombre y Código -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="nombre" class="block text-xs font-semibold text-rose-400 mb-1">Nombre de la Ubicación / Sede *</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Planta Industrial Ate - Nave 1"
                                   class="w-full bg-slate-950 border border-rose-500/40 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                            <span class="text-[10px] text-slate-500 mt-1 block">Rango recomendado: 3 a 150 caracteres</span>
                        </div>

                        <div>
                            <label for="codigo_ubicacion" class="block text-xs font-semibold text-slate-300 mb-1">Código Único *</label>
                            <input type="text" id="codigo_ubicacion" name="codigo_ubicacion" value="{{ old('codigo_ubicacion', $codigoSugerido) }}" required
                                   oninput="updateQrPreview(this.value)"
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-mono text-cyan-400 font-bold focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloque Central: Dirección Geográfica (Izquierda) + Mapa Interactivo Dark (Derecha) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-4 border-t border-slate-800">
                
                <!-- Campos Dirección (Columna Izquierda 5 Cols) -->
                <div class="lg:col-span-5 space-y-3.5">
                    <h3 class="text-xs font-extrabold text-blue-400 uppercase tracking-wider">Dirección & Región</h3>

                    <div>
                        <label for="empresa_subsidiaria" class="block text-xs font-semibold text-slate-400 mb-1">Empresa Subsidiaria / Razón Social</label>
                        <input type="text" id="empresa_subsidiaria" name="empresa_subsidiaria" value="{{ old('empresa_subsidiaria', 'León Plast S.A.C.') }}" placeholder="Ej: León Plast del Sur S.A.C."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="direccion" class="block text-xs font-semibold text-slate-400 mb-1">Dirección Fiscal / Referencia</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" placeholder="Ej: Av. Industrial 1450, Mz. C Lote 4"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ciudad" class="block text-xs font-semibold text-slate-400 mb-1">Ciudad *</label>
                            <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', 'Lima') }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label for="departamento" class="block text-xs font-semibold text-slate-400 mb-1">Departamento / Región *</label>
                            <input type="text" id="departamento" name="departamento" value="{{ old('departamento', 'Lima') }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="pais" class="block text-xs font-semibold text-slate-400 mb-1">País *</label>
                            <input type="text" id="pais" name="pais" value="{{ old('pais', 'Perú') }}" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label for="codigo_postal" class="block text-xs font-semibold text-slate-400 mb-1">Código Postal / Área</label>
                            <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal', '15012') }}"
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Mapa Geográfico Dark Theme (Columna Derecha 7 Cols según Imagen) -->
                <div class="lg:col-span-7 space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-extrabold text-cyan-400 uppercase tracking-wider flex items-center space-x-1.5">
                            <span>🗺️ Ubicación Geográfica en Mapa (Haz clic para fijar coordenadas)</span>
                        </h3>
                        <span class="text-[10px] text-slate-400">Lima / Provincias Perú</span>
                    </div>

                    <!-- Canvas del Mapa -->
                    <div id="map" class="w-full h-72 rounded-2xl border border-slate-800 shadow-inner z-10"></div>
                </div>
            </div>

            <!-- Bloque Inferior: Coordenadas, Tipo, Prioridad, Centro Costo, Presupuesto & Notas (Según Imagen) -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h3 class="text-xs font-extrabold text-indigo-400 uppercase tracking-wider">Clasificación & Operación</h3>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="latitud" class="block text-xs font-semibold text-slate-400 mb-1">Latitud</label>
                        <input type="text" id="latitud" name="latitud" value="{{ old('latitud', '-12.046374') }}" readonly
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs font-mono text-cyan-400 font-bold focus:outline-none">
                    </div>

                    <div>
                        <label for="longitud" class="block text-xs font-semibold text-slate-400 mb-1">Longitud</label>
                        <input type="text" id="longitud" name="longitud" value="{{ old('longitud', '-76.953500') }}" readonly
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs font-mono text-cyan-400 font-bold focus:outline-none">
                    </div>

                    <div>
                        <label for="tipo" class="block text-xs font-semibold text-slate-400 mb-1">Tipo de Ubicación *</label>
                        <select id="tipo" name="tipo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none">
                            <option value="Planta_Industrial" {{ old('tipo') == 'Planta_Industrial' ? 'selected' : '' }}>⚙️ Planta Industrial</option>
                            <option value="Sede_Principal" {{ old('tipo') == 'Sede_Principal' ? 'selected' : '' }}>🏭 Sede Principal / Central</option>
                            <option value="Almacen_Deposito" {{ old('tipo') == 'Almacen_Deposito' ? 'selected' : '' }}>🏬 Almacén / Depósito Central</option>
                            <option value="Oficina_Regional" {{ old('tipo') == 'Oficina_Regional' ? 'selected' : '' }}>🏢 Oficina Regional de Ventas</option>
                            <option value="Empresa_Subsidiaria" {{ old('tipo') == 'Empresa_Subsidiaria' ? 'selected' : '' }}>🌐 Empresa Subsidiaria</option>
                            <option value="Area_Planta" {{ old('tipo') == 'Area_Planta' ? 'selected' : '' }}>📍 Área de Planta</option>
                        </select>
                    </div>

                    <div>
                        <label for="prioridad" class="block text-xs font-semibold text-slate-400 mb-1">Prioridad Operativa *</label>
                        <select id="prioridad" name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-amber-400 font-bold focus:outline-none">
                            <option value="Alta" {{ old('prioridad') == 'Alta' ? 'selected' : '' }}>🔴 Alta (Crítico / 24 hrs)</option>
                            <option value="Media" {{ old('prioridad', 'Media') == 'Media' ? 'selected' : '' }}>🟡 Media (Estándar)</option>
                            <option value="Baja" {{ old('prioridad') == 'Baja' ? 'selected' : '' }}>🟢 Baja (Secundario)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="codigo_barras_nfc" class="block text-xs font-semibold text-slate-400 mb-1">Código de Barras / NFC</label>
                        <input type="text" id="codigo_barras_nfc" name="codigo_barras_nfc" value="{{ old('codigo_barras_nfc') }}" placeholder="Ej: NFC-LIM-ATE-01"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none">
                    </div>

                    <div>
                        <label for="centro_costo" class="block text-xs font-semibold text-slate-400 mb-1">Centro de Costo</label>
                        <input type="text" id="centro_costo" name="centro_costo" value="{{ old('centro_costo', 'CC-LIM-001') }}" placeholder="Ej: CC-LIM-001"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none">
                    </div>

                    <div>
                        <label for="presupuesto_anual" class="block text-xs font-semibold text-slate-400 mb-1">Presupuesto Anual (S/.)</label>
                        <input type="number" step="0.01" id="presupuesto_anual" name="presupuesto_anual" value="{{ old('presupuesto_anual', '50000.00') }}" placeholder="50000.00"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-emerald-400 font-bold focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="notas" class="block text-xs font-semibold text-slate-400 mb-1">Notas & Observaciones Operativas</label>
                    <textarea id="notas" name="notas" rows="3" placeholder="Información técnica relevante de la nave o sede regional..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none"></textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('ubicaciones.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Registrar Ubicación
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Leaflet Map Script with CartoDB Dark Tiles -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = -12.046374;
        const defaultLng = -76.953500;

        const map = L.map('map').setView([defaultLat, defaultLng], 12);

        // CartoDB Dark Matter Tile Layer (Mapa en tono oscuro coordinado con la UI)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
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

    function updateQrPreview(code) {
        if (code.length > 2) {
            const encoded = encodeURIComponent(code);
            document.getElementById('qr-preview').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encoded}`;
        }
    }
</script>
@endsection
