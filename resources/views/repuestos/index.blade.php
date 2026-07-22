@extends('layouts.app')

@section('title', 'Inventario de Repuestos & Almacén')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Repuestos & Almacén</h2>
            <p class="text-xs text-slate-400 mt-1">Control de existencias físicas, Kárdex de movimientos y alertas de reabastecimiento</p>
        </div>

        @if(auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
        <a href="{{ route('repuestos.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Registrar Repuesto</span>
        </a>
        @endif
    </div>

    <!-- Alert Banner if Low Stock Exists -->
    @if($metrics['alertas_stock'] > 0)
    <div class="p-4 rounded-3xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between">
        <div class="flex items-center space-x-3 text-amber-300">
            <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="text-xs">
                <strong class="font-bold text-white text-sm">¡Alerta de Reabastecimiento Requerido!</strong>
                <p>Se han detectado <strong>{{ $metrics['alertas_stock'] }} repuestos</strong> con stock igual o menor al mínimo de seguridad.</p>
            </div>
        </div>

        <a href="{{ route('repuestos.index', ['solo_bajo_stock' => 1]) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition">
            Ver Repuestos en Alerta
        </a>
    </div>
    @endif

    <!-- Valuation & Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Items Registrados</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_items'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Valoración Total</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1 font-mono">${{ number_format($metrics['valoracion_total'], 2) }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-amber-400 uppercase">Stock Bajo Mínimo</p>
            <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['alertas_stock'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-rose-400 uppercase">Críticos Sin Stock</p>
            <p class="text-2xl font-extrabold text-rose-400 mt-1">{{ $metrics['criticos_sin_stock'] }}</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('repuestos.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU, repuesto o proveedor..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <select name="categoria" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="solo_bajo_stock" value="1" {{ request('solo_bajo_stock') ? 'checked' : '' }} class="rounded border-slate-800 text-amber-500">
                    <span>Solo Stock Bajo</span>
                </label>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar Almacén
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-semibold uppercase">
                        <th class="py-4 px-6">SKU / Repuesto</th>
                        <th class="py-4 px-6">Categoría & Marca</th>
                        <th class="py-4 px-6">Ubicación Almacén</th>
                        <th class="py-4 px-6 text-center">Stock Actual / Mín.</th>
                        <th class="py-4 px-6 text-right">Costo Unit.</th>
                        <th class="py-4 px-6 text-center">Estado Stock</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($repuestos as $rep)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6">
                            <span class="font-mono text-blue-400 font-bold block">{{ $rep->codigo_sku }}</span>
                            <a href="{{ route('repuestos.show', $rep->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                {{ $rep->nombre }}
                            </a>
                            @if($rep->es_critico)
                            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 ml-1">REQUISITO CRÍTICO</span>
                            @endif
                        </td>

                        <td class="py-4 px-6">
                            <span class="text-slate-300 block font-medium">{{ $rep->categoria }}</span>
                            <span class="text-[10px] text-slate-500">{{ $rep->marca ?? 'Genérico' }}</span>
                        </td>

                        <td class="py-4 px-6">
                            <span class="text-slate-300 font-medium block">{{ $rep->ubicacion_almacen }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">Estante: {{ $rep->estante ?? '-' }} | Pos: {{ $rep->posicion ?? '-' }}</span>
                        </td>

                        <td class="py-4 px-6 text-center">
                            <strong class="text-sm font-bold {{ $rep->stock_actual <= $rep->stock_minimo ? 'text-amber-400' : 'text-white' }}">
                                {{ $rep->stock_actual }} un.
                            </strong>
                            <span class="text-[10px] text-slate-500 block">Mín: {{ $rep->stock_minimo }} | Máx: {{ $rep->stock_maximo }}</span>
                        </td>

                        <td class="py-4 px-6 text-right font-mono font-bold text-slate-200">
                            ${{ number_format($rep->costo_unitario, 2) }}
                        </td>

                        <td class="py-4 px-6 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($rep->stock_actual <= 0) bg-rose-500/10 text-rose-400 border-rose-500/30
                                @elseif($rep->stock_actual <= $rep->stock_minimo) bg-amber-500/10 text-amber-400 border-amber-500/30
                                @else bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @endif">
                                @if($rep->stock_actual <= 0) Sin Stock
                                @elseif($rep->stock_actual <= $rep->stock_minimo) Stock Bajo
                                @else Óptimo @endif
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right space-x-2">
                            <a href="{{ route('repuestos.show', $rep->id) }}" class="px-3 py-1 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 font-semibold text-[11px] transition">
                                Ver Kárdex
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            No hay repuestos registrados en almacén con el filtro seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $repuestos->links() }}
    </div>

</div>
@endsection
