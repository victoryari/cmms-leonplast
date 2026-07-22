@extends('layouts.app')

@section('title', 'Gestión de Usuarios & Personal de Planta')

@section('content')
<div class="space-y-6" x-data="{ resetModal: false, resetUserId: null, resetUserName: '' }">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Personal & Permisos Granulares</h2>
            <p class="text-xs text-slate-400 mt-1">Administración de roles (Administrador, Gerente, Supervisor, Técnico, Solicitante) y control de acceso</p>
        </div>

        <a href="{{ route('usuarios.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>+ Registrar Nuevo Usuario</span>
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Personal</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_usuarios'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Accesos Activos</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['activos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-blue-400 uppercase">Técnicos de Planta</p>
            <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $metrics['tecnicos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-purple-400 uppercase">Supervisores & Mandos</p>
            <p class="text-2xl font-extrabold text-purple-400 mt-1">{{ $metrics['supervisores'] }}</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('usuarios.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, correo o EMP..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <select name="rol_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none">
                    <option value="">Todos los Roles</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ request('rol_id') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2 px-4 rounded-xl border border-slate-700 transition">
                    Filtrar Personal
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-semibold uppercase">
                        <th class="py-4 px-6">Código / Empleado</th>
                        <th class="py-4 px-6">Rol de Sistema</th>
                        <th class="py-4 px-6">Contacto / Correo</th>
                        <th class="py-4 px-6">Especialidad</th>
                        <th class="py-4 px-6 text-center">Estado Acceso</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($usuarios as $usr)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-300 text-xs shrink-0">
                                    {{ substr($usr->nombres, 0, 1) }}{{ substr($usr->apellidos, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-mono text-[10px] text-blue-400 block font-bold">{{ $usr->codigo_empleado ?? 'EMP-000' }}</span>
                                    <a href="{{ route('usuarios.show', $usr->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                        {{ $usr->nombre_completo }}
                                    </a>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border
                                @if($usr->role?->nombre == 'Administrador') bg-purple-500/10 text-purple-400 border-purple-500/30
                                @elseif($usr->role?->nombre == 'Gerente_Mantenimiento') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                                @elseif($usr->role?->nombre == 'Supervisor') bg-blue-500/10 text-blue-400 border-blue-500/30
                                @elseif($usr->role?->nombre == 'Tecnico') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @else bg-slate-500/10 text-slate-400 border-slate-500/30 @endif">
                                {{ $usr->role?->nombre }}
                            </span>
                        </td>

                        <td class="py-4 px-6">
                            <span class="text-slate-200 font-medium block">{{ $usr->email }}</span>
                            <span class="text-[10px] text-slate-500">{{ $usr->telefono ?? 'Sin teléfono' }}</span>
                        </td>

                        <td class="py-4 px-6 text-slate-300">
                            {{ $usr->especialidad ?? 'Planta General' }}
                        </td>

                        <td class="py-4 px-6 text-center">
                            <form action="{{ route('usuarios.toggle-status', $usr->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition hover:opacity-80
                                    @if($usr->activo) bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                                    {{ $usr->activo ? '● Activo' : '○ Suspendido' }}
                                </button>
                            </form>
                        </td>

                        <td class="py-4 px-6 text-right space-x-1">
                            <a href="{{ route('usuarios.show', $usr->id) }}" class="px-2.5 py-1 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 font-semibold text-[11px] transition">
                                Ver Ficha
                            </a>
                            <a href="{{ route('usuarios.edit', $usr->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:text-white border border-slate-700 font-semibold text-[11px] transition">
                                Editar
                            </a>
                            <button @click="resetModal = true; resetUserId = {{ $usr->id }}; resetUserName = '{{ addslashes($usr->nombre_completo) }}'" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-600/20 text-amber-300 hover:bg-amber-600 hover:text-white border border-amber-500/30 font-semibold text-[11px] transition">
                                Clave
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">No se encontraron usuarios registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $usuarios->links() }}
    </div>

    <!-- MODAL RESTABLECER CONTRASEÑA -->
    <div x-show="resetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Restablecer Contraseña</h3>
            <p class="text-xs text-slate-400">Ingresa la nueva clave de acceso para <strong class="text-white" x-text="resetUserName"></strong>:</p>

            <form :action="'/usuarios/' + resetUserId + '/restablecer-clave'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nueva Contraseña *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="Repite la contraseña" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="resetModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/30">Guardar Nueva Clave</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
