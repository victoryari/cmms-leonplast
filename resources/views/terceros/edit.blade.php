@extends('layouts.app')

@section('title', 'Editar Tercero - ' . $tercero->razon_social)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Editar Empresa / Tercero</h2>
            <p class="text-xs text-slate-400 mt-1">Actualización de datos comerciales, contactos y estado de homologación</p>
        </div>
        <a href="{{ route('terceros.show', $tercero->id) }}" 
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            ← Volver a la Ficha
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <form action="{{ route('terceros.update', $tercero->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Identificación Comercial -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider">1. Datos Comerciales</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="ruc_documento" class="block text-xs font-semibold text-slate-300 mb-1">RUC / Doc. Identidad *</label>
                        <input type="text" id="ruc_documento" name="ruc_documento" value="{{ old('ruc_documento', $tercero->ruc_documento) }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono">
                    </div>

                    <div>
                        <label for="tipo" class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Servicio *</label>
                        <select id="tipo" name="tipo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                            <option value="Proveedor_Repuestos" {{ old('tipo', $tercero->tipo) == 'Proveedor_Repuestos' ? 'selected' : '' }}>🏬 Proveedor de Repuestos & Suministros</option>
                            <option value="Contratista_Servicios" {{ old('tipo', $tercero->tipo) == 'Contratista_Servicios' ? 'selected' : '' }}>🛠️ Contratista de Servicios Técnicos</option>
                            <option value="Ambos" {{ old('tipo', $tercero->tipo) == 'Ambos' ? 'selected' : '' }}>🌐 Proveedor & Contratista Integral</option>
                        </select>
                    </div>

                    <div>
                        <label for="activo" class="block text-xs font-semibold text-slate-300 mb-1">Estado Homologación *</label>
                        <select id="activo" name="activo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                            <option value="1" {{ $tercero->activo ? 'selected' : '' }}>Habilitado / Activo</option>
                            <option value="0" {{ !$tercero->activo ? 'selected' : '' }}>Inactivo / Suspendido</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="razon_social" class="block text-xs font-semibold text-slate-300 mb-1">Razón Social *</label>
                        <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $tercero->razon_social) }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="nombre_comercial" class="block text-xs font-semibold text-slate-300 mb-1">Nombre Comercial</label>
                        <input type="text" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial', $tercero->nombre_comercial) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <!-- Contacto y Ubicación -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">2. Contacto Principal & Ubicación</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="contacto_nombre" class="block text-xs font-semibold text-slate-300 mb-1">Contacto Principal</label>
                        <input type="text" id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre', $tercero->contacto_nombre) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="telefono" class="block text-xs font-semibold text-slate-300 mb-1">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $tercero->telefono) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">Correo Electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $tercero->email) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-xs font-semibold text-slate-300 mb-1">Dirección Fiscal / Planta</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $tercero->direccion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="ciudad" class="block text-xs font-semibold text-slate-300 mb-1">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $tercero->ciudad) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <!-- Evaluación y Calificación -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider">3. Calificación de Servicio</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="calificacion" class="block text-xs font-semibold text-slate-300 mb-1">Calificación de Desempeño *</label>
                        <select id="calificacion" name="calificacion" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-amber-400 font-bold">
                            <option value="5" {{ old('calificacion', $tercero->calificacion) == 5 ? 'selected' : '' }}>★★★★★ (5/5 - Excelente Proveedor)</option>
                            <option value="4" {{ old('calificacion', $tercero->calificacion) == 4 ? 'selected' : '' }}>★★★★☆ (4/5 - Muy Bueno)</option>
                            <option value="3" {{ old('calificacion', $tercero->calificacion) == 3 ? 'selected' : '' }}>★★★☆☆ (3/5 - Regular)</option>
                            <option value="2" {{ old('calificacion', $tercero->calificacion) == 2 ? 'selected' : '' }}>★★☆☆☆ (2/5 - Con Observaciones)</option>
                            <option value="1" {{ old('calificacion', $tercero->calificacion) == 1 ? 'selected' : '' }}>★☆☆☆☆ (1/5 - No Recomendado)</option>
                        </select>
                    </div>

                    <div>
                        <label for="observaciones" class="block text-xs font-semibold text-slate-300 mb-1">Observaciones</label>
                        <input type="text" id="observaciones" name="observaciones" value="{{ old('observaciones', $tercero->observaciones) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('terceros.show', $tercero->id) }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Guardar Cambios
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
