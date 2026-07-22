<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreventivePlan;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\PlanExecutionHistory;

class PreventivePlanController extends Controller
{
    public function index(Request $request)
    {
        $query = PreventivePlan::with(['activo', 'tecnicoAsignado']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_plan', 'like', "%{$search}%")
                  ->orWhereHas('activo', function ($aQuery) use ($search) {
                      $aQuery->where('nombre', 'like', "%{$search}%")
                             ->orWhere('codigo_activo', 'like', "%{$search}%");
                  });
            });
        }

        if ($tipo = $request->input('tipo_plan')) {
            $query->where('tipo_plan', $tipo);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $planes = $query->orderBy('proxima_ejecucion', 'asc')->paginate(12)->withQueryString();

        $metrics = [
            'total' => PreventivePlan::count(),
            'activos' => PreventivePlan::where('estado', 'Activo')->count(),
            'pausados' => PreventivePlan::where('estado', 'Pausado')->count(),
            'vencidos' => PreventivePlan::where('estado', 'Activo')->where('proxima_ejecucion', '<=', now())->count(),
        ];

        return view('planes.index', compact('planes', 'metrics'));
    }

    public function create()
    {
        $activos = Asset::where('activo', true)->orderBy('codigo_activo', 'asc')->get();
        $tecnicos = User::whereHas('role', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('activo', true)->get();

        return view('planes.create', compact('activos', 'tecnicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_plan' => 'required|string|max:255',
            'activo_id' => 'required|exists:activos,id',
            'tipo_plan' => 'required|in:Por_Calendario,Por_Medidor',
            'frecuencia_dias' => 'nullable|required_if:tipo_plan,Por_Calendario|integer|min:1',
            'unidad_medicion' => 'nullable|required_if:tipo_plan,Por_Medidor|string|max:50',
            'umbral_medidor' => 'nullable|required_if:tipo_plan,Por_Medidor|numeric|min:1',
            'titulo_ot_generada' => 'required|string|max:255',
            'descripcion_ot_generada' => 'required|string',
            'instrucciones_especificas' => 'nullable|string',
            'tecnico_asignado_id' => 'nullable|exists:usuarios,id',
            'prioridad_defecto' => 'required|in:Baja,Media,Alta,Crítica',
        ]);

        $validated['creado_por'] = auth()->id();
        $validated['estado'] = 'Activo';
        $validated['fecha_inicio'] = now();

        $plan = PreventivePlan::create($validated);
        $plan->update(['proxima_ejecucion' => $plan->calcularProximaFecha()]);

        return redirect()->route('planes.show', $plan->id)
            ->with('success', "Plan preventivo '{$plan->nombre_plan}' configurado exitosamente.");
    }

    public function show($id)
    {
        $plan = PreventivePlan::with(['activo', 'tecnicoAsignado', 'creador', 'historialEjecuciones.ordenTrabajo'])->findOrFail($id);
        return view('planes.show', compact('plan'));
    }

    public function executeNow($id)
    {
        $plan = PreventivePlan::findOrFail($id);

        $otCount = WorkOrder::count() + 1;
        $codigoOt = 'OT-' . date('Y') . '-' . str_pad($otCount, 3, '0', STR_PAD_LEFT);

        $ot = WorkOrder::create([
            'codigo_ot' => $codigoOt,
            'titulo' => $plan->titulo_ot_generada ?? "Mantenimiento Preventivo: {$plan->nombre_plan}",
            'descripcion' => $plan->descripcion_ot_generada ?? $plan->descripcion,
            'activo_id' => $plan->activo_id,
            'solicitante_id' => auth()->id(),
            'tecnico_id' => $plan->tecnico_asignado_id,
            'supervisor_id' => auth()->id(),
            'tipo_ot' => 'Preventivo',
            'prioridad' => $plan->prioridad_defecto ?? 'Media',
            'estado' => 'Aprobada',
            'fecha_solicitud' => now(),
            'fecha_aprobacion' => now(),
            'duracion_estimada_horas' => 3.00,
            'creado_por' => auth()->id(),
            'activo' => true,
        ]);

        $ot->registrarCambioEstado('Aprobada', "Generada manualmente por usuario en Plan Preventivo #{$plan->id}", auth()->id());

        PlanExecutionHistory::create([
            'plan_preventivo_id' => $plan->id,
            'orden_trabajo_generada_id' => $ot->id,
            'fecha_ejecucion' => now(),
            'tipo_ejecucion' => 'Manual',
            'observaciones' => "Disparado manualmente por " . auth()->user()->nombre_completo,
        ]);

        $proxima = $plan->calcularProximaFecha();
        $plan->update([
            'ultima_ejecucion' => now(),
            'proxima_ejecucion' => $proxima,
        ]);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "¡OT Preventiva {$ot->codigo_ot} generada inmediatamente para el plan '{$plan->nombre_plan}'!");
    }

    public function toggleStatus($id)
    {
        $plan = PreventivePlan::findOrFail($id);
        $nuevoEstado = $plan->estado === 'Activo' ? 'Pausado' : 'Activo';
        
        $plan->update(['estado' => $nuevoEstado]);

        return redirect()->route('planes.show', $plan->id)
            ->with('success', "Estado del plan actualizado a {$nuevoEstado}.");
    }
}
