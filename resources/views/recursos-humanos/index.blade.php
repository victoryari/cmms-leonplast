@extends('layouts.app')

@section('title', 'Recursos Humanos - Personal de Planta')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Catálogo de Personal & Ficha de Trabajadores</h2>
            <p class="text-xs text-slate-400 mt-1">Gestión de personal de planta, especialidades, sueldos y tarifas de costo hora hombre (HH)</p>
        </div>

        <div>
            <a href="{{ route('recursos-humanos.create') }}" 
               class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>+ Registrar Nuevo Trabajador</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Personal</p>
            <p class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_personal'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-blue-400 uppercase">Técnicos de Planta</p>
            <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $metrics['tecnicos'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-purple-400 uppercase">Supervisores & Jefaturas</p>
            <p class="text-2xl font-extrabold text-purple-400 mt-1">{{ $metrics['supervisores'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-[11px] font-semibold text-emerald-400 uppercase">Tarifa Promedio HH</p>
            <p class="text-2xl font-extrabold text-emerald-400 mt-1">S/. {{ number_format($metrics['promedio_costo_hora'], 2) }} / h</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('recursos-humanos.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código EMP, nombre o especialidad..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <select name="especialidad" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-300 focus:outline-none">
                    <option value="">Todas las Especialidades</option>
                    @foreach($especialidades as $esp)
                    <option value="{{ $esp }}" {{ request('especialidad') == $esp ? 'selected' : '' }}>{{ $esp }}</option>
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

    <!-- Personal Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-semibold uppercase">
                        <th class="py-4 px-6">Código / Trabajador</th>
                        <th class="py-4 px-6">DNI / Doc. Identidad</th>
                        <th class="py-4 px-6">Especialidad / Área</th>
                        <th class="py-4 px-6">Teléfono Contacto</th>
                        <th class="py-4 px-6">Sueldo Mensual</th>
                        <th class="py-4 px-6 text-center">Costo Hora (HH)</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($empleados as $emp)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-blue-600/20 border border-blue-500/30 flex items-center justify-center font-bold text-blue-300 text-xs shrink-0">
                                    {{ substr($emp->nombres, 0, 1) }}{{ substr($emp->apellidos, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-mono text-[10px] text-blue-400 block font-bold">{{ $emp->codigo_empleado ?? 'EMP-000' }}</span>
                                    <a href="{{ route('recursos-humanos.show', $emp->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                        {{ $emp->nombre_completo }}
                                    </a>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-6 font-mono text-slate-300 font-semibold">
                            {{ $emp->documento_identidad ?? '-' }}
                        </td>

                        <td class="py-4 px-6 text-slate-300 font-medium">
                            {{ $emp->especialidad ?? 'Planta General' }}
                        </td>

                        <td class="py-4 px-6 text-slate-300">
                            {{ $emp->telefono ?? 'Sin registrar' }}
                        </td>

                        <td class="py-4 px-6 text-slate-300 font-mono">
                            {{ $emp->sueldo_mensual ? 'S/. ' . number_format($emp->sueldo_mensual, 2) : 'No especificado' }}
                        </td>

                        <td class="py-4 px-6 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono">
                                S/. {{ number_format($emp->costo_hora_calculado, 2) }} / h
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right space-x-1">
                            <a href="{{ route('recursos-humanos.show', $emp->id) }}" class="px-2.5 py-1 rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600 hover:text-white border border-blue-500/30 font-semibold text-[11px] transition">
                                Ver Ficha
                            </a>
                            <a href="{{ route('recursos-humanos.edit', $emp->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:text-white border border-slate-700 font-semibold text-[11px] transition">
                                Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">No se encontraron trabajadores en Recursos Humanos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $empleados->links() }}
    </div>

</div>
@endsection
