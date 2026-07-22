<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CMMS Leon Plast S.A.C.</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-4 relative overflow-hidden" x-data="{ email: 'admin@leonplast.com', password: 'Password123!' }">

    <!-- Glowing background accents -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-xl shadow-blue-500/25 mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">LEON PLAST S.A.C.</h1>
            <p class="text-xs text-blue-400 font-semibold tracking-widest uppercase mt-1">Gestión Computarizada de Mantenimiento</p>
        </div>

        <!-- Form Card -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-6 flex items-center justify-between">
                <span>Acceso al Sistema</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 font-mono border border-slate-700">BD: cmms_leonplast</span>
            </h2>

            @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" x-model="email" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Contraseña</label>
                    <input type="password" id="password" name="password" x-model="password" required 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-400 hover:text-slate-200">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-blue-600 focus:ring-0">
                        <span>Recordar sesión</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-blue-600/30 transition duration-200 text-sm">
                    Iniciar Sesión
                </button>
            </form>

            <!-- Credenciales de Prueba Rápidas -->
            <div class="mt-8 pt-6 border-t border-slate-800">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Seleccionar usuario de prueba local:</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="email='admin@leonplast.com'; password='Password123!'" 
                            class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-left hover:border-blue-500/50 transition">
                        <p class="font-semibold text-blue-400">1. Administrador</p>
                        <p class="text-[10px] text-slate-500">admin@leonplast.com</p>
                    </button>

                    <button type="button" @click="email='gerente@leonplast.com'; password='Password123!'" 
                            class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-left hover:border-blue-500/50 transition">
                        <p class="font-semibold text-indigo-400">2. Gerente Mant.</p>
                        <p class="text-[10px] text-slate-500">gerente@leonplast.com</p>
                    </button>

                    <button type="button" @click="email='supervisor@leonplast.com'; password='Password123!'" 
                            class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-left hover:border-blue-500/50 transition">
                        <p class="font-semibold text-purple-400">3. Supervisor</p>
                        <p class="text-[10px] text-slate-500">supervisor@leonplast.com</p>
                    </button>

                    <button type="button" @click="email='tecnico@leonplast.com'; password='Password123!'" 
                            class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-left hover:border-blue-500/50 transition">
                        <p class="font-semibold text-amber-400">4. Técnico</p>
                        <p class="text-[10px] text-slate-500">tecnico@leonplast.com</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
