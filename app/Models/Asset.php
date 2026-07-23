<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $table = 'activos';

    protected $fillable = [
        'codigo_activo',
        'nombre',
        'descripcion',
        'categoria',
        'marca',
        'modelo',
        'numero_serie',
        'especificaciones_tecnicas',
        'manual_url',
        'proveedor_id',
        'fecha_adquisicion',
        'costo_adquisicion',
        'vida_util_estimada',
        'garantia_vencimiento',
        'ubicacion',
        'area',
        'estado_operativo',
        'estado_condicion',
        'mtbf_horas',
        'mttr_horas',
        'disponibilidad_porcentaje',
        'qr_code_url',
        'qr_code_content',
        'documentos',
        'imagenes',
        'creado_por',
        'activo',
        'observaciones',
    ];

    protected $appends = [
        'qr_image_url',
        'estado_operativo_color',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_adquisicion' => 'date',
        'garantia_vencimiento' => 'date',
        'costo_adquisicion' => 'decimal:2',
        'mtbf_horas' => 'decimal:2',
        'mttr_horas' => 'decimal:2',
        'disponibilidad_porcentaje' => 'decimal:2',
        'especificaciones_tecnicas' => 'array',
        'documentos' => 'array',
        'imagenes' => 'array',
    ];

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'activo_id');
    }

    public function preventivePlans(): HasMany
    {
        return $this->hasMany(PreventivePlan::class, 'activo_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Color para badge de estado operativo
     */
    public function getEstadoOperativoColorAttribute(): string
    {
        return match ($this->estado_operativo) {
            'Operativo' => 'emerald',
            'Mantenimiento' => 'amber',
            'Reparacion' => 'rose',
            'Fuera_de_servicio' => 'purple',
            'Baja' => 'slate',
            default => 'blue',
        };
    }

    /**
     * Generar URL para la imagen del código QR codificando el enlace web directo a la Solicitud Rápida
     */
    public function getQrImageUrlAttribute(): string
    {
        $targetUrl = route('public.create', $this->codigo_activo);
        $encodedUrl = urlencode($targetUrl);
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedUrl}";
    }
}
