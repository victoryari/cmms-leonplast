<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\User;
use App\Models\SparePart;
use App\Models\WorkOrderSparePart;
use App\Models\LaborTime;
use App\Services\NotificationService;

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
            'en_pausa' => (clone $metricsQuery)->where('estado', 'En_Pausa')->count(),
            'completadas' => (clone $metricsQuery)->where('estado', 'Completada')->count(),
        ];

        return view('ordenes.index', compact('ordenes', 'metrics'));
    }

    public function create(Request $request)
    {
        $activos = Asset::where('activo', true)->orderBy('codigo_activo', 'asc')->get();
        $activoSeleccionadoId = $request->input('activo_id');

        if ($codigo = $request->input('codigo_activo')) {
            $found = Asset::where('codigo_activo', $codigo)->first();
            if ($found) {
                $activoSeleccionadoId = $found->id;
            }
        }

        return view('ordenes.create', compact('activos', 'activoSeleccionadoId'));
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
            ->with('success', "Orden de trabajo {$ot->codigo_ot} registrada correctamente.");
    }

    public function show($id)
    {
        $ot = WorkOrder::with([
            'activo', 'solicitante', 'tecnico', 'supervisor', 
            'laborTimes.usuario', 'spareParts.repuesto'
        ])->where('activo', true)->findOrFail($id);

        $tecnicos = User::whereHas('role', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('activo', true)->get();

        $repuestosAlmacen = SparePart::where('activo', true)->where('stock_actual', '>', 0)->get();

        return view('ordenes.show', compact('ot', 'tecnicos', 'repuestosAlmacen'));
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

        app(NotificationService::class)->notifyTechnicianAssigned($ot);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Técnico asignado y notificación Push enviada al smartphone de {$tecnico->nombre_completo}.");
    }

    public function updateStatus(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'nuevo_estado' => 'required|in:Pendiente,Aprobada,En_Progreso,En_Pausa,En_Revision,Completada,Cancelada',
            'observacion' => 'nullable|string|max:500',
        ]);

        $estadoAnterior = $ot->estado;
        $nuevoEstado = $validated['nuevo_estado'];

        if ($nuevoEstado === 'En_Progreso') {
            if (!$ot->fecha_inicio) {
                $ot->fecha_inicio = now();
            }

            // Abrir tramo activo si no existe uno en progreso
            $existingActive = LaborTime::where('orden_trabajo_id', $ot->id)->where('estado', 'En_Progreso')->first();
            if (!$existingActive) {
                LaborTime::create([
                    'orden_trabajo_id' => $ot->id,
                    'tecnico_id' => auth()->id() ?? $ot->tecnico_id ?? 1,
                    'fecha_inicio' => now(),
                    'estado' => 'En_Progreso',
                    'observaciones' => 'Inicio de atención técnica en campo',
                ]);
            }
        }

        if ($nuevoEstado === 'Completada') {
            $ot->fecha_fin_real = now();
            
            $activeLabor = LaborTime::where('orden_trabajo_id', $ot->id)->where('estado', 'En_Progreso')->first();
            if ($activeLabor) {
                $duracionMinutos = now()->diffInMinutes($activeLabor->fecha_inicio);
                $horas = max(round($duracionMinutos / 60, 2), 0.1);
                $activeLabor->update([
                    'fecha_fin' => now(),
                    'horas_trabajadas' => $horas,
                    'estado' => 'Completado',
                ]);
            }

            $totalHoras = LaborTime::where('orden_trabajo_id', $ot->id)->sum('horas_trabajadas');
            $ot->duracion_real_horas = max($totalHoras, 0.5);
            $ot->costo_mano_obra = $ot->duracion_real_horas * 25.00;
            $ot->costo_real = ($ot->costo_repuestos ?? 0) + $ot->costo_mano_obra;
        }

        $ot->save();
        $ot->registrarCambioEstado($nuevoEstado, $validated['observacion'] ?? "Cambio manual a {$nuevoEstado}", auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Estado actualizado de {$estadoAnterior} a {$nuevoEstado}.");
    }

    public function pause(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'motivo_pausa' => 'required|in:Falta_Repuesto,Fin_Jornada,Operativa_Planta,Permiso_Seguridad,Otro',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $motivoTexto = str_replace('_', ' ', $validated['motivo_pausa']);
        $nota = "Pausado por [{$motivoTexto}]: " . ($validated['observaciones'] ?? 'Sin detalle adicional');

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
        } else {
            LaborTime::create([
                'orden_trabajo_id' => $ot->id,
                'tecnico_id' => auth()->id() ?? $ot->tecnico_id ?? 1,
                'fecha_inicio' => $ot->fecha_inicio ?? now(),
                'fecha_pausa' => now(),
                'fecha_fin' => now(),
                'horas_trabajadas' => 0.5,
                'estado' => 'En_Pausa',
                'observaciones' => $nota,
            ]);
        }

        $totalHoras = LaborTime::where('orden_trabajo_id', $ot->id)->sum('horas_trabajadas');
        $ot->duracion_real_horas = $totalHoras;
        $ot->costo_mano_obra = $totalHoras * 25.00;
        $ot->costo_real = ($ot->costo_repuestos ?? 0) + $ot->costo_mano_obra;

        $ot->update(['estado' => 'En_Pausa']);
        $ot->registrarCambioEstado('En_Pausa', $nota, auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "OT {$ot->codigo_ot} pausada correctamente por {$motivoTexto}. Horas registradas hasta el momento: {$ot->duracion_real_horas} hrs.");
    }

    public function resume(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        LaborTime::create([
            'orden_trabajo_id' => $ot->id,
            'tecnico_id' => auth()->id() ?? $ot->tecnico_id ?? 1,
            'fecha_inicio' => now(),
            'fecha_reanudacion' => now(),
            'estado' => 'En_Progreso',
            'observaciones' => 'Trabajo reanudado en campo',
        ]);

        $ot->update(['estado' => 'En_Progreso']);
        $ot->registrarCambioEstado('En_Progreso', 'Reanudación de labores técnicas en campo', auth()->id());

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Trabajo reanudado en la OT {$ot->codigo_ot}. Conteo de tiempo reactivado.");
    }

    public function addSparePart(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $repuesto = SparePart::findOrFail($validated['repuesto_id']);

        if ($repuesto->stock_actual < $validated['cantidad']) {
            return back()->with('error', "Stock insuficiente para {$repuesto->nombre}. Stock disponible: {$repuesto->stock_actual}.");
        }

        $costoUnitario = $repuesto->costo_unitario;
        $costoTotal = $costoUnitario * $validated['cantidad'];

        WorkOrderSparePart::create([
            'orden_trabajo_id' => $ot->id,
            'repuesto_id' => $repuesto->id,
            'cantidad_usada' => $validated['cantidad'],
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'observaciones' => $validated['observaciones'],
        ]);

        $repuesto->registrarMovimiento(
            'Salida',
            $validated['cantidad'],
            auth()->id(),
            "Consumo en OT {$ot->codigo_ot}",
            $ot->id
        );

        $ot->costo_repuestos = WorkOrderSparePart::where('orden_trabajo_id', $ot->id)->sum('costo_total');
        $ot->costo_real = $ot->costo_repuestos + ($ot->costo_mano_obra ?? 0);
        $ot->save();

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', "Repuesto {$repuesto->nombre} registrado en la OT y stock actualizado en almacén.");
    }

    public function uploadPhoto(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $request->validate([
            'foto' => 'required|image|max:10240',
            'tipo_foto' => 'required|in:Antes,Durante,Despues',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $path = $request->file('foto')->store('fotos_ots', 'public');
        $fotosArray = $ot->fotos ?? [];
        $fotosArray[] = [
            'url_foto' => "/storage/" . $path,
            'tipo' => $request->input('tipo_foto'),
            'subido_por' => auth()->user()->nombre_completo,
            'descripcion' => $request->input('descripcion') ?? '',
            'fecha' => now()->toIso8601String(),
        ];

        $ot->update(['fotos' => $fotosArray]);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', 'Foto adjuntada correctamente a la Orden de Trabajo.');
    }

    public function rate(Request $request, $id)
    {
        $ot = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'calificacion_usuario' => 'required|integer|min:1|max:5',
            'comentario_usuario' => 'nullable|string|max:500',
        ]);

        $ot->update($validated);

        return redirect()->route('ordenes.show', $ot->id)
            ->with('success', '¡Gracias! Tu calificación de la atención técnica ha sido registrada.');
    }
}
