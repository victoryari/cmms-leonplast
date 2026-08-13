@extends('layouts.app')

@section('title', 'Registrar Nuevo Tercero / Proveedor')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Registrar Nuevo Tercero / Proveedor</h2>
            <p class="text-xs text-slate-400 mt-1">Registra proveedores de repuestos o empresas contratistas de servicios técnicos de mantenimiento</p>
        </div>
        <a href="{{ route('terceros.index') }}" 
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            ← Volver al Catálogo
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <form action="{{ route('terceros.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Identificación Comercial -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider">1. Datos Comerciales de la Empresa</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ruc_documento" class="block text-xs font-semibold text-slate-300 mb-1">RUC / Doc. Identidad *</label>
                        <input type="text" id="ruc_documento" name="ruc_documento" value="{{ old('ruc_documento') }}" required placeholder="Ej: 20501234567"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-blue-500">
                    </div>

                    <div>
                        <label for="tipo" class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Servicio *</label>
                        <select id="tipo" name="tipo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                            <option value="Proveedor_Repuestos" {{ old('tipo') == 'Proveedor_Repuestos' ? 'selected' : '' }}>🏬 Proveedor de Repuestos & Suministros</option>
                            <option value="Contratista_Servicios" {{ old('tipo') == 'Contratista_Servicios' ? 'selected' : '' }}>🛠️ Contratista de Servicios Técnicos</option>
                            <option value="Ambos" {{ old('tipo') == 'Ambos' ? 'selected' : '' }}>🌐 Proveedor & Contratista Integral</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="razon_social" class="block text-xs font-semibold text-slate-300 mb-1">Razón Social *</label>
                        <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social') }}" required placeholder="Ej: Engel Austria GmbH Perú S.A.C."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:border-blue-500">
                    </div>

                    <div>
                        <label for="nombre_comercial" class="block text-xs font-semibold text-slate-300 mb-1">Nombre Comercial</label>
                        <input type="text" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial') }}" placeholder="Ej: Engel Inyección"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Contacto y Ubicación -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">2. Contacto Principal & Ubicación</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="contacto_nombre" class="block text-xs font-semibold text-slate-300 mb-1">Contacto Principal</label>
                        <input type="text" id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre') }}" placeholder="Ej: Ing. Carlos Mendoza"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="telefono" class="block text-xs font-semibold text-slate-300 mb-1">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: +51 1 456-7890"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">Correo Electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contacto@empresa.com"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-xs font-semibold text-slate-300 mb-1">Dirección Fiscal / Planta</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" placeholder="Ej: Av. Industrial 1450, Ate"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label for="ciudad" class="block text-xs font-semibold text-slate-300 mb-1">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', 'Lima') }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <!-- Evaluación y Calificación -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider">3. Calificación de Servicio</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="calificacion" class="block text-xs font-semibold text-slate-300 mb-1">Calificación Inicial (1 a 5 Estrellas) *</label>
                        <select id="calificacion" name="calificacion" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-amber-400 font-bold">
                            <option value="5" {{ old('calificacion', 5) == 5 ? 'selected' : '' }}>★★★★★ (5/5 - Excelente Proveedor)</option>
                            <option value="4" {{ old('calificacion') == 4 ? 'selected' : '' }}>★★★★☆ (4/5 - Muy Bueno)</option>
                            <option value="3" {{ old('calificacion') == 3 ? 'selected' : '' }}>★★★☆☆ (3/5 - Regular / En Evaluación)</option>
                            <option value="2" {{ old('calificacion') == 2 ? 'selected' : '' }}>★★☆☆☆ (2/5 - Con Observaciones)</option>
                            <option value="1" {{ old('calificacion') == 1 ? 'selected' : '' }}>★☆☆☆☆ (1/5 - No Recomendado)</option>
                        </select>
                    </div>

                    <div>
                        <label for="observaciones" class="block text-xs font-semibold text-slate-300 mb-1">Observaciones / Notas Adicionales</label>
                        <input type="text" id="observaciones" name="observaciones" value="{{ old('observaciones') }}" placeholder="Ej: Proveedor oficial homologado para inyectoras"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('terceros.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Guardar Tercero
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
