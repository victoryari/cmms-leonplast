@extends('layouts.app')

@section('title', "Plan Preventivo: {$plan->nombre_plan}")

@section('content')
<div class="space-y-6">

    <!-- Header Navigation & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('planes.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border
                        @if($plan->estado == 'Activo') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                        @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                        ● Plan {{ $plan->estado }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/30">
                        {{ $plan->tipo_plan == 'Por_Calendario' ? '📅 Por Calendario' : '⏱ Por Horómetro' }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $plan->nombre_plan }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <form method="POST" action="{{ route('planes.toggle-status', $plan->id) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition">
                    {{ $plan->estado === 'Activo' ? '⏸ Pausar Plan' : '▶ Activar Plan' }}
                </button>
            </form>

            <form method="POST" action="{{ route('planes.execute-now', $plan->id) }}">
                @csrf
                <button type="submit" onclick="return confirm('¿Deseas generar inmediatamente la OT preventiva?')"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    ⚡ Ejecutar OT Ahora
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Execution History -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Plan Details Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-emerald-400">Especificaciones de la Rutina Preventiva</h3>

                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="font-mono text-xs font-bold text-blue-400">[{{ $plan->activo?->codigo_activo }}]</span>
                        <h4 class="text-sm font-bold text-white mt-0.5">{{ $plan->activo?->nombre }}</h4>
                        <p class="text-xs text-slate-400">Ubicación: {{ $plan->activo?->ubicacion }}</p>
                    </div>

                    @if($plan->activo)
                    <a href="{{ route('activos.show', $plan->activo->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700">
                        Ver Máquina
                    </a>
                    @endif
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase">Descripción General:</span>
                    <p class="text-xs text-slate-300 bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80">{{ $plan->descripcion }}</p>
                </div>

                @if($plan->instrucciones_especificas)
                <div class="space-y-1">
                    <span class="text-[10px] font-semibold text-emerald-400 uppercase">Lista de Chequeo / Instrucciones Técnicas Especificas:</span>
                    <pre class="text-xs text-slate-200 bg-slate-950 p-4 rounded-2xl border border-slate-800 font-sans whitespace-pre-line leading-relaxed">{{ $plan->instrucciones_especificas }}</pre>
                </div>
                @endif
            </div>

            <!-- Execution History Table -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400">Historial de Generación de Órdenes de Trabajo</h3>

                <div class="overflow-x-auto rounded-2xl border border-slate-800">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase">
                            <tr>
                                <th class="p-3">Fecha Generación</th>
                                <th class="p-3">OT Generada</th>
                                <th class="p-3">Modo Disparo</th>
                                <th class="p-3">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($plan->historialEjecuciones as $hist)
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 text-slate-300 font-mono">{{ $hist->created_at ? $hist->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="p-3">
                                    @if($hist->ordenTrabajo)
                                    <a href="{{ route('ordenes.show', $hist->ordenTrabajo->id) }}" class="font-mono font-bold text-blue-400 hover:underline">
                                        {{ $hist->ordenTrabajo->codigo_ot }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 block">{{ $hist->ordenTrabajo->estado }}</span>
                                    @else
                                    <span class="text-slate-500 italic">N/A</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border
                                        @if($hist->tipo_ejecucion == 'Automatica') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                        @else bg-purple-500/10 text-purple-400 border-purple-500/30 @endif">
                                        {{ $hist->tipo_ejecucion }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-400 text-[11px]">{{ $hist->observaciones }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-500 italic">No hay historial de ejecuciones previas para este plan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Col: Frequency & Next Run Info -->
        <div class="space-y-6">

            <!-- Frecuencia Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 text-xs">
                <h4 class="font-bold text-white uppercase text-[11px] text-slate-400">Parámetros de Frecuencia</h4>

                <div class="space-y-3">
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Intervalo de Programación:</span>
                        <strong class="text-emerald-400 text-sm font-bold">{{ $plan->frecuencia_texto }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Próxima Fecha Programada:</span>
                        <strong class="text-white text-sm font-bold block">{{ $plan->proxima_ejecucion ? $plan->proxima_ejecucion->format('d/m/Y') : 'Pendiente' }}</strong>
                        <span class="text-[10px] text-slate-400">{{ $plan->proxima_ejecucion ? $plan->proxima_ejecucion->diffForHumans() : '' }}</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Técnico Asignado:</span>
                        <strong class="text-blue-400 font-medium">{{ $plan->tecnicoAsignado?->nombre_completo ?? 'Sin asignar' }}</strong>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold block">Prioridad por Defecto:</span>
                        <strong class="text-amber-400 font-medium">{{ $plan->prioridad_defecto }}</strong>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
