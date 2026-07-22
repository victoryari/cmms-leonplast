<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'codigo_ot',
        'titulo',
        'descripcion',
        'activo_id',
        'solicitante_id',
        'tecnico_id',
        'supervisor_id',
        'tipo_ot',
        'prioridad',
        'estado',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'duracion_estimada_horas',
        'duracion_real_horas',
        'costo_estimado',
        'costo_real',
        'costo_repuestos',
        'costo_mano_obra',
        'diagnosticos',
        'soluciones',
        'observaciones_tecnico',
        'observaciones_cierre',
        'archivos',
        'fotos',
        'requiere_permiso_especial',
        'permisos_especiales',
        'checklist_seguridad',
        'calificacion_usuario',
        'comentario_usuario',
        'historial_estados',
        'creado_por',
        'activo',
    ];

    protected $appends = [
        'estado_color',
        'prioridad_color',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_permiso_especial' => 'boolean',
        'fecha_solicitud' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_fin_estimada' => 'datetime',
        'fecha_fin_real' => 'datetime',
        'duracion_estimada_horas' => 'decimal:2',
        'duracion_real_horas' => 'decimal:2',
        'costo_estimado' => 'decimal:2',
        'costo_real' => 'decimal:2',
        'costo_repuestos' => 'decimal:2',
        'costo_mano_obra' => 'decimal:2',
        'diagnosticos' => 'array',
        'soluciones' => 'array',
        'archivos' => 'array',
        'fotos' => 'array',
        'checklist_seguridad' => 'array',
        'historial_estados' => 'array',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'activo_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function laborTimes(): HasMany
    {
        return $this->hasMany(LaborTime::class, 'orden_trabajo_id');
    }

    public function spareParts(): HasMany
    {
        return $this->hasMany(WorkOrderSparePart::class, 'orden_trabajo_id');
    }

    /**
     * Helper para color de badge según el estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'Pendiente' => 'amber',
            'Aprobada' => 'blue',
            'En_Progreso' => 'indigo',
            'En_Pausa' => 'purple',
            'En_Revision' => 'cyan',
            'Completada' => 'emerald',
            'Cancelada' => 'rose',
            default => 'slate',
        };
    }

    /**
     * Helper para color de badge según la prioridad
     */
    public function getPrioridadColorAttribute(): string
    {
        return match ($this->prioridad) {
            'Baja' => 'slate',
            'Media' => 'blue',
            'Alta' => 'amber',
            'Crítica' => 'rose',
            default => 'slate',
        };
    }
}
