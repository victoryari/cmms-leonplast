@extends('layouts.app')

@section('title', 'Editar Ficha Laboral - ' . $empleado->nombre_completo)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('recursos-humanos.show', $empleado->id) }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white">Editar Ficha Laboral [{{ $empleado->codigo_empleado }}]</h2>
            <p class="text-xs text-slate-400">Modifica especialidad, función, datos personales y costos de mano de obra (HH)</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">
        @if ($errors->any())
        <div class="p-4 mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300">
            <div class="flex items-center space-x-2 font-bold mb-1">
                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Por favor corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('recursos-humanos.update', $empleado->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="nombres" class="block text-xs font-semibold text-slate-300 mb-1">Nombres *</label>
                    <input type="text" id="nombres" name="nombres" value="{{ old('nombres', $empleado->nombres) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label for="apellidos" class="block text-xs font-semibold text-slate-300 mb-1">Apellidos *</label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $empleado->apellidos) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label for="documento_identidad" class="block text-xs font-semibold text-slate-300 mb-1">DNI / Doc. Identidad</label>
                    <input type="text" id="documento_identidad" name="documento_identidad" value="{{ old('documento_identidad', $empleado->documento_identidad) }}" placeholder="Ej: 74839201"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="codigo_empleado" class="block text-xs font-semibold text-slate-300 mb-1">Código de Empleado *</label>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" value="{{ old('codigo_empleado', $empleado->codigo_empleado) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono uppercase">
                </div>

                <div>
                    <label for="especialidad" class="block text-xs font-semibold text-slate-300 mb-1">Especialidad / Área *</label>
                    <input type="text" id="especialidad" name="especialidad" value="{{ old('especialidad', $empleado->especialidad) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>

                <div>
                    <label for="rol_id" class="block text-xs font-semibold text-slate-300 mb-1">Función Operativa *</label>
                    <select id="rol_id" name="rol_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                        <option value="">Selecciona Función</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ old('rol_id', $empleado->rol_id) == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="telefono" class="block text-xs font-semibold text-slate-300 mb-1">Teléfono de Contacto</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $empleado->telefono) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white">
                </div>

                <div>
                    <label for="fecha_ingreso" class="block text-xs font-semibold text-slate-300 mb-1">Fecha de Ingreso</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', $empleado->fecha_ingreso?->format('Y-m-d')) }}"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <!-- Remuneración y Costo Hora Hombre -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-emerald-900/40 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center space-x-1.5">
                        <span>💵 Remuneración & Costo Hora Hombre (HH)</span>
                    </h4>
                    <span class="text-[10px] text-slate-400">Jornada base: 208 horas/mes (48h semanales)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sueldo_mensual" class="block text-xs font-semibold text-slate-300 mb-1">Sueldo Mensual (S/.)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-emerald-400 font-bold">S/.</span>
                            <input type="number" step="0.01" min="0" id="sueldo_mensual" name="sueldo_mensual" value="{{ old('sueldo_mensual', $empleado->sueldo_mensual) }}"
                                   oninput="calcularCostoHora()"
                                   class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-12 pr-4 py-2.5 text-xs text-white focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label for="costo_hora" class="block text-xs font-semibold text-slate-300 mb-1">Costo Hora Hombre (Calculado)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-emerald-400 font-bold">/ hora</span>
                            <input type="number" step="0.01" min="0" id="costo_hora" name="costo_hora" value="{{ old('costo_hora', $empleado->costo_hora) }}"
                                   class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-16 pr-4 py-2.5 text-xs text-emerald-400 font-bold focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('recursos-humanos.show', $empleado->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30">
                    Guardar Cambios en Ficha
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function calcularCostoHora() {
    const sueldoInput = document.getElementById('sueldo_mensual');
    const costoHoraInput = document.getElementById('costo_hora');
    const val = parseFloat(sueldoInput.value);
    
    if (!isNaN(val) && val > 0) {
        const costoHora = (val / 208).toFixed(2);
        costoHoraInput.value = costoHora;
    } else {
        costoHoraInput.value = '';
    }
}
</script>
@endsection
