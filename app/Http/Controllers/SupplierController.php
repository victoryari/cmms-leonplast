<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('nombre_comercial', 'like', "%{$search}%")
                  ->orWhere('ruc_documento', 'like', "%{$search}%")
                  ->orWhere('contacto_nombre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $terceros = $query->orderBy('id', 'desc')->paginate(15);

        $metrics = [
            'total' => Supplier::count(),
            'proveedores' => Supplier::whereIn('tipo', ['Proveedor_Repuestos', 'Ambos'])->count(),
            'contratistas' => Supplier::whereIn('tipo', ['Contratista_Servicios', 'Ambos'])->count(),
            'activos' => Supplier::where('activo', true)->count(),
        ];

        return view('terceros.index', compact('terceros', 'metrics'));
    }

    public function create()
    {
        return view('terceros.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruc_documento' => 'required|string|max:20|unique:terceros,ruc_documento',
            'razon_social' => 'required|string|max:150',
            'nombre_comercial' => 'nullable|string|max:150',
            'tipo' => 'required|in:Proveedor_Repuestos,Contratista_Servicios,Ambos',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ciudad' => 'nullable|string|max:80',
            'calificacion' => 'required|integer|min:1|max:5',
            'observaciones' => 'nullable|string',
        ]);

        $validated['activo'] = true;

        $tercero = Supplier::create($validated);

        return redirect()->route('terceros.show', $tercero->id)
            ->with('success', "Empresa / Tercero '{$tercero->razon_social}' registrado exitosamente.");
    }

    public function show($id)
    {
        $tercero = Supplier::with(['activos', 'repuestos'])->findOrFail($id);
        return view('terceros.show', compact('tercero'));
    }

    public function edit($id)
    {
        $tercero = Supplier::findOrFail($id);
        return view('terceros.edit', compact('tercero'));
    }

    public function update(Request $request, $id)
    {
        $tercero = Supplier::findOrFail($id);

        $validated = $request->validate([
            'ruc_documento' => 'required|string|max:20|unique:terceros,ruc_documento,' . $tercero->id,
            'razon_social' => 'required|string|max:150',
            'nombre_comercial' => 'nullable|string|max:150',
            'tipo' => 'required|in:Proveedor_Repuestos,Contratista_Servicios,Ambos',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ciudad' => 'nullable|string|max:80',
            'calificacion' => 'required|integer|min:1|max:5',
            'activo' => 'required|boolean',
            'observaciones' => 'nullable|string',
        ]);

        $tercero->update($validated);

        return redirect()->route('terceros.show', $tercero->id)
            ->with('success', "Datos de '{$tercero->razon_social}' actualizados correctamente.");
    }

    public function destroy($id)
    {
        $tercero = Supplier::findOrFail($id);

        if ($tercero->activos()->exists() || $tercero->repuestos()->exists()) {
            return back()->with('error', "No se puede eliminar el tercero '{$tercero->razon_social}' porque tiene activos o repuestos vinculados.");
        }

        $tercero->delete();

        return redirect()->route('terceros.index')
            ->with('success', "Empresa '{$tercero->razon_social}' eliminada del sistema.");
    }
}
