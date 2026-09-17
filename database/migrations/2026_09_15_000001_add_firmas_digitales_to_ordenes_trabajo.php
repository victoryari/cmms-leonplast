<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_trabajo', 'firma_tecnico')) {
                $table->longText('firma_tecnico')->nullable()->after('comentario_usuario');
            }
            if (!Schema::hasColumn('ordenes_trabajo', 'firma_supervisor')) {
                $table->longText('firma_supervisor')->nullable()->after('firma_tecnico');
            }
            if (!Schema::hasColumn('ordenes_trabajo', 'fecha_firma_tecnico')) {
                $table->timestamp('fecha_firma_tecnico')->nullable()->after('firma_supervisor');
            }
            if (!Schema::hasColumn('ordenes_trabajo', 'fecha_firma_supervisor')) {
                $table->timestamp('fecha_firma_supervisor')->nullable()->after('fecha_firma_tecnico');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['firma_tecnico', 'firma_supervisor', 'fecha_firma_tecnico', 'fecha_firma_supervisor'] as $col) {
                if (Schema::hasColumn('ordenes_trabajo', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
