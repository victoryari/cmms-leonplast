<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'rol_id' => 1, // Administrador
                'nombres' => 'Carlos',
                'apellidos' => 'Mendoza Valles',
                'documento_identidad' => '45892134',
                'telefono' => '+51 987654321',
                'email' => 'admin@leonplast.com',
                'codigo_empleado' => 'EMP-001',
                'especialidad' => 'Gestión General CMMS',
                'password_hash' => Hash::make('Password123!'),
                'activo' => true,
                'fecha_ingreso' => '2024-01-15',
            ],
            [
                'rol_id' => 2, // Gerente_Mantenimiento
                'nombres' => 'Ing. Fernando',
                'apellidos' => 'Rojas Benítez',
                'documento_identidad' => '41235678',
                'telefono' => '+51 987123456',
                'email' => 'gerente@leonplast.com',
                'codigo_empleado' => 'EMP-002',
                'especialidad' => 'Planificación Estratégica Industrial',
                'password_hash' => Hash::make('Password123!'),
                'activo' => true,
                'fecha_ingreso' => '2024-02-01',
            ],
            [
                'rol_id' => 3, // Supervisor
                'nombres' => 'Roberto',
                'apellidos' => 'Gómez Peralta',
                'documento_identidad' => '43567812',
                'telefono' => '+51 976543210',
                'email' => 'supervisor@leonplast.com',
                'codigo_empleado' => 'EMP-003',
                'especialidad' => 'Supervisión de Inyectoras y Neumática',
                'password_hash' => Hash::make('Password123!'),
                'activo' => true,
                'fecha_ingreso' => '2024-03-10',
            ],
            [
                'rol_id' => 4, // Tecnico
                'nombres' => 'Juan',
                'apellidos' => 'Pérez Ramos',
                'documento_identidad' => '47890123',
                'telefono' => '+51 965432109',
                'email' => 'tecnico@leonplast.com',
                'codigo_empleado' => 'EMP-004',
                'especialidad' => 'Mecánica Industrial & Hidráulica',
                'password_hash' => Hash::make('Password123!'),
                'activo' => true,
                'fecha_ingreso' => '2024-04-05',
            ],
            [
                'rol_id' => 5, // Solicitante
                'nombres' => 'María',
                'apellidos' => 'Alva Cárdenas',
                'documento_identidad' => '48901234',
                'telefono' => '+51 954321098',
                'email' => 'solicitante@leonplast.com',
                'codigo_empleado' => 'EMP-005',
                'especialidad' => 'Operaciones de Planta y Producción',
                'password_hash' => Hash::make('Password123!'),
                'activo' => true,
                'fecha_ingreso' => '2024-05-12',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
