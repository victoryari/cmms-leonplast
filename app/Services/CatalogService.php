<?php

namespace App\Services;

use App\Models\AssetCategory;
use App\Models\SystemParameter;
use App\Models\SparePart;
use App\Models\Asset;

class CatalogService
{
    /**
     * Obtiene las categorías de activos dinámicas
     */
    public function getCategoriasActivos(): array
    {
        $dbCategories = AssetCategory::where('activo', true)->pluck('nombre')->toArray();
        $paramCategories = SystemParameter::getValoresGrupo('catalog_categorias_activos', [
            'Inyectoras de Plástico',
            'Compresores de Aire',
            'Grúas Puente & Polipastos',
            'Chiller & Climatización',
            'Torres de Enfriamiento',
            'Moldes & Matricería',
            'Robótica & Automatización',
            'Periféricos & Auxiliares'
        ]);

        return array_values(array_unique(array_merge($dbCategories, $paramCategories)));
    }

    /**
     * Obtiene los estados operativos de máquinas
     */
    public function getEstadosOperativos(): array
    {
        return SystemParameter::getValoresGrupo('catalog_estados_operativos', [
            'Operativo',
            'Mantenimiento',
            'Reparacion',
            'Fuera_de_servicio',
            'Baja',
            'En_Pruebas',
            'Standby'
        ]);
    }

    /**
     * Obtiene las condiciones físicas de conservación
     */
    public function getCondicionesFisicas(): array
    {
        return SystemParameter::getValoresGrupo('catalog_condiciones_fisicas', [
            'Excelente',
            'Bueno',
            'Regular',
            'Malo',
            'Crítico'
        ]);
    }

    /**
     * Obtiene las áreas y ubicaciones de planta
     */
    public function getAreasPlanta(): array
    {
        $dbAreas = Asset::whereNotNull('area')->distinct()->pluck('area')->toArray();
        $paramAreas = SystemParameter::getValoresGrupo('catalog_areas_planta', [
            'Planta Inyección 1',
            'Planta Inyección 2',
            'Planta Extrusión',
            'Sala de Compresores',
            'Matricería & Moldes',
            'Subestación Eléctrica',
            'Patio de Tambores',
            'Almacén Central'
        ]);

        return array_values(array_unique(array_merge($dbAreas, $paramAreas)));
    }

    /**
     * Obtiene las categorías de repuestos
     */
    public function getCategoriasRepuestos(): array
    {
        $dbCategories = SparePart::whereNotNull('categoria')->distinct()->pluck('categoria')->toArray();
        $paramCategories = SystemParameter::getValoresGrupo('catalog_categorias_repuestos', [
            'Hidráulica',
            'Neumática',
            'Inyección',
            'Electricidad & Electrónica',
            'Lubricantes',
            'Instrumentación',
            'Mecánica'
        ]);

        return array_values(array_unique(array_merge($dbCategories, $paramCategories)));
    }

    /**
     * Obtiene los tipos de Órdenes de Trabajo
     */
    public function getTiposOt(): array
    {
        return SystemParameter::getValoresGrupo('catalog_tipos_ot', [
            'Correctivo',
            'Preventivo',
            'Predictivo',
            'Urgente',
            'Mejora',
            'Inspección',
            'Seguridad'
        ]);
    }

    /**
     * Devuelve la totalidad de catálogos dinámicos en una sola llamada
     */
    public function getAllCatalogs(): array
    {
        return [
            'categorias_activos' => $this->getCategoriasActivos(),
            'estados_operativos' => $this->getEstadosOperativos(),
            'condiciones_fisicas' => $this->getCondicionesFisicas(),
            'areas_planta' => $this->getAreasPlanta(),
            'categorias_repuestos' => $this->getCategoriasRepuestos(),
            'tipos_ot' => $this->getTiposOt(),
        ];
    }
}
