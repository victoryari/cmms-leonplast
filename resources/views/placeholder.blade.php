@extends('layouts.app')

@section('title', $title ?? 'Módulo CMMS')

@section('content')
<div class="p-12 rounded-3xl bg-slate-900 border border-slate-800 text-center space-y-4">
    <div class="w-16 h-16 rounded-2xl bg-blue-600/20 border border-blue-500/30 text-blue-400 flex items-center justify-center mx-auto">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    </div>
    <h3 class="text-2xl font-bold text-white">{{ $title ?? 'Módulo CMMS' }}</h3>
    <p class="text-slate-400 text-sm max-w-md mx-auto">
        Este módulo está conectado a la base de datos <code class="text-blue-400 font-mono">cmms_leonplast</code> y listo para la construcción de sus vistas específicas.
    </p>
    <div class="pt-4">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Volver al Dashboard</span>
        </a>
    </div>
</div>
@endsection
