<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;

class ApiWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = WorkOrder::with(['activo', 'solicitante', 'supervisor', 'tecnico'])->where('activo', true);

        if ($user->isTechnician()) {
            $query->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $query->where('solicitante_id', $user->id);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($prioridad = $request->input('prioridad')) {
            $query->where('prioridad', $prioridad);
        }

        $workOrders = $query->orderBy('updated_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    public function show($id)
    {
        $ot = WorkOrder::with(['activo', 'solicitante', 'tecnico', 'supervisor', 'laborTimes'])->find($id);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'Orden de trabajo no encontrada.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ot
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'activo_id' => 'required|exists:activos,id',
            'tipo_ot' => 'required|in:Correctivo,Preventivo,Predictivo,Urgente,Mejora',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
        ]);

        $count = WorkOrder::count() + 1;
        $codigoOt = 'OT-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $ot = WorkOrder::create([
            'codigo_ot' => $codigoOt,
            'titulo' => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'activo_id' => $request->input('activo_id'),
            'tipo_ot' => $request->input('tipo_ot'),
            'prioridad' => $request->input('prioridad'),
            'solicitante_id' => $request->user()->id,
            'creado_por' => $request->user()->id,
            'estado' => 'Pendiente',
            'fecha_solicitud' => now(),
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Solicitud de OT {$ot->codigo_ot} registrada desde la App móvil.",
            'data' => $ot
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:En_Progreso,En_Pausa,En_Revision,Completada,Cancelada',
            'observaciones' => 'nullable|string',
        ]);

        $ot = WorkOrder::find($id);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'Orden de trabajo no encontrada.'], 404);
        }

        $updateData = [
            'estado' => $request->input('estado'),
            'observaciones_tecnico' => $request->input('observaciones', $ot->observaciones_tecnico),
        ];

        if ($request->input('estado') === 'En_Progreso' && !$ot->fecha_inicio) {
            $updateData['fecha_inicio'] = now();
        }

        $ot->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Estado de la OT {$ot->codigo_ot} actualizado a {$ot->estado}.",
            'data' => $ot
        ]);
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'diagnostico' => 'required|string',
            'solucion' => 'required|string',
            'duracion_real_horas' => 'required|numeric|min:0.1',
            'observaciones_cierre' => 'nullable|string',
        ]);

        $ot = WorkOrder::find($id);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'Orden de trabajo no encontrada.'], 404);
        }

        $diag = $ot->diagnosticos ?? [];
        $diag[] = $request->input('diagnostico');

        $sol = $ot->soluciones ?? [];
        $sol[] = $request->input('solucion');

        $ot->update([
            'estado' => 'Completada',
            'fecha_fin_real' => now(),
            'duracion_real_horas' => $request->input('duracion_real_horas'),
            'diagnosticos' => $diag,
            'soluciones' => $sol,
            'observaciones_cierre' => $request->input('observaciones_cierre'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Orden de trabajo {$ot->codigo_ot} completada y registrada desde Flutter.",
            'data' => $ot
        ]);
    }
}
