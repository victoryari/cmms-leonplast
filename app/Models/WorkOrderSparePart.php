<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderSparePart extends Model
{
    protected $table = 'ordenes_repuestos';

    protected $fillable = [
        'orden_trabajo_id',
        'repuesto_id',
        'cantidad',
        'costo_unitario',
        'descuento',
        'total',
        'motivo_uso',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_id');
    }

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'repuesto_id');
    }
}
