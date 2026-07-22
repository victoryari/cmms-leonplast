<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        // Recalcular métricas de activos en base de datos
        $this->analyticsService->refreshAssetMetrics();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $kpis = $this->analyticsService->getGlobalKpis($startDate, $endDate);
        $paretoData = $this->analyticsService->getParetoBreakdown();

        // Lista de activos con sus costos acumulados e indicadores
        $activosReporte = Asset::where('activo', true)
            ->withCount(['ordenesTrabajo as total_ots'])
            ->withCount(['ordenesTrabajo as correctivas_count' => function ($q) {
                $q->whereIn('tipo_ot', ['Correctivo', 'Urgente']);
            }])
            ->get()
            ->map(function ($act) {
                $costoManoObra = WorkOrder::where('activo_id', $act->id)->sum('costo_mano_obra') ?? 0;
                $costoRepuestos = WorkOrder::where('activo_id', $act->id)->sum('costo_repuestos') ?? 0;
                $costoTotal = WorkOrder::where('activo_id', $act->id)->sum('costo_real') ?? 0;

                if ($costoTotal == 0) {
                    $costoTotal = $costoManoObra + $costoRepuestos;
                }

                $act->costo_mano_obra = $costoManoObra;
                $act->costo_repuestos = $costoRepuestos;
                $act->costo_total_mantenimiento = $costoTotal;

                return $act;
            })
            ->sortByDesc('costo_total_mantenimiento');

        return view('reportes.index', compact('kpis', 'paretoData', 'activosReporte'));
    }

    public function exportCsv()
    {
        $this->analyticsService->refreshAssetMetrics();
        $activos = Asset::where('activo', true)->get();

        $filename = "reporte_kpi_planta_" . date('Y-m-d_H-i') . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($activos) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Código Activo', 
                'Nombre del Equipo', 
                'Categoría', 
                'Ubicación', 
                'MTBF (Horas)', 
                'MTTR (Horas)', 
                'Disponibilidad (%)', 
                'Estado Operativo', 
                'Costo Mantenimiento ($)'
            ]);

            foreach ($activos as $act) {
                $costoTotal = WorkOrder::where('activo_id', $act->id)->sum('costo_real') ?? 0;
                if ($costoTotal == 0) {
                    $costoTotal = (WorkOrder::where('activo_id', $act->id)->sum('costo_mano_obra') ?? 0) + 
                                  (WorkOrder::where('activo_id', $act->id)->sum('costo_repuestos') ?? 0);
                }

                fputcsv($file, [
                    $act->codigo_activo,
                    $act->nombre,
                    $act->categoria,
                    $act->ubicacion,
                    $act->mtbf_horas ?? 720,
                    $act->mttr_horas ?? 0,
                    ($act->disponibilidad_porcentaje ?? 98.5) . '%',
                    $act->estado_operativo,
                    number_format($costoTotal, 2, '.', '')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
