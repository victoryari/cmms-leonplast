@extends('layouts.app')

@section('title', 'Configuración & Catálogos Dinámicos')

@section('content')
<div class="space-y-6" x-data="{ tab: 'categorias' }">

    <!-- Header Title -->
    <div>
        <h2 class="text-2xl font-extrabold text-white tracking-tight">Parametrización & Catálogos en Vivo</h2>
        <p class="text-xs text-slate-400 mt-1">Administración de listas dinámicas para formularios sin necesidad de modificar código</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-800 pb-3">
        <button @click="tab = 'categorias'" :class="tab === 'categorias' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Categorías de Equipos ({{ count($catalogos['categorias_activos']) }})
        </button>
        <button @click="tab = 'estados'" :class="tab === 'estados' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Estados Operativos ({{ count($catalogos['estados_operativos']) }})
        </button>
        <button @click="tab = 'areas'" :class="tab === 'areas' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Áreas de Planta ({{ count($catalogos['areas_planta']) }})
        </button>
        <button @click="tab = 'repuestos'" :class="tab === 'repuestos' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Categorías Repuestos ({{ count($catalogos['categorias_repuestos']) }})
        </button>
        <button @click="tab = 'tipos_ot'" :class="tab === 'tipos_ot' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Tipos de OT ({{ count($catalogos['tipos_ot']) }})
        </button>
        <button @click="tab = 'empresa'" :class="tab === 'empresa' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
            Datos Corporativos
        </button>
    </div>

    <!-- TAB 1: CATEGORÍAS DE EQUIPOS -->
    <div x-show="tab === 'categorias'" class="space-y-4">
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Catálogo de Categorías de Equipos</h3>

            <!-- Form Agregar -->
            <form action="{{ route('configuracion.update-catalog') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="clave_catalogo" value="catalog_categorias_activos">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="valor" required placeholder="Nueva categoría (ej: Robótica & Servos)" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold">+ Agregar Categoría</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                @foreach($catalogos['categorias_activos'] as $item)
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">{{ $item }}</span>
                    <form action="{{ route('configuracion.update-catalog') }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría del catálogo?')">
                        @csrf
                        <input type="hidden" name="clave_catalogo" value="catalog_categorias_activos">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="valor" value="{{ $item }}">
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold p-1">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 2: ESTADOS OPERATIVOS -->
    <div x-show="tab === 'estados'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Catálogo de Estados Operativos de Máquina</h3>

            <form action="{{ route('configuracion.update-catalog') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="clave_catalogo" value="catalog_estados_operativos">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="valor" required placeholder="Nuevo estado (ej: En_Pruebas_Calibracion)" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold">+ Agregar Estado</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                @foreach($catalogos['estados_operativos'] as $item)
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white font-mono">{{ $item }}</span>
                    <form action="{{ route('configuracion.update-catalog') }}" method="POST" onsubmit="return confirm('¿Eliminar estado del catálogo?')">
                        @csrf
                        <input type="hidden" name="clave_catalogo" value="catalog_estados_operativos">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="valor" value="{{ $item }}">
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold p-1">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 3: ÁREAS DE PLANTA -->
    <div x-show="tab === 'areas'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Catálogo de Áreas y Ubicaciones de Planta</h3>

            <form action="{{ route('configuracion.update-catalog') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="clave_catalogo" value="catalog_areas_planta">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="valor" required placeholder="Nueva área (ej: Planta 2 - Soplado PET)" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold">+ Agregar Área</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                @foreach($catalogos['areas_planta'] as $item)
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">📍 {{ $item }}</span>
                    <form action="{{ route('configuracion.update-catalog') }}" method="POST" onsubmit="return confirm('¿Eliminar área del catálogo?')">
                        @csrf
                        <input type="hidden" name="clave_catalogo" value="catalog_areas_planta">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="valor" value="{{ $item }}">
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold p-1">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 4: CATEGORÍAS DE REPUESTOS -->
    <div x-show="tab === 'repuestos'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Catálogo de Categorías de Repuestos & Almacén</h3>

            <form action="{{ route('configuracion.update-catalog') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="clave_catalogo" value="catalog_categorias_repuestos">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="valor" required placeholder="Nueva categoría insumo (ej: Neumática de Precisión)" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold">+ Agregar Categoría</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                @foreach($catalogos['categorias_repuestos'] as $item)
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">📦 {{ $item }}</span>
                    <form action="{{ route('configuracion.update-catalog') }}" method="POST" onsubmit="return confirm('¿Eliminar categoría?')">
                        @csrf
                        <input type="hidden" name="clave_catalogo" value="catalog_categorias_repuestos">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="valor" value="{{ $item }}">
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold p-1">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 5: TIPOS DE OT -->
    <div x-show="tab === 'tipos_ot'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Catálogo de Tipos de Órdenes de Trabajo</h3>

            <form action="{{ route('configuracion.update-catalog') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="clave_catalogo" value="catalog_tipos_ot">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="valor" required placeholder="Nuevo tipo OT (ej: Auditoría LOTO)" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold">+ Agregar Tipo</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                @foreach($catalogos['tipos_ot'] as $item)
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">📋 {{ $item }}</span>
                    <form action="{{ route('configuracion.update-catalog') }}" method="POST" onsubmit="return confirm('¿Eliminar tipo de OT?')">
                        @csrf
                        <input type="hidden" name="clave_catalogo" value="catalog_tipos_ot">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="valor" value="{{ $item }}">
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold p-1">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 6: DATOS EMPRESA -->
    <div x-show="tab === 'empresa'" class="space-y-4" x-cloak>
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase text-blue-400">Datos Corporativos de la Empresa</h3>

            <form action="{{ route('configuracion.update-company') }}" method="POST" class="space-y-4 max-w-xl">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre de la Empresa *</label>
                    <input type="text" name="empresa_nombre" value="{{ $empresaParams['nombre'] }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">RUC *</label>
                    <input type="text" name="empresa_ruc" value="{{ $empresaParams['ruc'] }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Dirección Legal *</label>
                    <input type="text" name="empresa_direccion" value="{{ $empresaParams['direccion'] }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white">
                </div>

                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30">
                    Guardar Datos Corporativos
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
