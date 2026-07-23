<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\SparePart;
use App\Models\WorkOrderSparePart;
use App\Models\LaborTime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiWorkOrderController extends Controller
{
    /**
     * Listado general paginado de OTs
     */
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

    /**
     * Sincronización Delta / Tiempo Real para la App Móvil Flutter
     */
    public function sync(Request $request)
    {
        $user = $request->user();
        $since = $request->input('since');

        $query = WorkOrder::with([
            'activo', 'solicitante', 'supervisor', 'tecnico', 
            'spareParts.repuesto', 'laborTimes'
        ])->where('activo', true);

        if ($user->isTechnician()) {
            $query->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $query->where('solicitante_id', $user->id);
        }

        if ($since) {
            $query->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime($since)));
        }

        $modifiedWorkOrders = $query->orderBy('updated_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'server_timestamp' => now()->toIso8601String(),
            'count' => $modifiedWorkOrders->count(),
            'data' => $modifiedWorkOrders
        ]);
    }

    /**
     * Detalle completo de una OT
     */
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

    /**
     * Historial de auditoría de cambios de estado de una OT
     */
    public function history($id)
    {
        $ot = WorkOrder::find($id);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'Orden de trabajo no encontrada.'], 404);
        }

        return response()->json([
            'success' => true,
            'codigo_ot' => $ot->codigo_ot,
            'estado_actual' => $ot->estado,
            'historial' => $ot->historial_estados ?? []
        ]);
    }

    /**
     * Crear solicitud de OT desde Flutter
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'activo_id' => 'required|exists:activos,id',
            'tipo_ot' => 'required|in:Correctivo,Preventivo,Predictivo,Urgente,Mejora',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
            'foto_base64' => 'nullable|string',
            'foto' => 'nullable|image|max:10240',
        ]);

        $count = WorkOrder::count() + 1;
        $codigoOt = 'OT-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $fotos = ['antes' => [], 'despues' => []];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_ot', 'public');
            $fotos['antes'][] = Storage::url($path);
        } elseif ($request->filled('foto_base64')) {
            $path = $this->saveBase64Image($request->input('foto_base64'));
            if ($path) $fotos['antes'][] = Storage::url($path);
        }

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
            'fotos' => $fotos,
            'activo' => true,
        ]);

        $ot->registrarCambioEstado('Pendiente', 'Solicitud registrada desde App móvil', $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Solicitud de OT {$ot->codigo_ot} registrada desde la App móvil.",
            'data' => $ot
        ], 201);
    }

    /**
     * Cambio de estado en campo desde la App móvil
     */
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

        $nuevoEstado = $request->input('estado');
        $observaciones = $request->input('observaciones', '');

        if ($nuevoEstado === 'En_Progreso' && !$ot->fecha_inicio) {
            $ot->fecha_inicio = now();
        }

        $ot->observaciones_tecnico = $observaciones;
        $ot->save();

        $ot->registrarCambioEstado($nuevoEstado, $observaciones, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Estado de la OT {$ot->codigo_ot} actualizado a {$nuevoEstado}.",
            'data' => $ot
        ]);
    }

    /**
     * Pausar la ejecución de una OT desde Flutter
     */
    public function pause(Request $request, $id)
    {
        $request->validate([
            'motivo_pausa' => 'required|in:Falta_Repuesto,Fin_Jornada,Operativa_Planta,Permiso_Seguridad,Otro',
            'observaciones' => 'nullable|string',
        ]);

        $ot = WorkOrder::find($id);
        if (!$ot) return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);

        $motivoTexto = str_replace('_', ' ', $request->input('motivo_pausa'));
        $nota = "Pausado por [{$motivoTexto}]: " . ($request->input('observaciones') ?? 'Sin detalle');

        $activeLabor = LaborTime::where('orden_trabajo_id', $ot->id)->where('estado', 'En_Progreso')->first();
        if ($activeLabor) {
            $duracionMinutos = now()->diffInMinutes($activeLabor->fecha_inicio);
            $horas = max(round($duracionMinutos / 60, 2), 0.1);
            $activeLabor->update([
                'fecha_pausa' => now(),
                'fecha_fin' => now(),
                'horas_trabajadas' => $horas,
                'estado' => 'En_Pausa',
                'observaciones' => $nota,
            ]);
        }

        $totalHoras = LaborTime::where('orden_trabajo_id', $ot->id)->sum('horas_trabajadas');
        $ot->duracion_real_horas = $totalHoras;
        $ot->costo_mano_obra = $totalHoras * 25.00;
        $ot->costo_real = ($ot->costo_repuestos ?? 0) + $ot->costo_mano_obra;

        $ot->update(['estado' => 'En_Pausa']);
        $ot->registrarCambioEstado('En_Pausa', $nota, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "OT {$ot->codigo_ot} pausada correctamente por {$motivoTexto}.",
            'duracion_real_horas' => $ot->duracion_real_horas,
            'data' => $ot
        ]);
    }

    /**
     * Reanudar la ejecución de una OT desde Flutter
     */
    public function resume(Request $request, $id)
    {
        $ot = WorkOrder::find($id);
        if (!$ot) return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);

        LaborTime::create([
            'orden_trabajo_id' => $ot->id,
            'tecnico_id' => $request->user()->id,
            'fecha_inicio' => now(),
            'fecha_reanudacion' => now(),
            'estado' => 'En_Progreso',
            'observaciones' => 'Trabajo reanudado desde Flutter App',
        ]);

        $ot->update(['estado' => 'En_Progreso']);
        $ot->registrarCambioEstado('En_Progreso', 'Reanudación de labores técnicas desde Flutter App', $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Trabajo reanudado en la OT {$ot->codigo_ot}.",
            'data' => $ot
        ]);
    }

    /**
     * Subir foto de evidencia desde Flutter
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'tipo_foto' => 'required|in:antes,despues',
            'foto' => 'nullable|image|max:10240',
            'foto_base64' => 'nullable|string',
        ]);

        $ot = WorkOrder::find($id);
        if (!$ot) return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);

        $tipo = $request->input('tipo_foto');
        $publicUrl = null;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_ot', 'public');
            $publicUrl = Storage::url($path);
        } elseif ($request->filled('foto_base64')) {
            $path = $this->saveBase64Image($request->input('foto_base64'));
            if ($path) $publicUrl = Storage::url($path);
        }

        if (!$publicUrl) {
            return response()->json(['success' => false, 'message' => 'Debe proporcionar una imagen en archivo multipart o string Base64.'], 422);
        }

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

    /**
     * Asignar repuesto utilizado en la OT
     */
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

    /**
     * Completar OT y registrar informe final
     */
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
            'fecha_fin_real' => now(),
            'duracion_real_horas' => $request->input('duracion_real_horas'),
            'diagnosticos' => $diag,
            'soluciones' => $sol,
            'observaciones_cierre' => $request->input('observaciones_cierre'),
        ]);

        $ot->registrarCambioEstado('Completada', 'OT completada desde Flutter. Diagnóstico: ' . $request->input('diagnostico'), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Orden de trabajo {$ot->codigo_ot} completada y registrada desde Flutter.",
            'data' => $ot
        ]);
    }

    /**
     * Helper privado para guardar imágenes codificadas en Base64
     */
    private function saveBase64Image(string $base64String): ?string
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
                $type = strtolower($type[1]);
            } else {
                $type = 'png';
            }

            $imageData = base64_decode($base64String);

            if ($imageData === false) {
                return null;
            }

            $fileName = 'fotos_ot/mobile_' . Str::random(20) . '.' . $type;
            Storage::disk('public')->put($fileName, $imageData);

            return $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }
}
