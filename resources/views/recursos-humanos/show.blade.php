@extends('layouts.app')

@section('title', 'Ficha de Trabajador - ' . $empleado->nombre_completo)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('recursos-humanos.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <span class="text-xs font-mono font-bold text-blue-400 uppercase tracking-widest">{{ $empleado->codigo_empleado }}</span>
                <h2 class="text-2xl font-extrabold text-white">{{ $empleado->nombre_completo }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('recursos-humanos.edit', $empleado->id) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs border border-slate-700 transition">
                Editar Ficha Laboral
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Profile Card Left -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-blue-600/20 border-2 border-blue-500/40 flex items-center justify-center font-black text-blue-300 text-2xl">
                {{ substr($empleado->nombres, 0, 1) }}{{ substr($empleado->apellidos, 0, 1) }}
            </div>

            <div>
                <h3 class="text-lg font-bold text-white">{{ $empleado->nombre_completo }}</h3>
                <p class="text-xs text-blue-400 font-semibold mt-0.5">{{ $empleado->especialidad ?? 'Planta General' }}</p>
            </div>

            <div class="pt-3 border-t border-slate-800 space-y-2 text-left text-xs">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Código EMP:</span>
                    <span class="font-mono text-white font-bold">{{ $empleado->codigo_empleado }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">DNI / Doc.:</span>
                    <span class="font-mono text-white font-bold">{{ $empleado->documento_identidad ?? 'Sin registrar' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Teléfono:</span>
                    <span class="text-white">{{ $empleado->telefono ?? 'Sin registrar' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Función:</span>
                    <span class="text-white font-semibold">{{ $empleado->role?->nombre ?? 'Técnico' }}</span>
                </div>
            </div>
        </div>

        <!-- Costs & Work Info Right -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Tarifa & Costo Hora Hombre (HH)</h4>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase">Sueldo Mensual Base</p>
                        <p class="text-xl font-extrabold text-white mt-1">
                            {{ $empleado->sueldo_mensual ? 'S/. ' . number_format($empleado->sueldo_mensual, 2) : 'No especificado' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950 border border-emerald-900/40">
                        <p class="text-[11px] text-emerald-400 font-semibold uppercase">Costo Hora Aplicado OTs</p>
                        <p class="text-xl font-extrabold text-emerald-400 mt-1">
                            S/. {{ number_format($empleado->costo_hora_calculado, 2) }} / h
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recent Work Orders Assigned -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-3">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Últimas Órdenes de Trabajo Asignadas</h4>

                <div class="divide-y divide-slate-800 text-xs">
                    @forelse($otsAsignadas as $ot)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('ordenes.show', $ot->id) }}" class="font-bold text-white hover:text-blue-400 transition font-mono">
                                {{ $ot->codigo_ot }} - {{ $ot->titulo }}
                            </a>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $ot->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">
                            {{ $ot->estado }}
                        </span>
                    </div>
                    @empty
                    <p class="py-4 text-center text-slate-500">No registra órdenes de trabajo asignadas.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
