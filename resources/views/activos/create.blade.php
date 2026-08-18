@extends('layouts.app')

@section('title', 'Registrar Nuevo Activo Industrial')

@section('content')
@php
    $tipoActual = old('tipo_clasificacion', request('tipo_clasificacion', 'Equipo'));
    $tituloPagina = match($tipoActual) {
        'Herramienta' => 'Registrar Nueva Herramienta / Instrumento',
        'Repuesto_Suministro' => 'Registrar Ficha de Repuesto / Suministro',
        'Digital' => 'Registrar Nuevo Activo Digital / Licencia',
        'Ubicacion' => 'Registrar Nueva Ubicación / Planta',
        default => 'Registrar Nuevo Activo Industrial',
    };
    $subtituloPagina = match($tipoActual) {
        'Herramienta' => 'Alta de herramientas de taller, instrumentos de medición y calibración',
        'Repuesto_Suministro' => 'Registro de ficha técnica para catálogo de repuestos y consumibles',
        'Digital' => 'Alta de licencias de software industrial, recetas PLC, firmwares y esquemas CAD/CAM',
        'Ubicacion' => 'Alta de naves, zonas de planta o áreas operativas',
        default => 'Alta de máquinas, inyectoras, compresores o auxiliares en el inventario de planta',
    };
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">{{ $tituloPagina }}</h2>
            <p class="text-xs text-slate-400 mt-1">{{ $subtituloPagina }}</p>
        </div>
        <a href="{{ url()->previous() ?: route('activos.index') }}" 
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            ← Volver a Lista
        </a>
    </div>

    <!-- Main Card Form -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <form action="{{ route('activos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Information -->
            <div>
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">1. Identificación Principal</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre o Denominación *</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required 
                               placeholder="{{ $tipoActual == 'Herramienta' ? 'Ej: Calibrador Pie de Rey Digital 0-150mm Mitutoyo' : ($tipoActual == 'Digital' ? 'Ej: Licencia Siemens TIA Portal v17 SCADA' : 'Ej: Inyectora de Plástico Engel Victory 250T') }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="tipo_clasificacion" class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Clasificación (Menú Catálogos) *</label>
                        <select id="tipo_clasificacion" name="tipo_clasificacion" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-cyan-400 font-bold focus:outline-none focus:border-blue-500">
                            <option value="Equipo" {{ $tipoActual == 'Equipo' ? 'selected' : '' }}>⚙️ Equipo (Maquinaria de Planta)</option>
                            <option value="Ubicacion" {{ $tipoActual == 'Ubicacion' ? 'selected' : '' }}>📍 Ubicación (Nave, Planta, Zona)</option>
                            <option value="Herramienta" {{ $tipoActual == 'Herramienta' ? 'selected' : '' }}>🔧 Herramienta (Taller, Calibración)</option>
                            <option value="Repuesto_Suministro" {{ $tipoActual == 'Repuesto_Suministro' ? 'selected' : '' }}>🏬 Repuesto y Suministro</option>
                            <option value="Digital" {{ $tipoActual == 'Digital' ? 'selected' : '' }}>💻 Digital (Software, Sensor IoT, Licencia)</option>
                        </select>
                    </div>

                    <div>
                        <label for="parent_id" class="block text-xs font-semibold text-slate-300 mb-1">Ubicación / Activo Padre (Árbol Jerárquico)</label>
                        <select id="parent_id" name="parent_id" 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            <option value="">Sin Padre (Nodo Raíz / Planta)</option>
                            @foreach($activosPadres ?? [] as $padre)
                            <option value="{{ $padre->id }}" {{ old('parent_id') == $padre->id ? 'selected' : '' }}>
                                [{{ $padre->tipo_clasificacion }}] {{ $padre->nombre }} ({{ $padre->codigo_activo }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-semibold text-slate-300 mb-1">Categoría del Equipo *</label>
                        <select id="categoria" name="categoria" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            <option value="">Seleccione Categoría</option>
                            @foreach($catalogos['categorias_activos'] as $cat)
                            <option value="{{ $cat }}" {{ old('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="proveedor_id" class="block text-xs font-semibold text-slate-300 mb-1">Empresa Proveedora (Terceros)</label>
                        <select id="proveedor_id" name="proveedor_id" 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            <option value="">Seleccione Proveedor Homologado</option>
                            @foreach($proveedores ?? [] as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                🏢 {{ $prov->razon_social }} (RUC: {{ $prov->ruc_documento }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estado_operativo" class="block text-xs font-semibold text-slate-300 mb-1">Estado Operativo Inicial *</label>
                        <select id="estado_operativo" name="estado_operativo" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['estados_operativos'] as $est)
                            <option value="{{ $est }}" {{ old('estado_operativo') == $est ? 'selected' : '' }}>{{ str_replace('_', ' ', $est) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Technical Specifications & Location -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-4">2. Marca, Modelo y Ubicación</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="marca" class="block text-xs font-semibold text-slate-300 mb-1">Marca</label>
                        <input type="text" id="marca" name="marca" value="{{ old('marca') }}" placeholder="Ej: Engel / Kaeser / Demag"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="modelo" class="block text-xs font-semibold text-slate-300 mb-1">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="{{ old('modelo') }}" placeholder="Ej: Victory 500"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="numero_serie" class="block text-xs font-semibold text-slate-300 mb-1">Número de Serie</label>
                        <input type="text" id="numero_serie" name="numero_serie" value="{{ old('numero_serie') }}" placeholder="Ej: SN-2023-881"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="ubicacion_id" class="block text-xs font-semibold text-slate-300 mb-1">Sede / Ubicación Oficial (Catálogo Ubicaciones) *</label>
                        <select id="ubicacion_id" name="ubicacion_id" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-cyan-400 font-bold focus:outline-none focus:border-blue-500">
                            <option value="">Seleccione Sede / Ubicación Oficial</option>
                            @foreach($ubicaciones ?? [] as $ub)
                            <option value="{{ $ub->id }}" {{ (old('ubicacion_id') == $ub->id || $loop->first) ? 'selected' : '' }}>
                                📍 {{ $ub->nombre }} ({{ $ub->codigo_ubicacion }}) - {{ str_replace('_', ' ', $ub->tipo) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="area" class="block text-xs font-semibold text-slate-300 mb-1">Área / Zona de Planta *</label>
                        <select id="area" name="area" 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['areas_planta'] as $areaItem)
                            <option value="{{ $areaItem }}" {{ old('area') == $areaItem ? 'selected' : '' }}>{{ $areaItem }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ubicacion" class="block text-xs font-semibold text-slate-300 mb-1">Sub-Ubicación / Posición en Nave</label>
                        <input type="text" id="ubicacion" name="ubicacion" value="{{ old('ubicacion') }}" placeholder="Ej: Nave A - Línea 1"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="estado_condicion" class="block text-xs font-semibold text-slate-300 mb-1">Condición Física *</label>
                        <select id="estado_condicion" name="estado_condicion" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['condiciones_fisicas'] as $cond)
                            <option value="{{ $cond }}" {{ old('estado_condicion') == $cond ? 'selected' : '' }}>{{ $cond }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Commercial & Valuation -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4">3. Valorización y Adquisición</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="costo_adquisicion" class="block text-xs font-semibold text-slate-300 mb-1">Costo Adquisición (S/.)</label>
                        <input type="number" step="0.01" id="costo_adquisicion" name="costo_adquisicion" value="{{ old('costo_adquisicion') }}" placeholder="0.00"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="fecha_adquisicion" class="block text-xs font-semibold text-slate-300 mb-1">Fecha de Compra</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" value="{{ old('fecha_adquisicion') }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="vida_util_estimada" class="block text-xs font-semibold text-slate-300 mb-1">Vida Útil (Años)</label>
                        <input type="number" id="vida_util_estimada" name="vida_util_estimada" value="{{ old('vida_util_estimada', 10) }}" placeholder="10"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Section 4: Fotografía del Equipo -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-cyan-400 uppercase tracking-wider mb-4">4. Fotografía del Equipo / Activo</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Imagen del Activo (JPG, PNG, WEBP - Máx 5MB)</label>
                        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp"
                               onchange="previewAssetImage(event)"
                               class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-cyan-400 hover:file:bg-slate-700 file:cursor-pointer bg-slate-950 border border-slate-800 rounded-xl p-2 focus:outline-none focus:border-cyan-500">
                        <p class="text-[11px] text-slate-500 mt-1">Una fotografía clara del equipo facilita su identificación física y validación en rondas de inspección.</p>
                    </div>

                    <div class="flex flex-col items-center justify-center p-3 rounded-2xl bg-slate-950 border border-slate-800 text-center min-h-[120px]">
                        <div id="image-preview-container" class="hidden w-full h-28 rounded-xl overflow-hidden relative">
                            <img id="image-preview" src="#" alt="Vista previa de activo" class="w-full h-full object-cover rounded-xl">
                        </div>
                        <div id="image-placeholder" class="text-slate-500 flex flex-col items-center space-y-1 py-2">
                            <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-[11px]">Sin imagen seleccionada</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Remarks -->
            <div class="pt-4 border-t border-slate-800">
                <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Observaciones / Especificaciones Técnicas</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles de potencia, caudal, tonelaje de cierre, observaciones de instalación..."
                          class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">{{ old('descripcion') }}</textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('activos.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Registrar Activo Industrial
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function previewAssetImage(event) {
    const input = event.target;
    const previewContainer = document.getElementById('image-preview-container');
    const previewImage = document.getElementById('image-preview');
    const placeholder = document.getElementById('image-placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}
</script>
@endsection
