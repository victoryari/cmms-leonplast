@extends('layouts.app')

@section('title', 'Editar Rol - ' . $role->nombre)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Editar Rol & Permisos Granulares</h2>
            <p class="text-xs text-slate-400 mt-1">Modificación de la matriz de accesos para el rol {{ str_replace('_', ' ', $role->nombre) }}</p>
        </div>
        <a href="{{ route('roles.index') }}" 
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
            ← Volver a Roles
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="p-6 md:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <form action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Info -->
            <div>
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">1. Identificación del Rol</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nombre" class="block text-xs font-semibold text-slate-300 mb-1">Nombre del Rol *</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $role->nombre) }}" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Descripción de Funciones</label>
                        <input type="text" id="descripcion" name="descripcion" value="{{ old('descripcion', $role->descripcion) }}"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="activo" class="block text-xs font-semibold text-slate-300 mb-1">Estado de Acceso</label>
                        <select id="activo" name="activo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                            <option value="1" {{ $role->activo ? 'selected' : '' }}>Habilitado (Activo)</option>
                            <option value="0" {{ !$role->activo ? 'selected' : '' }}>Deshabilitado (Inactivo)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Matrix of Granular Permissions -->
            <div class="pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">2. Matriz de Permisos Granulares por Módulo</h3>
                        <p class="text-[11px] text-slate-400">Selecciona o remueve permisos para este rol</p>
                    </div>

                    <div class="flex items-center space-x-2 text-xs">
                        <button type="button" onclick="toggleAllPermissions(true)" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 font-semibold text-[11px]">
                            ✓ Marcar Todos
                        </button>
                        <button type="button" onclick="toggleAllPermissions(false)" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 font-semibold text-[11px]">
                            ✕ Desmarcar Todos
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($definitions as $modKey => $modInfo)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-900">
                            <span class="text-xs font-bold text-white">{{ $modInfo['label'] }}</span>
                            <button type="button" onclick="toggleModulePermissions('{{ $modKey }}')" class="text-[10px] text-cyan-400 font-semibold hover:underline">
                                Alternar Módulo
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($modInfo['acciones'] as $accKey => $accLabel)
                            @php
                                $isChecked = $role->nombre === 'Administrador' ? true : (!empty($role->permisos[$modKey][$accKey]));
                            @endphp
                            <label class="flex items-center space-x-2.5 p-2 rounded-xl bg-slate-900/60 border border-slate-800/60 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="permisos[{{ $modKey }}][{{ $accKey }}]" value="1" {{ $isChecked ? 'checked' : '' }}
                                       class="perm-check perm-mod-{{ $modKey }} rounded bg-slate-950 border-slate-800 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-slate-300">{{ $accLabel }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('roles.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                    ✓ Actualizar Permisos del Rol
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function toggleAllPermissions(checked) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = checked);
}

function toggleModulePermissions(modKey) {
    const checkboxes = document.querySelectorAll(`.perm-mod-${modKey}`);
    const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
    checkboxes.forEach(cb => cb.checked = anyUnchecked);
}
</script>
@endsection
