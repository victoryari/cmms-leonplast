<?php

namespace App\Services;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Envía una notificación de OT Crítica al grupo de técnicos en Telegram.
     */
    public function sendCriticalWorkOrderAlert(WorkOrder $ot): void
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        // Si no está configurado, ignorar silenciosamente o loguear
        if (!$botToken || !$chatId) {
            Log::info('Telegram Bot no configurado. No se envió la notificación para OT: ' . $ot->codigo_ot);
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $activoNombre = $ot->activo ? $ot->activo->nombre : 'N/A';
        $ubicacion = $ot->activo ? $ot->activo->ubicacion : 'N/A';
        $solicitante = $ot->solicitante ? $ot->solicitante->nombre_completo : 'Generada por Sistema';

        $mensaje = "🚨 *NUEVA ORDEN CRÍTICA* 🚨\n\n";
        $mensaje .= "📋 *OT:* `{$ot->codigo_ot}`\n";
        $mensaje .= "🏭 *Activo:* {$activoNombre} (Ubic: {$ubicacion})\n";
        $mensaje .= "👤 *Solicitante:* {$solicitante}\n";
        $mensaje .= "🔧 *Problema:* {$ot->titulo}\n";
        $mensaje .= "📝 *Detalle:* {$ot->descripcion}\n\n";
        $mensaje .= "⚡ _Por favor, revisión inmediata en planta._";

        try {
            Http::post($url, [
                'chat_id' => $chatId,
                'text' => $mensaje,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación a Telegram: ' . $e->getMessage());
        }
    }
}
