<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'nombres',
        'apellidos',
        'documento_identidad',
        'telefono',
        'email',
        'direccion',
        'password_hash',
        'codigo_empleado',
        'especialidad',
        'sueldo_mensual',
        'costo_hora',
        'fecha_ingreso',
        'activo',
        'fcm_token',
        'email_verificado',
        'ultimo_acceso',
        'foto_perfil',
        'preferencias',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verificado' => 'boolean',
        'fecha_ingreso' => 'date',
        'ultimo_acceso' => 'datetime',
        'preferencias' => 'array',
        'sueldo_mensual' => 'decimal:2',
        'costo_hora' => 'decimal:2',
    ];

    /**
     * Obtiene el costo hora calculado del empleado (o tarifa fija por defecto si no se especificó)
     */
    public function getCostoHoraCalculadoAttribute(): float
    {
        if ($this->costo_hora && $this->costo_hora > 0) {
            return (float) $this->costo_hora;
        }

        if ($this->sueldo_mensual && $this->sueldo_mensual > 0) {
            return round((float) $this->sueldo_mensual / 208, 2);
        }

        return 25.00; // Tarifa base por defecto
    }

    /**
     * Sobrescribir el campo de contraseña para la autenticación de Laravel
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Accesor para el nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Relación con la tabla roles
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    /**
     * Helper para verificar roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        $roleName = $this->role?->nombre;
        if (!$roleName) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($roleName, $roles, true);
        }

        return $roleName === $roles;
    }

    /**
     * Verifica si el usuario posee un permiso específico en un módulo.
     */
    public function hasPermission(string $modulo, string $accion): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        return $this->role ? $this->role->hasPermission($modulo, $accion) : false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Gerente_Mantenimiento');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('Supervisor');
    }

    public function isTechnician(): bool
    {
        return $this->hasRole('Tecnico');
    }

    public function isRequester(): bool
    {
        return $this->hasRole('Solicitante');
    }

    public function ordenesSolicitadas(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'solicitante_id');
    }

    public function ordenesAsignadas(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'tecnico_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notification::class, 'usuario_id');
    }
}
