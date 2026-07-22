@extends('layouts.app')

@section('title', "Editar Activo: {$activo->codigo_activo}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('activos.show', $activo->id) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <div class="flex items-center space-x-2">
                <span class="font-mono text-xs font-bold text-blue-400">{{ $activo->codigo_activo }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-white">Modificar Ficha del Activo</h2>
        </div>
    </div>

    <!-- Registration Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('activos.update', $activo->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: General Info -->
            <div>
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">1. Información Principal del Equipo</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre Completo del Activo *</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $activo->nombre) }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-semibold text-slate-300 mb-1">Categoría del Equipo *</label>
                        <select id="categoria" name="categoria" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->nombre }}" {{ old('categoria', $activo->categoria) == $cat->nombre ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estado_operativo" class="block text-xs font-semibold text-slate-300 mb-1">Estado Operativo *</label>
                        <select id="estado_operativo" name="estado_operativo" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            <option value="Operativo" {{ old('estado_operativo', $activo->estado_operativo) == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                            <option value="Mantenimiento" {{ old('estado_operativo', $activo->estado_operativo) == 'Mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                            <option value="Reparacion" {{ old('estado_operativo', $activo->estado_operativo) == 'Reparacion' ? 'selected' : '' }}>En Reparación</option>
                            <option value="Fuera_de_servicio" {{ old('estado_operativo', $activo->estado_operativo) == 'Fuera_de_servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                            <option value="Baja" {{ old('estado_operativo', $activo->estado_operativo) == 'Baja' ? 'selected' : '' }}>Baja Definitiva</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Specs & Location -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-4">2. Marca, Modelo y Ubicación</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="marca" class="block text-xs font-semibold text-slate-300 mb-1">Marca</label>
                        <input type="text" id="marca" name="marca" value="{{ old('marca', $activo->marca) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="modelo" class="block text-xs font-semibold text-slate-300 mb-1">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="{{ old('modelo', $activo->modelo) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="numero_serie" class="block text-xs font-semibold text-slate-300 mb-1">Número de Serie</label>
                        <input type="text" id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $activo->numero_serie) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="ubicacion" class="block text-xs font-semibold text-slate-300 mb-1">Ubicación en Planta</label>
                        <input type="text" id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $activo->ubicacion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="area" class="block text-xs font-semibold text-slate-300 mb-1">Área Operativa</label>
                        <input type="text" id="area" name="area" value="{{ old('area', $activo->area) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="estado_condicion" class="block text-xs font-semibold text-slate-300 mb-1">Condición Física</label>
                        <select id="estado_condicion" name="estado_condicion" required 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-blue-500">
                            <option value="Nuevo" {{ old('estado_condicion', $activo->estado_condicion) == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                            <option value="Bueno" {{ old('estado_condicion', $activo->estado_condicion) == 'Bueno' ? 'selected' : '' }}>Bueno</option>
                            <option value="Regular" {{ old('estado_condicion', $activo->estado_condicion) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Malo" {{ old('estado_condicion', $activo->estado_condicion) == 'Malo' ? 'selected' : '' }}>Malo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Description -->
            <div class="pt-4 border-t border-slate-800">
                <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Descripción Detallada</label>
                <textarea id="descripcion" name="descripcion" rows="3"
                          class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500">{{ old('descripcion', $activo->descripcion) }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <button type="button" onclick="if(confirm('¿Confirma dar de baja este activo?')) document.getElementById('delete-form').submit();" 
                        class="px-4 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold">
                    Dar de Baja Activo
                </button>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('activos.show', $activo->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs font-semibold transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
                        Actualizar Cambios
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-form" action="{{ route('activos.destroy', $activo->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

</div>
@endsection
