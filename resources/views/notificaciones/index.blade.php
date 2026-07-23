@extends('layouts.app')

@section('title', 'Centro de Notificaciones & Alertas')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Centro de Notificaciones</h2>
            <p class="text-xs text-slate-400 mt-1">Alertas en tiempo real de asignaciones de OTs, paradas de planta y stock</p>
        </div>
        <form action="{{ route('notificaciones.marcar-todas') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
                ✓ Marcar todas como leídas
            </button>
        </form>
    </div>

    <!-- Notification List Card -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 space-y-3 shadow-2xl">
        @forelse($notificaciones as $item)
        <div class="p-4 rounded-2xl border transition flex items-start justify-between space-x-4
            {{ $item->leido ? 'bg-slate-950/40 border-slate-800/60 opacity-75' : 'bg-blue-950/20 border-blue-500/30' }}">
            
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm shrink-0 font-bold
                    {{ str_contains($item->tipo, 'Averia') ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                    {{ str_contains($item->tipo, 'Averia') ? '🚨' : '📋' }}
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span>{{ $item->titulo }}</span>
                        @if(!$item->leido)
                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">{{ $item->mensaje }}</p>
                    <span class="text-[10px] text-slate-500 font-mono mt-1.5 block">
                        {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->format('d/m/Y H:i A') }})
                    </span>
                </div>
            </div>

            @if($item->url_accion)
            <a href="{{ route('notificaciones.marcar-leida', $item->id) }}" 
               class="px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white text-xs font-bold transition shrink-0">
                Ver Trabajo →
            </a>
            @endif
        </div>
        @empty
        <div class="py-12 text-center text-slate-500 text-xs">
            No tienes notificaciones registradas en el sistema.
        </div>
        @endforelse

        <!-- Pagination -->
        @if($notificaciones->hasPages())
        <div class="pt-4 border-t border-slate-800">
            {{ $notificaciones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
