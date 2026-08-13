<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            if (!Schema::hasColumn('activos', 'tipo_clasificacion')) {
                $table->enum('tipo_clasificacion', [
                    'Ubicacion', 
                    'Equipo', 
                    'Herramienta', 
                    'Repuesto_Suministro', 
                    'Digital'
                ])->default('Equipo')->after('categoria');
            }

            if (!Schema::hasColumn('activos', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('activos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            if (Schema::hasColumn('activos', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('activos', 'tipo_clasificacion')) {
                $table->dropColumn('tipo_clasificacion');
            }
        });
    }
};
