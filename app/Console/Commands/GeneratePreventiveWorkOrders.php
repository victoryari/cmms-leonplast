<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PreventivePlan;
use App\Models\WorkOrder;
use App\Models\PlanExecutionHistory;

class GeneratePreventiveWorkOrders extends Command
{
    protected $signature = 'cmms:generar-preventivos';

    protected $description = 'Evalúa planes preventivos activos y genera automáticamente las OTs preventivas correspondientes.';

    public function handle(): int
    {
        $this->info('Iniciando evaluación de planes preventivos...');

        $planes = PreventivePlan::where('estado', 'Activo')
            ->where(function ($q) {
                $q->whereNull('proxima_ejecucion')
                  ->orWhere('proxima_ejecucion', '<=', now());
            })
            ->get();

        if ($planes->isEmpty()) {
            $this->info('No hay planes preventivos pendientes de ejecución hoy.');
            return 0;
        }

        $countGeneradas = 0;

        foreach ($planes as $plan) {
            $otCount = WorkOrder::count() + 1;
            $codigoOt = 'OT-' . date('Y') . '-' . str_pad($otCount, 3, '0', STR_PAD_LEFT);

            $ot = WorkOrder::create([
                'codigo_ot' => $codigoOt,
                'titulo' => $plan->titulo_ot_generada ?? "Mantenimiento Preventivo: {$plan->nombre_plan}",
                'descripcion' => $plan->descripcion_ot_generada ?? $plan->descripcion,
                'activo_id' => $plan->activo_id,
                'solicitante_id' => $plan->creado_por ?? 1,
                'tecnico_id' => $plan->tecnico_asignado_id,
                'supervisor_id' => $plan->creado_por ?? 1,
                'tipo_ot' => 'Preventivo',
                'prioridad' => $plan->prioridad_defecto ?? 'Media',
                'estado' => 'Aprobada',
                'fecha_solicitud' => now(),
                'fecha_aprobacion' => now(),
                'duracion_estimada_horas' => 3.00,
                'creado_por' => $plan->creado_por ?? 1,
                'activo' => true,
            ]);

            $ot->registrarCambioEstado('Aprobada', "Generada automáticamente por Plan Preventivo #{$plan->id} ({$plan->nombre_plan})");

            PlanExecutionHistory::create([
                'plan_preventivo_id' => $plan->id,
                'orden_trabajo_generada_id' => $ot->id,
                'fecha_ejecucion' => now(),
                'tipo_ejecucion' => 'Automatica',
                'observaciones' => "OT {$ot->codigo_ot} generada exitosamente por rutina automática.",
            ]);

            $proxima = $plan->calcularProximaFecha();
            $plan->update([
                'ultima_ejecucion' => now(),
                'proxima_ejecucion' => $proxima,
            ]);

            $this->info("✔ OT {$ot->codigo_ot} generada para Plan: {$plan->nombre_plan}");
            $countGeneradas++;
        }

        $this->info("Proceso completado. Total OTs preventivas generadas: {$countGeneradas}");
        return 0;
    }
}
