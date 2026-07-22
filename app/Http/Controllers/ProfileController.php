<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return view('perfil.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'especialidad' => 'nullable|string|max:100',
        ]);

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if (!Hash::check($request->input('current_password'), $user->password_hash)) {
                return back()->withErrors(['current_password' => 'La contraseña actual ingresada es incorrecta.']);
            }

            $validated['password_hash'] = Hash::make($request->input('password'));
        }

        $user->update($validated);

        return redirect()->route('perfil.index')
            ->with('success', 'Tu perfil y datos de contacto han sido actualizados exitosamente.');
    }
}
