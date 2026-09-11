<?php

namespace App\Enums;

enum SystemRole: string
{
    case Admin = 'Administrador';
    case Manager = 'Gerente_Mantenimiento';
    case Supervisor = 'Supervisor';
    case Technician = 'Tecnico';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
