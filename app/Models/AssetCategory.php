<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    protected $table = 'categorias_activos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function activos(): HasMany
    {
        return $this->hasMany(Asset::class, 'categoria', 'nombre');
    }
}
