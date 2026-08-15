<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Asset;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use ReflectionMethod;

class SecurityAuditTest extends TestCase
{
    /**
     * La base de datos base (tablas en español) la crea un dump MySQL fuera de las
     * migraciones, por lo que los tests corren contra el motor MySQL de desarrollo
     * dentro de una transacción que se revierte para no contaminar datos.
     */
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

        RateLimiter::clear('login');
        RateLimiter::clear('api-login');
        RateLimiter::clear('public-qr');
    }

    protected function tearDown(): void
    {
        if (DB::connection()->transactionLevel() > 0) {
            DB::rollBack();
        }

        parent::tearDown();
    }

    private function crearRol(string $nombre, array $permisos = []): Role
    {
        return Role::create([
            'nombre' => $nombre . '-' . uniqid(),
            'permisos' => $permisos,
            'activo' => true,
        ]);
    }

    private function crearUsuario(Role $rol, array $datos = []): User
    {
        return User::create(array_merge([
            'rol_id' => $rol->id,
            'nombres' => 'Test',
            'apellidos' => 'Usuario',
            'email' => 'test.' . uniqid() . '@leonplast.local',
            'password_hash' => Hash::make('Secreto!123'),
            'codigo_empleado' => 'EMP-' . uniqid(),
            'activo' => true,
        ], $datos));
    }

    private function crearActivo(): Asset
    {
        return Asset::create([
            'codigo_activo' => 'ACT-' . uniqid(),
            'nombre' => 'Compresor de prueba',
            'tipo_clasificacion' => 'Equipo',
            'categoria' => 'Maquinaria',
            'activo' => true,
        ]);
    }

    private function obtenerOCrearRol(string $nombre, array $permisos): Role
    {
        $rol = Role::where('nombre', $nombre)->first();

        if ($rol) {
            return $rol;
        }

        return Role::create([
            'nombre' => $nombre,
            'permisos' => $permisos,
            'activo' => true,
        ]);
    }

    private function crearRolAdministrador(): Role
    {
        $modulos = ['activos', 'ordenes', 'planes', 'repuestos', 'reportes', 'usuarios_roles'];
        $acciones = ['ver', 'crear', 'editar', 'eliminar', 'asignar', 'ejecutar', 'movimientos', 'exportar', 'gestionar_roles'];

        return $this->crearRol('Administrador', [
            'activos' => array_fill_keys(['ver', 'crear', 'editar', 'eliminar'], true),
            'ordenes' => array_fill_keys(['ver', 'crear', 'asignar', 'ejecutar', 'eliminar'], true),
            'planes' => array_fill_keys(['ver', 'crear', 'ejecutar', 'eliminar'], true),
            'repuestos' => array_fill_keys(['ver', 'crear', 'editar', 'movimientos'], true),
            'reportes' => array_fill_keys(['ver', 'exportar'], true),
            'usuarios_roles' => array_fill_keys(['ver', 'crear_usuarios', 'editar_usuarios', 'gestionar_roles'], true),
        ]);
    }

    public function test_security_headers_present_on_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_web_login_is_rate_limited_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'brute@force.test',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'brute@force.test',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_api_login_is_rate_limited_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'brute@force.test',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'brute@force.test',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_technician_cannot_read_another_technicians_work_order(): void
    {
        $tecnico = $this->obtenerOCrearRol('Tecnico', [
            'ordenes' => ['ver' => true, 'ejecutar' => true],
            'activos' => ['ver' => true],
        ]);

        $tecnicoA = $this->crearUsuario($tecnico);
        $tecnicoB = $this->crearUsuario($tecnico);
        $activo = $this->crearActivo();

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-' . date('Y') . '-' . str_pad(uniqid(), 4, '0', STR_PAD_LEFT),
            'titulo' => 'OT del técnico A',
            'descripcion' => 'Descripción de prueba',
            'activo_id' => $activo->id,
            'solicitante_id' => $tecnicoA->id,
            'tecnico_id' => $tecnicoA->id,
            'fecha_solicitud' => now(),
        ]);

        Sanctum::actingAs($tecnicoA);
        $this->getJson('/api/v1/ordenes-trabajo/' . $ot->id)->assertOk();

        Sanctum::actingAs($tecnicoB);
        $this->getJson('/api/v1/ordenes-trabajo/' . $ot->id)
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_requester_cannot_modify_another_requesters_work_order(): void
    {
        $rolBasico = $this->obtenerOCrearRol('Usuario_Lectura', [
            'ordenes' => ['ver' => true, 'crear' => false, 'ejecutar' => false],
            'activos' => ['ver' => true],
        ]);

        $userA = $this->crearUsuario($rolBasico);
        $userB = $this->crearUsuario($rolBasico);
        $activo = $this->crearActivo();

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-' . date('Y') . '-' . str_pad(uniqid(), 4, '0', STR_PAD_LEFT),
            'titulo' => 'OT de prueba usuario A',
            'descripcion' => 'Descripción de prueba',
            'activo_id' => $activo->id,
            'solicitante_id' => $userA->id,
            'fecha_solicitud' => now(),
        ]);

        Sanctum::actingAs($userB);
        $this->postJson('/api/v1/ordenes-trabajo/' . $ot->id . '/pausar', ['motivo' => 'No autorizado'])
            ->assertStatus(403);
    }

    public function test_svg_upload_is_rejected(): void
    {
        $admin = $this->crearRolAdministrador();
        $usuario = $this->crearUsuario($admin);
        $activo = $this->crearActivo();

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-' . date('Y') . '-' . str_pad(uniqid(), 4, '0', STR_PAD_LEFT),
            'titulo' => 'OT para prueba de upload',
            'descripcion' => 'Descripción de prueba',
            'activo_id' => $activo->id,
            'solicitante_id' => $usuario->id,
            'fecha_solicitud' => now(),
        ]);

        Storage::fake('public');

        Sanctum::actingAs($usuario);
        $response = $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/ordenes-trabajo/' . $ot->id . '/fotos', [
                'tipo_foto' => 'antes',
                'foto' => UploadedFile::fake()->create('malicioso.svg', 100, 'image/svg+xml'),
            ]);

        $response->assertStatus(422);

        Storage::disk('public')->assertDirectoryEmpty('fotos_ot');
    }

    public function test_php_script_upload_is_rejected(): void
    {
        $admin = $this->crearRolAdministrador();
        $usuario = $this->crearUsuario($admin);
        $activo = $this->crearActivo();

        $ot = WorkOrder::create([
            'codigo_ot' => 'OT-' . date('Y') . '-' . str_pad(uniqid(), 4, '0', STR_PAD_LEFT),
            'titulo' => 'OT para prueba de upload',
            'descripcion' => 'Descripción de prueba',
            'activo_id' => $activo->id,
            'solicitante_id' => $usuario->id,
            'fecha_solicitud' => now(),
        ]);

        Storage::fake('public');

        Sanctum::actingAs($usuario);
        $response = $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/ordenes-trabajo/' . $ot->id . '/fotos', [
                'tipo_foto' => 'antes',
                'foto' => UploadedFile::fake()->create('shell.php', 100, 'application/x-php'),
            ]);

        $response->assertStatus(422);

        Storage::disk('public')->assertDirectoryEmpty('fotos_ot');
    }

    public function test_public_request_is_throttled(): void
    {
        $activo = $this->crearActivo();

        for ($i = 0; $i < 10; $i++) {
            $this->get('/solicitud-rapida/' . $activo->codigo_activo)->assertStatus(200);
        }

        $this->get('/solicitud-rapida/' . $activo->codigo_activo)->assertStatus(429);
    }

    public function test_csv_export_requires_authentication(): void
    {
        $this->get('/reportes-kpi/exportar-csv')->assertRedirect('/login');
    }

    public function test_csv_cells_are_sanitized_against_formula_injection(): void
    {
        $controller = app(ReportController::class);
        $method = new ReflectionMethod($controller, 'sanitizeCsvCell');
        $method->setAccessible(true);

        $this->assertSame("'=SUM(A1)", $method->invoke($controller, '=SUM(A1)'));
        $this->assertSame("'+cmd| /C calc", $method->invoke($controller, '+cmd| /C calc'));
        $this->assertSame("'@user@domain", $method->invoke($controller, '@user@domain'));
        $this->assertSame("'-1", $method->invoke($controller, '-1'));
        $this->assertSame("Normal", $method->invoke($controller, 'Normal'));
        $this->assertSame("01/01/2026", $method->invoke($controller, '01/01/2026'));
        $this->assertSame('', $method->invoke($controller, ''));
    }
}
