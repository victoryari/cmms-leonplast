<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'sueldo_mensual')) {
                $table->decimal('sueldo_mensual', 10, 2)->nullable()->after('especialidad');
            }
            if (!Schema::hasColumn('usuarios', 'costo_hora')) {
                $table->decimal('costo_hora', 8, 2)->nullable()->after('sueldo_mensual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'sueldo_mensual')) {
                $table->dropColumn('sueldo_mensual');
            }
            if (Schema::hasColumn('usuarios', 'costo_hora')) {
                $table->dropColumn('costo_hora');
            }
        });
    }
};
