<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PreventivePlan;
use App\Models\Asset;
use App\Models\User;

class PreventivePlanSeeder extends Seeder
{
    public function run(): void
    {
        $engel = Asset::where('codigo_activo', 'ACT-INY-001')->first();
        $kaeser = Asset::where('codigo_activo', 'ACT-CMP-001')->first();
        $demag = Asset::where('codigo_activo', 'ACT-GRU-001')->first();
        $piovan = Asset::where('codigo_activo', 'ACT-CHL-001')->first();

        $tecnico = User::where('email', 'tecnico@leonplast.com')->first();
        $supervisor = User::where('email', 'supervisor@leonplast.com')->first();

        $plans = [
            [
                'activo_id' => $engel?->id ?? 1,
                'nombre_plan' => 'Mantenimiento Preventivo Mensual - Inyectora Engel 250T',
                'descripcion' => 'Revisión mensual de presión de unidad de cierre, engrase de guías lineales y lubricación de columnas.',
                'tipo_plan' => 'Por_Calendario',
                'frecuencia_dias' => 30,
                'titulo_ot_generada' => 'Preventivo Mensual Inyectora Engel 250T',
                'descripcion_ot_generada' => 'Ejecutar rutina de lubricación de columnas de cierre, verificación de fugas en bloque hidráulico y limpieza de platina.',
                'instrucciones_especificas' => "1. Aplicar grasa de litio sintética ISO VG 220 en bujes.\n2. Medir presión hidráulica en puerto P1 (rango: 175-185 bar).\n3. Inspeccionar alineación de eyector neumático.",
                'tecnico_asignado_id' => $tecnico?->id ?? 4,
                'prioridad_defecto' => 'Media',
                'estado' => 'Activo',
                'fecha_inicio' => now()->subMonths(2),
                'ultima_ejecucion' => now()->subDays(28),
                'proxima_ejecucion' => now()->subDays(1), // Pendiente para prueba automática
                'creado_por' => $supervisor?->id ?? 3,
            ],
            [
                'activo_id' => $kaeser?->id ?? 4,
                'nombre_plan' => 'Cambio de Aceite & Filtros - Compresor Kaeser BSD 75',
                'descripcion' => 'Mantenimiento preventivo por horas de operación en sala de compresores.',
                'tipo_plan' => 'Por_Medidor',
                'unidad_medicion' => 'Horas',
                'medidor_nombre' => 'Horómetro Digital de Gabinete',
                'umbral_medidor' => 500.00,
                'titulo_ot_generada' => 'Mantenimiento 500H Compresor Kaeser BSD 75',
                'descripcion_ot_generada' => 'Reemplazo de cartucho desoleador, filtro de aire de admisión y lubricante sintético Kaeser SIGMA FLUID.',
                'instrucciones_especificas' => "1. Drenar aceite usado a 60°C.\n2. Instalar filtro Kaeser ref. 6.4149.0.\n3. Rellenar 14.5 litros de lubricante Sigma Fluid M-460.",
                'tecnico_asignado_id' => $tecnico?->id ?? 4,
                'prioridad_defecto' => 'Alta',
                'estado' => 'Activo',
                'fecha_inicio' => now()->subMonths(3),
                'ultima_ejecucion' => now()->subDays(45),
                'proxima_ejecucion' => now()->addDays(5),
                'creado_por' => $supervisor?->id ?? 3,
            ],
            [
                'activo_id' => $demag?->id ?? 3,
                'nombre_plan' => 'Inspección Trimestral Polipasto & Freno - Grúa Demag 10T',
                'descripcion' => 'Revisión de cables de acero, final de carrera y estado de las zapatas de freno electromagnético.',
                'tipo_plan' => 'Por_Calendario',
                'frecuencia_dias' => 90,
                'titulo_ot_generada' => 'Inspección Trimestral Grúa Puente Demag 10T',
                'descripcion_ot_generada' => 'Inspección técnica de seguridad en elementos de elevación y pruebas de carga suspendida.',
                'instrucciones_especificas' => "1. Medir desgaste de gancho forjado.\n2. Verificar tensión de freno en tambor.\n3. Aplicar lubricante Grafoil en cable de acero 5/8\".",
                'tecnico_asignado_id' => $tecnico?->id ?? 4,
                'prioridad_defecto' => 'Crítica',
                'estado' => 'Activo',
                'fecha_inicio' => now()->subMonths(6),
                'ultima_ejecucion' => now()->subDays(80),
                'proxima_ejecucion' => now()->addDays(10),
                'creado_por' => $supervisor?->id ?? 3,
            ],
            [
                'activo_id' => $piovan?->id ?? 5,
                'nombre_plan' => 'Limpieza Quincenal de Filtros & Condensador - Chiller Piovan',
                'descripcion' => 'Lavado de panal condensador de aluminio y verificación de glicol en circuito de agua.',
                'tipo_plan' => 'Por_Calendario',
                'frecuencia_dias' => 15,
                'titulo_ot_generada' => 'Limpieza Quincenal Chiller Piovan RTS 120',
                'descripcion_ot_generada' => 'Soplar panal condensador e inspeccionar nivel de refrigerante R410A.',
                'tecnico_asignado_id' => $tecnico?->id ?? 4,
                'prioridad_defecto' => 'Baja',
                'estado' => 'Activo',
                'fecha_inicio' => now()->subMonth(),
                'ultima_ejecucion' => now()->subDays(14),
                'proxima_ejecucion' => now()->addDays(1),
                'creado_por' => $supervisor?->id ?? 3,
            ],
        ];

        foreach ($plans as $planData) {
            PreventivePlan::updateOrCreate(
                ['nombre_plan' => $planData['nombre_plan']],
                $planData
            );
        }
    }
}
