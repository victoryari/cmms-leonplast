<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ubicaciones')) {
            Schema::create('ubicaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
                $table->string('codigo_ubicacion', 50)->unique();
                $table->string('nombre', 150);
                $table->string('empresa_subsidiaria', 150)->nullable();
                $table->enum('tipo', [
                    'Sede_Principal', 
                    'Planta_Industrial', 
                    'Almacen_Deposito', 
                    'Oficina_Regional', 
                    'Empresa_Subsidiaria', 
                    'Area_Planta'
                ])->default('Planta_Industrial');
                
                $table->string('direccion', 255)->nullable();
                $table->string('ciudad', 100)->default('Lima');
                $table->string('departamento', 100)->default('Lima');
                $table->string('pais', 100)->default('Perú');
                $table->string('codigo_postal', 20)->nullable();
                
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                
                $table->enum('prioridad', ['Alta', 'Media', 'Baja'])->default('Media');
                $table->string('centro_costo', 50)->nullable();
                $table->decimal('presupuesto_anual', 12, 2)->default(0.00);
                
                $table->string('codigo_barras_nfc', 100)->nullable();
                $table->string('qr_code_url', 255)->nullable();
                $table->text('notas')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            // Poblar ubicaciones iniciales para León Plast
            DB::table('ubicaciones')->insert([
                [
                    'codigo_ubicacion' => 'UBIC-LIM-ATE-01',
                    'nombre' => 'Planta Industrial Ate (Sede Principal)',
                    'empresa_subsidiaria' => 'León Plast S.A.C.',
                    'tipo' => 'Sede_Principal',
                    'direccion' => 'Av. Industrial 1450, Ate',
                    'ciudad' => 'Lima',
                    'departamento' => 'Lima',
                    'pais' => 'Perú',
                    'codigo_postal' => '15012',
                    'latitud' => -12.0463740,
                    'longitud' => -76.9535000,
                    'prioridad' => 'Alta',
                    'centro_costo' => 'CC-LIM-001',
                    'presupuesto_anual' => 150000.00,
                    'codigo_barras_nfc' => 'NFC-LIM-ATE-01',
                    'notas' => 'Planta principal de inyección de plásticos y moldes de precisión.',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'codigo_ubicacion' => 'UBIC-AQP-01',
                    'nombre' => 'Planta Regional Sur (Arequipa)',
                    'empresa_subsidiaria' => 'León Plast del Sur E.I.R.L.',
                    'tipo' => 'Empresa_Subsidiaria',
                    'direccion' => 'Parque Industrial Rio Seco Manzana G, Cerro Colorado',
                    'ciudad' => 'Arequipa',
                    'departamento' => 'Arequipa',
                    'pais' => 'Perú',
                    'codigo_postal' => '04000',
                    'latitud' => -16.3988900,
                    'longitud' => -71.5350000,
                    'prioridad' => 'Alta',
                    'centro_costo' => 'CC-AQP-002',
                    'presupuesto_anual' => 85000.00,
                    'codigo_barras_nfc' => 'NFC-AQP-01',
                    'notas' => 'Subsidiaria encargada del abastecimiento y distribución en la zona sur.',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'codigo_ubicacion' => 'UBIC-TRU-01',
                    'nombre' => 'Almacén Regional Norte (Trujillo)',
                    'empresa_subsidiaria' => 'León Plast Norte S.A.C.',
                    'tipo' => 'Almacen_Deposito',
                    'direccion' => 'Av. Nicolás de Piérola 890, Zona Industrial',
                    'ciudad' => 'Trujillo',
                    'departamento' => 'La Libertad',
                    'pais' => 'Perú',
                    'codigo_postal' => '13001',
                    'latitud' => -8.1116700,
                    'longitud' => -79.0286100,
                    'prioridad' => 'Media',
                    'centro_costo' => 'CC-TRU-003',
                    'presupuesto_anual' => 45000.00,
                    'codigo_barras_nfc' => 'NFC-TRU-01',
                    'notas' => 'Almacén centralizado para atención de pedidos agroindustriales del norte.',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
