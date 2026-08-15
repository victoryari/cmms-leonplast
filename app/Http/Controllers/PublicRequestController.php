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
        if (!$this->validarCodigoActivo($codigoQr)) {
            abort(404);
        }

        $activo = Asset::where('codigo_activo', $codigoQr)
            ->orWhere('qr_code_content', $codigoQr)
            ->where('activo', true)
            ->firstOrFail();

        return view('public_requests.create', compact('activo'));
    }

    /**
     * Procesa la solicitud pública de mantenimiento emitida sin inicio de sesión
     */
    public function store(Request $request, $codigoQr)
    {
        if (!$this->validarCodigoActivo($codigoQr)) {
            abort(404);
        }

        $activo = Asset::where('codigo_activo', $codigoQr)
            ->orWhere('qr_code_content', $codigoQr)
            ->where('activo', true)
            ->firstOrFail();

        $validated = $request->validate([
            'nombre_solicitante' => 'required|string|max:150',
            'area_turno' => 'required|string|max:150',
            'contacto' => 'nullable|string|max:100',
            'prioridad' => 'required|in:Baja,Media,Alta,Critica',
            'descripcion' => 'required|string|min:10',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240', // Max 10MB
        ]);

        $codigoOt = WorkOrder::nextCodigoOt();

        $esEmergencia = in_array($validated['prioridad'], ['Alta', 'Critica']);

        $orden = WorkOrder::create([
            'codigo_ot' => $codigoOt,
            'token_seguimiento' => Str::random(40),
            'activo_id' => $activo->id,
            'solicitante_id' => auth()->id() ?? null,
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
            $publicUrl = "/storage/" . $path;
            $fotos = $orden->fotos ?? ['antes' => [], 'despues' => []];
            if (!isset($fotos['antes']) || !is_array($fotos['antes'])) {
                $fotos['antes'] = [];
            }
            if (!isset($fotos['despues']) || !is_array($fotos['despues'])) {
                $fotos['despues'] = [];
            }
            $fotos['antes'][] = $publicUrl;
            $orden->update(['fotos' => $fotos]);
        }

        // Notificar inmediatamente a los supervisores sobre la avería reportada
        app(NotificationService::class)->notifySupervisorBreakdown($orden);

        return redirect()->route('public.track', $orden->token_seguimiento)
            ->with('success', '¡Tu reporte de avería ha sido recibido por el equipo de mantenimiento!');
    }

    /**
     * Muestra la pantalla pública de seguimiento de la OT para el operario
     */
    public function track($token)
    {
        $orden = WorkOrder::with(['equipo', 'activo', 'tecnico'])
            ->where('token_seguimiento', $token)
            ->firstOrFail();

        return view('public_requests.track', compact('orden'));
    }

    /**
     * Valida que el parámetro de la ruta pública corresponda a un código de activo
     * real (no un ID numérico) para evitar enumeración por secuencia.
     */
    private function validarCodigoActivo(string $codigoQr): bool
    {
        if (ctype_digit($codigoQr)) {
            return false;
        }

        return (bool) preg_match('/^[A-Z0-9\-\/_.]+$/i', $codigoQr);
    }
}
