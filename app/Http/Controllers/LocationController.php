<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::with(['parent', 'children']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_ubicacion', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%")
                  ->orWhere('empresa_subsidiaria', 'like', "%{$search}%");
            });
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($departamento = $request->input('departamento')) {
            $query->where('departamento', $departamento);
        }

        $ubicaciones = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $metrics = [
            'total' => Location::count(),
            'sedes_principales' => Location::where('tipo', 'Sede_Principal')->count(),
            'subsidiarias' => Location::where('tipo', 'Empresa_Subsidiaria')->count(),
            'almacenes' => Location::where('tipo', 'Almacen_Deposito')->count(),
            'departamentos_count' => Location::distinct('departamento')->count('departamento'),
        ];

        // Todos los marcadores para mapa geográfico
        $mapLocations = Location::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get(['id', 'nombre', 'codigo_ubicacion', 'ciudad', 'departamento', 'latitud', 'longitud', 'tipo']);

        return view('ubicaciones.index', compact('ubicaciones', 'metrics', 'mapLocations'));
    }

    public function create()
    {
        $padres = Location::orderBy('nombre', 'asc')->get();
        
        $year = date('Y');
        $last = Location::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $nextNum = $last ? ((int) Str::afterLast($last->codigo_ubicacion, '-')) + 1 : 1;
        $codigoSugerido = sprintf("UBIC-PERU-%s-%03d", $year, $nextNum);

        return view('ubicaciones.create', compact('padres', 'codigoSugerido'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo_ubicacion' => 'required|string|max:50|unique:ubicaciones,codigo_ubicacion',
            'parent_id' => 'nullable|exists:ubicaciones,id',
            'empresa_subsidiaria' => 'nullable|string|max:150',
            'tipo' => 'required|in:Sede_Principal,Planta_Industrial,Almacen_Deposito,Oficina_Regional,Empresa_Subsidiaria,Area_Planta',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'centro_costo' => 'nullable|string|max:50',
            'presupuesto_anual' => 'nullable|numeric|min:0',
            'codigo_barras_nfc' => 'nullable|string|max:100',
            'qr_code_url' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $validated['presupuesto_anual'] = $validated['presupuesto_anual'] ?? 0.00;
        $validated['activo'] = true;

        $location = Location::create($validated);

        return redirect()->route('ubicaciones.show', $location->id)
            ->with('success', "Ubicación / Sede '{$location->nombre}' registrada exitosamente.");
    }

    public function show($id)
    {
        $ubicacion = Location::with(['parent', 'children', 'activos'])->findOrFail($id);
        return view('ubicaciones.show', compact('ubicacion'));
    }

    public function edit($id)
    {
        $ubicacion = Location::findOrFail($id);
        $padres = Location::where('id', '!=', $id)->orderBy('nombre', 'asc')->get();

        return view('ubicaciones.edit', compact('ubicacion', 'padres'));
    }

    public function update(Request $request, $id)
    {
        $ubicacion = Location::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo_ubicacion' => 'required|string|max:50|unique:ubicaciones,codigo_ubicacion,' . $ubicacion->id,
            'parent_id' => 'nullable|exists:ubicaciones,id',
            'empresa_subsidiaria' => 'nullable|string|max:150',
            'tipo' => 'required|in:Sede_Principal,Planta_Industrial,Almacen_Deposito,Oficina_Regional,Empresa_Subsidiaria,Area_Planta',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'centro_costo' => 'nullable|string|max:50',
            'presupuesto_anual' => 'nullable|numeric|min:0',
            'codigo_barras_nfc' => 'nullable|string|max:100',
            'qr_code_url' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        $validated['presupuesto_anual'] = $validated['presupuesto_anual'] ?? 0.00;

        $ubicacion->update($validated);

        return redirect()->route('ubicaciones.show', $ubicacion->id)
            ->with('success', "Ubicación '{$ubicacion->nombre}' actualizada correctamente.");
    }

    public function destroy($id)
    {
        $ubicacion = Location::findOrFail($id);

        if ($ubicacion->children()->exists()) {
            return back()->with('error', "No se puede inactivar la ubicación '{$ubicacion->nombre}' porque tiene sub-ubicaciones dependientes.");
        }

        // Eliminación lógica (deshabilitar en lugar de borrar físicamente)
        $ubicacion->update(['activo' => !$ubicacion->activo]);

        $estado = $ubicacion->activo ? 'habilitada' : 'inactivada / eliminada lógicamente';

        return redirect()->route('ubicaciones.index')
            ->with('success', "Ubicación '{$ubicacion->nombre}' {$estado} correctamente.");
    }
}
