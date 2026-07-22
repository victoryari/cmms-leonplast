<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('codigo_empleado', 'like', "%{$search}%");
            });
        }

        if ($rolId = $request->input('rol_id')) {
            $query->where('rol_id', $rolId);
        }

        if ($request->has('estado')) {
            $query->where('activo', $request->boolean('estado'));
        }

        $usuarios = $query->orderBy('id', 'asc')->paginate(12)->withQueryString();
        $roles = Role::where('activo', true)->get();

        $metrics = [
            'total_usuarios' => User::count(),
            'activos' => User::where('activo', true)->count(),
            'tecnicos' => User::whereHas('role', fn($q) => $q->where('nombre', 'Tecnico'))->count(),
            'supervisores' => User::whereHas('role', fn($q) => $q->whereIn('nombre', ['Supervisor', 'Gerente_Mantenimiento']))->count(),
        ];

        return view('usuarios.index', compact('usuarios', 'roles', 'metrics'));
    }

    public function create()
    {
        $roles = Role::where('activo', true)->get();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'codigo_empleado' => 'required|string|max:50|unique:usuarios,codigo_empleado',
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'nullable|string|max:100',
            'fecha_ingreso' => 'nullable|date',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['password_hash'] = Hash::make($validated['password']);
        $validated['activo'] = true;
        unset($validated['password']);

        $usuario = User::create($validated);

        return redirect()->route('usuarios.show', $usuario->id)
            ->with('success', "Usuario {$usuario->nombre_completo} registrado exitosamente.");
    }

    public function show($id)
    {
        $usuario = User::with('role')->findOrFail($id);

        $otsAsignadas = \App\Models\WorkOrder::where('tecnico_id', $usuario->id)->orderBy('created_at', 'desc')->take(10)->get();
        $otsSolicitadas = \App\Models\WorkOrder::where('solicitante_id', $usuario->id)->orderBy('created_at', 'desc')->take(10)->get();

        return view('usuarios.show', compact('usuario', 'otsAsignadas', 'otsSolicitadas'));
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::where('activo', true)->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios')->ignore($usuario->id)],
            'codigo_empleado' => ['required', 'string', 'max:50', Rule::unique('usuarios')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'nullable|string|max:100',
            'fecha_ingreso' => 'nullable|date',
            'activo' => 'required|boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $validated['password_hash'] = Hash::make($request->input('password'));
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.show', $usuario->id)
            ->with('success', "Datos de {$usuario->nombre_completo} actualizados correctamente.");
    }

    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta de usuario.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $estadoTexto = $usuario->activo ? 'activado' : 'desactivado';
        return redirect()->route('usuarios.index')
            ->with('success', "Acceso del usuario {$usuario->nombre_completo} {$estadoTexto} correctamente.");
    }

    public function resetPassword(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $usuario = User::findOrFail($id);
        $usuario->update([
            'password_hash' => Hash::make($validated['password'])
        ]);

        return back()->with('success', "Contraseña de {$usuario->nombre_completo} restablecida correctamente.");
    }
}
