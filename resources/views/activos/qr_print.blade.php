<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $activo->codigo_activo }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .label-card { border: 2px solid black !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-950 min-h-screen flex flex-col items-center justify-center p-6 text-slate-100">

    <!-- Print Button Bar -->
    <div class="no-print mb-6 flex items-center space-x-4">
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Imprimir Etiqueta Industrial</span>
        </button>
        <button onclick="window.close()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700">
            Cerrar
        </button>
    </div>

    <!-- Printable Industrial Tag -->
    <div class="label-card w-full max-w-sm bg-white text-slate-900 rounded-3xl p-6 shadow-2xl border-4 border-slate-900 space-y-4">
        
        <!-- Company Header -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
            <div>
                <h1 class="text-lg font-black tracking-tighter text-slate-900">LEON PLAST S.A.C.</h1>
                <p class="text-[9px] font-bold text-blue-700 uppercase tracking-widest">Planta Industrial Perú</p>
            </div>
            <div class="px-2 py-1 bg-slate-900 text-white rounded text-[10px] font-mono font-bold">
                CMMS
            </div>
        </div>

        <!-- Asset Code Banner -->
        <div class="bg-slate-900 text-white p-2 rounded-xl text-center">
            <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-widest">Código de Activo Fijo</span>
            <span class="text-xl font-mono font-black tracking-wider">{{ $activo->codigo_activo }}</span>
        </div>

        <!-- QR Image Display -->
        <div class="text-center">
            <div class="inline-block p-2 bg-white border-2 border-slate-900 rounded-xl">
                <img src="{{ $activo->qr_image_url }}" alt="QR {{ $activo->codigo_activo }}" class="w-48 h-48 mx-auto">
            </div>
        </div>

        <!-- Asset Specifications -->
        <div class="space-y-1 text-center border-t-2 border-slate-900 pt-3">
            <h2 class="font-bold text-sm text-slate-900 leading-snug">{{ $activo->nombre }}</h2>
            <p class="text-[11px] text-slate-600 font-semibold">
                {{ $activo->marca }} {{ $activo->modelo }} 
                @if($activo->numero_serie)| S/N: {{ $activo->numero_serie }}@endif
            </p>
            <p class="text-[10px] text-slate-800 font-bold mt-1">Ubicación: {{ $activo->ubicacion ?? 'Planta Principal' }}</p>
        </div>

        <!-- Safety & Public Request Footer Notice -->
        <div class="bg-amber-100 border border-amber-400 text-amber-900 p-2 rounded-lg text-center text-[9px] font-bold space-y-0.5">
            <p>📷 Escanear con la cámara del celular para Reportar Avería (Sin Login)</p>
            <p class="font-mono text-[8px] text-amber-800">/solicitud-rapida/{{ $activo->codigo_qr ?? $activo->codigo_activo }}</p>
        </div>

    </div>

</body>
</html>
