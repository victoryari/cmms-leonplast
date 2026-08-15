<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('usuarios')->orderBy('id', 'asc')->get();
        $definitions = Role::getAvailablePermissionsDefinition();

        $metrics = [
            'total_roles' => $roles->count(),
            'roles_activos' => $roles->where('activo', true)->count(),
            'total_usuarios' => User::where('activo', true)->count(),
        ];

        return view('roles.index', compact('roles', 'definitions', 'metrics'));
    }

    public function create()
    {
        $definitions = Role::getAvailablePermissionsDefinition();
        return view('roles.create', compact('definitions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'nullable|array',
        ]);

        $permisosProcesados = [];
        $definitions = Role::getAvailablePermissionsDefinition();

        foreach ($definitions as $moduloKey => $moduloInfo) {
            foreach ($moduloInfo['acciones'] as $accionKey => $accionLabel) {
                $hasPerm = isset($request->input('permisos')[$moduloKey][$accionKey]);
                $permisosProcesados[$moduloKey][$accionKey] = $hasPerm;
            }
        }

        $role = Role::create([
            'nombre' => str_replace(' ', '_', trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'],
            'permisos' => $permisosProcesados,
            'activo' => true,
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Rol {$role->nombre} creado exitosamente con sus permisos asignados.");
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $definitions = Role::getAvailablePermissionsDefinition();

        return view('roles.edit', compact('role', 'definitions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:roles,nombre,' . $role->id,
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'nullable|array',
            'activo' => 'nullable|boolean',
        ]);

        $permisosProcesados = [];
        $definitions = Role::getAvailablePermissionsDefinition();

        foreach ($definitions as $moduloKey => $moduloInfo) {
            foreach ($moduloInfo['acciones'] as $accionKey => $accionLabel) {
                $hasPerm = isset($request->input('permisos')[$moduloKey][$accionKey]);
                $permisosProcesados[$moduloKey][$accionKey] = $hasPerm;
            }
        }

        $role->update([
            'nombre' => str_replace(' ', '_', trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'],
            'permisos' => $permisosProcesados,
            'activo' => $request->has('activo') ? $request->boolean('activo') : $role->activo,
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Permisos y ficha del rol {$role->nombre} actualizados correctamente.");
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->usuarios()->where('activo', true)->exists()) {
            return back()->with('error', "No se puede eliminar el rol {$role->nombre} porque tiene usuarios activos asignados.");
        }

        if (in_array($role->nombre, ['Administrador', 'Gerente_Mantenimiento', 'Supervisor', 'Tecnico'])) {
            return back()->with('error', "El rol del sistema '{$role->nombre}' no puede ser eliminado por seguridad.");
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol {$role->nombre} eliminado del sistema.");
    }
}
