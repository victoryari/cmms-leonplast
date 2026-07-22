<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;

class ApiWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = WorkOrder::with(['activo', 'solicitante', 'supervisor']);

        if ($user->isTechnician()) {
            $query->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $query->where('solicitante_id', $user->id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $workOrders = $query->orderBy('updated_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    public function findAssetByQr(Request $request, string $codigo)
    {
        $asset = Asset::where('codigo_activo', $codigo)
            ->orWhere('qr_code_content', $codigo)
            ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Activo no encontrado con el código QR proporcionado.'
            ], 404);
        }

        $asset->load(['ordenesTrabajo' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }]);

        return response()->json([
            'success' => true,
            'asset' => $asset
        ]);
    }
}
