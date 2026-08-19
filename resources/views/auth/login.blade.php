<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Iniciar Sesión - CMMS Leon Plast S.A.C.</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('vendor/js/tailwind-cdn.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('vendor/js/alpine.min.js') }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-screen h-[100dvh] w-full bg-slate-950 text-slate-100 flex flex-col justify-between p-6 sm:p-8 relative overflow-hidden select-none" 
      x-data="{ email: '', password: '', showPassword: false }">

    <!-- Ambient Glowing Accents -->
    <div class="absolute -top-24 -left-24 w-72 h-72 sm:w-96 sm:h-96 bg-blue-600/25 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-72 h-72 sm:w-96 sm:h-96 bg-indigo-600/25 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Main Container -->
    <div class="w-full max-w-sm sm:max-w-md mx-auto my-auto flex flex-col justify-center relative z-10">

        <!-- App Brand Header -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-xl shadow-blue-500/30 mb-3 sm:mb-4">
                <svg class="w-8 h-8 sm:w-9 sm:h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">LEON PLAST S.A.C.</h1>
            <p class="text-[10px] sm:text-xs text-blue-400 font-bold tracking-widest uppercase mt-1">Gestión Computarizada de Mantenimiento</p>
        </div>

        <!-- Form Box / Native App Card -->
        <div class="bg-slate-900/90 sm:bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <h2 class="text-base sm:text-lg font-bold text-white mb-5 sm:mb-6 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Acceso al Sistema</span>
            </h2>

            @if ($errors->any())
            <div class="mb-5 sm:mb-6 p-3.5 sm:p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" x-model="email" required autocomplete="username"
                               placeholder="tu-correo@leonplast.com"
                               class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 sm:py-3.5 text-slate-100 placeholder-slate-500 text-base sm:text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">Contraseña</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" x-model="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full bg-slate-950 border border-slate-800 rounded-2xl pl-4 pr-12 py-3 sm:py-3.5 text-slate-100 placeholder-slate-500 text-base sm:text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                        <button type="button" @click="showPassword = !showPassword" 
                                title="Mostrar / Ocultar Contraseña"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 p-1.5 focus:outline-none transition">
                            <template x-if="showPassword">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </template>
                            <template x-if="!showPassword">
                                <svg class="w-5 h-5 text-slate-500 hover:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.04 10.04 0 013.98-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-400 hover:text-slate-200">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-blue-600 focus:ring-0 w-4 h-4">
                        <span class="text-xs">Recordar sesión</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3.5 px-4 rounded-2xl shadow-xl shadow-blue-600/30 active:scale-[0.98] transition duration-200 text-sm tracking-wide">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>

    <!-- App Footer Status Bar -->
    <div class="text-center py-2 relative z-10">
        <p class="text-[10px] text-slate-500 font-mono tracking-wider">CMMS LEÓN PLAST S.A.C. v1.0 • PLANTA OPERATIVA</p>
    </div>

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
