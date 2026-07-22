<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Solicitud - {{ $orden->codigo_ot }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-200 p-4 md:p-6 flex flex-col justify-between">

    <div class="max-w-md mx-auto w-full space-y-5">

        <!-- Header Brand -->
        <div class="text-center space-y-1">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/30 text-white font-bold text-xl mb-1">
                ✓
            </div>
            <h1 class="text-xl font-extrabold text-white">¡Reporte Enviado Correctamente!</h1>
            <p class="text-xs text-emerald-400 font-semibold">Tu solicitud ha sido notificada al equipo de Mantenimiento</p>
        </div>

        <!-- Ticket Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Código de Solicitud</span>
                    <h2 class="text-xl font-extrabold font-mono text-blue-400">{{ $orden->codigo_ot }}</h2>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-bold border
                    @if($orden->estado == 'Completada') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                    @elseif($orden->estado == 'En_Progreso') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                    @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                    {{ $orden->estado == 'Pendiente' ? '🟡 Pendiente Aprobación' : $orden->estado }}
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 text-[11px]">Equipo Reportado:</span>
                    <strong class="text-white block font-bold text-sm">{{ $orden->equipo?->nombre }}</strong>
                </div>

                <div>
                    <span class="text-slate-400 text-[11px]">Técnico Asignado:</span>
                    <strong class="text-emerald-400 block font-semibold">{{ $orden->tecnico?->nombre_completo ?? 'En proceso de asignación' }}</strong>
                </div>

                <div>
                    <span class="text-slate-400 text-[11px]">Fecha y Hora:</span>
                    <span class="text-slate-300 font-mono block">{{ $orden->created_at->format('d/m/Y H:i A') }}</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-slate-300 space-y-1">
                <span class="text-[10px] font-semibold text-slate-400 uppercase block">Resumen del Reporte:</span>
                <p class="italic text-[11px] text-slate-300 leading-relaxed whitespace-pre-line">{{ $orden->descripcion }}</p>
            </div>

            <div class="pt-2">
                <a href="{{ route('public.create', $orden->equipo?->codigo_activo ?? $orden->activo_id) }}" 
                   class="w-full inline-block text-center py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition">
                    + Enviar Otro Reporte
                </a>
            </div>
        </div>

    </div>

    <!-- Public Footer -->
    <footer class="text-center text-[10px] text-slate-500 pt-6">
        Sistema CMMS Leon Plast S.A.C. &copy; {{ date('Y') }} - Seguimiento Rápido de Solicitud
    </footer>

</body>
</html>
