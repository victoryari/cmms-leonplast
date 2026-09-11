@extends('layouts.app')

@section('title', 'Editar Orden de Trabajo')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('ordenes.show', $ot->id) }}" class="p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Editar Orden: {{ $ot->codigo_ot }}</h2>
            <p class="text-xs text-slate-400">Solo aplicable a órdenes en estado Pendiente o con permisos de Administrador.</p>
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

    <form action="{{ route('ordenes.update', $ot->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">Información de la Solicitud</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Título del Problema *</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $ot->titulo) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Descripción Detallada *</label>
                    <textarea name="descripcion" rows="4" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">{{ old('descripcion', $ot->descripcion) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Activo Afectado *</label>
                    <select name="activo_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                        <option value="">Seleccione un activo...</option>
                        @foreach($activos as $activo)
                            <option value="{{ $activo->id }}" {{ old('activo_id', $ot->activo_id) == $activo->id ? 'selected' : '' }}>
                                {{ $activo->codigo_activo }} - {{ $activo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Prioridad *</label>
                    <select name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white">
                        <option value="Baja" {{ old('prioridad', $ot->prioridad) == 'Baja' ? 'selected' : '' }}>Baja (Cuanto antes)</option>
                        <option value="Media" {{ old('prioridad', $ot->prioridad) == 'Media' ? 'selected' : '' }}>Media (Esta semana)</option>
                        <option value="Alta" {{ old('prioridad', $ot->prioridad) == 'Alta' ? 'selected' : '' }}>Alta (Hoy)</option>
                        <option value="Crítica" {{ old('prioridad', $ot->prioridad) == 'Crítica' ? 'selected' : '' }}>Crítica (Emergencia - Parada de Planta)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5">
                Actualizar OT
            </button>
        </div>
    </form>
</div>
@endsection
