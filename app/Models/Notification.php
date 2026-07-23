<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'mensaje',
        'tipo',
        'orden_trabajo_id',
        'url_accion',
        'leido',
        'data_json',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'data_json' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_id');
    }
}
