<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('activos', 'ubicacion_id')) {
            Schema::table('activos', function (Blueprint $table) {
                $table->foreignId('ubicacion_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained('ubicaciones')
                    ->nullOnDelete();
            });
        }

        // Asignar por defecto la Sede Principal (id=1) a los activos existentes si ubicaciones existe
        if (Schema::hasTable('ubicaciones')) {
            $defaultLocationId = DB::table('ubicaciones')->orderBy('id', 'asc')->value('id');
            if ($defaultLocationId) {
                DB::table('activos')->whereNull('ubicacion_id')->update(['ubicacion_id' => $defaultLocationId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activos', 'ubicacion_id')) {
            Schema::table('activos', function (Blueprint $table) {
                $table->dropForeign(['ubicacion_id']);
                $table->dropColumn('ubicacion_id');
            });
        }
    }
};
