<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = WorkOrder::with(['activo', 'solicitante', 'tecnico', 'supervisor'])->where('activo', true);

        // Scoping según el rol del usuario autenticado
        if ($user->isTechnician()) {
            $query->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $query->where('solicitante_id', $user->id);
        }

        // Filtro por búsqueda de texto
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

        // Filtro por estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por prioridad
        if ($prioridad = $request->input('prioridad')) {
            $query->where('prioridad', $prioridad);
        }

        // Filtro por tipo de OT
        if ($tipo = $request->input('tipo_ot')) {
            $query->where('tipo_ot', $tipo);
        }

        $ordenes = $query->orderBy('updated_at', 'desc')->paginate(12)->withQueryString();

        // Métricas globales o del usuario
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

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Solicitud de Orden de Trabajo {$ot->codigo_ot} registrada correctamente.");
    }

    public function show($id)
    {
        $user = auth()->user();
        $ot = WorkOrder::with(['activo', 'solicitante', 'tecnico', 'supervisor', 'laborTimes.tecnico'])->findOrFail($id);

        $tecnicos = User::whereHas('role', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('activo', true)->get();

        return view('ordenes.show', compact('ot', 'tecnicos', 'user'));
    }

    public function assign(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'tecnico_id' => 'required|exists:usuarios,id',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
            'duracion_estimada_horas' => 'nullable|numeric|min:0.5',
        ]);

        $ot->update([
            'tecnico_id' => $validated['tecnico_id'],
            'prioridad' => $validated['prioridad'],
            'duracion_estimada_horas' => $validated['duracion_estimada_horas'] ?? $ot->duracion_estimada_horas,
            'supervisor_id' => auth()->id(),
            'estado' => 'Aprobada',
            'fecha_aprobacion' => now(),
        ]);

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

        $updateData = [
            'estado' => $validated['estado'],
            'observaciones_tecnico' => $validated['observaciones_tecnico'] ?? $ot->observaciones_tecnico,
        ];

        if ($validated['estado'] === 'En_Progreso' && !$ot->fecha_inicio) {
            $updateData['fecha_inicio'] = now();
        }

        if (in_array($validated['estado'], ['Completada', 'En_Revision'])) {
            $updateData['fecha_fin_real'] = now();
            if (isset($validated['duracion_real_horas'])) {
                $updateData['duracion_real_horas'] = $validated['duracion_real_horas'];
            }
        }

        if (!empty($validated['diagnostico'])) {
            $diag = $ot->diagnosticos ?? [];
            $diag[] = $validated['diagnostico'];
            $updateData['diagnosticos'] = $diag;
        }

        if (!empty($validated['solucion'])) {
            $sol = $ot->soluciones ?? [];
            $sol[] = $validated['solucion'];
            $updateData['soluciones'] = $sol;
        }

        $ot->update($updateData);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Estado de la OT {$ot->codigo_ot} actualizado a " . str_replace('_', ' ', $validated['estado']));
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
