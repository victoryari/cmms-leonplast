<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\User;
use App\Models\SparePart;
use App\Models\WorkOrderSparePart;
use Illuminate\Support\Facades\Storage;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = WorkOrder::with(['activo', 'solicitante', 'tecnico', 'supervisor'])->where('activo', true);

        if ($user->isTechnician()) {
            $query->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $query->where('solicitante_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('codigo_ot', 'like', "%{$search}%")
                  ->orWhereHas('activo', function ($aQuery) use ($search) {
                      $aQuery->where('nombre', 'like', "%{$search}%")
                             ->orWhere('codigo_activo', 'like', "%{$search}%");
                  });
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($prioridad = $request->input('prioridad')) {
            $query->where('prioridad', $prioridad);
        }

        if ($tipo = $request->input('tipo_ot')) {
            $query->where('tipo_ot', $tipo);
        }

        $ordenes = $query->orderBy('updated_at', 'desc')->paginate(12)->withQueryString();

        $metricsQuery = WorkOrder::where('activo', true);
        if ($user->isTechnician()) {
            $metricsQuery->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $metricsQuery->where('solicitante_id', $user->id);
        }

        $metrics = [
            'pendientes' => (clone $metricsQuery)->where('estado', 'Pendiente')->count(),
            'aprobadas' => (clone $metricsQuery)->where('estado', 'Aprobada')->count(),
            'en_progreso' => (clone $metricsQuery)->where('estado', 'En_Progreso')->count(),
            'completadas' => (clone $metricsQuery)->where('estado', 'Completada')->count(),
        ];

        $tecnicos = User::whereHas('role', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('activo', true)->get();

        return view('ordenes.index', compact('ordenes', 'metrics', 'tecnicos'));
    }

    public function create()
    {
        $activos = Asset::where('activo', true)->orderBy('codigo_activo', 'asc')->get();
        return view('ordenes.create', compact('activos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'activo_id' => 'required|exists:activos,id',
            'tipo_ot' => 'required|in:Correctivo,Preventivo,Predictivo,Urgente,Mejora',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
            'requiere_permiso_especial' => 'nullable|boolean',
            'permisos_especiales' => 'nullable|string|max:255',
        ]);

        $count = WorkOrder::count() + 1;
        $validated['codigo_ot'] = 'OT-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $validated['solicitante_id'] = auth()->id();
        $validated['creado_por'] = auth()->id();
        $validated['estado'] = 'Pendiente';
        $validated['fecha_solicitud'] = now();

        $ot = WorkOrder::create($validated);
        $ot->registrarCambioEstado('Pendiente', 'Solicitud registrada desde Panel Web', auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Solicitud de Orden de Trabajo {$ot->codigo_ot} registrada correctamente.");
    }

    public function show($id)
    {
        $user = auth()->user();
        $ot = WorkOrder::with([
            'activo', 'solicitante', 'tecnico', 'supervisor', 
            'laborTimes.tecnico', 'spareParts.repuesto'
        ])->findOrFail($id);

        $tecnicos = User::whereHas('role', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('activo', true)->get();

        $repuestosAlmacen = SparePart::where('activo', true)->where('stock_actual', '>', 0)->get();

        return view('ordenes.show', compact('ot', 'tecnicos', 'repuestosAlmacen', 'user'));
    }

    public function assign(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'tecnico_id' => 'required|exists:usuarios,id',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
            'duracion_estimada_horas' => 'nullable|numeric|min:0.5',
        ]);

        $tecnico = User::find($validated['tecnico_id']);

        $ot->update([
            'tecnico_id' => $validated['tecnico_id'],
            'prioridad' => $validated['prioridad'],
            'duracion_estimada_horas' => $validated['duracion_estimada_horas'] ?? $ot->duracion_estimada_horas,
            'supervisor_id' => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);

        $ot->registrarCambioEstado('Aprobada', "Asignado a técnico: {$tecnico->nombre_completo}", auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Técnico asignado y OT {$ot->codigo_ot} aprobada para ejecución.");
    }

    public function updateStatus(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:En_Progreso,En_Pausa,En_Revision,Completada,Cancelada',
            'diagnostico' => 'nullable|string',
            'solucion' => 'nullable|string',
            'duracion_real_horas' => 'nullable|numeric|min:0',
            'observaciones_tecnico' => 'nullable|string',
        ]);

        $nuevoEstado = $validated['estado'];

        if ($nuevoEstado === 'En_Progreso' && !$ot->fecha_inicio) {
            $ot->fecha_inicio = now();
        }

        if (in_array($nuevoEstado, ['Completada', 'En_Revision'])) {
            $ot->fecha_fin_real = now();
            if (isset($validated['duracion_real_horas'])) {
                $ot->duracion_real_horas = $validated['duracion_real_horas'];
            }
        }

        if (!empty($validated['diagnostico'])) {
            $diag = $ot->diagnosticos ?? [];
            $diag[] = $validated['diagnostico'];
            $ot->diagnosticos = $diag;
        }

        if (!empty($validated['solucion'])) {
            $sol = $ot->soluciones ?? [];
            $sol[] = $validated['solucion'];
            $ot->soluciones = $sol;
        }

        if (isset($validated['observaciones_tecnico'])) {
            $ot->observaciones_tecnico = $validated['observaciones_tecnico'];
        }

        $ot->save();
        $ot->registrarCambioEstado($nuevoEstado, $validated['observaciones_tecnico'] ?? null, auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Estado de la OT {$ot->codigo_ot} actualizado a " . str_replace('_', ' ', $nuevoEstado));
    }

    public function addSparePart(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad' => 'required|integer|min:1',
            'motivo_uso' => 'nullable|string',
        ]);

        $repuesto = SparePart::findOrFail($validated['repuesto_id']);

        if ($repuesto->stock_actual < $validated['cantidad']) {
            return back()->with('error', "Stock insuficiente de {$repuesto->nombre}. Disponible: {$repuesto->stock_actual}");
        }

        $existingItem = WorkOrderSparePart::where('orden_trabajo_id', $ot->id)
            ->where('repuesto_id', $repuesto->id)
            ->first();

        if ($existingItem) {
            $newCantidad = $existingItem->cantidad + $validated['cantidad'];
            $existingItem->update([
                'cantidad' => $newCantidad,
                'total' => $newCantidad * $repuesto->costo_unitario,
                'motivo_uso' => $validated['motivo_uso'] ?? $existingItem->motivo_uso,
            ]);
        } else {
            WorkOrderSparePart::create([
                'orden_trabajo_id' => $ot->id,
                'repuesto_id' => $repuesto->id,
                'cantidad' => $validated['cantidad'],
                'costo_unitario' => $repuesto->costo_unitario,
                'total' => $validated['cantidad'] * $repuesto->costo_unitario,
                'motivo_uso' => $validated['motivo_uso'],
            ]);
        }

        $repuesto->decrement('stock_actual', $validated['cantidad']);

        $costoTotalRepuestos = $ot->spareParts()->sum('total');
        $ot->update([
            'costo_repuestos' => $costoTotalRepuestos,
            'costo_real' => $costoTotalRepuestos + ($ot->costo_mano_obra ?? 0),
        ]);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Repuesto {$repuesto->nombre} asignado a la OT {$ot->codigo_ot} ({$validated['cantidad']} unidades).");
    }

    public function uploadPhoto(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $request->validate([
            'tipo_foto' => 'required|in:antes,despues',
            'foto' => 'required|image|max:10240',
        ]);

        $tipo = $request->input('tipo_foto');
        $path = $request->file('foto')->store('fotos_ot', 'public');
        $publicUrl = Storage::url($path);

        $fotos = $ot->fotos ?? ['antes' => [], 'despues' => []];
        if (!isset($fotos['antes'])) $fotos['antes'] = [];
        if (!isset($fotos['despues'])) $fotos['despues'] = [];

        $fotos[$tipo][] = $publicUrl;

        $ot->update(['fotos' => $fotos]);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Fotografía ({$tipo}) registrada correctamente en la OT.");
    }

    public function rate(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'calificacion_usuario' => 'required|integer|min:1|max:5',
            'comentario_usuario' => 'nullable|string',
        ]);

        $ot->update([
            'calificacion_usuario' => $validated['calificacion_usuario'],
            'comentario_usuario' => $validated['comentario_usuario'],
        ]);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "¡Gracias por calificar el servicio de mantenimiento de la OT {$ot->codigo_ot}!");
    }
}
