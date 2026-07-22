<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SparePart;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            [
                'codigo_sku' => 'REP-MANG-001',
                'nombre' => 'Manguera Hidráulica 3/4" 400 Bar (Alta Presión)',
                'descripcion' => 'Manguera reforzada con malla de acero para circuitos de cierre de inyectoras.',
                'categoria' => 'Hidráulica',
                'marca' => 'Gates',
                'stock_actual' => 15,
                'stock_minimo' => 5,
                'ubicacion_almacen' => 'Estante A2 - Almacén Central',
                'costo_unitario' => 45.00,
                'moneda' => 'USD',
                'activo' => true,
            ],
            [
                'codigo_sku' => 'REP-FILT-001',
                'nombre' => 'Filtro de Aire Sep. Aceite Kaeser BSD 75',
                'descripcion' => 'Cartucho filtrante desoleador de aire comprimido.',
                'categoria' => 'Neumática',
                'marca' => 'Kaeser',
                'stock_actual' => 8,
                'stock_minimo' => 2,
                'ubicacion_almacen' => 'Estante B1 - Neumática',
                'costo_unitario' => 85.00,
                'moneda' => 'USD',
                'activo' => true,
            ],
            [
                'codigo_sku' => 'REP-SELL-001',
                'nombre' => 'Juego de Empaquetaduras V-Ring Husillo 55mm',
                'descripcion' => 'Kit de sellos dinámicos en Vitón resistentes a alta temperatura.',
                'categoria' => 'Inyección',
                'marca' => 'SKF',
                'stock_actual' => 12,
                'stock_minimo' => 3,
                'ubicacion_almacen' => 'Estante A4 - Matricería',
                'costo_unitario' => 60.00,
                'moneda' => 'USD',
                'activo' => true,
            ],
            [
                'codigo_sku' => 'REP-SENS-001',
                'nombre' => 'Transductor de Presión 0-250 Bar (4-20mA)',
                'descripcion' => 'Sensor electrónico de presión hidráulica para PLC de inyectoras.',
                'categoria' => 'Electricidad & Electrónica',
                'marca' => 'WIKA',
                'stock_actual' => 6,
                'stock_minimo' => 2,
                'ubicacion_almacen' => 'Estante C3 - Instrumentación',
                'costo_unitario' => 120.00,
                'moneda' => 'USD',
                'activo' => true,
            ],
            [
                'codigo_sku' => 'REP-ACEI-001',
                'nombre' => 'Aceite Hidráulico ISO VG 68 (Cilindro 208L)',
                'descripcion' => 'Lubricante antidesgaste para sistemas hidráulicos industriales.',
                'categoria' => 'Lubricantes',
                'marca' => 'Mobil DTE 26',
                'stock_actual' => 4,
                'stock_minimo' => 1,
                'ubicacion_almacen' => 'Patio de Tambores - Zona Almacén',
                'costo_unitario' => 450.00,
                'moneda' => 'USD',
                'activo' => true,
            ],
        ];

        foreach ($parts as $partData) {
            SparePart::updateOrCreate(
                ['codigo_sku' => $partData['codigo_sku']],
                $partData
            );
        }
    }
}
