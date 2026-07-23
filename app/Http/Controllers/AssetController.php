<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\CatalogService;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index(Request $request)
    {
        $query = Asset::where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($estado = $request->input('estado_operativo')) {
            $query->where('estado_operativo', $estado);
        }

        if ($area = $request->input('area')) {
            $query->where('area', $area);
        }

        $activos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $metrics = [
            'total' => Asset::where('activo', true)->count(),
            'operativos' => Asset::where('activo', true)->where('estado_operativo', 'Operativo')->count(),
            'mantenimiento' => Asset::where('activo', true)->where('estado_operativo', 'Mantenimiento')->count(),
            'reparacion' => Asset::where('activo', true)->where('estado_operativo', 'Reparacion')->count(),
            'fuera_servicio' => Asset::where('activo', true)->whereIn('estado_operativo', ['Fuera_de_servicio', 'Baja'])->count(),
        ];

        $categorias = $this->catalogService->getCategoriasActivos();
        $areas = $this->catalogService->getAreasPlanta();
        $estadosOperativos = $this->catalogService->getEstadosOperativos();

        return view('activos.index', compact('activos', 'metrics', 'categorias', 'areas', 'estadosOperativos'));
    }

    public function create()
    {
        $catalogos = $this->catalogService->getAllCatalogs();
        return view('activos.create', compact('catalogos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'estado_operativo' => 'required|string',
            'estado_condicion' => 'required|string',
            'costo_adquisicion' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_estimada' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
        ]);

        $year = date('Y');
        $lastAsset = Asset::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $nextNum = $lastAsset ? ((int) Str::afterLast($lastAsset->codigo_activo, '-')) + 1 : 1;
        $codigoActivo = sprintf("ACT-PLAST-%s-%03d", $year, $nextNum);

        $validated['codigo_activo'] = $codigoActivo;
        $validated['qr_code_content'] = $codigoActivo;
        $validated['creado_por'] = auth()->id();
        $validated['activo'] = true;

        $asset = Asset::create($validated);

        return redirect()->route('activos.show', $asset->id)
            ->with('success', "Activo {$asset->nombre} [{$asset->codigo_activo}] registrado exitosamente.");
    }

    public function show($id)
    {
        $activo = Asset::with([
            'ordenesTrabajo' => fn($q) => $q->orderBy('created_at', 'desc')->take(10),
            'planesPreventivos',
            'lecturasMedidores' => fn($q) => $q->orderBy('created_at', 'desc')->take(10),
        ])->findOrFail($id);

        return view('activos.show', compact('activo'));
    }

    public function edit($id)
    {
        $activo = Asset::findOrFail($id);
        $catalogos = $this->catalogService->getAllCatalogs();

        return view('activos.edit', compact('activo', 'catalogos'));
    }

    public function update(Request $request, $id)
    {
        $activo = Asset::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'estado_operativo' => 'required|string',
            'estado_condicion' => 'required|string',
            'costo_adquisicion' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_estimada' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string',
        ]);

        $activo->update($validated);

        return redirect()->route('activos.show', $activo->id)
            ->with('success', "Datos de {$activo->nombre} actualizados correctamente.");
    }

    public function destroy($id)
    {
        $activo = Asset::findOrFail($id);
        $activo->update(['activo' => false, 'estado_operativo' => 'Baja']);

        return redirect()->route('activos.index')
            ->with('success', "El activo {$activo->nombre} ha sido dado de baja.");
    }

    public function printQr($id)
    {
        $activo = Asset::findOrFail($id);
        return view('activos.qr_print', compact('activo'));
    }
}
