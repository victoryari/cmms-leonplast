@extends('layouts.app')

@section('title', 'Mi Perfil de Usuario')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Title -->
    <div>
        <h2 class="text-2xl font-extrabold text-white">Mi Perfil & Configuración de Cuenta</h2>
        <p class="text-xs text-slate-400 mt-1">Gestiona tus datos personales y actualiza tu contraseña de acceso al CMMS</p>
    </div>

    <!-- Alert System -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center space-x-3 text-emerald-400">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-xs font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 space-y-2">
        <div class="flex items-center space-x-3 text-rose-450">
            <svg class="w-5 h-5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="text-xs font-bold text-rose-400">Por favor corrige los siguientes errores:</span>
        </div>
        <ul class="list-disc list-inside text-[11px] pl-8 space-y-1 text-slate-350">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- User Info Header Card -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl flex items-center space-x-4">
        <div class="w-16 h-16 rounded-full bg-blue-600/30 border-2 border-blue-500/40 flex items-center justify-center text-blue-300 font-bold text-xl shrink-0 shadow-lg">
            {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellidos, 0, 1) }}
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <h3 class="text-lg font-bold text-white">{{ $user->nombre_completo }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    {{ $user->role?->nombre }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Correo: <strong class="text-white">{{ $user->email }}</strong> | Código: <span class="font-mono text-blue-400 font-bold">{{ $user->codigo_empleado ?? 'EMP-001' }}</span></p>
        </div>
    </div>

    <!-- Update Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        <form method="POST" action="{{ route('perfil.update') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="telefono" class="block text-xs font-semibold text-slate-300 mb-1">Teléfono de Contacto</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" placeholder="+51 987 654 321"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="especialidad" class="block text-xs font-semibold text-slate-300 mb-1">Especialidad / Área</label>
                    <input type="text" id="especialidad" name="especialidad" value="{{ old('especialidad', $user->especialidad) }}" placeholder="Ej: Electromecánica / Inyección"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <h4 class="text-xs font-bold text-amber-400 uppercase">Cambiar Contraseña de Acceso (Opcional)</h4>

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-300 mb-1">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" minlength="6" placeholder="Mínimo 6 caracteres"
                               class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1">Confirmar Nueva Contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="6" placeholder="Repite la nueva clave"
                               class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30">
                    Guardar Cambios de Perfil
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
