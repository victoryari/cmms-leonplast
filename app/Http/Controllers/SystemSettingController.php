<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Models\SystemParameter;
use App\Models\AssetCategory;

class SystemSettingController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index()
    {
        $catalogos = $this->catalogService->getAllCatalogs();

        $empresaParams = [
            'nombre' => SystemParameter::where('clave', 'empresa_nombre')->value('valor') ?? 'Leon Plast S.A.C.',
            'ruc' => SystemParameter::where('clave', 'empresa_ruc')->value('valor') ?? '20547896321',
            'direccion' => SystemParameter::where('clave', 'empresa_direccion')->value('valor') ?? 'Av. Industrial N° 1234, Parque Industrial',
        ];

        return view('configuracion.index', compact('catalogos', 'empresaParams'));
    }

    public function updateCatalog(Request $request)
    {
        $request->validate([
            'clave_catalogo' => 'required|string',
            'accion' => 'required|in:agregar,eliminar',
            'valor' => 'required|string|max:100',
        ]);

        $clave = $request->input('clave_catalogo');
        $accion = $request->input('accion');
        $nuevoValor = trim($request->input('valor'));

        // Si la clave es de categorías de activos, también sincronizar con la tabla categorias_activos
        if ($clave === 'catalog_categorias_activos') {
            if ($accion === 'agregar') {
                AssetCategory::updateOrCreate(['nombre' => $nuevoValor], ['activo' => true]);
            } else {
                AssetCategory::where('nombre', $nuevoValor)->delete();
            }
        }

        $valoresActuales = SystemParameter::getValoresGrupo($clave);

        if ($accion === 'agregar') {
            if (!in_array($nuevoValor, $valoresActuales)) {
                $valoresActuales[] = $nuevoValor;
            }
        } else {
            $valoresActuales = array_values(array_filter($valoresActuales, fn($item) => $item !== $nuevoValor));
        }

        SystemParameter::setValoresGrupo($clave, $valoresActuales);

        return back()->with('success', "Catálogo " . str_replace('catalog_', '', $clave) . " actualizado correctamente.");
    }

    public function updateCompany(Request $request)
    {
        $request->validate([
            'empresa_nombre' => 'required|string|max:200',
            'empresa_ruc' => 'required|string|max:20',
            'empresa_direccion' => 'required|string|max:255',
        ]);

        SystemParameter::updateOrCreate(['clave' => 'empresa_nombre'], ['valor' => $request->input('empresa_nombre'), 'grupo' => 'Empresa', 'tipo_dato' => 'String']);
        SystemParameter::updateOrCreate(['clave' => 'empresa_ruc'], ['valor' => $request->input('empresa_ruc'), 'grupo' => 'Empresa', 'tipo_dato' => 'String']);
        SystemParameter::updateOrCreate(['clave' => 'empresa_direccion'], ['valor' => $request->input('empresa_direccion'), 'grupo' => 'Empresa', 'tipo_dato' => 'String']);

        return back()->with('success', 'Datos corporativos de la empresa actualizados exitosamente.');
    }
}
