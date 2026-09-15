<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoTrabajoSeguro extends Model
{
    protected $table = 'permisos_trabajo_seguro';

    protected $fillable = [
        'orden_trabajo_id',
        'tipo_riesgo',
        'checklist_verificacion',
        'epp_requeridos',
        'bloqueo_loto_confirmado',
        'aprobado_por',
        'fecha_aprobacion',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'checklist_verificacion' => 'array',
        'epp_requeridos' => 'array',
        'bloqueo_loto_confirmado' => 'boolean',
        'fecha_aprobacion' => 'datetime',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
