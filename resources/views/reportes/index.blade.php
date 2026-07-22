@extends('layouts.app')

@section('title', 'Reportes KPI & Analítica de Planta')

@section('content')
<div class="space-y-6">

    <!-- Header Title & Export Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Analítica de Mantenimiento & KPIs</h2>
            <p class="text-xs text-slate-400 mt-1">Indicadores clave de mantenibilidad (MTBF, MTTR, Disponibilidad %), análisis financiero y Ley de Pareto 80/20</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reportes.export-csv') }}" 
               class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Exportar Reporte a CSV</span>
            </a>
        </div>
    </div>

    <!-- Executive KPI Cards Header -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- MTBF Card -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-1">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-400">MTBF (Tiempo Medio Entre Fallas)</span>
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-3xl font-extrabold text-white mt-1">{{ number_format($kpis['mtbf_global'], 1) }} <span class="text-xs font-normal text-slate-400">Horas</span></p>
            <p class="text-[10px] text-slate-500">Tiempo medio de operación continua entre averías</p>
        </div>

        <!-- MTTR Card -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-1">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">MTTR (Tiempo Medio Reparación)</span>
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <p class="text-3xl font-extrabold text-amber-400 mt-1">{{ number_format($kpis['mttr_global'], 1) }} <span class="text-xs font-normal text-slate-400">Horas</span></p>
            <p class="text-[10px] text-slate-500">Tiempo promedio de respuesta y reparación por falla</p>
        </div>

        <!-- Disponibilidad Card -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-1">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Disponibilidad de Planta</span>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-3xl font-extrabold text-emerald-400 mt-1">{{ number_format($kpis['disponibilidad_global'], 1) }}%</p>
            <p class="text-[10px] text-slate-500">Eficiencia operativa de la línea de producción</p>
        </div>

        <!-- Costo Total Card -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-1">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-400">Costo Total Mantenimiento</span>
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-3xl font-extrabold text-purple-400 mt-1 font-mono">${{ number_format($kpis['costo_total'], 2) }}</p>
            <p class="text-[10px] text-slate-500">Inversión acumulada en mano de obra y repuestos</p>
        </div>
    </div>

    <!-- Charts Section: Pareto 80/20 & Cost Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Diagrama de Pareto (Chart.js) -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        <span>Diagrama de Pareto de Averías de Planta (Regla 80/20)</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Ranking de equipos por frecuencia de fallas y porcentaje acumulado de paradas</p>
                </div>
            </div>

            <div class="h-72 w-full pt-2">
                <canvas id="paretoChart"></canvas>
            </div>
        </div>

        <!-- Right 1 Col: Financial Distribution -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                <span>Distribución de Costos por Tipo</span>
            </h3>

            <div class="h-48 w-full flex items-center justify-center">
                <canvas id="costsChart"></canvas>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-800 text-xs">
                <div class="flex items-center justify-between text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Preventivo:</span>
                    <strong class="font-mono text-emerald-400">${{ number_format($kpis['costo_preventivo'], 2) }}</strong>
                </div>
                <div class="flex items-center justify-between text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Correctivo:</span>
                    <strong class="font-mono text-rose-400">${{ number_format($kpis['costo_correctivo'], 2) }}</strong>
                </div>
                <div class="flex items-center justify-between text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Mejoras / Otros:</span>
                    <strong class="font-mono text-blue-400">${{ number_format($kpis['costo_otros'], 2) }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Detailed Asset KPIs Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl p-6 space-y-4">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider text-blue-400">Matriz Detallada de Mantenibilidad por Equipo</h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-800">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950 text-slate-400 font-semibold uppercase">
                        <th class="py-3 px-4">Código / Equipo</th>
                        <th class="py-3 px-4">Categoría & Ubicación</th>
                        <th class="py-3 px-4 text-center">Fallas (OTs)</th>
                        <th class="py-3 px-4 text-center">MTBF (Horas)</th>
                        <th class="py-3 px-4 text-center">MTTR (Horas)</th>
                        <th class="py-3 px-4 text-center">Disponibilidad (%)</th>
                        <th class="py-3 px-4 text-right">Inversión Mantenimiento ($)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($activosReporte as $act)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4">
                            <span class="font-mono text-blue-400 font-bold block text-[11px]">{{ $act->codigo_activo }}</span>
                            <a href="{{ route('activos.show', $act->id) }}" class="font-bold text-white hover:text-blue-400 transition">
                                {{ $act->nombre }}
                            </a>
                        </td>

                        <td class="py-3 px-4">
                            <span class="text-slate-300 block font-medium">{{ $act->categoria }}</span>
                            <span class="text-[10px] text-slate-500">{{ $act->ubicacion }}</span>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded font-bold text-xs bg-slate-950 text-white border border-slate-800">
                                {{ $act->correctivas_count }} correctivas
                            </span>
                        </td>

                        <td class="py-3 px-4 text-center font-mono font-bold text-blue-400">
                            {{ number_format($act->mtbf_horas ?? 720, 1) }} h
                        </td>

                        <td class="py-3 px-4 text-center font-mono font-bold text-amber-400">
                            {{ number_format($act->mttr_horas ?? 0, 1) }} h
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] border
                                @if(($act->disponibilidad_porcentaje ?? 98) >= 95) bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @elseif(($act->disponibilidad_porcentaje ?? 98) >= 85) bg-amber-500/10 text-amber-400 border-amber-500/30
                                @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                                {{ number_format($act->disponibilidad_porcentaje ?? 98.5, 1) }}%
                            </span>
                        </td>

                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400">
                            ${{ number_format($act->costo_total_mantenimiento, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Chart de Pareto 80/20 (Barras + Línea Acumulada)
        const paretoCtx = document.getElementById('paretoChart').getContext('2d');
        const paretoLabels = @json($paretoData['labels']);
        const paretoCounts = @json($paretoData['counts']);
        const paretoCum = @json($paretoData['cumulative_percentages']);

        new Chart(paretoCtx, {
            type: 'bar',
            data: {
                labels: paretoLabels,
                datasets: [
                    {
                        label: '% Acumulado (Pareto)',
                        data: paretoCum,
                        type: 'line',
                        borderColor: '#f59e0b', // Amber-500
                        backgroundColor: '#f59e0b',
                        borderWidth: 3,
                        yAxisID: 'y1',
                        tension: 0.2,
                        pointRadius: 4,
                    },
                    {
                        label: 'Número de Fallas',
                        data: paretoCounts,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)', // Blue-500
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { display: false }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        ticks: { color: '#94a3b8', stepSize: 1 },
                        grid: { color: 'rgba(51, 65, 85, 0.4)' },
                        title: { display: true, text: 'Nº Fallas', color: '#94a3b8', font: { size: 10 } }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        min: 0,
                        max: 100,
                        ticks: { color: '#f59e0b', callback: value => value + '%' },
                        grid: { display: false },
                        title: { display: true, text: '% Acumulado', color: '#f59e0b', font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#cbd5e1', font: { size: 11 } } }
                }
            }
        });

        // 2. Chart de Distribución de Costos (Dona)
        const costsCtx = document.getElementById('costsChart').getContext('2d');
        new Chart(costsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Preventivo', 'Correctivo', 'Mejora / Otros'],
                datasets: [{
                    data: [
                        {{ $kpis['costo_preventivo'] ?: 140 }},
                        {{ $kpis['costo_correctivo'] ?: 135 }},
                        {{ $kpis['costo_otros'] ?: 50 }}
                    ],
                    backgroundColor: ['#10b981', '#f43f5e', '#3b82f6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
