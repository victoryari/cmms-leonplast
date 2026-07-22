<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SparePart;
use App\Models\InventoryMovement;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePart::where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_sku', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('proveedor_principal', 'like', "%{$search}%");
            });
        }

        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($request->boolean('solo_bajo_stock')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $repuestos = $query->orderBy('nombre', 'asc')->paginate(12)->withQueryString();

        $metrics = [
            'total_items' => SparePart::where('activo', true)->count(),
            'valoracion_total' => SparePart::where('activo', true)->selectRaw('SUM(stock_actual * costo_unitario) as total')->value('total') ?? 0,
            'alertas_stock' => SparePart::where('activo', true)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'criticos_sin_stock' => SparePart::where('activo', true)->where('es_critico', true)->where('stock_actual', '<=', 0)->count(),
        ];

        $categorias = SparePart::where('activo', true)->distinct()->pluck('categoria')->filter();

        return view('repuestos.index', compact('repuestos', 'metrics', 'categorias'));
    }

    public function create()
    {
        return view('repuestos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_sku' => 'required|string|max:50|unique:repuestos,codigo_sku',
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'proveedor_principal' => 'nullable|string|max:200',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:1',
            'ubicacion_almacen' => 'required|string|max:100',
            'estante' => 'nullable|string|max:50',
            'posicion' => 'nullable|string|max:50',
            'costo_unitario' => 'required|numeric|min:0',
            'moneda' => 'required|in:PEN,USD',
            'es_critico' => 'nullable|boolean',
            'descripcion' => 'nullable|string',
        ]);

        $validated['activo'] = true;
        $repuesto = SparePart::create($validated);

        // Movimiento inicial de Kárdex
        if ($repuesto->stock_actual > 0) {
            InventoryMovement::create([
                'repuesto_id' => $repuesto->id,
                'usuario_id' => auth()->id(),
                'tipo_movimiento' => 'Entrada',
                'cantidad' => $repuesto->stock_actual,
                'stock_anterior' => 0,
                'stock_nuevo' => $repuesto->stock_actual,
                'motivo' => 'Inventario Inicial de Registro de Repuesto',
                'documento_referencia' => 'ALTA-INICIAL',
            ]);
        }

        return redirect()->route('repuestos.show', $repuesto->id)
            ->with('success', "Repuesto {$repuesto->nombre} registrado correctamente en almacén.");
    }

    public function show($id)
    {
        $repuesto = SparePart::with(['movimientos.usuario', 'movimientos.ordenTrabajo'])->findOrFail($id);
        $movimientos = $repuesto->movimientos()->orderBy('created_at', 'desc')->paginate(15);

        return view('repuestos.show', compact('repuesto', 'movimientos'));
    }

    public function edit($id)
    {
        $repuesto = SparePart::findOrFail($id);
        return view('repuestos.edit', compact('repuesto'));
    }

    public function update(Request $request, $id)
    {
        $repuesto = SparePart::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'proveedor_principal' => 'nullable|string|max:200',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:1',
            'ubicacion_almacen' => 'required|string|max:100',
            'estante' => 'nullable|string|max:50',
            'posicion' => 'nullable|string|max:50',
            'costo_unitario' => 'required|numeric|min:0',
            'moneda' => 'required|in:PEN,USD',
            'es_critico' => 'nullable|boolean',
            'descripcion' => 'nullable|string',
        ]);

        $repuesto->update($validated);

        return redirect()->route('repuestos.show', $repuesto->id)
            ->with('success', "Datos de {$repuesto->nombre} actualizados correctamente.");
    }

    public function registerMovement(Request $request, $id)
    {
        $repuesto = SparePart::findOrFail($id);

        $validated = $request->validate([
            'tipo_movimiento' => 'required|in:Entrada,Salida,Ajuste,Devolucion,Merma',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string',
            'documento_referencia' => 'nullable|string|max:100',
        ]);

        if ($validated['tipo_movimiento'] === 'Salida' && $repuesto->stock_actual < $validated['cantidad']) {
            return back()->with('error', "Stock insuficiente. Existencias actuales: {$repuesto->stock_actual} unidades.");
        }

        $repuesto->registrarMovimiento(
            $validated['tipo_movimiento'],
            $validated['cantidad'],
            $validated['motivo'],
            $validated['documento_referencia'],
            null,
            auth()->id()
        );

        return redirect()->route('repuestos.show', $repuesto->id)
            ->with('success', "Movimiento de Kárdex ({$validated['tipo_movimiento']}) registrado exitosamente.");
    }
}
