<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCompra extends Model
{
    protected $table = 'solicitudes_compra';

    protected $fillable = [
        'codigo_solicitud',
        'repuesto_id',
        'cantidad_solicitada',
        'cantidad_sugerida',
        'motivo',
        'solicitado_por',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
        'cantidad_sugerida' => 'integer',
    ];

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'repuesto_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }
}
