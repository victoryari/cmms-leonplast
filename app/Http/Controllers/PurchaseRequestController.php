<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudCompra;
use App\Models\SparePart;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudCompra::with(['repuesto', 'solicitante'])->orderBy('created_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $solicitudes = $query->paginate(15);
        return view('repuestos.recompras', compact('solicitudes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad_solicitada' => 'required|integer|min:1',
            'motivo' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $repuesto = SparePart::findOrFail($validated['repuesto_id']);

        SolicitudCompra::create([
            'codigo_solicitud' => 'SC-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'repuesto_id' => $repuesto->id,
            'cantidad_solicitada' => $validated['cantidad_solicitada'],
            'cantidad_sugerida' => $validated['cantidad_solicitada'],
            'motivo' => $validated['motivo'],
            'solicitado_por' => auth()->id(),
            'estado' => 'PENDIENTE',
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('recompras.index')
            ->with('success', "Solicitud de compra generada exitosamente para {$repuesto->nombre}.");
    }

    public function updateStatus(Request $request, $id)
    {
        $solicitud = SolicitudCompra::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:APROBADO,RECHAZADO,COMPRADO',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $solicitud->update([
            'estado' => $validated['estado'],
            'observaciones' => $validated['observaciones'] ? ($solicitud->observaciones . ' | ' . $validated['observaciones']) : $solicitud->observaciones,
        ]);

        // Si se marca como COMPRADO, registrar entrada al stock automáticamente
        if ($validated['estado'] === 'COMPRADO') {
            $repuesto = $solicitud->repuesto;
            $repuesto->registrarMovimiento(
                'Entrada',
                $solicitud->cantidad_solicitada,
                "Ingreso por Orden de Compra Recompra {$solicitud->codigo_solicitud}",
                $solicitud->codigo_solicitud,
                null,
                auth()->id()
            );
        }

        return redirect()->route('recompras.index')
            ->with('success', "Estado de solicitud {$solicitud->codigo_solicitud} actualizado a {$validated['estado']}.");
    }
}
