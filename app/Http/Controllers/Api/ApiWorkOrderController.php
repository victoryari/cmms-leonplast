<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\SparePart;
use App\Models\WorkOrderSparePart;
use Illuminate\Support\Facades\Storage;

class ApiWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = WorkOrder::with(['activo', 'solicitante', 'supervisor', 'tecnico', 'spareParts.repuesto'])->where('activo', true);

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
        $ot = WorkOrder::with(['activo', 'solicitante', 'tecnico', 'supervisor', 'laborTimes', 'spareParts.repuesto'])->find($id);

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

    public function addSparePart(Request $request, $id)
    {
        $request->validate([
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad' => 'required|integer|min:1',
            'motivo_uso' => 'nullable|string',
        ]);

        $ot = WorkOrder::find($id);
        if (!$ot) return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);

        $repuesto = SparePart::findOrFail($request->input('repuesto_id'));

        if ($repuesto->stock_actual < $request->input('cantidad')) {
            return response()->json(['success' => false, 'message' => "Stock insuficiente de {$repuesto->nombre}. Disponible: {$repuesto->stock_actual}"], 400);
        }

        $existingItem = WorkOrderSparePart::where('orden_trabajo_id', $ot->id)
            ->where('repuesto_id', $repuesto->id)
            ->first();

        if ($existingItem) {
            $newCantidad = $existingItem->cantidad + $request->input('cantidad');
            $existingItem->update([
                'cantidad' => $newCantidad,
                'total' => $newCantidad * $repuesto->costo_unitario,
                'motivo_uso' => $request->input('motivo_uso', $existingItem->motivo_uso),
            ]);
            $item = $existingItem;
        } else {
            $item = WorkOrderSparePart::create([
                'orden_trabajo_id' => $ot->id,
                'repuesto_id' => $repuesto->id,
                'cantidad' => $request->input('cantidad'),
                'costo_unitario' => $repuesto->costo_unitario,
                'total' => $request->input('cantidad') * $repuesto->costo_unitario,
                'motivo_uso' => $request->input('motivo_uso'),
            ]);
        }

        $repuesto->decrement('stock_actual', $request->input('cantidad'));

        $costoTotalRepuestos = $ot->spareParts()->sum('total');
        $ot->update([
            'costo_repuestos' => $costoTotalRepuestos,
            'costo_real' => $costoTotalRepuestos + ($ot->costo_mano_obra ?? 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Repuesto {$repuesto->nombre} registrado en la OT {$ot->codigo_ot}.",
            'data' => $item
        ]);
    }

    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'tipo_foto' => 'required|in:antes,despues',
            'foto' => 'required|image|max:10240',
        ]);

        $ot = WorkOrder::find($id);
        if (!$ot) return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);

        $tipo = $request->input('tipo_foto');
        $path = $request->file('foto')->store('fotos_ot', 'public');
        $publicUrl = Storage::url($path);

        $fotos = $ot->fotos ?? ['antes' => [], 'despues' => []];
        if (!isset($fotos['antes'])) $fotos['antes'] = [];
        if (!isset($fotos['despues'])) $fotos['despues'] = [];

        $fotos[$tipo][] = $publicUrl;

        $ot->update(['fotos' => $fotos]);

        return response()->json([
            'success' => true,
            'message' => "Fotografía ({$tipo}) adjuntada exitosamente a la OT {$ot->codigo_ot}.",
            'foto_url' => $publicUrl,
            'fotos' => $fotos
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
