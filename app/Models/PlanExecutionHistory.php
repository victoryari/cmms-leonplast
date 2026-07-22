<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanExecutionHistory extends Model
{
    protected $table = 'historial_ejecucion_planes';

    const UPDATED_AT = null; // Solo utiliza created_at

    protected $fillable = [
        'plan_preventivo_id',
        'orden_trabajo_generada_id',
        'fecha_ejecucion',
        'tipo_ejecucion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_ejecucion' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PreventivePlan::class, 'plan_preventivo_id');
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_generada_id');
    }
}
