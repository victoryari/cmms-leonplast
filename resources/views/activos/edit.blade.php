@extends('layouts.app')

@section('title', 'Editar Activo - ' . $activo->nombre)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Editar Activo Industrial</h2>
            <p class="text-xs text-slate-400 mt-1">Actualización de ficha técnica y ubicación de {{ $activo->nombre }} [{{ $activo->codigo_activo }}]</p>
        </div>
        <a href="{{ route('activos.show', $activo->id) }}" 
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            ← Volver a Ficha
        </a>
    </div>

    <!-- Main Card Form -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <form action="{{ route('activos.update', $activo->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Info -->
            <div>
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">1. Identificación Principal del Equipo</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre Completo del Activo *</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $activo->nombre) }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-semibold text-slate-300 mb-1">Categoría del Equipo *</label>
                        <select id="categoria" name="categoria" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['categorias_activos'] as $cat)
                            <option value="{{ $cat }}" {{ old('categoria', $activo->categoria) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estado_operativo" class="block text-xs font-semibold text-slate-300 mb-1">Estado Operativo *</label>
                        <select id="estado_operativo" name="estado_operativo" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['estados_operativos'] as $est)
                            <option value="{{ $est }}" {{ old('estado_operativo', $activo->estado_operativo) == $est ? 'selected' : '' }}>{{ str_replace('_', ' ', $est) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Specs & Location -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-4">2. Marca, Modelo y Ubicación</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="marca" class="block text-xs font-semibold text-slate-300 mb-1">Marca</label>
                        <input type="text" id="marca" name="marca" value="{{ old('marca', $activo->marca) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="modelo" class="block text-xs font-semibold text-slate-300 mb-1">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="{{ old('modelo', $activo->modelo) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="numero_serie" class="block text-xs font-semibold text-slate-300 mb-1">Número de Serie</label>
                        <input type="text" id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $activo->numero_serie) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="area" class="block text-xs font-semibold text-slate-300 mb-1">Área de Planta</label>
                        <select id="area" name="area" 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['areas_planta'] as $areaItem)
                            <option value="{{ $areaItem }}" {{ old('area', $activo->area) == $areaItem ? 'selected' : '' }}>{{ $areaItem }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ubicacion" class="block text-xs font-semibold text-slate-300 mb-1">Ubicación Específica</label>
                        <input type="text" id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $activo->ubicacion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="estado_condicion" class="block text-xs font-semibold text-slate-300 mb-1">Condición Física</label>
                        <select id="estado_condicion" name="estado_condicion" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($catalogos['condiciones_fisicas'] as $cond)
                            <option value="{{ $cond }}" {{ old('estado_condicion', $activo->estado_condicion) == $cond ? 'selected' : '' }}>{{ $cond }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Commercial -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4">3. Valorización y Adquisición</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="costo_adquisicion" class="block text-xs font-semibold text-slate-300 mb-1">Costo Adquisición (USD $)</label>
                        <input type="number" step="0.01" id="costo_adquisicion" name="costo_adquisicion" value="{{ old('costo_adquisicion', $activo->costo_adquisicion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="fecha_adquisicion" class="block text-xs font-semibold text-slate-300 mb-1">Fecha de Compra</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $activo->fecha_adquisicion?->format('Y-m-d')) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="vida_util_estimada" class="block text-xs font-semibold text-slate-300 mb-1">Vida Útil (Años)</label>
                        <input type="number" id="vida_util_estimada" name="vida_util_estimada" value="{{ old('vida_util_estimada', $activo->vida_util_estimada) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Section 4: Remarks -->
            <div class="pt-4 border-t border-slate-800">
                <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Observaciones / Especificaciones Técnicas</label>
                <textarea id="descripcion" name="descripcion" rows="3"
                          class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500">{{ old('descripcion', $activo->descripcion) }}</textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('activos.show', $activo->id) }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Actualizar Ficha de Activo
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
