<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SparePart;
use App\Models\InventoryMovement;

class ApiSparePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePart::where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_sku', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('solo_bajo_stock')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $repuestos = $query->orderBy('nombre', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $repuestos
        ]);
    }

    public function alerts()
    {
        $alertas = SparePart::where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('es_critico', 'desc')
            ->orderBy('stock_actual', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $alertas->count(),
            'data' => $alertas
        ]);
    }

    public function show($id)
    {
        $repuesto = SparePart::with(['movimientos' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }])->find($id);

        if (!$repuesto) {
            return response()->json(['success' => false, 'message' => 'Repuesto no encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $repuesto
        ]);
    }

    public function registerMovement(Request $request, $id)
    {
        $request->validate([
            'tipo_movimiento' => 'required|in:Entrada,Salida,Ajuste,Devolucion,Merma',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string',
            'documento_referencia' => 'nullable|string|max:100',
        ]);

        $repuesto = SparePart::find($id);

        if (!$repuesto) {
            return response()->json(['success' => false, 'message' => 'Repuesto no encontrado.'], 404);
        }

        if ($request->input('tipo_movimiento') === 'Salida' && $repuesto->stock_actual < $request->input('cantidad')) {
            return response()->json(['success' => false, 'message' => "Stock insuficiente. Existencias: {$repuesto->stock_actual}"], 400);
        }

        $movimiento = $repuesto->registrarMovimiento(
            $request->input('tipo_movimiento'),
            $request->input('cantidad'),
            $request->input('motivo'),
            $request->input('documento_referencia'),
            null,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "Movimiento de Kárdex ({$request->input('tipo_movimiento')}) registrado desde Flutter.",
            'nuevo_stock' => $repuesto->stock_actual,
            'movimiento' => $movimiento
        ]);
    }
}
