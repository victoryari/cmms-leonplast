<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApiUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->where('activo', true);

        if ($rol = $request->input('rol')) {
            $query->whereHas('role', fn($q) => $q->where('nombre', $rol));
        }

        $usuarios = $query->orderBy('nombres', 'asc')->get(['id', 'rol_id', 'nombres', 'apellidos', 'codigo_empleado', 'especialidad', 'email', 'telefono']);

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'nombre_completo' => $user->nombre_completo,
                'email' => $user->email,
                'codigo_empleado' => $user->codigo_empleado,
                'especialidad' => $user->especialidad,
                'telefono' => $user->telefono,
                'rol' => $user->role?->nombre,
                'descripcion_rol' => $user->role?->descripcion,
                'permisos' => [
                    'es_admin' => $user->isAdmin(),
                    'es_gerente' => $user->isManager(),
                    'es_supervisor' => $user->isSupervisor(),
                    'es_tecnico' => $user->isTechnician(),
                    'es_solicitante' => $user->isRequester(),
                ]
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'nullable|string|max:100',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'required|string|min:6']);
            $user->password_hash = Hash::make($request->input('password'));
        }

        if ($request->has('telefono')) {
            $user->telefono = $request->input('telefono');
        }
        if ($request->has('especialidad')) {
            $user->especialidad = $request->input('especialidad');
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente desde la App móvil.',
            'user' => $user
        ]);
    }
}
