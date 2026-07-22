@extends('layouts.app')

@section('title', "Editar Repuesto: {$repuesto->codigo_sku}")

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('repuestos.show', $repuesto->id) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Editar Repuesto <span class="font-mono text-blue-400">[{{ $repuesto->codigo_sku }}]</span></h2>
            <p class="text-xs text-slate-400">Modifica precios, ubicación en estante o umbrales de reabastecimiento</p>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('repuestos.update', $repuesto->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre del Repuesto *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $repuesto->nombre) }}" required
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="categoria" class="block text-xs font-semibold text-slate-300 mb-1">Categoría *</label>
                    <input type="text" id="categoria" name="categoria" value="{{ old('categoria', $repuesto->categoria) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="marca" class="block text-xs font-semibold text-slate-300 mb-1">Marca</label>
                    <input type="text" id="marca" name="marca" value="{{ old('marca', $repuesto->marca) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="proveedor_principal" class="block text-xs font-semibold text-slate-300 mb-1">Proveedor Principal</label>
                    <input type="text" id="proveedor_principal" name="proveedor_principal" value="{{ old('proveedor_principal', $repuesto->proveedor_principal) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="stock_minimo" class="block text-xs font-semibold text-slate-300 mb-1">Stock Mínimo (Alerta) *</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" value="{{ old('stock_minimo', $repuesto->stock_minimo) }}" min="0" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-amber-400 font-bold">
                </div>

                <div>
                    <label for="stock_maximo" class="block text-xs font-semibold text-slate-300 mb-1">Stock Máximo *</label>
                    <input type="number" id="stock_maximo" name="stock_maximo" value="{{ old('stock_maximo', $repuesto->stock_maximo) }}" min="1" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-emerald-400 font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="ubicacion_almacen" class="block text-xs font-semibold text-slate-300 mb-1">Ubicación Almacén *</label>
                    <input type="text" id="ubicacion_almacen" name="ubicacion_almacen" value="{{ old('ubicacion_almacen', $repuesto->ubicacion_almacen) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="estante" class="block text-xs font-semibold text-slate-300 mb-1">Estante</label>
                    <input type="text" id="estante" name="estante" value="{{ old('estante', $repuesto->estante) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="posicion" class="block text-xs font-semibold text-slate-300 mb-1">Posición / Gaveta</label>
                    <input type="text" id="posicion" name="posicion" value="{{ old('posicion', $repuesto->posicion) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="costo_unitario" class="block text-xs font-semibold text-slate-300 mb-1">Costo Unitario *</label>
                    <input type="number" step="0.01" id="costo_unitario" name="costo_unitario" value="{{ old('costo_unitario', $repuesto->costo_unitario) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono">
                </div>

                <div>
                    <label for="moneda" class="block text-xs font-semibold text-slate-300 mb-1">Moneda *</label>
                    <select id="moneda" name="moneda" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                        <option value="USD" {{ $repuesto->moneda == 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="PEN" {{ $repuesto->moneda == 'PEN' ? 'selected' : '' }}>PEN (S/)</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Descripción / Especificaciones Técnicas</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">{{ old('descripcion', $repuesto->descripcion) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('repuestos.show', $repuesto->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
