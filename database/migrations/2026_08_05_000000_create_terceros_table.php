<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('terceros')) {
            Schema::create('terceros', function (Blueprint $table) {
                $table->id();
                $table->string('ruc_documento', 20)->unique();
                $table->string('razon_social', 150);
                $table->string('nombre_comercial', 150)->nullable();
                $table->enum('tipo', ['Proveedor_Repuestos', 'Contratista_Servicios', 'Ambos'])->default('Proveedor_Repuestos');
                $table->string('contacto_nombre', 100)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('direccion', 200)->nullable();
                $table->string('ciudad', 80)->default('Lima');
                $table->tinyInteger('calificacion')->default(5); // 1 a 5 estrellas
                $table->boolean('activo')->default(true);
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });

            // Poblar proveedores y contratistas iniciales para León Plast
            DB::table('terceros')->insert([
                [
                    'ruc_documento' => '20501234567',
                    'razon_social' => 'Engel Austria GmbH - Representante Perú',
                    'nombre_comercial' => 'Engel Inyección Perú',
                    'tipo' => 'Ambos',
                    'contacto_nombre' => 'Ing. Carlos Mendoza',
                    'telefono' => '+51 1 456-7890',
                    'email' => 'soporte@engel-peru.com',
                    'direccion' => 'Av. Industrial 1450, Ate',
                    'ciudad' => 'Lima',
                    'calificacion' => 5,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'ruc_documento' => '20109876543',
                    'razon_social' => 'Hidráulica y Neumática Industrial S.A.C.',
                    'nombre_comercial' => 'Hidraumat Perú',
                    'tipo' => 'Proveedor_Repuestos',
                    'contacto_nombre' => 'Lic. Roberto Silva',
                    'telefono' => '+51 987-654-321',
                    'email' => 'ventas@hidraumat.pe',
                    'direccion' => 'Calle Los Calderos 320, Vulcano',
                    'ciudad' => 'Lima',
                    'calificacion' => 4,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'ruc_documento' => '20334455667',
                    'razon_social' => 'Servicios Electromecánicos del Pacífico E.I.R.L.',
                    'nombre_comercial' => 'ServiPacífico Mantenimiento',
                    'tipo' => 'Contratista_Servicios',
                    'contacto_nombre' => 'Tec. Mario Paredes',
                    'telefono' => '+51 999-111-222',
                    'email' => 'servicios@servipacifico.pe',
                    'direccion' => 'Av. Argentina 2890, Callao',
                    'ciudad' => 'Callao',
                    'calificacion' => 5,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terceros');
    }
};
