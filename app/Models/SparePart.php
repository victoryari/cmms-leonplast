<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    protected $table = 'repuestos';

    protected $fillable = [
        'codigo_sku',
        'nombre',
        'descripcion',
        'categoria',
        'subcategoria',
        'marca',
        'modelo',
        'especificaciones',
        'proveedor_principal',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'ubicacion_almacen',
        'costo_unitario',
        'moneda',
        'activo',
        'es_critico',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_critico' => 'boolean',
        'costo_unitario' => 'decimal:2',
        'stock_actual' => 'integer',
    ];

    public function ordenesUsos(): HasMany
    {
        return $this->hasMany(WorkOrderSparePart::class, 'repuesto_id');
    }
}
