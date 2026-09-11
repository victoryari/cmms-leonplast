<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class AuditController extends Controller
{
    public function index()
    {
        // Solo administradores o gerentes
        if (!auth()->user()->hasRole(['Administrador', 'Gerente_Mantenimiento'])) {
            abort(403, 'No tiene permisos para ver el registro de auditoría.');
        }

        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('auditoria.index', compact('logs'));
    }
}
