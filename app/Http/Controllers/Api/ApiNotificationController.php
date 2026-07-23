<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class ApiNotificationController extends Controller
{
    /**
     * Retorna el listado de notificaciones pendientes para el celular del técnico
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notificaciones = Notification::where('usuario_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $unreadCount = Notification::where('usuario_id', $user->id)
            ->where('leido', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notificaciones,
        ]);
    }

    /**
     * Marca una notificación como leída desde la App móvil
     */
    public function markAsRead(Request $request, $id)
    {
        $notificacion = Notification::where('usuario_id', $request->user()->id)->findOrFail($id);
        $notificacion->update(['leido' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída.',
        ]);
    }

    /**
     * Registra o actualiza el FCM token del smartphone del técnico
     */
    public function updateFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $validated['fcm_token']]);

        return response()->json([
            'success' => true,
            'message' => 'Token FCM de celular registrado correctamente.',
        ]);
    }
}
