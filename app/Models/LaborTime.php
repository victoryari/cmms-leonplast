<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborTime extends Model
{
    protected $table = 'tiempos_mano_obra';

    protected $fillable = [
        'orden_trabajo_id',
        'tecnico_id',
        'fecha_inicio',
        'fecha_pausa',
        'fecha_reanudacion',
        'fecha_fin',
        'horas_trabajadas',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_pausa' => 'datetime',
        'fecha_reanudacion' => 'datetime',
        'fecha_fin' => 'datetime',
        'horas_trabajadas' => 'decimal:2',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}
