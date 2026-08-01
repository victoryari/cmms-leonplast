@extends('layouts.app')

@section('title', 'Gestión de Roles & Permisos Granulares')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('usuarios.index') }}" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                    ←
                </a>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Catálogo de Roles & Matriz de Permisos</h2>
            </div>
            <p class="text-xs text-slate-400 mt-1">Definición de roles del sistema y asignación granular de permisos por cada módulo</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('usuarios.index') }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
                👥 Lista de Personal
            </a>
            <a href="{{ route('roles.create') }}" 
               class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Crear Nuevo Rol</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Roles Registrados</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_roles'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Roles Activos</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['roles_activos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-blue-400 uppercase">Usuarios Asignados</p>
            <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $metrics['total_usuarios'] }}</p>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        {{ str_replace('_', ' ', $role->nombre) }}
                    </span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $role->activo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                        ● {{ $role->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <p class="text-xs text-slate-300 min-h-[36px]">{{ $role->descripcion ?? 'Sin descripción ingresada.' }}</p>

                <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Resumen de Permisos por Módulo:</span>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        @foreach($definitions as $modKey => $modInfo)
                            @php
                                $permisoMod = $role->nombre === 'Administrador' ? true : (!empty($role->permisos[$modKey]['ver']));
                            @endphp
                            <span class="px-2 py-0.5 rounded-md font-semibold border {{ $permisoMod ? 'bg-emerald-950/80 text-emerald-300 border-emerald-800' : 'bg-slate-900 text-slate-500 border-slate-800' }}">
                                {{ explode(' ', $modInfo['label'])[0] }} {{ Str::after($modInfo['label'], ' ') }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <strong class="text-white">{{ $role->usuarios_count }}</strong> usuarios asignados
                </span>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('roles.edit', $role->id) }}" 
                       class="px-3 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white text-xs font-bold border border-blue-500/30 transition">
                        ✏️ Editar Permisos
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
