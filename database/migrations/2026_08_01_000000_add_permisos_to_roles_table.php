<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'permisos')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->json('permisos')->nullable()->after('descripcion');
            });
        }

        // Definir la estructura base de permisos por módulo
        $defaultPermissions = [
            'Administrador' => [
                'activos' => ['ver' => true, 'crear' => true, 'editar' => true, 'eliminar' => true],
                'ordenes' => ['ver' => true, 'crear' => true, 'asignar' => true, 'ejecutar' => true, 'eliminar' => true],
                'planes' => ['ver' => true, 'crear' => true, 'ejecutar' => true, 'eliminar' => true],
                'repuestos' => ['ver' => true, 'crear' => true, 'editar' => true, 'movimientos' => true],
                'reportes' => ['ver' => true, 'exportar' => true],
                'usuarios_roles' => ['ver' => true, 'crear_usuarios' => true, 'editar_usuarios' => true, 'gestionar_roles' => true],
            ],
            'Gerente_Mantenimiento' => [
                'activos' => ['ver' => true, 'crear' => true, 'editar' => true, 'eliminar' => true],
                'ordenes' => ['ver' => true, 'crear' => true, 'asignar' => true, 'ejecutar' => true, 'eliminar' => false],
                'planes' => ['ver' => true, 'crear' => true, 'ejecutar' => true, 'eliminar' => true],
                'repuestos' => ['ver' => true, 'crear' => true, 'editar' => true, 'movimientos' => true],
                'reportes' => ['ver' => true, 'exportar' => true],
                'usuarios_roles' => ['ver' => true, 'crear_usuarios' => true, 'editar_usuarios' => true, 'gestionar_roles' => true],
            ],
            'Supervisor' => [
                'activos' => ['ver' => true, 'crear' => true, 'editar' => true, 'eliminar' => false],
                'ordenes' => ['ver' => true, 'crear' => true, 'asignar' => true, 'ejecutar' => true, 'eliminar' => false],
                'planes' => ['ver' => true, 'crear' => true, 'ejecutar' => true, 'eliminar' => false],
                'repuestos' => ['ver' => true, 'crear' => true, 'editar' => true, 'movimientos' => true],
                'reportes' => ['ver' => true, 'exportar' => true],
                'usuarios_roles' => ['ver' => true, 'crear_usuarios' => false, 'editar_usuarios' => false, 'gestionar_roles' => false],
            ],
            'Tecnico' => [
                'activos' => ['ver' => true, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'ordenes' => ['ver' => true, 'crear' => true, 'asignar' => false, 'ejecutar' => true, 'eliminar' => false],
                'planes' => ['ver' => true, 'crear' => false, 'ejecutar' => true, 'eliminar' => false],
                'repuestos' => ['ver' => true, 'crear' => false, 'editar' => false, 'movimientos' => true],
                'reportes' => ['ver' => false, 'exportar' => false],
                'usuarios_roles' => ['ver' => false, 'crear_usuarios' => false, 'editar_usuarios' => false, 'gestionar_roles' => false],
            ],
            'Solicitante' => [
                'activos' => ['ver' => true, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'ordenes' => ['ver' => true, 'crear' => true, 'asignar' => false, 'ejecutar' => false, 'eliminar' => false],
                'planes' => ['ver' => false, 'crear' => false, 'ejecutar' => false, 'eliminar' => false],
                'repuestos' => ['ver' => false, 'crear' => false, 'editar' => false, 'movimientos' => false],
                'reportes' => ['ver' => false, 'exportar' => false],
                'usuarios_roles' => ['ver' => false, 'crear_usuarios' => false, 'editar_usuarios' => false, 'gestionar_roles' => false],
            ],
        ];

        foreach ($defaultPermissions as $roleName => $perms) {
            DB::table('roles')
                ->where('nombre', $roleName)
                ->update(['permisos' => json_encode($perms)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'permisos')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('permisos');
            });
        }
    }
};
