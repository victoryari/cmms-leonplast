<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nombre' => 'Inyectoras de Plástico',
                'descripcion' => 'Maquinaria de inyección termoplástica de alto tonelaje',
                'icono' => 'cog',
                'color' => 'blue',
                'activo' => true,
            ],
            [
                'nombre' => 'Grúas y Equipos de Izaje',
                'descripcion' => 'Grúas puente y polipastos para manipulación de moldes pesados',
                'icono' => 'truck',
                'color' => 'amber',
                'activo' => true,
            ],
            [
                'nombre' => 'Compresores y Neumática',
                'descripcion' => 'Compresores de tornillo y tanques acumuladores de aire compresionado',
                'icono' => 'wind',
                'color' => 'cyan',
                'activo' => true,
            ],
            [
                'nombre' => 'Sistemas de Enfriamiento',
                'descripcion' => 'Chillers industriales, atemperadores y torres de enfriamiento',
                'icono' => 'snowflake',
                'color' => 'indigo',
                'activo' => true,
            ],
            [
                'nombre' => 'Periféricos y Mezcladores',
                'descripcion' => 'Tolvas secadoras, dosificadores de pigmentos y molinos de triturado',
                'icono' => 'refresh',
                'color' => 'emerald',
                'activo' => true,
            ],
        ];

        foreach ($categories as $cat) {
            AssetCategory::updateOrCreate(
                ['nombre' => $cat['nombre']],
                $cat
            );
        }
    }
}
