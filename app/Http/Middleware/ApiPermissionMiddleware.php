<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiPermissionMiddleware
{
    /**
     * Valida autorización granular para endpoints de la API móvil.
     *
     * Uso: middleware('api-permission:modulo:accion')
     *      middleware('api-permission:activos:ver,ordenes:ver')  // OR entre permisos
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$perms): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (empty($perms)) {
            return $next($request);
        }

        $allowed = false;
        foreach ($perms as $perm) {
            [$modulo, $accion] = array_pad(explode(':', $perm, 2), 2, 'ver');
            if ($user->hasPermission($modulo, $accion)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return response()->json([
                'message' => 'Acceso denegado. No posee el permiso requerido para esta operación.',
                'permisos_requeridos' => $perms,
            ], 403);
        }

        return $next($request);
    }
}
