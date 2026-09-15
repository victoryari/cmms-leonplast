@extends('layouts.app')

@section('title', "Orden de Trabajo: {$ot->codigo_ot}")

@section('content')
<style>
@media print {
    body, main, .bg-slate-950 { background-color: white !important; color: black !important; }
    header, aside, .print\:hidden, form, button { display: none !important; }
    .bg-slate-900, .bg-slate-950, .p-6, .p-4 { background-color: white !important; border: 1px solid #cbd5e1 !important; box-shadow: none !important; }
    .text-white, .text-slate-300, .text-slate-400, .text-slate-500 { color: black !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="space-y-6" x-data="{ addSpareModal: false, uploadPhotoModal: false, pauseModal: false, ptsModal: false, signatureModal: false, signatureRole: 'tecnico', photoType: 'antes' }">

    <!-- Top Navigation & Status Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('ordenes.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition print:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <button type="button" onclick="window.print()" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition print:hidden" title="Imprimir Orden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            </button>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        {{ $ot->codigo_ot }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border
                        @if($ot->estado == 'Completada') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @elseif($ot->estado == 'En_Progreso') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                        @elseif($ot->estado == 'En_Pausa') bg-amber-500/10 text-amber-400 border-amber-500/30
                        @elseif($ot->estado == 'Aprobada') bg-blue-500/10 text-blue-400 border-blue-500/30
                        @elseif($ot->estado == 'Pendiente') bg-amber-500/10 text-amber-400 border-amber-500/30
                        @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                        ● Estado: {{ str_replace('_', ' ', $ot->estado) }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $ot->titulo }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-3 print:hidden">
            @if($ot->estado === 'Pendiente' || auth()->user()->isAdmin())
            <a href="{{ route('ordenes.edit', $ot->id) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition">
                Editar OT
            </a>
            @endif

            @if(auth()->user()->hasRole([\App\Enums\SystemRole::Admin->value, \App\Enums\SystemRole::Manager->value]) && $ot->estado !== 'Cancelada' && $ot->estado !== 'Completada')
            <form action="{{ route('ordenes.destroy', $ot->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de anular esta Orden de Trabajo? Si usó repuestos, retornarán al almacén.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500 hover:text-white text-rose-400 text-xs font-bold rounded-xl transition">
                    Anular OT
                </button>
            </form>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            <!-- Botones de Acción (Técnicos/Supervisores) -->
            @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
                @if($ot->estado == 'Aprobada')
                <form action="{{ route('ordenes.update-status', $ot->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="nuevo_estado" value="En_Progreso">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center space-x-1.5 transform active:scale-95">
                        <span>▶️ Iniciar Trabajo</span>
                    </button>
                </form>
                @elseif($ot->estado == 'En_Progreso')
                <div class="flex items-center space-x-2">
                    <button @click="pauseModal = true" class="px-4 py-2 rounded-xl bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 text-xs font-bold transition flex items-center space-x-1.5">
                        <span>⏸️ Pausar Trabajo</span>
                    </button>
                    <form action="{{ route('ordenes.update-status', $ot->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="nuevo_estado" value="Completada">
                        <button type="submit" onclick="return confirm('¿Confirmas que la reparación/mantenimiento se ha completado en su totalidad?')" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center space-x-1.5">
                            <span>✓ Finalizar OT</span>
                        </button>
                    </form>
                </div>
                @elseif($ot->estado == 'En_Pausa')
                <form action="{{ route('ordenes.resume', $ot->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center space-x-1.5">
                        <span>▶️ Reanudar Trabajo</span>
                    </button>
                </form>
                @endif
            @endif

            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300">
                Prioridad: <strong class="text-amber-400">{{ $ot->prioridad }}</strong>
            </span>
        </div>
    </div>

    <!-- Status Lifecycle Progress Timeline -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Progreso de la Orden de Trabajo</h4>
        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['Pendiente', 'Aprobada', 'En_Progreso', 'En_Pausa', 'En_Revision', 'Completada']) ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">1. Solicitada</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_solicitud?->format('d/m/Y H:i') }}</span>
            </div>

            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['Aprobada', 'En_Progreso', 'En_Pausa', 'En_Revision', 'Completada']) ? 'bg-indigo-600/20 border-indigo-500/40 text-indigo-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">2. Aprobada / Asignada</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_aprobacion ? $ot->fecha_aprobacion->format('d/m/Y H:i') : 'Pendiente' }}</span>
            </div>

            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['En_Progreso', 'En_Pausa', 'En_Revision', 'Completada']) ? ($ot->estado == 'En_Pausa' ? 'bg-amber-600/20 border-amber-500/40 text-amber-300' : 'bg-indigo-600/20 border-indigo-500/40 text-indigo-300') : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">{{ $ot->estado == 'En_Pausa' ? '⏸️ En Pausa' : '3. En Ejecución' }}</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_inicio ? $ot->fecha_inicio->format('d/m/Y H:i') : 'Por iniciar' }}</span>
            </div>

            <div class="p-3 rounded-2xl border transition {{ $ot->estado == 'Completada' ? 'bg-emerald-600/20 border-emerald-500/40 text-emerald-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">4. Cierre & Entrega</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_fin_real ? $ot->fecha_fin_real->format('d/m/Y H:i') : 'Pendiente' }}</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details, Photos, Spare Parts, Diagnosis & Rating -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Asset & Description Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Activo Afectado y Requerimiento</span>
                </h3>

                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="font-mono text-xs font-bold text-blue-400">{{ $ot->activo?->codigo_activo }}</span>
                        <h4 class="text-sm font-bold text-white mt-0.5">{{ $ot->activo?->nombre }}</h4>
                        <p class="text-xs text-slate-400">Ubicación: {{ $ot->activo?->ubicacion }} ({{ $ot->activo?->area }})</p>
                    </div>

                    @if($ot->activo)
                    <a href="{{ route('activos.show', $ot->activo->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700">
                        Ver Máquina
                    </a>
                    @endif
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase">Detalle del Requerimiento:</span>
                    <p class="text-xs text-slate-300 leading-relaxed bg-slate-950 p-4 rounded-2xl border border-slate-800/80">{{ $ot->descripcion }}</p>
                </div>
            </div>

            <!-- FOTOS ANTES Y DESPUÉS DE LA REPARACIÓN -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider text-cyan-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Evidencia Fotográfica (Antes / Después)</span>
                    </h3>

                    @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
                    <button @click="uploadPhotoModal = true" class="px-3 py-1.5 rounded-xl bg-cyan-600/20 text-cyan-300 hover:bg-cyan-600 hover:text-white border border-cyan-500/30 text-xs font-semibold transition">
                        + Adjuntar Foto
                    </button>
                    @endif
                </div>

                @php
                    $rawFotos = $ot->fotos ?? ['antes' => [], 'despues' => []];
                    $fotosAntes = [];
                    $fotosDespues = [];

                    if (is_array($rawFotos)) {
                        if (isset($rawFotos[0]) && is_array($rawFotos[0])) {
                            foreach ($rawFotos as $item) {
                                $t = strtolower($item['tipo'] ?? 'antes');
                                $url = $item['url_foto'] ?? ($item['url'] ?? '');
                                if ($url) {
                                    if ($t === 'despues') {
                                        $fotosDespues[] = $url;
                                    } else {
                                        $fotosAntes[] = $url;
                                    }
                                }
                            }
                        } else {
                            $fotosAntes = $rawFotos['antes'] ?? [];
                            $fotosDespues = $rawFotos['despues'] ?? [];
                        }
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Column: Fotos ANTES -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-rose-500/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-400 uppercase">🔴 Fotos ANTES de la Reparación</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @forelse($fotosAntes as $fotoUrl)
                            <a href="{{ $fotoUrl }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-slate-800 hover:border-blue-500 transition">
                                <img src="{{ $fotoUrl }}" class="w-full h-full object-cover">
                            </a>
                            @empty
                            <p class="text-[11px] text-slate-500 italic col-span-2 py-4 text-center">Sin fotos registradas del estado inicial.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Column: Fotos DESPUÉS -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-emerald-500/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-400 uppercase">🟢 Fotos DESPUÉS de la Reparación</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @forelse($fotosDespues as $fotoUrl)
                            <a href="{{ $fotoUrl }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-slate-800 hover:border-emerald-500 transition">
                                <img src="{{ $fotoUrl }}" class="w-full h-full object-cover">
                            </a>
                            @empty
                            <p class="text-[11px] text-slate-500 italic col-span-2 py-4 text-center">Sin fotos registradas del trabajo finalizado.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- REPUESTOS Y MATERIALES UTILIZADOS -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span>Repuestos y Materiales Consumidos</span>
                    </h3>

                    @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
                    <button @click="addSpareModal = true" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500 hover:text-slate-950 border border-amber-500/30 text-xs font-semibold transition">
                        + Asignar Repuesto
                    </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-500 uppercase text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Código SKU / Repuesto</th>
                                <th class="py-2.5 px-3 text-center">Cantidad</th>
                                <th class="py-2.5 px-3 text-right">Costo Unit.</th>
                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                                @if(auth()->user()->isAdmin() || (in_array($ot->estado, ['Pendiente', 'Aprobada', 'En_Progreso', 'En_Pausa']) && auth()->user()->isTechnician()))
                                <th class="py-2.5 px-3 text-right">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @forelse($ot->spareParts as $sp)
                            <tr>
                                <td class="py-2.5 px-3">
                                    <span class="font-mono text-[10px] text-blue-400 font-bold block">{{ $sp->repuesto?->codigo_sku }}</span>
                                    <span class="font-semibold text-white">{{ $sp->repuesto?->nombre }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-amber-400 font-mono">{{ $sp->cantidad_usada }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-400">S/. {{ number_format($sp->costo_unitario, 2) }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-400">S/. {{ number_format($sp->costo_total, 2) }}</td>
                                @if(auth()->user()->isAdmin() || (in_array($ot->estado, ['Pendiente', 'Aprobada', 'En_Progreso', 'En_Pausa']) && auth()->user()->isTechnician()))
                                <td class="py-2.5 px-3 text-right">
                                    <form action="{{ route('ordenes.remove-spare-part', $sp->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea retirar este repuesto de la OT? El stock retornará al almacén.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 transition" title="Eliminar y devolver a stock">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500 italic">No se han registrado repuestos descontados de almacén para esta OT.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AUDITORÍA DE TRAMOS DE MANO DE OBRA Y TIEMPOS -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Registro de Tramos de Trabajo & Pausas</span>
                </h3>

                <div class="space-y-2">
                    @forelse($ot->laborTimes as $labor)
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-white block">{{ $labor->usuario?->nombre_completo ?? 'Técnico de Planta' }}</span>
                            <span class="text-[11px] text-slate-400">
                                ⏱️ {{ $labor->fecha_inicio?->format('d/m/Y H:i') }}
                                @if($labor->fecha_fin)
                                 ➜ {{ $labor->fecha_fin->format('H:i') }}
                                @else
                                 (En ejecución...)
                                @endif
                            </span>
                            @if($labor->observaciones)
                            <p class="text-[10px] text-amber-300/80 italic mt-0.5">{{ $labor->observaciones }}</p>
                            @endif
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold border block
                                    @if($labor->estado == 'En_Progreso') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                                    @elseif($labor->estado == 'En_Pausa') bg-amber-500/10 text-amber-400 border-amber-500/30
                                    @else bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @endif">
                                    {{ str_replace('_', ' ', $labor->estado) }}
                                </span>
                                <span class="font-mono text-xs font-extrabold text-white mt-1 block">
                                    {{ number_format($labor->horas_trabajadas ?? 0, 2) }} hrs
                                </span>
                            </div>

                            @if(auth()->user()->isAdmin() || (in_array($ot->estado, ['Pendiente', 'Aprobada', 'En_Progreso', 'En_Pausa']) && auth()->user()->isTechnician()))
                            <form action="{{ route('ordenes.remove-labor-time', $labor->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este registro de tiempo?')" class="border-l border-slate-700 pl-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 transition" title="Eliminar registro de tiempo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 italic text-center py-4">No hay tramos de mano de obra registrados aún.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: Personnel & Actions Sidebar -->
        <div class="space-y-6">

            <!-- Technical Assignment Form for Supervisor -->
            @if(!$ot->tecnico_id && auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h4 class="font-bold text-white uppercase text-xs text-amber-400">Asignar Técnico a esta OT</h4>

                <form action="{{ route('ordenes.assign', $ot->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Seleccionar Técnico *</label>
                        <select name="tecnico_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                            <option value="">Seleccione Técnico</option>
                            @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->nombre_completo }} ({{ $tec->especialidad ?? 'General' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Prioridad Ajustada</label>
                        <select name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                            <option value="Media" {{ $ot->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                            <option value="Alta" {{ $ot->prioridad == 'Alta' ? 'selected' : '' }}>Alta</option>
                            <option value="Crítica" {{ $ot->prioridad == 'Crítica' ? 'selected' : '' }}>🚨 Crítica</option>
                            <option value="Baja" {{ $ot->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-extrabold text-xs shadow-lg">
                        ✓ Aprobar y Asignar Técnico
                    </button>
                </form>
            </div>
            @endif

            <!-- Personnel Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 text-xs">
                <h4 class="font-bold text-white uppercase text-[11px] text-slate-400">Personal Involucrado</h4>

                <div class="space-y-3">
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Solicitado por:</span>
                        <strong class="text-white font-medium text-xs">{{ $ot->solicitante?->nombre_completo ?? '📱 Reporte QR (Planta)' }}</strong>
                        <p class="text-[10px] text-slate-400">{{ $ot->solicitante?->email ?? 'Operario / Personal de Planta' }}</p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Técnico Responsable:</span>
                        @if($ot->tecnico)
                        <strong class="text-blue-400 font-medium text-xs">{{ $ot->tecnico->nombre_completo }}</strong>
                        <p class="text-[10px] text-slate-400">{{ $ot->tecnico->especialidad ?? 'Técnico de Planta' }}</p>
                        @else
                        <span class="text-amber-400 italic text-[11px]">Aún sin asignar</span>
                        @endif
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="pt-3 border-t border-slate-800 space-y-1.5">
                    <div class="flex justify-between text-slate-400">
                        <span>Horas Trabajadas:</span>
                        <span class="font-mono text-white">{{ number_format($ot->duracion_real_horas ?? 0, 2) }} hrs</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Mano de Obra:</span>
                        <span class="font-mono text-white">S/. {{ number_format($ot->costo_mano_obra ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Repuestos:</span>
                        <span class="font-mono text-amber-400">S/. {{ number_format($ot->costo_repuestos ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-white font-bold pt-1 border-t border-slate-800">
                        <span>Costo Total:</span>
                        <span class="font-mono text-emerald-400 text-sm">S/. {{ number_format($ot->costo_real ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- TARJETA PERMISO DE TRABAJO SEGURO (PTS / LOTO) -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-white uppercase text-xs text-rose-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Seguridad PTS & LOTO</span>
                    </h4>

                    @php
                        $latestPts = $ot->permisosTrabajoSeguro()->latest()->first();
                    @endphp

                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                        @if($latestPts && $latestPts->estado === 'APROBADO') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @elseif($ot->requiere_permiso_especial) bg-amber-500/10 text-amber-400 border-amber-500/30
                        @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                        {{ $latestPts ? $latestPts->estado : ($ot->requiere_permiso_especial ? 'REQUIERE PTS' : 'ESTÁNDAR') }}
                    </span>
                </div>

                <p class="text-xs text-slate-400">
                    @if($ot->requiere_permiso_especial)
                        Esta OT requiere bloqueo de energías (LOTO) y permiso de trabajo especial verificado antes de iniciar.
                    @else
                        Evaluación de riesgos operacionales y equipo de protección personal.
                    @endif
                </p>

                @if($latestPts)
                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1 text-xs">
                    <div class="flex justify-between text-slate-300 font-medium">
                        <span>Riesgo: <strong class="text-white">{{ str_replace('_', ' ', $latestPts->tipo_riesgo) }}</strong></span>
                        <span>Aprobado: <strong class="text-emerald-400">{{ $latestPts->fecha_aprobacion?->format('d/m H:i') }}</strong></span>
                    </div>
                    <p class="text-[10px] text-slate-500">LOTO Confirmado: {{ $latestPts->bloqueo_loto_confirmado ? 'Sí (Candado colocado)' : 'No' }}</p>
                </div>
                @endif

                @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
                <button @click="ptsModal = true" class="w-full py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 text-xs font-bold transition">
                    🛡️ {{ $latestPts ? 'Registrar Nuevo Checklist PTS' : 'Diligenciar Permiso PTS / LOTO' }}
                </button>
                @endif
            </div>

            <!-- TARJETA FIRMAS DIGITALES DE CIERRE -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-3">
                <h4 class="font-bold text-white uppercase text-xs text-cyan-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span>Firmas Digitales de Cierre</span>
                </h4>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <!-- Firma Técnico (Mandatoria) -->
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-2 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Firma Técnico *</span>
                        @if($ot->firma_tecnico)
                            <img src="{{ $ot->firma_tecnico }}" class="h-12 mx-auto object-contain bg-white/10 rounded border border-slate-700 p-1">
                            <span class="text-[9px] text-emerald-400 font-mono block">✓ Registrada {{ $ot->fecha_firma_tecnico?->format('d/m H:i') }}</span>
                        @else
                            <div class="h-12 flex items-center justify-center border border-dashed border-amber-500/40 rounded bg-amber-500/5">
                                <span class="text-[10px] text-amber-400 font-bold">Pendiente (Obligatorio)</span>
                            </div>
                        @endif
                        @if(!$ot->firma_tecnico && (auth()->user()->isTechnician() || auth()->user()->isAdmin()))
                        <button @click="signatureRole = 'tecnico'; signatureModal = true; $nextTick(() => initCanvas());" class="w-full py-1 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-[10px] font-bold transition">
                            ✍️ Firmar ahora
                        </button>
                        @endif
                    </div>

                    <!-- Firma Supervisor (Opcional) -->
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-2 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Firma Supervisor</span>
                        @if($ot->firma_supervisor)
                            <img src="{{ $ot->firma_supervisor }}" class="h-12 mx-auto object-contain bg-white/10 rounded border border-slate-700 p-1">
                            <span class="text-[9px] text-emerald-400 font-mono block">✓ Registrada {{ $ot->fecha_firma_supervisor?->format('d/m H:i') }}</span>
                        @else
                            <div class="h-12 flex items-center justify-center border border-dashed border-slate-800 rounded bg-slate-900/50">
                                <span class="text-[10px] text-slate-500">Opcional</span>
                            </div>
                        @endif
                        @if(!$ot->firma_supervisor && (auth()->user()->isSupervisor() || auth()->user()->isAdmin()))
                        <button @click="signatureRole = 'supervisor'; signatureModal = true; $nextTick(() => initCanvas());" class="w-full py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-bold transition">
                            ✍️ Firmar ahora
                        </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL PARA PAUSAR TRABAJO -->
    <div x-show="pauseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                    <span>⏸️ Pausar Ejecución de OT</span>
                </h3>
                <button @click="pauseModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form action="{{ route('ordenes.pause', $ot->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Motivo Principal de la Pausa *</label>
                    <select name="motivo_pausa" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">
                        <option value="Falta_Repuesto" selected>📦 Falta de Repuestos en Almacén</option>
                        <option value="Fin_Jornada">⏰ Fin de Jornada / Cambio de Turno</option>
                        <option value="Operativa_Planta">🏭 Requerimiento de Lote de Producción</option>
                        <option value="Permiso_Seguridad">🛡️ Permiso de Trabajo Seguro / Enfriamiento</option>
                        <option value="Otro">💬 Otro Motivo Justificado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Detalles / Observaciones de la Pausa</label>
                    <textarea name="observaciones" rows="3" placeholder="Ej: Esperando empaque de nitrilo 2'' solicitado a proveedor..." 
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="pauseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/30">
                        ⏸️ Confirmar Pausa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ASIGNAR REPUESTO DE ALMACÉN -->
    <div x-show="addSpareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Asignar Repuesto a <span class="text-amber-400 font-mono">{{ $ot->codigo_ot }}</span></h3>
            
            <form action="{{ route('ordenes.add-spare-part', $ot->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Seleccionar Repuesto de Almacén *</label>
                    <select name="repuesto_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="">Seleccione Repuesto</option>
                        @foreach($repuestosAlmacen as $rep)
                        <option value="{{ $rep->id }}">
                            [{{ $rep->codigo_sku }}] {{ $rep->nombre }} (Stock: {{ $rep->stock_actual }} un. | ${{ number_format($rep->costo_unitario, 2) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Cantidad Utilizada *</label>
                    <input type="number" name="cantidad" value="1" min="1" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Motivo de Uso / Observación</label>
                    <input type="text" name="motivo_uso" placeholder="Ej: Reemplazo por desgaste por horas de uso" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="addSpareModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg">Agregar y Descontar Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADJUNTAR FOTO (ANTES / DESPUÉS) -->
    <div x-show="uploadPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Adjuntar Evidencia Fotográfica</h3>
            
            <form action="{{ route('ordenes.upload-photo', $ot->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Etiqueta de la Foto *</label>
                    <select name="tipo_foto" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="antes">🔴 Foto ANTES de la Reparación (Estado inicial)</option>
                        <option value="despues">🟢 Foto DESPUÉS de la Reparación (Trabajo completado)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Seleccionar Imagen (JPG, PNG) *</label>
                    <input type="file" name="foto" accept="image/*" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-slate-300">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="uploadPhotoModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-600/30">Subir Fotografía</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: DILIGENCIAR PERMISO DE TRABAJO SEGURO (PTS / LOTO) -->
    <div x-show="ptsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider flex items-center gap-2">
                    <span>🛡️ Permiso de Trabajo Seguro (PTS / LOTO)</span>
                </h3>
                <button @click="ptsModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form action="{{ route('ordenes.store-pts', $ot->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Clasificación Principal del Riesgo *</label>
                    <select name="tipo_riesgo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                        <option value="LOTO_MECANICO_ELECTRICO">🔒 LOTO (Bloqueo Eléctrico / Mecánico de Energía)</option>
                        <option value="TRABAJO_EN_ALTURA">🧗 Trabajo en Altura (>1.80m / Uso de Arnés)</option>
                        <option value="ESPACIO_CONFINADO">🕳️ Espacio Confinado / Atmósfera Restringida</option>
                        <option value="ALTO_VOLTAJE">⚡ Alto Voltaje / Tableros Energizados</option>
                        <option value="TRABAJO_EN_CALIENTE">🔥 Trabajo en Caliente (Soldadura / Corte)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Equipo de Protección Personal (EPP Obligatorio)</label>
                    <div class="grid grid-cols-2 gap-2 text-slate-300 bg-slate-950 p-3 rounded-xl border border-slate-800">
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Casco_Dieléctrico" checked class="rounded border-slate-800 text-rose-500"> <span>Casco Dieléctrico</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Gafas_Seguridad" checked class="rounded border-slate-800 text-rose-500"> <span>Gafas de Seguridad</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Zapatos_Punta_Acero" checked class="rounded border-slate-800 text-rose-500"> <span>Calzado de Seguridad</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Guantes_Multirriesgo" checked class="rounded border-slate-800 text-rose-500"> <span>Guantes Aislantes</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Arnés_Anticaídas" class="rounded border-slate-800 text-rose-500"> <span>Arnés de Seguridad</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="epp_requeridos[]" value="Candado_Pinza_LOTO" class="rounded border-slate-800 text-rose-500"> <span>Candado & Tarjeta LOTO</span></label>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Checklist Verificación de Riesgos</label>
                    <div class="space-y-1.5 bg-slate-950 p-3 rounded-xl border border-slate-800 text-slate-300">
                        <label class="flex items-center space-x-2"><input type="checkbox" name="checklist_verificacion[]" value="Energias_Cero_Verificadas" checked class="rounded border-slate-800 text-rose-500"> <span>¿Se verificó ausencia de energía residual?</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="checklist_verificacion[]" value="Area_Delimitada" checked class="rounded border-slate-800 text-rose-500"> <span>¿Área delimitada y señalizada?</span></label>
                        <label class="flex items-center space-x-2"><input type="checkbox" name="checklist_verificacion[]" value="Extintor_Cercano" class="rounded border-slate-800 text-rose-500"> <span>¿Extintor operacional cercano?</span></label>
                    </div>
                </div>

                <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl space-y-2">
                    <label class="flex items-center space-x-2 text-rose-300 font-bold">
                        <input type="checkbox" name="bloqueo_loto_confirmado" value="1" required class="rounded border-rose-500/40 text-rose-500">
                        <span>CONFIRMO EL BLOQUEO FÍSICO LOTO (CANDADO Y TARJETA COLOCADOS)</span>
                    </label>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Observaciones Adicionales de Seguridad</label>
                    <textarea name="observaciones" rows="2" placeholder="Ej: Válvula principal cerrada con cadena y candado #4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="ptsModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/30">Aprobar Permiso PTS</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: LIENZO CANVAS DE FIRMA DIGITAL -->
    <div x-show="signatureModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                    <span>✍️ Captura de Firma Digital</span>
                </h3>
                <button @click="signatureModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form action="{{ route('ordenes.save-signatures', $ot->id) }}" method="POST" id="signatureForm" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="firma_tecnico" id="firmaTecnicoInput">
                <input type="hidden" name="firma_supervisor" id="firmaSupervisorInput">

                <p class="text-slate-400 text-center text-[11px]">Por favor dibuje su firma manuscrita dentro del recuadro (soporta pantalla táctil o mouse).</p>

                <div class="border-2 border-dashed border-slate-700 bg-white rounded-2xl p-1 relative overflow-hidden">
                    <canvas id="signatureCanvas" width="380" height="180" class="w-full h-44 cursor-crosshair touch-none"></canvas>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" onclick="clearCanvas()" class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-[11px] font-semibold">
                        🗑️ Limpiar Lienzo
                    </button>
                    <span class="text-[10px] text-slate-500 uppercase font-mono" x-text="signatureRole === 'tecnico' ? 'Firmando como: TÉCNICO' : 'Firmando como: SUPERVISOR'"></span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="signatureModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="button" onclick="saveSignaturePad()" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-600/30">Guardar Firma</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
let canvas, ctx, isDrawing = false;

function initCanvas() {
    canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;
    ctx = canvas.getContext('2d');
    
    // Configuración trazo firma
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    // Mouse events
    canvas.onmousedown = (e) => { isDrawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); };
    canvas.onmousemove = (e) => { if (isDrawing) { ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); } };
    canvas.onmouseup = () => { isDrawing = false; };

    // Touch events para móviles/tablets
    canvas.ontouchstart = (e) => {
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];
        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    };
    canvas.ontouchmove = (e) => {
        e.preventDefault();
        if (isDrawing) {
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
            ctx.stroke();
        }
    };
    canvas.ontouchend = () => { isDrawing = false; };
}

function clearCanvas() {
    if (ctx && canvas) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function saveSignaturePad() {
    if (!canvas) return;
    const dataUrl = canvas.toDataURL('image/png');
    const form = document.getElementById('signatureForm');
    const alpineData = Alpine.$data(document.querySelector('[x-data]'));

    if (alpineData.signatureRole === 'tecnico') {
        document.getElementById('firmaTecnicoInput').value = dataUrl;
    } else {
        document.getElementById('firmaSupervisorInput').value = dataUrl;
    }

    form.submit();
}
</script>
@endsection

