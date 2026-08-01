<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $modulo, string $accion = 'ver'): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect()->route('login')->with('error', 'Por favor inicie sesión.');
        }

        if (!$user->hasPermission($modulo, $accion)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Acceso denegado. No posee el permiso granular necesario para este módulo.',
                    'modulo_requerido' => $modulo,
                    'accion_requerida' => $accion,
                ], 403);
            }

            abort(403, "No posee el permiso necesario ({$modulo}:{$accion}) para acceder a esta sección del CMMS.");
        }

        return $next($request);
    }
}
