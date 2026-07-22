@extends('layouts.app')

@section('title', "Editar Usuario: {$usuario->nombre_completo}")

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('usuarios.show', $usuario->id) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Editar Usuario <span class="font-mono text-blue-400">[{{ $usuario->codigo_empleado }}]</span></h2>
            <p class="text-xs text-slate-400">Modifica datos personales, asignación de rol o estado del empleado</p>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombres" class="block text-xs font-semibold text-slate-300 mb-1">Nombres *</label>
                    <input type="text" id="nombres" name="nombres" value="{{ old('nombres', $usuario->nombres) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label for="apellidos" class="block text-xs font-semibold text-slate-300 mb-1">Apellidos *</label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="rol_id" class="block text-xs font-semibold text-slate-300 mb-1">Rol de Sistema *</label>
                    <select id="rol_id" name="rol_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $usuario->rol_id == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="codigo_empleado" class="block text-xs font-semibold text-slate-300 mb-1">Código de Empleado *</label>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" value="{{ old('codigo_empleado', $usuario->codigo_empleado) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono uppercase">
                </div>

                <div>
                    <label for="especialidad" class="block text-xs font-semibold text-slate-300 mb-1">Especialidad / Área</label>
                    <input type="text" id="especialidad" name="especialidad" value="{{ old('especialidad', $usuario->especialidad) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="telefono" class="block text-xs font-semibold text-slate-300 mb-1">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="activo" class="block text-xs font-semibold text-slate-300 mb-1">Estado Acceso *</label>
                    <select id="activo" name="activo" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                        <option value="1" {{ $usuario->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$usuario->activo ? 'selected' : '' }}>Suspendido / Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('usuarios.show', $usuario->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
