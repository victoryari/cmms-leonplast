<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notificaciones = Notification::where('usuario_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function markAsRead($id)
    {
        $notificacion = Notification::where('usuario_id', auth()->id())->findOrFail($id);
        $notificacion->update(['leido' => true]);

        if ($notificacion->url_accion) {
            return redirect($notificacion->url_accion);
        }

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function markAllAsRead()
    {
        Notification::where('usuario_id', auth()->id())->where('leido', false)->update(['leido' => true]);

        return back()->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }
}
