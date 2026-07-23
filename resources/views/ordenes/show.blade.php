@extends('layouts.app')

@section('title', "Orden de Trabajo: {$ot->codigo_ot}")

@section('content')
<div class="space-y-6" x-data="{ addSpareModal: false, uploadPhotoModal: false, pauseModal: false, photoType: 'antes' }">

    <!-- Top Navigation & Status Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('ordenes.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
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

        <div class="flex items-center space-x-2">
            <!-- Botones de Acción de Pausa / Reanudación para el Técnico -->
            @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
                @if($ot->estado == 'En_Progreso')
                <button @click="pauseModal = true" class="px-4 py-2 rounded-xl bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 text-xs font-bold transition flex items-center space-x-1.5">
                    <span>⏸️ Pausar Trabajo</span>
                </button>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Column: Fotos ANTES -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-rose-500/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-400 uppercase">🔴 Fotos ANTES de la Reparación</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @forelse(is_array($ot->fotos) ? ($ot->fotos['antes'] ?? []) : [] as $fotoUrl)
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
                            @forelse(is_array($ot->fotos) ? ($ot->fotos['despues'] ?? []) : [] as $fotoUrl)
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
                                <td class="py-2.5 px-3 text-right font-mono text-slate-400">${{ number_format($sp->costo_unitario, 2) }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-400">${{ number_format($sp->costo_total, 2) }}</td>
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
                        <strong class="text-white font-medium text-xs">{{ $ot->solicitante?->nombre_completo ?? 'N/A' }}</strong>
                        <p class="text-[10px] text-slate-400">{{ $ot->solicitante?->email }}</p>
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
                        <span class="font-mono text-white">${{ number_format($ot->costo_mano_obra ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Repuestos:</span>
                        <span class="font-mono text-amber-400">${{ number_format($ot->costo_repuestos ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-white font-bold pt-1 border-t border-slate-800">
                        <span>Costo Total:</span>
                        <span class="font-mono text-emerald-400 text-sm">${{ number_format($ot->costo_real ?? 0, 2) }}</span>
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

</div>
@endsection
