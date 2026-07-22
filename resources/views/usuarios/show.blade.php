@extends('layouts.app')

@section('title', "Ficha de Usuario: {$usuario->nombre_completo}")

@section('content')
<div class="space-y-6">

    <!-- Header Navigation & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('usuarios.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30">
                        {{ $usuario->codigo_empleado ?? 'EMP-001' }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border
                        @if($usuario->activo) bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                        ● {{ $usuario->activo ? 'Acceso Activo' : 'Suspendido' }}
                    </span>
                </div>
                <h2 class="text-2xl font-extrabold text-white mt-1">{{ $usuario->nombre_completo }}</h2>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition">
                Editar Empleado
            </a>
        </div>
    </div>

    <!-- User Profile Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 1 Col: Profile Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 text-xs">
            <div class="flex flex-col items-center text-center pb-4 border-b border-slate-800">
                <div class="w-20 h-20 rounded-full bg-indigo-600/20 border-2 border-indigo-500/40 flex items-center justify-center text-indigo-300 font-bold text-2xl mb-3 shadow-xl">
                    {{ substr($usuario->nombres, 0, 1) }}{{ substr($usuario->apellidos, 0, 1) }}
                </div>
                <h3 class="text-lg font-bold text-white">{{ $usuario->nombre_completo }}</h3>
                <span class="mt-1 px-3 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    {{ $usuario->role?->nombre }}
                </span>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-[10px] text-slate-500 uppercase block font-semibold">Correo Electrónico:</span>
                    <strong class="text-white font-medium block">{{ $usuario->email }}</strong>
                </div>

                <div>
                    <span class="text-[10px] text-slate-500 uppercase block font-semibold">Teléfono:</span>
                    <strong class="text-white font-medium block">{{ $usuario->telefono ?? 'Sin registrar' }}</strong>
                </div>

                <div>
                    <span class="text-[10px] text-slate-500 uppercase block font-semibold">Especialidad / Área:</span>
                    <strong class="text-blue-400 font-medium block">{{ $usuario->especialidad ?? 'Planta General' }}</strong>
                </div>

                <div>
                    <span class="text-[10px] text-slate-500 uppercase block font-semibold">Descripción del Rol:</span>
                    <p class="text-slate-300 leading-relaxed text-[11px] bg-slate-950 p-3 rounded-xl border border-slate-800/80 mt-1">
                        {{ $usuario->role?->descripcion }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right 2 Cols: Activity Log (Assigned / Requested OTs) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- OTs Asignadas (Si es Técnico) -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Historial de Órdenes de Trabajo Intervenidas</span>
                </h3>

                <div class="overflow-x-auto rounded-2xl border border-slate-800">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase">
                            <tr>
                                <th class="p-3">Código OT</th>
                                <th class="p-3">Activo</th>
                                <th class="p-3">Tipo</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-right">Ver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($otsAsignadas as $ot)
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 font-mono font-bold text-blue-400">{{ $ot->codigo_ot }}</td>
                                <td class="p-3 text-slate-200 font-medium">{{ $ot->activo?->nombre ?? 'N/A' }}</td>
                                <td class="p-3 text-slate-300">{{ $ot->tipo_ot }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                                        @if($ot->estado == 'Completada') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                        @elseif($ot->estado == 'En_Progreso') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                                        @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                                        {{ $ot->estado }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <a href="{{ route('ordenes.show', $ot->id) }}" class="text-blue-400 hover:underline text-[11px] font-bold">Ver OT ➔</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-500 italic">No hay órdenes de trabajo asignadas a este usuario.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
