<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PreventivePlan extends Model
{
    protected $table = 'planes_preventivos';

    protected $fillable = [
        'activo_id',
        'nombre_plan',
        'descripcion',
        'tipo_plan',
        'frecuencia_dias',
        'frecuencia_meses',
        'dia_mes',
        'dia_semana',
        'unidad_medicion',
        'medidor_nombre',
        'umbral_medidor',
        'umbral_minimo',
        'umbral_maximo',
        'titulo_ot_generada',
        'descripcion_ot_generada',
        'instrucciones_especificas',
        'tecnico_asignado_id',
        'prioridad_defecto',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'ultima_ejecucion',
        'proxima_ejecucion',
        'creado_por',
        'observaciones',
    ];

    protected $appends = [
        'frecuencia_texto',
        'estado_color',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'ultima_ejecucion' => 'datetime',
        'proxima_ejecucion' => 'datetime',
        'umbral_medidor' => 'decimal:2',
        'frecuencia_dias' => 'integer',
        'frecuencia_meses' => 'integer',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'activo_id');
    }

    public function tecnicoAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_asignado_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function historialEjecuciones(): HasMany
    {
        return $this->hasMany(PlanExecutionHistory::class, 'plan_preventivo_id');
    }

    public function getFrecuenciaTextoAttribute(): string
    {
        if ($this->tipo_plan === 'Por_Medidor') {
            return "Cada {$this->umbral_medidor} {$this->unidad_medicion}";
        }

        if ($this->frecuencia_dias) {
            return "Cada {$this->frecuencia_dias} días";
        }

        if ($this->frecuencia_meses) {
            return "Cada {$this->frecuencia_meses} meses";
        }

        return "Programación Especial";
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'Activo' => 'emerald',
            'Pausado' => 'amber',
            'Completado' => 'blue',
            'Cancelado' => 'rose',
            default => 'slate',
        };
    }

    public function calcularProximaFecha(): Carbon
    {
        $base = $this->ultima_ejecucion ? Carbon::parse($this->ultima_ejecucion) : now();

        if ($this->frecuencia_dias) {
            return (clone $base)->addDays($this->frecuencia_dias);
        }

        if ($this->frecuencia_meses) {
            return (clone $base)->addMonths($this->frecuencia_meses);
        }

        return (clone $base)->addDays(30);
    }
}
