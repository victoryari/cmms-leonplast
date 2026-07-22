<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::where('activo', true);

        // Filtro por búsqueda de texto
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        // Filtro por estado operativo
        if ($estado = $request->input('estado_operativo')) {
            $query->where('estado_operativo', $estado);
        }

        // Filtro por área
        if ($area = $request->input('area')) {
            $query->where('area', $area);
        }

        $activos = $query->orderBy('codigo_activo', 'asc')->paginate(12)->withQueryString();

        // Métricas dinámicas para las tarjetas superiores
        $metrics = [
            'total' => Asset::where('activo', true)->count(),
            'operativos' => Asset::where('activo', true)->where('estado_operativo', 'Operativo')->count(),
            'mantenimiento' => Asset::where('activo', true)->where('estado_operativo', 'Mantenimiento')->count(),
            'reparacion' => Asset::where('activo', true)->where('estado_operativo', 'Reparacion')->count(),
            'fuera_servicio' => Asset::where('activo', true)->whereIn('estado_operativo', ['Fuera_de_servicio', 'Baja'])->count(),
        ];

        $categorias = AssetCategory::where('activo', true)->get();
        $areas = Asset::where('activo', true)->whereNotNull('area')->distinct()->pluck('area');

        return view('activos.index', compact('activos', 'metrics', 'categorias', 'areas'));
    }

    public function create()
    {
        $categorias = AssetCategory::where('activo', true)->get();
        return view('activos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:50',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'estado_operativo' => 'required|in:Operativo,Mantenimiento,Reparacion,Fuera_de_servicio,Baja',
            'estado_condicion' => 'required|in:Nuevo,Bueno,Regular,Malo,Crítico',
            'costo_adquisicion' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_estimada' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        // Autogenerar código de activo si no se especifica
        $prefix = match ($validated['categoria']) {
            'Inyectoras de Plástico' => 'ACT-INY-',
            'Grúas y Equipos de Izaje' => 'ACT-GRU-',
            'Compresores y Neumática' => 'ACT-CMP-',
            'Sistemas de Enfriamiento' => 'ACT-CHL-',
            default => 'ACT-EQP-',
        };

        $count = Asset::where('codigo_activo', 'like', "{$prefix}%")->count() + 1;
        $validated['codigo_activo'] = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
        $validated['qr_code_content'] = $validated['codigo_activo'];
        $validated['creado_por'] = auth()->id();

        $asset = Asset::create($validated);

        return redirect()->route('activos.show', $asset->id)
            ->with('success', "Activo {$asset->codigo_activo} registrado exitosamente.");
    }

    public function show($id)
    {
        $activo = Asset::with(['ordenesTrabajo' => function ($q) {
            $q->with(['solicitante', 'tecnico'])->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('activos.show', compact('activo'));
    }

    public function edit($id)
    {
        $activo = Asset::findOrFail($id);
        $categorias = AssetCategory::where('activo', true)->get();

        return view('activos.edit', compact('activo', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $activo = Asset::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:50',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'estado_operativo' => 'required|in:Operativo,Mantenimiento,Reparacion,Fuera_de_servicio,Baja',
            'estado_condicion' => 'required|in:Nuevo,Bueno,Regular,Malo,Crítico',
            'costo_adquisicion' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_estimada' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $activo->update($validated);

        return redirect()->route('activos.show', $activo->id)
            ->with('success', "Ficha técnica del activo {$activo->codigo_activo} actualizada correctamente.");
    }

    public function destroy($id)
    {
        $activo = Asset::findOrFail($id);
        $activo->update(['activo' => false, 'estado_operativo' => 'Baja']);

        return redirect()->route('activos.index')
            ->with('success', "El activo {$activo->codigo_activo} ha sido dado de baja.");
    }

    public function printQr($id)
    {
        $activo = Asset::findOrFail($id);
        return view('activos.qr_print', compact('activo'));
    }
}
