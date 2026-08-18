<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'parent_id',
        'codigo_ubicacion',
        'nombre',
        'empresa_subsidiaria',
        'tipo',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'codigo_postal',
        'latitud',
        'longitud',
        'prioridad',
        'centro_costo',
        'presupuesto_anual',
        'codigo_barras_nfc',
        'qr_code_url',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'presupuesto_anual' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Asset::class, 'ubicacion_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'Sede_Principal' => '🏭 Sede Principal / Planta Central',
            'Planta_Industrial' => '⚙️ Planta Industrial',
            'Almacen_Deposito' => '🏬 Almacén / Depósito Central',
            'Oficina_Regional' => '🏢 Oficina Regional de Ventas',
            'Empresa_Subsidiaria' => '🌐 Empresa Subsidiaria',
            'Area_Planta' => '📍 Área de Planta',
            default => str_replace('_', ' ', $this->tipo),
        };
    }

    public function getQrImageUrlAttribute(): string
    {
        $encodedData = urlencode($this->codigo_ubicacion);
        return "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={$encodedData}";
    }
}
