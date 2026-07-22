<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta de usuario inactiva.'
            ], 403);
        }

        $user->update(['ultimo_acceso' => now()]);
        $deviceName = $request->input('device_name', 'flutter-mobile-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Autenticación exitosa.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nombre_completo' => $user->nombre_completo,
                'email' => $user->email,
                'codigo_empleado' => $user->codigo_empleado,
                'especialidad' => $user->especialidad,
                'rol' => $user->role?->nombre,
                'foto_perfil' => $user->foto_perfil,
            ]
        ]);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nombre_completo' => $user->nombre_completo,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'email' => $user->email,
                'codigo_empleado' => $user->codigo_empleado,
                'especialidad' => $user->especialidad,
                'telefono' => $user->telefono,
                'rol' => $user->role?->nombre,
                'foto_perfil' => $user->foto_perfil,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente en la App móvil.'
        ]);
    }
}
