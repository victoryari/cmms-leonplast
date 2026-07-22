@extends('layouts.app')

@section('title', 'Solicitar Mantenimiento / Crear OT')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('ordenes.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Solicitud de Mantenimiento</h2>
            <p class="text-xs text-slate-400">Genera un reporte de avería o requerimiento de intervención técnica en planta</p>
        </div>
    </div>

    <!-- Request Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('ordenes.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="activo_id" class="block text-xs font-semibold text-slate-300 mb-1">Activo Afectado / Máquina *</label>
                <select id="activo_id" name="activo_id" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                    <option value="">Seleccione el activo o máquina de la planta</option>
                    @foreach($activos as $activo)
                    <option value="{{ $activo->id }}" {{ old('activo_id') == $activo->id ? 'selected' : '' }}>
                        [{{ $activo->codigo_activo }}] {{ $activo->nombre }} ({{ $activo->ubicacion }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="titulo" class="block text-xs font-semibold text-slate-300 mb-1">Título Resumido de la Falla *</label>
                <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" required placeholder="Ej: Fuga de líquido hidráulico en cilindro principal"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tipo_ot" class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Mantenimiento *</label>
                    <select id="tipo_ot" name="tipo_ot" required 
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                        <option value="Correctivo">Correctivo (Reparación de Falla)</option>
                        <option value="Preventivo">Preventivo Programado</option>
                        <option value="Predictivo">Predictivo / Monitoreo</option>
                        <option value="Urgente">Urgente (Planta Detenida)</option>
                        <option value="Mejora">Mejora / Optimización</option>
                    </select>
                </div>

                <div>
                    <label for="prioridad" class="block text-xs font-semibold text-slate-300 mb-1">Nivel de Prioridad *</label>
                    <select id="prioridad" name="prioridad" required 
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                        <option value="Baja">Baja (No afecta producción)</option>
                        <option value="Media" selected>Media (Afecta rendimiento parcial)</option>
                        <option value="Alta">Alta (Riesgo de parada inmediata)</option>
                        <option value="Crítica">Crítica (Máquina o línea parada)</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Descripción Detallada de la Avería *</label>
                <textarea id="descripcion" name="descripcion" rows="4" required placeholder="Describe qué ocurrió, ruidos sospechosos, mensajes de alarma en la pantalla de la máquina..."
                          class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">{{ old('descripcion') }}</textarea>
            </div>

            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center space-x-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>La solicitud será notificada al Supervisor para asignación de un técnico especializado.</span>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('ordenes.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
                    Enviar Solicitud de OT
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
