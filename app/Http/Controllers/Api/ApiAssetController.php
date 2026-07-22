<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;

class ApiAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->input('estado_operativo')) {
            $query->where('estado_operativo', $estado);
        }

        $activos = $query->orderBy('codigo_activo', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => $activos->count(),
            'data' => $activos
        ]);
    }

    public function show($id)
    {
        $activo = Asset::with(['ordenesTrabajo' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }])->find($id);

        if (!$activo) {
            return response()->json([
                'success' => false,
                'message' => 'Activo no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activo
        ]);
    }

    public function findByQr(string $codigo)
    {
        $activo = Asset::where('codigo_activo', $codigo)
            ->orWhere('qr_code_content', $codigo)
            ->first();

        if (!$activo) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró ningún activo con el código QR '{$codigo}'."
            ], 404);
        }

        $activo->load(['ordenesTrabajo' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Activo identificado mediante código QR.',
            'activo' => $activo
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado_operativo' => 'required|in:Operativo,Mantenimiento,Reparacion,Fuera_de_servicio,Baja',
            'observaciones' => 'nullable|string',
        ]);

        $activo = Asset::find($id);

        if (!$activo) {
            return response()->json(['success' => false, 'message' => 'Activo no encontrado.'], 404);
        }

        $activo->update([
            'estado_operativo' => $request->input('estado_operativo'),
            'observaciones' => $request->input('observaciones', $activo->observaciones),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Estado de {$activo->codigo_activo} actualizado a {$activo->estado_operativo}.",
            'activo' => $activo
        ]);
    }
}
