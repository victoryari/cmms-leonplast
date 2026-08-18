<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\SparePart;
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
        $query = Asset::with(['parent', 'proveedor', 'location'])->where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        if ($tipoClasificacion = $request->input('tipo_clasificacion')) {
            $query->where('tipo_clasificacion', $tipoClasificacion);
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

        $activos = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Árbol Jerárquico Completo (Nodos Raíz con sus Hijos)
        $arbolActivos = Asset::with('children.children')->whereNull('parent_id')->where('activo', true)->orderBy('nombre', 'asc')->get();

        $metrics = [
            'total' => Asset::where('activo', true)->count(),
            'ubicaciones' => Asset::where('activo', true)->where('tipo_clasificacion', 'Ubicacion')->count(),
            'equipos' => Asset::where('activo', true)->where('tipo_clasificacion', 'Equipo')->count(),
            'herramientas' => Asset::where('activo', true)->where('tipo_clasificacion', 'Herramienta')->count(),
            'repuestos_suministros' => Asset::where('activo', true)->where('tipo_clasificacion', 'Repuesto_Suministro')->count(),
            'digitales' => Asset::where('activo', true)->where('tipo_clasificacion', 'Digital')->count(),
        ];

        $categorias = $this->catalogService->getCategoriasActivos();
        $areas = $this->catalogService->getAreasPlanta();
        $estadosOperativos = $this->catalogService->getEstadosOperativos();
        $ubicacionesCat = \App\Models\Location::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('activos.index', compact('activos', 'arbolActivos', 'metrics', 'categorias', 'areas', 'estadosOperativos', 'ubicacionesCat'));
    }

    public function create()
    {
        $catalogos = $this->catalogService->getAllCatalogs();
        $activosPadres = Asset::where('activo', true)->orderBy('nombre', 'asc')->get();
        $proveedores = \App\Models\Supplier::where('activo', true)->orderBy('razon_social', 'asc')->get();
        $ubicaciones = \App\Models\Location::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('activos.create', compact('catalogos', 'activosPadres', 'proveedores', 'ubicaciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'tipo_clasificacion' => 'required|in:Ubicacion,Equipo,Herramienta,Repuesto_Suministro,Digital',
            'parent_id' => 'nullable|exists:activos,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'proveedor_id' => 'nullable|exists:terceros,id',
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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if (!empty($validated['ubicacion_id'])) {
            $loc = \App\Models\Location::find($validated['ubicacion_id']);
            if ($loc) {
                $validated['ubicacion'] = $loc->nombre;
            }
        }

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('activos', 'public');
            $validated['imagenes'] = [$path];
        }

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
            'parent',
            'children',
            'location',
            'proveedor',
            'ordenesTrabajo' => fn($q) => $q->orderBy('created_at', 'desc')->take(10),
            'planesPreventivos',
        ])->findOrFail($id);

        return view('activos.show', compact('activo'));
    }

    public function edit($id)
    {
        $activo = Asset::findOrFail($id);
        $catalogos = $this->catalogService->getAllCatalogs();
        $activosPadres = Asset::where('activo', true)->where('id', '!=', $activo->id)->orderBy('nombre', 'asc')->get();
        $proveedores = \App\Models\Supplier::where('activo', true)->orderBy('razon_social', 'asc')->get();
        $ubicaciones = \App\Models\Location::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('activos.edit', compact('activo', 'catalogos', 'activosPadres', 'proveedores', 'ubicaciones'));
    }

    public function update(Request $request, $id)
    {
        $activo = Asset::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'tipo_clasificacion' => 'required|in:Ubicacion,Equipo,Herramienta,Repuesto_Suministro,Digital',
            'parent_id' => 'nullable|exists:activos,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'proveedor_id' => 'nullable|exists:terceros,id',
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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'eliminar_imagen' => 'nullable|boolean',
        ]);

        if (!empty($validated['ubicacion_id'])) {
            $loc = \App\Models\Location::find($validated['ubicacion_id']);
            if ($loc) {
                $validated['ubicacion'] = $loc->nombre;
            }
        }

        if ($request->boolean('eliminar_imagen')) {
            $validated['imagenes'] = null;
        }

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('activos', 'public');
            $validated['imagenes'] = [$path];
        }

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

    public function herramientas(Request $request)
    {
        $query = Asset::where('activo', true)
            ->where(function($q) {
                $q->where('tipo_clasificacion', 'Herramienta')
                  ->orWhere('categoria', 'like', '%Herramienta%');
            });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%");
            });
        }

        $activos = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        $metrics = [
            'total' => Asset::where('activo', true)->where('tipo_clasificacion', 'Herramienta')->count(),
            'operativas' => Asset::where('activo', true)->where('tipo_clasificacion', 'Herramienta')->where('estado_operativo', 'Operativo')->count(),
            'mantenimiento' => Asset::where('activo', true)->where('tipo_clasificacion', 'Herramienta')->where('estado_operativo', 'En_Mantenimiento')->count(),
            'criticas' => Asset::where('activo', true)->where('tipo_clasificacion', 'Herramienta')->where(function($q) {
                $q->where('estado_condicion', 'Critico')->orWhere('estado_operativo', 'Fuera_Servicio');
            })->count(),
        ];

        return view('activos.herramientas', compact('activos', 'metrics'));
    }

    public function repuestosSuministros(Request $request)
    {
        $query = SparePart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_sku', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%");
            });
        }

        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        $repuestos = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        $metrics = [
            'total_articulos' => SparePart::count(),
            'categorias' => SparePart::distinct('categoria')->count('categoria'),
            'marcas' => SparePart::distinct('marca')->whereNotNull('marca')->count('marca'),
            'criticos' => SparePart::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
        ];

        return view('activos.repuestos_suministros', compact('repuestos', 'metrics'));
    }

    public function digitales(Request $request)
    {
        $query = Asset::where('activo', true)->where('tipo_clasificacion', 'Digital');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_activo', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        $activos = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        $metrics = [
            'total' => Asset::where('activo', true)->where('tipo_clasificacion', 'Digital')->count(),
            'operativos' => Asset::where('activo', true)->where('tipo_clasificacion', 'Digital')->where('estado_operativo', 'Operativo')->count(),
            'revision' => Asset::where('activo', true)->where('tipo_clasificacion', 'Digital')->where('estado_operativo', 'En_Revision')->count(),
        ];

        return view('activos.digitales', compact('activos', 'metrics'));
    }
}
