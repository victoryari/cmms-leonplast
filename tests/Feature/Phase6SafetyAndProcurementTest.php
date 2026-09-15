<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\SparePart;
use App\Models\PermisoTrabajoSeguro;
use App\Models\SolicitudCompra;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Phase6SafetyAndProcurementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $dotenv = [];
        foreach (file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }
            [$clave, $valor] = array_map('trim', explode('=', $linea, 2));
            $dotenv[$clave] = trim($valor, '"\'');
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $dotenv['DB_DATABASE'] ?? 'cmms_leonplast',
            'database.connections.mysql.host' => $dotenv['DB_HOST'] ?? '127.0.0.1',
            'database.connections.mysql.username' => $dotenv['DB_USERNAME'] ?? 'root',
            'database.connections.mysql.password' => $dotenv['DB_PASSWORD'] ?? '',
            'database.connections.mysql.port' => $dotenv['DB_PORT'] ?? '3306',
        ]);

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::connection()->transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function getAdminUser()
    {
        $roleAdmin = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Admin System', 'permisos' => ['*']]
        );

        return User::firstOrCreate(
            ['email' => 'admin_phase6_test@leonplast.com'],
            [
                'rol_id' => $roleAdmin->id,
                'nombres' => 'Admin',
                'apellidos' => 'Phase 6',
                'codigo_empleado' => 'EMP-P6-TEST',
                'password_hash' => Hash::make('password123'),
                'activo' => true,
            ]
        );
    }

    /** @test */
    public function test_pts_blocks_work_order_start_when_special_permission_required()
    {
        $user = $this->getAdminUser();

        $asset = Asset::firstOrCreate(
            ['codigo_activo' => 'TEST-PTS-01'],
            ['nombre' => 'Inyectora PTS Test', 'activo' => true]
        );

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-PTS-' . rand(1000, 9999),
            'titulo' => 'Mantenimiento de Alto Riesgo',
            'descripcion' => 'Trabajo con riesgo eléctrico LOTO',
            'activo_id' => $asset->id,
            'tipo_ot' => 'Correctivo',
            'prioridad' => 'Alta',
            'estado' => 'Aprobada',
            'requiere_permiso_especial' => true,
            'solicitante_id' => $user->id,
            'creado_por' => $user->id,
        ]);

        // Intentar iniciar sin PTS aprobado debe fallar / rebotar con error
        $response = $this->actingAs($user)->post(route('ordenes.update-status', $ot->id), [
            'nuevo_estado' => 'En_Progreso',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('Aprobada', $ot->fresh()->estado);

        // Aprobar PTS
        PermisoTrabajoSeguro::create([
            'orden_trabajo_id' => $ot->id,
            'tipo_riesgo' => 'LOTO_ELECTRICO',
            'checklist_verificacion' => ['Energia_Cero_Verificada'],
            'epp_requeridos' => ['Casco', 'Guantes'],
            'bloqueo_loto_confirmado' => true,
            'aprobado_por' => $user->id,
            'fecha_aprobacion' => now(),
            'estado' => 'APROBADO',
        ]);

        // Ahora iniciar la OT debe ser exitoso
        $responseSuccess = $this->actingAs($user)->post(route('ordenes.update-status', $ot->id), [
            'nuevo_estado' => 'En_Progreso',
        ]);

        $responseSuccess->assertSessionHas('success');
        $this->assertEquals('En_Progreso', $ot->fresh()->estado);
    }

    /** @test */
    public function test_technician_signature_is_mandatory_for_completion()
    {
        $user = $this->getAdminUser();

        $asset = Asset::firstOrCreate(
            ['codigo_activo' => 'TEST-SIG-01'],
            ['nombre' => 'Extrusora Signature Test', 'activo' => true]
        );

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-SIG-' . rand(1000, 9999),
            'titulo' => 'Reparación de Bomba',
            'descripcion' => 'Reemplazo de faja',
            'activo_id' => $asset->id,
            'tipo_ot' => 'Correctivo',
            'prioridad' => 'Media',
            'estado' => 'En_Progreso',
            'solicitante_id' => $user->id,
            'creado_por' => $user->id,
        ]);

        // Intentar completar sin firma debe fallar
        $response = $this->actingAs($user)->post(route('ordenes.update-status', $ot->id), [
            'nuevo_estado' => 'Completada',
        ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals('Completada', $ot->fresh()->estado);

        // Enviar firma digital Base64 debe permitir el cierre
        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $responseSuccess = $this->actingAs($user)->post(route('ordenes.update-status', $ot->id), [
            'nuevo_estado' => 'Completada',
            'firma_tecnico' => $fakeSignature,
        ]);

        $responseSuccess->assertSessionHas('success');
        $this->assertEquals('Completada', $ot->fresh()->estado);
        $this->assertNotNull($ot->fresh()->firma_tecnico);
    }

    /** @test */
    public function test_auto_purchase_request_created_when_stock_reaches_minimum()
    {
        $user = $this->getAdminUser();

        $repuesto = SparePart::create([
            'codigo_sku' => 'REP-AUTO-' . rand(100, 999),
            'nombre' => 'Rodamiento SKF 6205',
            'categoria' => 'Mecánica',
            'stock_actual' => 5,
            'stock_minimo' => 3,
            'stock_maximo' => 10,
            'costo_unitario' => 45.00,
            'moneda' => 'PEN',
            'ubicacion_almacen' => 'A1-01',
            'activo' => true,
        ]);

        // Consumir 3 unidades -> Nuevo stock = 2 (menor al stock mínimo de 3)
        $repuesto->registrarMovimiento('Salida', 3, 'Uso en mantenimiento', 'OT-100', null, $user->id);

        $this->assertEquals(2, $repuesto->fresh()->stock_actual);

        // Verificar que se creó automáticamente una SolicitudCompra PENDIENTE
        $solicitud = SolicitudCompra::where('repuesto_id', $repuesto->id)->first();
        $this->assertNotNull($solicitud);
        $this->assertEquals('PENDIENTE', $solicitud->estado);
        $this->assertEquals('STOCK_MINIMO', $solicitud->motivo);
    }
}
