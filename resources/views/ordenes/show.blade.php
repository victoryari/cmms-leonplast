@extends('layouts.app')

@section('title', "Orden de Trabajo: {$ot->codigo_ot}")

@section('content')
<div class="space-y-6">

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
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300">
                Prioridad: <strong class="text-amber-400">{{ $ot->prioridad }}</strong>
            </span>
        </div>
    </div>

    <!-- Status Lifecycle Progress Timeline -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Progreso de la Orden de Trabajo</h4>
        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['Pendiente', 'Aprobada', 'En_Progreso', 'En_Revision', 'Completada']) ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">1. Solicitada</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_solicitud?->format('d/m/Y H:i') }}</span>
            </div>

            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['Aprobada', 'En_Progreso', 'En_Revision', 'Completada']) ? 'bg-indigo-600/20 border-indigo-500/40 text-indigo-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">2. Aprobada / Asignada</span>
                <span class="text-[10px] opacity-75">{{ $ot->fecha_aprobacion ? $ot->fecha_aprobacion->format('d/m/Y H:i') : 'Pendiente' }}</span>
            </div>

            <div class="p-3 rounded-2xl border transition {{ in_array($ot->estado, ['En_Progreso', 'En_Revision', 'Completada']) ? 'bg-amber-600/20 border-amber-500/40 text-amber-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                <span class="block font-bold">3. En Ejecución</span>
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

        <!-- Left 2 Cols: Details, Diagnosis, Solution & Rating -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Asset & Description Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Activo Afectado y Descripción de la Falla</span>
                </h3>

                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="font-mono text-xs font-bold text-blue-400">{{ $ot->activo?->codigo_activo }}</span>
                        <h4 class="text-sm font-bold text-white mt-0.5">{{ $ot->activo?->nombre }}</h4>
                        <p class="text-xs text-slate-400">Ubicación: {{ $ot->activo?->ubicacion }} ({{ $ot->activo?->area }})</p>
                    </div>

                    @if($ot->activo)
                    <a href="{{ route('activos.show', $ot->activo->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700">
                        Ver Ficha Máquina
                    </a>
                    @endif
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase">Detalle del Requerimiento:</span>
                    <p class="text-xs text-slate-300 leading-relaxed bg-slate-950 p-4 rounded-2xl border border-slate-800/80">{{ $ot->descripcion }}</p>
                </div>
            </div>

            <!-- Technician Action Form (When assigned & in execution) -->
            @if(auth()->user()->isTechnician() || auth()->user()->hasRole(['Administrador', 'Supervisor', 'Gerente_Mantenimiento']))
            <div class="p-6 rounded-3xl bg-slate-900 border border-indigo-500/30 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Registro Técnico de Intervención (Técnico)</span>
                </h3>

                <form method="POST" action="{{ route('ordenes.update-status', $ot->id) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Cambiar Estado Operativo *</label>
                            <select name="estado" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                <option value="En_Progreso" {{ $ot->estado == 'En_Progreso' ? 'selected' : '' }}>En Progreso (Iniciado)</option>
                                <option value="En_Pausa" {{ $ot->estado == 'En_Pausa' ? 'selected' : '' }}>En Pausa (Esperando Repuesto)</option>
                                <option value="En_Revision" {{ $ot->estado == 'En_Revision' ? 'selected' : '' }}>En Revisión / Pruebas</option>
                                <option value="Completada" {{ $ot->estado == 'Completada' ? 'selected' : '' }}>Completada (Finalizada)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Horas Reales de Trabajo</label>
                            <input type="number" step="0.1" name="duracion_real_horas" value="{{ old('duracion_real_horas', $ot->duracion_real_horas ?? 2.5) }}" placeholder="Ej: 3.5"
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Diagnóstico Encontrado</label>
                        <input type="text" name="diagnostico" placeholder="Ej: Fisura en sellos tóricos de la bomba hidráulica..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Solución Aplicada</label>
                        <input type="text" name="solucion" placeholder="Ej: Reemplazo de kit de empaquetadura V-ring y prueba de presión..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition">
                            Actualizar Avance de OT
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Diagnósticos y Soluciones Registradas -->
            @if(!empty($ot->diagnosticos) || !empty($ot->soluciones))
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-emerald-400">Diagnóstico e Historial de Soluciones</h3>

                @if(!empty($ot->diagnosticos))
                <div>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase block mb-1">Diagnósticos:</span>
                    <ul class="list-disc list-inside text-xs text-slate-300 space-y-1 bg-slate-950 p-3 rounded-xl border border-slate-800">
                        @foreach($ot->diagnosticos as $diag)
                        <li>{{ $diag }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($ot->soluciones))
                <div>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase block mb-1">Soluciones Aplicadas:</span>
                    <ul class="list-disc list-inside text-xs text-emerald-300 space-y-1 bg-slate-950 p-3 rounded-xl border border-slate-800">
                        @foreach($ot->soluciones as $sol)
                        <li>{{ $sol }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif

            <!-- Satisfaction Rating (For Requester when Completed) -->
            @if($ot->estado == 'Completada')
            <div class="p-6 rounded-3xl bg-slate-900 border border-amber-500/30 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400 flex items-center gap-2">
                    <span>Evaluación de Calidad del Servicio de Mantenimiento</span>
                </h3>

                @if($ot->calificacion_usuario)
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs space-y-1">
                    <div class="flex items-center space-x-1 text-amber-400 text-lg">
                        @for($i=1; $i<=5; $i++)
                        <span>{{ $i <= $ot->calificacion_usuario ? '★' : '☆' }}</span>
                        @endfor
                        <span class="text-xs font-bold text-white ml-2">({{ $ot->calificacion_usuario }}/5 Estrellas)</span>
                    </div>
                    @if($ot->comentario_usuario)
                    <p class="text-slate-300 italic pt-1">"{{ $ot->comentario_usuario }}"</p>
                    @endif
                </div>
                @elseif(auth()->user()->id == $ot->solicitante_id || auth()->user()->isAdmin())
                <form method="POST" action="{{ route('ordenes.rate', $ot->id) }}" class="space-y-3" x-data="{ stars: 5 }">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Califica el trabajo del técnico (1 a 5 Estrellas):</label>
                        <div class="flex items-center space-x-2 text-2xl cursor-pointer text-amber-400">
                            <template x-for="i in 5">
                                <span @click="stars = i" x-text="i <= stars ? '★' : '☆'"></span>
                            </template>
                            <input type="hidden" name="calificacion_usuario" :value="stars">
                        </div>
                    </div>

                    <div>
                        <textarea name="comentario_usuario" rows="2" placeholder="Comentario adicional sobre el servicio..."
                                  class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500"></textarea>
                    </div>

                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg transition">
                        Enviar Calificación
                    </button>
                </form>
                @endif
            </div>
            @endif

        </div>

        <!-- Right Col: Personnel & Safety Checklist -->
        <div class="space-y-6">

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

                <!-- Supervisor Assignment Box if Pending -->
                @if(!$ot->tecnico_id && auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento', 'Supervisor']))
                <div class="pt-2">
                    <form method="POST" action="{{ route('ordenes.assign', $ot->id) }}" class="space-y-3 p-3 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                        @csrf
                        <span class="text-xs font-bold text-amber-300 block">Aprobar y Asignar Técnico</span>
                        <select name="tecnico_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-white">
                            <option value="">Seleccione Técnico</option>
                            @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->nombre_completo }}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="prioridad" value="{{ $ot->prioridad }}">

                        <button type="submit" class="w-full py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition">
                            Asignar y Aprobar
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Safety Checklist & Special Permits Card -->
            @if($ot->requiere_permiso_especial || !empty($ot->checklist_seguridad))
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-3 text-xs">
                <h4 class="font-bold text-rose-400 uppercase text-[11px] flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Seguridad y Permisos Especiales</span>
                </h4>

                @if($ot->permisos_especiales)
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300">
                    <span class="block text-[10px] uppercase font-bold">Permiso Requerido:</span>
                    <p class="font-medium mt-0.5">{{ $ot->permisos_especiales }}</p>
                </div>
                @endif

                @if(!empty($ot->checklist_seguridad))
                <div class="space-y-1.5 pt-1">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Lista de Chequeo LOTO / EPP:</span>
                    @foreach($ot->checklist_seguridad as $item => $estadoCheck)
                    <div class="flex items-center space-x-2 text-slate-300">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
