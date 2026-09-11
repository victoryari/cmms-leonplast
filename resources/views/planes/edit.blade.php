@extends('layouts.app')

@section('title', 'Editar Plan Preventivo')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ tipoPlan: '{{ old('tipo_plan', $plan->tipo_plan) }}' }">

    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('planes.show', $plan->id) }}" class="p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Editar Plan: {{ $plan->nombre_plan }}</h2>
            <p class="text-xs text-slate-400">Modifica los parámetros de ejecución o la información del plan.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/50 p-4 rounded-xl text-rose-400 text-sm mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('planes.update', $plan->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">1. Datos Generales</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre del Plan *</label>
                    <input type="text" name="nombre_plan" value="{{ old('nombre_plan', $plan->nombre_plan) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Activo Asociado *</label>
                    <select name="activo_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                        <option value="">Seleccione un activo...</option>
                        @foreach($activos as $activo)
                            <option value="{{ $activo->id }}" {{ old('activo_id', $plan->activo_id) == $activo->id ? 'selected' : '' }}>
                                {{ $activo->codigo_activo }} - {{ $activo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">2. Parámetros de Disparo</h3>
            
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Tipo de Plan *</label>
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2 bg-slate-950 px-4 py-3 rounded-xl border border-slate-800 cursor-pointer w-full">
                        <input type="radio" name="tipo_plan" value="Por_Calendario" x-model="tipoPlan" class="text-blue-500 bg-slate-900 border-slate-700">
                        <span class="text-sm text-white font-medium">Por Calendario (Días)</span>
                    </label>
                    <label class="flex items-center space-x-2 bg-slate-950 px-4 py-3 rounded-xl border border-slate-800 cursor-pointer w-full">
                        <input type="radio" name="tipo_plan" value="Por_Medidor" x-model="tipoPlan" class="text-blue-500 bg-slate-900 border-slate-700">
                        <span class="text-sm text-white font-medium">Por Medidor (Ciclos/Horas)</span>
                    </label>
                </div>
            </div>

            <!-- Inputs Dinámicos -->
            <div x-show="tipoPlan === 'Por_Calendario'" class="grid grid-cols-1 gap-5" x-cloak>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Frecuencia en Días *</label>
                    <input type="number" min="1" name="frecuencia_dias" value="{{ old('frecuencia_dias', $plan->frecuencia_dias) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white" placeholder="Ej: 30 para mensual">
                </div>
            </div>

            <div x-show="tipoPlan === 'Por_Medidor'" class="grid grid-cols-1 md:grid-cols-2 gap-5" x-cloak>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Unidad de Medición *</label>
                    <input type="text" name="unidad_medicion" value="{{ old('unidad_medicion', $plan->unidad_medicion) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white" placeholder="Ej: Horas Máquina, Golpes, Ciclos">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Umbral de Disparo (Valor) *</label>
                    <input type="number" min="1" step="0.01" name="umbral_medidor" value="{{ old('umbral_medidor', $plan->umbral_medidor) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white" placeholder="Ej: 5000">
                </div>
            </div>
        </div>

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">3. Plantilla de Orden de Trabajo a Generar</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Título de la OT *</label>
                    <input type="text" name="titulo_ot_generada" value="{{ old('titulo_ot_generada', $plan->titulo_ot_generada) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Descripción del Trabajo (Problema/Motivo) *</label>
                    <textarea name="descripcion_ot_generada" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">{{ old('descripcion_ot_generada', $plan->descripcion_ot_generada) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Instrucciones Específicas / Checklist Opcional</label>
                    <textarea name="instrucciones_especificas" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white" placeholder="1. Apagar máquina...&#10;2. Lubricar rodamientos...">{{ old('instrucciones_especificas', $plan->instrucciones_especificas) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Técnico Pre-Asignado (Opcional)</label>
                    <select name="tecnico_asignado_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                        <option value="">Sin asignar (Auto-asignación posterior)</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ old('tecnico_asignado_id', $plan->tecnico_asignado_id) == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Prioridad por Defecto *</label>
                    <select name="prioridad_defecto" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                        <option value="Baja" {{ old('prioridad_defecto', $plan->prioridad_defecto) == 'Baja' ? 'selected' : '' }}>Baja (Inspección Rutina)</option>
                        <option value="Media" {{ old('prioridad_defecto', $plan->prioridad_defecto) == 'Media' ? 'selected' : '' }}>Media (Mantenimiento Estándar)</option>
                        <option value="Alta" {{ old('prioridad_defecto', $plan->prioridad_defecto) == 'Alta' ? 'selected' : '' }}>Alta (Lubricación Mayor/Cambio Componente)</option>
                        <option value="Crítica" {{ old('prioridad_defecto', $plan->prioridad_defecto) == 'Crítica' ? 'selected' : '' }}>Crítica (Parada de Planta Preventiva)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
