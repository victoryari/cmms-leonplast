<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SystemRole;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'permisos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'permisos' => 'array',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    /**
     * Verifica si el rol tiene un permiso específico para un módulo y acción.
     */
    public function hasPermission(string $modulo, string $accion): bool
    {
        if ($this->nombre === SystemRole::Admin->value) {
            return true;
        }

        $permisos = $this->permisos;
        if (!is_array($permisos)) {
            return false;
        }

        if (!empty($permisos[$modulo][$accion])) {
            return true;
        }

        // Mapeo flexible para usuarios_roles
        if ($modulo === 'usuarios_roles') {
            if ($accion === 'ver' && (!empty($permisos['usuarios_roles']['crear_usuarios']) || !empty($permisos['usuarios_roles']['editar_usuarios']) || !empty($permisos['usuarios_roles']['gestionar_roles']))) {
                return true;
            }
            if (($accion === 'crear_usuarios' || $accion === 'editar_usuarios') && !empty($permisos['usuarios_roles']['ver'])) {
                return true;
            }
        }

        if ($accion === 'ver' && isset($permisos[$modulo]) && is_array($permisos[$modulo])) {
            foreach ($permisos[$modulo] as $act => $val) {
                if ($val) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Estructura completa de módulos y acciones soportados en el sistema CMMS.
     */
    public static function getAvailablePermissionsDefinition(): array
    {
        return [
            'activos' => [
                'label' => '📦 Gestión de Activos Industriales',
                'acciones' => [
                    'ver' => 'Ver Inventario & Fichas Técnicas',
                    'crear' => 'Registrar Nuevos Activos',
                    'editar' => 'Editar Información & Ajustar Datos',
                    'eliminar' => 'Dar de Baja Activos',
                ]
            ],
            'ordenes' => [
                'label' => '🛠️ Órdenes de Trabajo (OTs)',
                'acciones' => [
                    'ver' => 'Ver Listado y Detalle de OTs',
                    'crear' => 'Crear Solicitudes y OTs',
                    'asignar' => 'Asignar Técnicos & Prioridades',
                    'ejecutar' => 'Ejecutar, Pausar & Registrar Tiempos',
                    'eliminar' => 'Cancelar / Eliminar OTs',
                ]
            ],
            'planes' => [
                'label' => '📅 Mantenimiento Preventivo',
                'acciones' => [
                    'ver' => 'Ver Rutinas & Calendario Preventivo',
                    'crear' => 'Crear Planes Preventivos',
                    'ejecutar' => 'Ejecutar Planes Manualmente',
                    'eliminar' => 'Eliminar Planes Preventivos',
                ]
            ],
            'repuestos' => [
                'label' => '🏬 Inventario & Almacén de Repuestos',
                'acciones' => [
                    'ver' => 'Ver Stock y Catálogo de Repuestos',
                    'crear' => 'Crear Nuevos Repuestos SKU',
                    'editar' => 'Editar Datos de Repuestos',
                    'movimientos' => 'Registrar Entradas / Salidas de Almacén',
                ]
            ],
            'reportes' => [
                'label' => '📊 Reportes KPI & Analítica',
                'acciones' => [
                    'ver' => 'Ver Dashboard de Indicadores (MTBF/MTTR)',
                    'exportar' => 'Exportar Reportes a CSV/Excel',
                ]
            ],
            'usuarios_roles' => [
                'label' => '👥 Personal, Roles & Permisos',
                'acciones' => [
                    'ver' => 'Ver Directorio de Personal',
                    'crear_usuarios' => 'Registrar Nuevos Usuarios',
                    'editar_usuarios' => 'Editar Fichas de Usuarios & Claves',
                    'gestionar_roles' => 'Crear y Modificar Roles y Permisos',
                ]
            ],
        ];
    }
}
