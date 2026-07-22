<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Models\Asset;
use App\Models\WorkOrder;

class ApiReportController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function kpis(Request $request)
    {
        $this->analyticsService->refreshAssetMetrics();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $kpis = $this->analyticsService->getGlobalKpis($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $kpis
        ]);
    }

    public function pareto()
    {
        $pareto = $this->analyticsService->getParetoBreakdown();

        return response()->json([
            'success' => true,
            'data' => $pareto
        ]);
    }

    public function assets()
    {
        $this->analyticsService->refreshAssetMetrics();

        $activos = Asset::where('activo', true)
            ->select([
                'id', 'codigo_activo', 'nombre', 'categoria', 
                'ubicacion', 'estado_operativo', 'mtbf_horas', 
                'mttr_horas', 'disponibilidad_porcentaje'
            ])
            ->get()
            ->map(function ($act) {
                $costoTotal = WorkOrder::where('activo_id', $act->id)->sum('costo_real') ?? 0;
                if ($costoTotal == 0) {
                    $costoTotal = (WorkOrder::where('activo_id', $act->id)->sum('costo_mano_obra') ?? 0) + 
                                  (WorkOrder::where('activo_id', $act->id)->sum('costo_repuestos') ?? 0);
                }
                $act->costo_total_mantenimiento = round($costoTotal, 2);
                return $act;
            });

        return response()->json([
            'success' => true,
            'data' => $activos
        ]);
    }
}
