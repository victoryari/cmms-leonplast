@extends('layouts.app')

@section('title', 'Recompras & Solicitudes de Compra - CMMS Leonplast')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                <span>Gestión de Recompras Automáticas</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Solicitudes automáticas de reposición de stock al alcanzar o superar el stock mínimo.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('repuestos.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition">
                ← Volver a Almacén
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Pendientes de Aprobación</span>
            <p class="text-2xl font-extrabold text-white">{{ \App\Models\SolicitudCompra::where('estado', 'PENDIENTE')->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[11px] font-bold text-blue-400 uppercase tracking-wider">Aprobados / En Proceso</span>
            <p class="text-2xl font-extrabold text-white">{{ \App\Models\SolicitudCompra::where('estado', 'APROBADO')->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Comprados e Ingresados</span>
            <p class="text-2xl font-extrabold text-white">{{ \App\Models\SolicitudCompra::where('estado', 'COMPRADO')->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[11px] font-bold text-rose-400 uppercase tracking-wider">Rechazados</span>
            <p class="text-2xl font-extrabold text-white">{{ \App\Models\SolicitudCompra::where('estado', 'RECHAZADO')->count() }}</p>
        </div>
    </div>

    <!-- Solicitudes Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Listado de Solicitudes de Recompra</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 uppercase font-bold text-slate-400 border-b border-slate-800/80">
                    <tr>
                        <th class="p-3">Código</th>
                        <th class="p-3">Repuesto</th>
                        <th class="p-3">Stock Actual / Mín.</th>
                        <th class="p-3">Cant. Solicitada</th>
                        <th class="p-3">Motivo</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($solicitudes as $sol)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-3 font-mono font-bold text-amber-400">{{ $sol->codigo_solicitud }}</td>
                        <td class="p-3 font-semibold text-white">
                            {{ $sol->repuesto?->nombre ?? 'N/A' }}
                            <span class="block text-[10px] text-slate-500 font-mono">{{ $sol->repuesto?->codigo_sku }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-rose-400">{{ $sol->repuesto?->stock_actual ?? 0 }}</span>
                            <span class="text-slate-500"> / {{ $sol->repuesto?->stock_minimo ?? 0 }} unid.</span>
                        </td>
                        <td class="p-3 font-bold text-white">{{ $sol->cantidad_solicitada }} unid.</td>
                        <td class="p-3 text-slate-400">{{ str_replace('_', ' ', $sol->motivo) }}</td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border
                                @if($sol->estado === 'PENDIENTE') bg-amber-500/10 text-amber-400 border-amber-500/30
                                @elseif($sol->estado === 'APROBADO') bg-blue-500/10 text-blue-400 border-blue-500/30
                                @elseif($sol->estado === 'COMPRADO') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                                {{ $sol->estado }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            @if($sol->estado === 'PENDIENTE')
                            <div class="flex items-center justify-end space-x-1">
                                <form action="{{ route('recompras.update-status', $sol->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="estado" value="APROBADO">
                                    <button type="submit" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold transition">Aprobar</button>
                                </form>
                                <form action="{{ route('recompras.update-status', $sol->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="estado" value="RECHAZADO">
                                    <button type="submit" class="px-2.5 py-1 bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white rounded-lg text-[10px] font-bold transition">Rechazar</button>
                                </form>
                            </div>
                            @elseif($sol->estado === 'APROBADO')
                            <form action="{{ route('recompras.update-status', $sol->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="estado" value="COMPRADO">
                                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold transition">Registrar Compra e Ingreso</button>
                            </form>
                            @else
                            <span class="text-[10px] text-slate-500">Completado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">No hay solicitudes de recompra registradas por el momento.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($solicitudes->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $solicitudes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
