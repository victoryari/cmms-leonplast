<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('activos', 'editar');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'tipo_clasificacion' => 'required|in:Ubicacion,Equipo,Herramienta,Repuesto_Suministro,Digital',
            'parent_id' => 'nullable|exists:activos,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'proveedor_id' => 'nullable|exists:terceros,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'estado_operativo' => 'required|string',
            'estado_condicion' => 'required|string',
            'costo_adquisicion' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_estimada' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'eliminar_imagen' => 'nullable|boolean',
        ];
    }
}
