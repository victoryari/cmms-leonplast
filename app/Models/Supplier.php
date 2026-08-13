<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'terceros';

    protected $fillable = [
        'ruc_documento',
        'razon_social',
        'nombre_comercial',
        'tipo',
        'contacto_nombre',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'calificacion',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'calificacion' => 'integer',
    ];

    public function activos(): HasMany
    {
        return $this->hasMany(Asset::class, 'proveedor_id');
    }

    public function repuestos(): HasMany
    {
        return $this->hasMany(SparePart::class, 'proveedor_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'Proveedor_Repuestos' => '🏬 Proveedor de Repuestos & Suministros',
            'Contratista_Servicios' => '🛠️ Contratista de Servicios de Mantenimiento',
            'Ambos' => '🌐 Proveedor & Contratista Integral',
            default => str_replace('_', ' ', $this->tipo),
        };
    }
}
