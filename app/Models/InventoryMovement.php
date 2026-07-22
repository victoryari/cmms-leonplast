<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'repuesto_id',
        'usuario_id',
        'orden_trabajo_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'documento_referencia',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'repuesto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'orden_trabajo_id');
    }

    public function getTipoMovimientoColorAttribute(): string
    {
        return match ($this->tipo_movimiento) {
            'Entrada' => 'emerald',
            'Salida' => 'rose',
            'Ajuste' => 'amber',
            'Traslado' => 'blue',
            'Devolucion' => 'cyan',
            'Merma' => 'purple',
            default => 'slate',
        };
    }
}
