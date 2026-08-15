<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ordenes_trabajo MODIFY prioridad VARCHAR(50) NOT NULL DEFAULT 'Media'");
        DB::statement("ALTER TABLE planes_preventivos MODIFY prioridad_defecto VARCHAR(50) NOT NULL DEFAULT 'Media'");
        DB::statement("ALTER TABLE activos MODIFY estado_condicion VARCHAR(50) NOT NULL DEFAULT 'Bueno'");
    }

    public function down(): void
    {
    }
};
