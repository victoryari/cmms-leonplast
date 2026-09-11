@extends('layouts.app')

@section('title', 'Registro de Auditoría y Trazabilidad')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-bold font-mono">
                    🛡️ SEGURIDAD
                </span>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Registro de Auditoría</h2>
            </div>
            <p class="text-xs text-slate-400 mt-1">Historial inmutable de cambios en Activos y Repuestos del sistema.</p>
        </div>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800 text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Fecha & Hora</th>
                        <th class="py-3.5 px-4">Usuario Responsable</th>
                        <th class="py-3.5 px-4">Acción</th>
                        <th class="py-3.5 px-4">Módulo / Modelo</th>
                        <th class="py-3.5 px-4">Detalle de Cambios</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-bold text-white block">{{ $log->created_at->format('d/m/Y') }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->user)
                            <span class="font-bold text-blue-400 block">{{ $log->user->nombre_completo }}</span>
                            <span class="text-[10px] text-slate-500">{{ $log->ip_address }}</span>
                            @else
                            <span class="text-amber-400 italic">Sistema / Consola</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase border 
                                @if($log->action == 'created') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @elseif($log->action == 'updated') bg-amber-500/10 text-amber-400 border-amber-500/30
                                @elseif($log->action == 'deleted') bg-rose-500/10 text-rose-400 border-rose-500/30
                                @else bg-slate-800 text-slate-300 border-slate-700 @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-white block font-semibold">{{ class_basename($log->model_type) }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">ID: {{ $log->model_id }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->action == 'updated' && $log->new_values)
                                <div class="space-y-1 max-w-xs">
                                    @foreach($log->new_values as $key => $newValue)
                                        @php $oldValue = $log->old_values[$key] ?? 'N/A'; @endphp
                                        <div class="text-[10px] bg-slate-950 p-1.5 rounded border border-slate-800">
                                            <span class="text-slate-500 font-bold uppercase">{{ $key }}:</span> 
                                            <span class="line-through text-rose-400">{{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}</span> 
                                            ➔ 
                                            <span class="text-emerald-400 font-bold">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($log->action == 'created' && $log->new_values)
                                <span class="text-[10px] text-emerald-400 italic">Registro creado exitosamente.</span>
                            @elseif($log->action == 'deleted' && $log->old_values)
                                <span class="text-[10px] text-rose-400 italic">Registro eliminado.</span>
                            @else
                                <span class="text-[10px] text-slate-500 italic">Sin detalle específico.</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                            No se ha registrado ninguna actividad auditable en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
