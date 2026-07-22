<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Avería - {{ $activo->nombre }}</title>
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
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-lg shadow-blue-500/30 text-white font-bold text-xl mb-1">
                LP
            </div>
            <h1 class="text-xl font-extrabold text-white">LEON PLAST S.A.C.</h1>
            <p class="text-xs text-blue-400 font-semibold uppercase tracking-wider">Reporte Rápido de Mantenimiento por QR</p>
        </div>

        <!-- Target Machine Identification Card -->
        <div class="p-4 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                QR
            </div>
            <div class="overflow-hidden">
                <span class="font-mono text-[10px] text-blue-400 font-bold block">[{{ $activo->codigo_activo }}]</span>
                <h3 class="text-sm font-bold text-white truncate">{{ $activo->nombre }}</h3>
                <p class="text-[11px] text-slate-400 font-medium truncate">📍 Ubicación: {{ $activo->ubicacion }}</p>
            </div>
        </div>

        <!-- Public Request Form Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
            <form action="{{ route('public.store', $activo->codigo_activo) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="nombre_solicitante" class="block text-xs font-semibold text-slate-300 mb-1">Tu Nombre y Apellidos *</label>
                    <input type="text" id="nombre_solicitante" name="nombre_solicitante" value="{{ old('nombre_solicitante') }}" required placeholder="Ej: Juan Operario"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label for="area_turno" class="block text-xs font-semibold text-slate-300 mb-1">Área / Turno de Trabajo *</label>
                    <input type="text" id="area_turno" name="area_turno" value="{{ old('area_turno', 'Turno Mañana / Inyección') }}" required placeholder="Ej: Inyección 1 - Turno Mañana"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label for="contacto" class="block text-xs font-semibold text-slate-300 mb-1">Celular / Teléfono de Contacto (Opcional)</label>
                    <input type="text" id="contacto" name="contacto" value="{{ old('contacto') }}" placeholder="Ej: 987 654 321"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Prioridad / Gravedad de la Falla *</label>
                    <select name="prioridad" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none">
                        <option value="Media" selected>🟡 Media (Permite seguir operando)</option>
                        <option value="Alta">🟧 Alta (Rendimiento afectado / Fuga)</option>
                        <option value="Critica">🚨 Crítica (PARADA DE PLANTA / MÁQUINA DETENIDA)</option>
                        <option value="Baja">🟢 Baja (Ruidos menores / Ajuste)</option>
                    </select>
                </div>

                <div>
                    <label for="descripcion" class="block text-xs font-semibold text-slate-300 mb-1">Descripción del Problema / Síntomas *</label>
                    <textarea id="descripcion" name="descripcion" rows="3" required placeholder="Describe qué ocurrió, ruidos, fugas, alarma en pantalla del PLC..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">{{ old('descripcion') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tomar Foto del Fallo (Opcional)</label>
                    <input type="file" name="foto" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600/20 file:text-blue-300 hover:file:bg-blue-600 cursor-pointer">
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-sm shadow-lg shadow-blue-600/30 transition transform active:scale-95">
                        🚀 Enviar Reporte de Avería
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Public Footer -->
    <footer class="text-center text-[10px] text-slate-500 pt-6">
        Sistema CMMS Leon Plast S.A.C. &copy; {{ date('Y') }} - Reporte Rápido QR
    </footer>

</body>
</html>
