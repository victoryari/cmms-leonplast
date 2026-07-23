<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;

class ApiConfigController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function catalogs()
    {
        $catalogos = $this->catalogService->getAllCatalogs();

        return response()->json([
            'success' => true,
            'data' => $catalogos
        ]);
    }
}
