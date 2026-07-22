<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    protected $table = 'repuestos';

    protected $fillable = [
        'codigo_sku',
        'nombre',
        'descripcion',
        'categoria',
        'subcategoria',
        'marca',
        'modelo',
        'especificaciones',
        'proveedor_principal',
        'proveedor_secundario',
        'codigo_proveedor',
        'url_proveedor',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'stock_seguridad',
        'ubicacion_almacen',
        'estante',
        'posicion',
        'costo_unitario',
        'costo_promedio',
        'moneda',
        'vida_util_promedio',
        'fecha_ultima_compra',
        'fecha_proxima_compra',
        'frecuencia_uso',
        'ficha_tecnica_url',
        'hoja_seguridad_url',
        'documentos',
        'imagenes',
        'activo',
        'es_critico',
        'observaciones',
        'notas_internas',
    ];

    protected $appends = [
        'is_low_stock',
        'stock_badge_color',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_critico' => 'boolean',
        'costo_unitario' => 'decimal:2',
        'costo_promedio' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'stock_seguridad' => 'integer',
        'fecha_ultima_compra' => 'date',
        'fecha_proxima_compra' => 'date',
    ];

    public function ordenesUsos(): HasMany
    {
        return $this->hasMany(WorkOrderSparePart::class, 'repuesto_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'repuesto_id');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function getStockBadgeColorAttribute(): string
    {
        if ($this->stock_actual <= 0) {
            return 'rose'; // Sin stock
        }
        if ($this->stock_actual <= $this->stock_minimo) {
            return 'amber'; // Stock bajo el mínimo
        }
        return 'emerald'; // Stock óptimo
    }

    /**
     * Registrar movimiento de Kárdex y actualizar stock de forma atómica
     */
    public function registrarMovimiento(
        string $tipoMovimiento,
        int $cantidad,
        ?string $motivo = null,
        ?string $docRef = null,
        ?int $otId = null,
        ?int $usuarioId = null
    ): InventoryMovement {
        $stockAnterior = $this->stock_actual;

        if (in_array($tipoMovimiento, ['Entrada', 'Devolucion'])) {
            $stockNuevo = $stockAnterior + $cantidad;
        } elseif (in_array($tipoMovimiento, ['Salida', 'Merma'])) {
            $stockNuevo = max(0, $stockAnterior - $cantidad);
        } else { // Ajuste
            $stockNuevo = $cantidad; // Para ajuste, cantidad representa el nuevo stock absoluto
        }

        $this->update(['stock_actual' => $stockNuevo]);

        return InventoryMovement::create([
            'repuesto_id' => $this->id,
            'usuario_id' => $usuarioId ?? auth()->id() ?? 1,
            'orden_trabajo_id' => $otId,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo' => $motivo,
            'documento_referencia' => $docRef,
        ]);
    }
}
