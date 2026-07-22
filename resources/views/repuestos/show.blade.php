@extends('layouts.app')

@section('title', "Ficha de Kárdex: {$repuesto->codigo_sku}")

@section('content')
<div class="space-y-6" x-data="{ movementModal: false }">

    <!-- Header Navigation & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('repuestos.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        {{ $repuesto->codigo_sku }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border
                        @if($repuesto->stock_actual <= 0) bg-rose-500/10 text-rose-400 border-rose-500/30
                        @elseif($repuesto->stock_actual <= $repuesto->stock_minimo) bg-amber-500/10 text-amber-400 border-amber-500/30
                        @else bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @endif">
                        ● Stock: {{ $repuesto->stock_actual }} un.
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $repuesto->nombre }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('repuestos.edit', $repuesto->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition">
                Editar Datos
            </a>

            <button @click="movementModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                + Registrar Movimiento (Kárdex)
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Datasheet & Kardex Table -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Datasheet Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400">Ficha Técnica & Ubicación en Almacén</h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block font-semibold">Categoría:</span>
                        <strong class="text-white font-medium">{{ $repuesto->categoria }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block font-semibold">Marca:</span>
                        <strong class="text-white font-medium">{{ $repuesto->marca ?? 'Genérico' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block font-semibold">Costo Unitario:</span>
                        <strong class="text-emerald-400 font-mono font-bold">${{ number_format($repuesto->costo_unitario, 2) }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 col-span-2">
                        <span class="text-[10px] text-slate-500 uppercase block font-semibold">Ubicación Física:</span>
                        <strong class="text-white font-medium">{{ $repuesto->ubicacion_almacen }}</strong>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Estante: {{ $repuesto->estante ?? '-' }} | Posición: {{ $repuesto->posicion ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block font-semibold">Valoración en Stock:</span>
                        <strong class="text-emerald-400 font-mono font-bold">${{ number_format($repuesto->stock_actual * $repuesto->costo_unitario, 2) }}</strong>
                    </div>
                </div>

                @if($repuesto->descripcion)
                <div class="space-y-1">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase">Especificaciones Técnicas:</span>
                    <p class="text-xs text-slate-300 bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80">{{ $repuesto->descripcion }}</p>
                </div>
                @endif
            </div>

            <!-- Kardex Movements History Table -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Kárdex Auditado de Movimientos (Entradas / Salidas / Ajustes)</span>
                </h3>

                <div class="overflow-x-auto rounded-2xl border border-slate-800">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase">
                            <tr>
                                <th class="p-3">Fecha & Hora</th>
                                <th class="p-3">Tipo Movimiento</th>
                                <th class="p-3 text-center">Cant.</th>
                                <th class="p-3 text-center">Ant. ➔ Nuevo</th>
                                <th class="p-3">Motivo / Ref.</th>
                                <th class="p-3">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 text-slate-300 font-mono">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                                        @if($mov->tipo_movimiento == 'Entrada') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                        @elseif($mov->tipo_movimiento == 'Salida') bg-rose-500/10 text-rose-400 border-rose-500/30
                                        @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                                        {{ $mov->tipo_movimiento }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-bold font-mono text-white">
                                    {{ $mov->tipo_movimiento == 'Salida' ? '-' : '+' }}{{ $mov->cantidad }}
                                </td>
                                <td class="p-3 text-center font-mono text-slate-400">
                                    {{ $mov->stock_anterior }} ➔ <strong class="text-white">{{ $mov->stock_nuevo }}</strong>
                                </td>
                                <td class="p-3">
                                    <span class="text-slate-300 block font-medium">{{ $mov->motivo }}</span>
                                    @if($mov->documento_referencia)
                                    <span class="text-[10px] font-mono text-blue-400">Ref: {{ $mov->documento_referencia }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-400 text-[11px]">{{ $mov->usuario?->nombre_completo ?? 'Sistema' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500 italic">No hay movimientos registrados en el Kárdex para este repuesto.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-2">
                    {{ $movimientos->links() }}
                </div>
            </div>

        </div>

        <!-- Right Col: Supplier & Stock Levels -->
        <div class="space-y-6">

            <!-- Stock Levels Summary Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 text-xs">
                <h4 class="font-bold text-white uppercase text-[11px] text-slate-400">Estado de Reabastecimiento</h4>

                <div class="space-y-3">
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase block font-semibold">Stock Actual:</span>
                            <strong class="text-xl font-extrabold {{ $repuesto->stock_actual <= $repuesto->stock_minimo ? 'text-amber-400' : 'text-white' }}">
                                {{ $repuesto->stock_actual }} un.
                            </strong>
                        </div>

                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold border
                            @if($repuesto->stock_actual <= 0) bg-rose-500/10 text-rose-400 border-rose-500/30
                            @elseif($repuesto->stock_actual <= $repuesto->stock_minimo) bg-amber-500/10 text-amber-400 border-amber-500/30
                            @else bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @endif">
                            @if($repuesto->stock_actual <= 0) Sin Stock
                            @elseif($repuesto->stock_actual <= $repuesto->stock_minimo) Stock Bajo
                            @else Normal @endif
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase block font-semibold">Stock Mínimo:</span>
                            <strong class="text-amber-400 font-bold text-sm">{{ $repuesto->stock_minimo }} un.</strong>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase block font-semibold">Stock Máximo:</span>
                            <strong class="text-emerald-400 font-bold text-sm">{{ $repuesto->stock_maximo }} un.</strong>
                        </div>
                    </div>
                </div>

                <!-- Supplier Info -->
                <div class="pt-3 border-t border-slate-800 space-y-2">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase block">Proveedor Principal:</span>
                    <strong class="text-white text-xs font-bold block">{{ $repuesto->proveedor_principal ?? 'Sin proveedor asignado' }}</strong>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL: REGISTRAR MOVIMIENTO DE KÁRDEX -->
    <div x-show="movementModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Registrar Movimiento de Kárdex</h3>
            
            <form action="{{ route('repuestos.movimiento', $repuesto->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Movimiento *</label>
                    <select name="tipo_movimiento" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="Entrada">🟢 Entrada (Ingreso por Compra / Recepción)</option>
                        <option value="Salida">🔴 Salida (Consumo Manual en Planta)</option>
                        <option value="Ajuste">🟡 Ajuste (Corrección por Conteo Físico)</option>
                        <option value="Devolucion">🔵 Devolución a Almacén</option>
                        <option value="Merma">🟣 Merma / Daño</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Cantidad *</label>
                    <input type="number" name="cantidad" value="1" min="1" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Motivo / Justificación *</label>
                    <input type="text" name="motivo" required placeholder="Ej: Compra por Factura F001-9284" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Documento Referencia (OC, Factura, Guía)</label>
                    <input type="text" name="documento_referencia" placeholder="Ej: GR-10294" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="movementModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30">Registrar en Kárdex</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
