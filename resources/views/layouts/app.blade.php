<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CMMS Leon Plast') - Sistema de Mantenimiento Industrial</title>
    
    <!-- Alpine.js & Tailwind CSS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full text-slate-100 bg-slate-950 antialiased selection:bg-blue-500 selection:text-white">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0">
            <div>
                <!-- Brand Logo & Header -->
                <div class="h-20 flex items-center px-6 border-b border-slate-800 space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-lg shadow-blue-500/30 flex items-center justify-center font-black text-white text-lg tracking-wider">
                        LP
                    </div>
                    <div>
                        <h1 class="font-extrabold text-sm tracking-tight text-white">LEON PLAST</h1>
                        <p class="text-[10px] text-blue-400 font-semibold tracking-widest uppercase">CMMS Industrial</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Dashboard</span>
                    </a>

                    @if(!auth()->user()->isRequester())
                    <a href="{{ route('activos.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('activos.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Activos Industriales</span>
                    </a>
                    @endif

                    <a href="{{ route('ordenes.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('ordenes.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span>Órdenes de Trabajo</span>
                    </a>

                    @if(!auth()->user()->isRequester())
                    <a href="{{ route('planes.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('planes.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Planes Preventivos</span>
                    </a>

                    <a href="{{ route('repuestos.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('repuestos.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span>Inventario & Almacén</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isSupervisor())
                    <a href="{{ route('reportes.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('reportes.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>Reportes KPI & Analítica</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('configuracion.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('configuracion.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Configuración & Catálogos</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('usuarios.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('usuarios.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Personal & Usuarios</span>
                    </a>
                    @endif

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Integraciones</p>
                        <div class="mx-3 p-3 rounded-xl bg-slate-800/60 border border-slate-700/50">
                            <div class="flex items-center space-x-2 text-cyan-400 font-semibold text-xs mb-1">
                                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                                <span>API Flutter activa</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">Conexión Sanctum habilitada para la App móvil en planta.</p>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Profile & Logout Section Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/80 space-y-2">
                <a href="{{ route('perfil.index') }}" class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-800 transition group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-indigo-400 font-bold text-xs shrink-0">
                            {{ substr(auth()->user()->nombres, 0, 1) }}{{ substr(auth()->user()->apellidos, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-white truncate group-hover:text-blue-400 transition">{{ auth()->user()->nombre_completo }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                {{ auth()->user()->role?->nombre }}
                            </span>
                        </div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-400 hover:bg-rose-500/10 border border-rose-500/20 transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar Header -->
            <header class="h-20 bg-slate-900/60 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold text-slate-300">Planta Operativa</span>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @php
                        $unreadNotifCount = \App\Models\Notification::where('usuario_id', auth()->id())->where('leido', false)->count();
                    @endphp
                    <a href="{{ route('notificaciones.index') }}" class="relative p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="Centro de Notificaciones">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"></path></svg>
                        @if($unreadNotifCount > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold flex items-center justify-center border-2 border-slate-900 animate-pulse">
                            {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                        </span>
                        @endif
                    </a>

                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-white">{{ date('d/m/Y') }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">Turno Activo</p>
                    </div>
                </div>
            </header>

            <!-- Main Body Content -->
            <main class="flex-1 p-6 overflow-y-auto">
                <!-- Notifications Alert Success -->
                @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                @endif

                <!-- Notifications Alert Error -->
                @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="h-12 bg-slate-900/40 border-t border-slate-800 px-6 flex items-center justify-between text-xs text-slate-500">
                <p>Sistema CMMS Leon Plast S.A.C. &copy; {{ date('Y') }} - Gestión de Mantenimiento Industrial</p>
                <p class="font-mono text-[11px]">v2.5 Enterprise</p>
            </footer>
        </div>

    </div>

</body>
</html>
