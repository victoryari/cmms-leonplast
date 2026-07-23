<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\WorkOrder;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notifica al técnico asignado que tiene una nueva Orden de Trabajo
     */
    public function notifyTechnicianAssigned(WorkOrder $ot): ?Notification
    {
        if (!$ot->tecnico_id) {
            return null;
        }

        $tecnico = User::find($ot->tecnico_id);
        if (!$tecnico) {
            return null;
        }

        $equipoNombre = $ot->equipo?->nombre ?? 'Equipo de Planta';
        $titulo = "🚨 OT Asignada: {$ot->codigo_ot}";
        $mensaje = "Se te ha asignado el trabajo en: {$equipoNombre}. Prioridad: {$ot->prioridad}.";

        $notificacion = Notification::create([
            'usuario_id' => $tecnico->id,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => 'OT_Asignada',
            'orden_trabajo_id' => $ot->id,
            'url_accion' => "/ordenes-trabajo/{$ot->id}",
            'leido' => false,
            'data_json' => [
                'codigo_ot' => $ot->codigo_ot,
                'prioridad' => $ot->prioridad,
                'activo_nombre' => $equipoNombre,
                'fecha' => now()->toIso8601String(),
            ],
        ]);

        // Si el técnico tiene registrado el FCM token de su smartphone, enviar notificación Push
        if (!empty($tecnico->fcm_token)) {
            $this->sendPushFcm($tecnico->fcm_token, $titulo, $mensaje, [
                'type' => 'OT_ASSIGNED',
                'order_id' => (string) $ot->id,
                'codigo_ot' => $ot->codigo_ot,
            ]);
        }

        return $notificacion;
    }

    /**
     * Notifica a los supervisores cuando se reporta una avería por QR o máquina parada
     */
    public function notifySupervisorBreakdown(WorkOrder $ot): void
    {
        $supervisores = User::whereHas('role', fn($q) => $q->whereIn('nombre', ['Supervisor', 'Gerente_Mantenimiento', 'Administrador']))->get();
        $equipoNombre = $ot->equipo?->nombre ?? 'Equipo de Planta';
        $titulo = "⚠️ Avería Reportada: {$ot->codigo_ot}";
        $mensaje = "Nuevo reporte de avería en máquina {$equipoNombre}. Prioridad: {$ot->prioridad}.";

        foreach ($supervisores as $sup) {
            Notification::create([
                'usuario_id' => $sup->id,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => 'Averia_QR',
                'orden_trabajo_id' => $ot->id,
                'url_accion' => "/ordenes-trabajo/{$ot->id}",
                'leido' => false,
                'data_json' => [
                    'codigo_ot' => $ot->codigo_ot,
                    'prioridad' => $ot->prioridad,
                ],
            ]);

            if (!empty($sup->fcm_token)) {
                $this->sendPushFcm($sup->fcm_token, $titulo, $mensaje, [
                    'type' => 'BREAKDOWN_REPORTED',
                    'order_id' => (string) $ot->id,
                ]);
            }
        }
    }

    /**
     * Envía payload Push via Firebase Cloud Messaging (FCM API)
     */
    public function sendPushFcm(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.firebase.server_key') ?? env('FCM_SERVER_KEY');
        if (empty($serverKey)) {
            Log::info("Notificación Push simulada para FCM Token [{$fcmToken}]: {$title} - {$body}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Error enviando FCM Push: " . $e->getMessage());
            return false;
        }
    }
}
