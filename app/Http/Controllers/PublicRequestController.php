<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class PublicRequestController extends Controller
{
    /**
     * Muestra el formulario público de reporte de averías al escanear el QR del activo
     */
    public function create($codigoQr)
    {
        $activo = Asset::where('codigo_activo', $codigoQr)
            ->orWhere('qr_code_content', $codigoQr)
            ->orWhere('id', $codigoQr)
            ->firstOrFail();

        return view('public_requests.create', compact('activo'));
    }

    /**
     * Procesa la solicitud pública de mantenimiento emitida sin inicio de sesión
     */
    public function store(Request $request, $codigoQr)
    {
        $activo = Asset::where('codigo_activo', $codigoQr)
            ->orWhere('qr_code_content', $codigoQr)
            ->orWhere('id', $codigoQr)
            ->firstOrFail();

        $validated = $request->validate([
            'nombre_solicitante' => 'required|string|max:150',
            'area_turno' => 'required|string|max:150',
            'contacto' => 'nullable|string|max:100',
            'prioridad' => 'required|in:Baja,Media,Alta,Critica',
            'descripcion' => 'required|string|min:10',
            'foto' => 'nullable|image|max:10240', // Max 10MB
        ]);

        // Asignar al usuario solicitante por defecto de sistema
        $solicitanteDefault = User::whereHas('role', fn($q) => $q->where('nombre', 'Solicitante'))->first() 
            ?? User::first();

        $year = date('Y');
        $lastOrder = WorkOrder::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $nextNum = $lastOrder ? ((int) Str::afterLast($lastOrder->codigo_ot, '-')) + 1 : 1;
        $codigoOt = sprintf("OT-%s-%03d", $year, $nextNum);

        $esEmergencia = in_array($validated['prioridad'], ['Alta', 'Critica']);

        $orden = WorkOrder::create([
            'codigo_ot' => $codigoOt,
            'activo_id' => $activo->id,
            'solicitante_id' => $solicitanteDefault->id,
            'tipo_ot' => $esEmergencia ? 'Urgente' : 'Correctivo',
            'prioridad' => $validated['prioridad'],
            'estado' => 'Pendiente',
            'titulo' => "Reporte de Avería QR: " . Str::limit($validated['descripcion'], 40),
            'descripcion' => "Solicitante en Planta: {$validated['nombre_solicitante']} ({$validated['area_turno']})\n" .
                             "Contacto: " . ($validated['contacto'] ?? 'No especificado') . "\n\n" .
                             "Detalle del problema:\n" . $validated['descripcion'],
            'fecha_solicitud' => now(),
            'activo' => true,
        ]);

        // Guardar foto del fallo si el operario la capturó con su celular
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_ots', 'public');
            $fotosArray = $orden->fotos ?? [];
            $fotosArray[] = [
                'url_foto' => "/storage/" . $path,
                'tipo' => 'Antes',
                'subido_por' => $validated['nombre_solicitante'],
                'fecha' => now()->toIso8601String(),
            ];
            $orden->update(['fotos' => $fotosArray]);
        }

        // Notificar inmediatamente a los supervisores sobre la avería reportada
        app(NotificationService::class)->notifySupervisorBreakdown($orden);

        return redirect()->route('public.track', $orden->codigo_ot)
            ->with('success', '¡Tu reporte de avería ha sido recibido por el equipo de mantenimiento!');
    }

    /**
     * Muestra la pantalla pública de seguimiento de la OT para el operario
     */
    public function track($codigoOt)
    {
        $orden = WorkOrder::with(['equipo', 'tecnico'])->where('codigo_ot', $codigoOt)->firstOrFail();

        return view('public_requests.track', compact('orden'));
    }
}
