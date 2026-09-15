<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->longText('firma_tecnico')->nullable()->after('comentario_usuario');
            $table->longText('firma_supervisor')->nullable()->after('firma_tecnico');
            $table->timestamp('fecha_firma_tecnico')->nullable()->after('firma_supervisor');
            $table->timestamp('fecha_firma_supervisor')->nullable()->after('fecha_firma_tecnico');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->dropColumn([
                'firma_tecnico',
                'firma_supervisor',
                'fecha_firma_tecnico',
                'fecha_firma_supervisor',
            ]);
        });
    }
};
