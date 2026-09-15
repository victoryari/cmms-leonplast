<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Calcular métricas KPI globales de planta
     */
    public function getGlobalKpis(?string $startDate = null, ?string $endDate = null): array
    {
        $query = WorkOrder::where('activo', true);

        if ($startDate) {
            $query->where('fecha_solicitud', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('fecha_solicitud', '<=', $endDate);
        }

        // OTs correctivas u urgentes (averías de planta)
        $correctivas = (clone $query)->whereIn('tipo_ot', ['Correctivo', 'Urgente']);
        $numCorrectivas = (clone $correctivas)->count();
        $horasReparacionTotal = (clone $correctivas)->whereNotNull('duracion_real_horas')->sum('duracion_real_horas') ?? 0;

        if ($numCorrectivas > 0) {
            if ($horasReparacionTotal == 0) {
                $horasReparacionTotal = $numCorrectivas * 3.5;
            }
            $mttrGlobal = round($horasReparacionTotal / $numCorrectivas, 2);

            $numActivos = Asset::where('activo', true)->count() ?: 1;
            $horasOperativasPlanta = 720 * $numActivos;
            $horasInactividad = $horasReparacionTotal;

            $mtbfGlobal = round(max(1, $horasOperativasPlanta - $horasInactividad) / $numCorrectivas, 2);
            $denominador = ($mtbfGlobal + $mttrGlobal) ?: 1;
            $disponibilidadGlobal = round(($mtbfGlobal / $denominador) * 100, 2);
            if ($disponibilidadGlobal > 100) $disponibilidadGlobal = 100.0;
        } else {
            // Sin fallas registradas
            $mttrGlobal = 0.0;
            $mtbfGlobal = 0.0;
            $disponibilidadGlobal = 100.0;
        }

        // Costos acumulados
        $costoTotal = (clone $query)->sum('costo_real') ?? 0;
        if ($costoTotal == 0) {
            $costoTotal = (clone $query)->sum(DB::raw('COALESCE(costo_mano_obra, 0) + COALESCE(costo_repuestos, 0)')) ?? 0;
        }

        $costoPreventivo = (clone $query)->where('tipo_ot', 'Preventivo')->sum('costo_real') ?? 0;
        $costoCorrectivo = (clone $query)->whereIn('tipo_ot', ['Correctivo', 'Urgente'])->sum('costo_real') ?? 0;
        $costoOtros = max(0, $costoTotal - ($costoPreventivo + $costoCorrectivo));

        return [
            'mtbf_global' => $mtbfGlobal,
            'mttr_global' => $mttrGlobal,
            'disponibilidad_global' => $disponibilidadGlobal,
            'costo_total' => round($costoTotal, 2),
            'costo_preventivo' => round($costoPreventivo, 2),
            'costo_correctivo' => round($costoCorrectivo, 2),
            'costo_otros' => round($costoOtros, 2),
            'total_ots' => (clone $query)->count(),
            'ots_correctivas' => $numCorrectivas,
            'ots_preventivas' => (clone $query)->where('tipo_ot', 'Preventivo')->count(),
            'ots_completadas' => (clone $query)->where('estado', 'Completada')->count(),
        ];
    }

    /**
     * Generar estructura para el Diagrama de Pareto 80/20 de Fallas por Activo
     */
    public function getParetoBreakdown(): array
    {
        $fallasPorActivo = WorkOrder::select('activo_id', DB::raw('COUNT(*) as total_fallas'))
            ->where('activo', true)
            ->whereIn('tipo_ot', ['Correctivo', 'Urgente'])
            ->groupBy('activo_id')
            ->orderBy('total_fallas', 'desc')
            ->get();

        $labels = [];
        $counts = [];
        $cumulativePercentages = [];

        if ($fallasPorActivo->isNotEmpty()) {
            $totalFallas = $fallasPorActivo->sum('total_fallas') ?: 1;
            $runningSum = 0;

            foreach ($fallasPorActivo as $item) {
                $activo = Asset::find($item->activo_id);
                $nombreActivo = $activo ? "[{$activo->codigo_activo}] {$activo->nombre}" : "Activo #{$item->activo_id}";
                
                $labels[] = StrLimit($nombreActivo, 25);
                $counts[] = (int) $item->total_fallas;

                $runningSum += $item->total_fallas;
                $cumulativePercentages[] = round(($runningSum / $totalFallas) * 100, 1);
            }
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'cumulative_percentages' => $cumulativePercentages,
        ];
    }

    /**
     * Recalcular y actualizar métricas de cada activo en la base de datos
     */
    public function refreshAssetMetrics(): void
    {
        $activos = Asset::where('activo', true)->get();

        foreach ($activos as $act) {
            $otsCorrectivas = WorkOrder::where('activo_id', $act->id)
                ->whereIn('tipo_ot', ['Correctivo', 'Urgente'])
                ->get();

            $numFallas = $otsCorrectivas->count();

            if ($numFallas > 0) {
                $horasReparacion = $otsCorrectivas->sum('duracion_real_horas') ?: ($numFallas * 3.0);
                $mttr = round($horasReparacion / $numFallas, 2);
                $mtbf = round(max(1, 720 - $horasReparacion) / $numFallas, 2);
                $den = ($mtbf + $mttr) ?: 1;
                $disp = round(($mtbf / $den) * 100, 2);
                if ($disp > 100) $disp = 100.0;
            } else {
                $mttr = 0.0;
                $mtbf = 0.0;
                $disp = 100.0;
            }

            $act->update([
                'mtbf_horas' => $mtbf,
                'mttr_horas' => $mttr,
                'disponibilidad_porcentaje' => $disp,
            ]);
        }
    }
}

function StrLimit($string, $limit = 25) {
    return mb_strlen($string) > $limit ? mb_substr($string, 0, $limit) . '...' : $string;
}

