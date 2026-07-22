<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\User;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $engel = Asset::where('codigo_activo', 'ACT-INY-001')->first();
        $kaeser = Asset::where('codigo_activo', 'ACT-CMP-001')->first();
        $demag = Asset::where('codigo_activo', 'ACT-GRU-001')->first();
        $piovan = Asset::where('codigo_activo', 'ACT-CHL-001')->first();
        $marley = Asset::where('codigo_activo', 'ACT-TRE-001')->first();

        $solicitante = User::where('email', 'solicitante@leonplast.com')->first();
        $tecnico = User::where('email', 'tecnico@leonplast.com')->first();
        $supervisor = User::where('email', 'supervisor@leonplast.com')->first();

        $workOrders = [
            [
                'codigo_ot' => 'OT-2026-001',
                'titulo' => 'Fuga de aceite hidráulico en manguera principal de Inyectora Engel 250T',
                'descripcion' => 'Se detectó pérdida constante de fluido hidráulico en la zona de cierre del molde durante el turno de noche.',
                'activo_id' => $engel?->id ?? 1,
                'solicitante_id' => $solicitante?->id ?? 5,
                'tecnico_id' => $tecnico?->id ?? 4,
                'supervisor_id' => $supervisor?->id ?? 3,
                'tipo_ot' => 'Correctivo',
                'prioridad' => 'Alta',
                'estado' => 'En_Progreso',
                'fecha_solicitud' => now()->subHours(8),
                'fecha_aprobacion' => now()->subHours(6),
                'fecha_inicio' => now()->subHours(4),
                'fecha_fin_estimada' => now()->addHours(2),
                'duracion_estimada_horas' => 4.00,
                'costo_estimado' => 250.00,
                'requiere_permiso_especial' => true,
                'permisos_especiales' => 'Trabajo con fluido caliente / Bloqueo de energía LOTO',
                'checklist_seguridad' => [
                    'Desconexión eléctrica LOTO realizada' => true,
                    'Despresurización de línea hidráulica' => true,
                    'Uso de EPP térmico y antiparras' => true,
                ],
                'diagnosticos' => [
                    'Manguera de 3/4" con fisura por desgaste térmico.',
                ],
                'creado_por' => $solicitante?->id ?? 5,
                'activo' => true,
            ],
            [
                'codigo_ot' => 'OT-2026-002',
                'titulo' => 'Fallo en transductor de presión en Compresor de Tornillo Kaeser',
                'descripcion' => 'El compresor marca alarma por sobrepresión de aire y se detiene automáticamente en la línea de neumática.',
                'activo_id' => $kaeser?->id ?? 4,
                'solicitante_id' => $solicitante?->id ?? 5,
                'tecnico_id' => $tecnico?->id ?? 4,
                'supervisor_id' => $supervisor?->id ?? 3,
                'tipo_ot' => 'Urgente',
                'prioridad' => 'Media',
                'estado' => 'Aprobada',
                'fecha_solicitud' => now()->subHours(3),
                'fecha_aprobacion' => now()->subHours(1),
                'duracion_estimada_horas' => 2.50,
                'costo_estimado' => 180.00,
                'creado_por' => $solicitante?->id ?? 5,
                'activo' => true,
            ],
            [
                'codigo_ot' => 'OT-2026-003',
                'titulo' => 'Ruido anómalo en freno del polipasto de Grúa Puente Demag 10T',
                'descripcion' => 'Al izar moldes pesados mayores a 5 toneladas se escucha un rechinamiento metálico en el tambor de elevación.',
                'activo_id' => $demag?->id ?? 3,
                'solicitante_id' => $solicitante?->id ?? 5,
                'tecnico_id' => null,
                'supervisor_id' => null,
                'tipo_ot' => 'Correctivo',
                'prioridad' => 'Crítica',
                'estado' => 'Pendiente',
                'fecha_solicitud' => now()->subMinutes(45),
                'duracion_estimada_horas' => 5.00,
                'creado_por' => $solicitante?->id ?? 5,
                'activo' => true,
            ],
            [
                'codigo_ot' => 'OT-2026-004',
                'titulo' => 'Mantenimiento Preventivo Mensual de Chiller Piovan RTS 120',
                'descripcion' => 'Limpieza de condensador, verificación de gas refrigerante R410A y ajuste de bornes eléctricos.',
                'activo_id' => $piovan?->id ?? 5,
                'solicitante_id' => $supervisor?->id ?? 3,
                'tecnico_id' => $tecnico?->id ?? 4,
                'supervisor_id' => $supervisor?->id ?? 3,
                'tipo_ot' => 'Preventivo',
                'prioridad' => 'Media',
                'estado' => 'Completada',
                'fecha_solicitud' => now()->subDays(3),
                'fecha_aprobacion' => now()->subDays(3),
                'fecha_inicio' => now()->subDays(2),
                'fecha_fin_real' => now()->subDays(2)->addHours(4),
                'duracion_estimada_horas' => 4.00,
                'duracion_real_horas' => 3.50,
                'costo_estimado' => 150.00,
                'costo_real' => 140.00,
                'costo_mano_obra' => 90.00,
                'costo_repuestos' => 50.00,
                'diagnosticos' => [
                    'Filtros de condensador limpios con hidrolavadora.',
                    'Presión de refrigerante dentro del rango de operación (65 PSI).',
                ],
                'soluciones' => [
                    'Reajuste de pernos y reapriete de contactores de potencia.',
                ],
                'calificacion_usuario' => 5,
                'comentario_usuario' => 'Excelente trabajo preventivo. El chiller mantiene el agua a 12°C estables.',
                'creado_por' => $supervisor?->id ?? 3,
                'activo' => true,
            ],
            [
                'codigo_ot' => 'OT-2026-005',
                'titulo' => 'Limpieza de filtros y paneles de Torre de Enfriamiento Marley',
                'descripcion' => 'Mantenimiento predictivo por elevación de temperatura en el circuito de aceite hidráulico de inyección.',
                'activo_id' => $marley?->id ?? 6,
                'solicitante_id' => $solicitante?->id ?? 5,
                'tecnico_id' => $tecnico?->id ?? 4,
                'supervisor_id' => $supervisor?->id ?? 3,
                'tipo_ot' => 'Mejora',
                'prioridad' => 'Baja',
                'estado' => 'En_Revision',
                'fecha_solicitud' => now()->subDays(1),
                'fecha_aprobacion' => now()->subDays(1),
                'fecha_inicio' => now()->subHours(12),
                'duracion_estimada_horas' => 3.00,
                'duracion_real_horas' => 2.80,
                'diagnosticos' => [
                    'Remoción de incrustaciones de sarro en rociadores.',
                ],
                'soluciones' => [
                    'Lavado químico desincrustante aplicado a empaques Marley.',
                ],
                'creado_por' => $solicitante?->id ?? 5,
                'activo' => true,
            ],
        ];

        foreach ($workOrders as $otData) {
            WorkOrder::updateOrCreate(
                ['codigo_ot' => $otData['codigo_ot']],
                $otData
            );
        }
    }
}
