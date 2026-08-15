<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hacer la columna solicitante_id nullable en ordenes_trabajo
        DB::statement('ALTER TABLE ordenes_trabajo MODIFY solicitante_id BIGINT(20) UNSIGNED NULL');

        // 2. Desactivar o eliminar el rol 'Solicitante' si existe
        DB::table('roles')->where('nombre', 'Solicitante')->update(['activo' => false]);
    }

    public function down(): void
    {
        DB::table('roles')->where('nombre', 'Solicitante')->update(['activo' => true]);
        DB::statement('ALTER TABLE ordenes_trabajo MODIFY solicitante_id BIGINT(20) UNSIGNED NOT NULL');
    }
};
