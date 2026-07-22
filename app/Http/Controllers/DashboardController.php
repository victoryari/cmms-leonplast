<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\SparePart;
use App\Models\PreventivePlan;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        $metrics = [
            'total_activos' => Asset::where('activo', true)->count(),
            'activos_operativos' => Asset::where('estado_operativo', 'Operativo')->count(),
            'activos_mantenimiento' => Asset::whereIn('estado_operativo', ['Mantenimiento', 'Reparacion'])->count(),
            'ots_pendientes' => WorkOrder::whereIn('estado', ['Pendiente', 'Aprobada'])->count(),
            'ots_en_progreso' => WorkOrder::where('estado', 'En_Progreso')->count(),
            'ots_completadas' => WorkOrder::where('estado', 'Completada')->count(),
            'total_planes' => PreventivePlan::where('activo', true)->count(),
            'total_repuestos' => SparePart::where('activo', true)->count(),
            'alertas_repuestos' => SparePart::where('activo', true)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
        ];

        $recentOrdersQuery = WorkOrder::with(['activo', 'solicitante', 'tecnico']);

        if ($user->isTechnician()) {
            $recentOrdersQuery->where('tecnico_id', $user->id);
        } elseif ($user->isRequester()) {
            $recentOrdersQuery->where('solicitante_id', $user->id);
        }

        $recentOrders = $recentOrdersQuery->orderBy('created_at', 'desc')->take(6)->get();

        return view('dashboard', compact('user', 'metrics', 'recentOrders'));
    }
}
