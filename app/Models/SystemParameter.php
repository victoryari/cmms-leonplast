<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemParameter extends Model
{
    protected $table = 'parametros_sistema';

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'descripcion',
        'tipo_dato',
        'editable',
    ];

    protected $casts = [
        'editable' => 'boolean',
    ];

    /**
     * Obtiene la lista de elementos en formato array para un catálogo por clave/grupo
     */
    public static function getValoresGrupo(string $clave, array $default = []): array
    {
        $param = self::where('clave', $clave)->first();
        if (!$param || empty($param->valor)) {
            return $default;
        }

        $decoded = json_decode($param->valor, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return array_values(array_filter(array_map('trim', explode(',', $param->valor))));
    }

    /**
     * Guarda la lista de elementos en formato JSON para un catálogo
     */
    public static function setValoresGrupo(string $clave, array $valores, string $grupo = 'Catálogos', ?string $descripcion = null): self
    {
        $valores = array_values(array_unique(array_filter(array_map('trim', $valores))));

        return self::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => json_encode($valores, JSON_UNESCAPED_UNICODE),
                'grupo' => $grupo,
                'descripcion' => $descripcion ?? "Catálogo dinámico de {$clave}",
                'tipo_dato' => 'JSON',
                'editable' => true,
            ]
        );
    }
}
