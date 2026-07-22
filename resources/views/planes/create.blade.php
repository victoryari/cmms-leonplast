@extends('layouts.app')

@section('title', 'Configurar Nuevo Plan Preventivo')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ tipoPlan: 'Por_Calendario' }">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('planes.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Configurar Plan de Mantenimiento Preventivo</h2>
            <p class="text-xs text-slate-400">Establece la frecuencia y plantilla para la generación automática de Órdenes de Trabajo</p>
        </div>
    </div>

    <!-- Plan Registration Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('planes.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="nombre_plan" class="block text-xs font-semibold text-slate-300 mb-1">Nombre del Plan Preventivo *</label>
                <input type="text" id="nombre_plan" name="nombre_plan" value="{{ old('nombre_plan') }}" required placeholder="Ej: Mantenimiento Mensual Inyectora Engel 250T"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="activo_id" class="block text-xs font-semibold text-slate-300 mb-1">Activo Afectado *</label>
                    <select id="activo_id" name="activo_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white">
                        <option value="">Seleccione Máquina</option>
                        @foreach($activos as $act)
                        <option value="{{ $act->id }}" {{ old('activo_id') == $act->id ? 'selected' : '' }}>
                            [{{ $act->codigo_activo }}] {{ $act->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tipo_plan" class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Programación *</label>
                    <select id="tipo_plan" name="tipo_plan" x-model="tipoPlan" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-200">
                        <option value="Por_Calendario">Por Calendario (Días / Meses)</option>
                        <option value="Por_Medidor">Por Medidor (Horómetro / Horas)</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Section: Por Calendario -->
            <div x-show="tipoPlan === 'Por_Calendario'" class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/30 space-y-3">
                <label for="frecuencia_dias" class="block text-xs font-semibold text-blue-300">Frecuencia en Días *</label>
                <input type="number" id="frecuencia_dias" name="frecuencia_dias" value="{{ old('frecuencia_dias', 30) }}" placeholder="Ej: 30"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <span class="text-[10px] text-slate-400">La OT se generará automáticamente cada número especificado de días.</span>
            </div>

            <!-- Dynamic Section: Por Medidor / Horómetro -->
            <div x-show="tipoPlan === 'Por_Medidor'" class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 space-y-3" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="unidad_medicion" class="block text-xs font-semibold text-purple-300 mb-1">Unidad de Medición</label>
                        <input type="text" id="unidad_medicion" name="unidad_medicion" value="{{ old('unidad_medicion', 'Horas') }}" placeholder="Ej: Horas"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                    <div>
                        <label for="umbral_medidor" class="block text-xs font-semibold text-purple-300 mb-1">Horas / Intervalo Umbral</label>
                        <input type="number" id="umbral_medidor" name="umbral_medidor" value="{{ old('umbral_medidor', 500) }}" placeholder="Ej: 500"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-2 border-t border-slate-800">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider text-emerald-400">Plantilla de la OT a Generar</h4>

                <div>
                    <label for="titulo_ot_generada" class="block text-xs font-semibold text-slate-300 mb-1">Título de la OT Generada *</label>
                    <input type="text" id="titulo_ot_generada" name="titulo_ot_generada" value="{{ old('titulo_ot_generada') }}" required placeholder="Ej: Preventive Mensual Inyectora Engel"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="descripcion_ot_generada" class="block text-xs font-semibold text-slate-300 mb-1">Descripción de la Rutina *</label>
                    <textarea id="descripcion_ot_generada" name="descripcion_ot_generada" rows="3" required placeholder="Instrucciones técnicas generales de la inspección..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">{{ old('descripcion_ot_generada') }}</textarea>
                </div>

                <div>
                    <label for="instrucciones_especificas" class="block text-xs font-semibold text-slate-300 mb-1">Puntos de Chequeo / Checklist Paso a Paso</label>
                    <textarea id="instrucciones_especificas" name="instrucciones_especificas" rows="3" placeholder="1. Engrasar guías lineales.&#10;2. Medir presión hidráulica..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">{{ old('instrucciones_especificas') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tecnico_asignado_id" class="block text-xs font-semibold text-slate-300 mb-1">Técnico Asignado por Defecto</label>
                        <select id="tecnico_asignado_id" name="tecnico_asignado_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                            <option value="">Sin Asignar</option>
                            @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="prioridad_defecto" class="block text-xs font-semibold text-slate-300 mb-1">Prioridad por Defecto *</label>
                        <select id="prioridad_defecto" name="prioridad_defecto" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200">
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('planes.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30">
                    Guardar Plan Preventivo
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
