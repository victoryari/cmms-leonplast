<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Muestra el catálogo de Recursos Humanos (Personal & Trabajadores de Planta)
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('codigo_empleado', 'like', "%{$search}%")
                  ->orWhere('documento_identidad', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%");
            });
        }

        if ($especialidad = $request->input('especialidad')) {
            $query->where('especialidad', $especialidad);
        }

        $empleados = $query->orderBy('codigo_empleado', 'asc')->paginate(12)->withQueryString();

        $metrics = [
            'total_personal' => User::count(),
            'tecnicos' => User::whereHas('role', fn($q) => $q->where('nombre', 'Tecnico'))->count(),
            'supervisores' => User::whereHas('role', fn($q) => $q->whereIn('nombre', ['Supervisor', 'Gerente_Mantenimiento']))->count(),
            'promedio_costo_hora' => round((float) User::avg('costo_hora'), 2),
        ];

        $especialidades = User::whereNotNull('especialidad')->distinct()->pluck('especialidad');

        return view('recursos-humanos.index', compact('empleados', 'metrics', 'especialidades'));
    }

    /**
     * Muestra el formulario de Alta de Trabajador de Planta (Sin exigir credenciales)
     */
    public function create()
    {
        $roles = Role::where('activo', true)->get();
        return view('recursos-humanos.create', compact('roles'));
    }

    /**
     * Guarda un nuevo trabajador de planta
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'documento_identidad' => 'nullable|string|max:20',
            'codigo_empleado' => 'required|string|max:50|unique:usuarios,codigo_empleado',
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'sueldo_mensual' => 'nullable|numeric|min:0',
            'costo_hora' => 'nullable|numeric|min:0',
            'fecha_ingreso' => 'nullable|date',
            'rol_id' => 'required|exists:roles,id',
        ]);

        // Generar email único de ficha de empleado si no se especifica
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $validated['nombres']));
        $validated['email'] = strtolower($validated['codigo_empleado']) . '.' . $cleanName . '@leonplast.pe';
        $validated['password_hash'] = Hash::make('LeonPlast2026!');
        $validated['activo'] = true;

        $empleado = User::create($validated);

        return redirect()->route('recursos-humanos.show', $empleado->id)
            ->with('success', "Trabajador {$empleado->nombre_completo} registrado exitosamente en Recursos Humanos.");
    }

    /**
     * Ficha técnica y laboral del trabajador
     */
    public function show($id)
    {
        $empleado = User::with('role')->findOrFail($id);
        $otsAsignadas = \App\Models\WorkOrder::where('tecnico_id', $empleado->id)->orderBy('created_at', 'desc')->take(10)->get();
        $otsSolicitadas = \App\Models\WorkOrder::where('solicitante_id', $empleado->id)->orderBy('created_at', 'desc')->take(10)->get();

        return view('recursos-humanos.show', compact('empleado', 'otsAsignadas', 'otsSolicitadas'));
    }

    /**
     * Muestra el formulario para editar datos laborales del trabajador
     */
    public function edit($id)
    {
        $empleado = User::findOrFail($id);
        $roles = Role::where('activo', true)->get();

        return view('recursos-humanos.edit', compact('empleado', 'roles'));
    }

    /**
     * Actualiza la información del trabajador de planta
     */
    public function update(Request $request, $id)
    {
        $empleado = User::findOrFail($id);

        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'documento_identidad' => 'nullable|string|max:20',
            'codigo_empleado' => ['required', 'string', 'max:50', Rule::unique('usuarios')->ignore($empleado->id)],
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'sueldo_mensual' => 'nullable|numeric|min:0',
            'costo_hora' => 'nullable|numeric|min:0',
            'fecha_ingreso' => 'nullable|date',
            'rol_id' => 'required|exists:roles,id',
        ]);

        $empleado->update($validated);

        return redirect()->route('recursos-humanos.show', $empleado->id)
            ->with('success', "Datos laborales de {$empleado->nombre_completo} actualizados correctamente.");
    }

    /**
     * Dar de baja o eliminar trabajador de planta
     */
    public function destroy($id)
    {
        $empleado = User::findOrFail($id);

        if ($empleado->id === auth()->id()) {
            return back()->with('error', 'No puedes dar de baja tu propio registro.');
        }

        $empleado->update(['activo' => false]);

        return redirect()->route('recursos-humanos.index')
            ->with('success', "El trabajador {$empleado->nombre_completo} ha sido dado de baja.");
    }
}
