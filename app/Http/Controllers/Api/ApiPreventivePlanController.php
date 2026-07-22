<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreventivePlan;
use App\Models\WorkOrder;
use App\Models\PlanExecutionHistory;

class ApiPreventivePlanController extends Controller
{
    public function index(Request $request)
    {
        $planes = PreventivePlan::with(['activo', 'tecnicoAsignado'])
            ->where('estado', 'Activo')
            ->orderBy('proxima_ejecucion', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $planes
        ]);
    }

    public function show($id)
    {
        $plan = PreventivePlan::with(['activo', 'tecnicoAsignado', 'historialEjecuciones.ordenTrabajo'])->find($id);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan preventivo no encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    public function executeNow(Request $request, $id)
    {
        $plan = PreventivePlan::find($id);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan preventivo no encontrado.'], 404);
        }

        $otCount = WorkOrder::count() + 1;
        $codigoOt = 'OT-' . date('Y') . '-' . str_pad($otCount, 3, '0', STR_PAD_LEFT);

        $ot = WorkOrder::create([
            'codigo_ot' => $codigoOt,
            'titulo' => $plan->titulo_ot_generada ?? "Mantenimiento Preventivo: {$plan->nombre_plan}",
            'descripcion' => $plan->descripcion_ot_generada ?? $plan->descripcion,
            'activo_id' => $plan->activo_id,
            'solicitante_id' => $request->user()->id,
            'tecnico_id' => $plan->tecnico_asignado_id,
            'supervisor_id' => $request->user()->id,
            'tipo_ot' => 'Preventivo',
            'prioridad' => $plan->prioridad_defecto ?? 'Media',
            'estado' => 'Aprobada',
            'fecha_solicitud' => now(),
            'fecha_aprobacion' => now(),
            'duracion_estimada_horas' => 3.00,
            'creado_por' => $request->user()->id,
            'activo' => true,
        ]);

        $ot->registrarCambioEstado('Aprobada', "Generada manualmente desde Flutter App por " . $request->user()->nombre_completo, $request->user()->id);

        PlanExecutionHistory::create([
            'plan_preventivo_id' => $plan->id,
            'orden_trabajo_generada_id' => $ot->id,
            'fecha_ejecucion' => now(),
            'tipo_ejecucion' => 'Manual',
            'observaciones' => "Disparado desde la App Móvil por " . $request->user()->nombre_completo,
        ]);

        $proxima = $plan->calcularProximaFecha();
        $plan->update([
            'ultima_ejecucion' => now(),
            'proxima_ejecucion' => $proxima,
        ]);

        return response()->json([
            'success' => true,
            'message' => "OT Preventiva {$ot->codigo_ot} generada exitosamente desde Flutter.",
            'orden_trabajo' => $ot
        ]);
    }
}
