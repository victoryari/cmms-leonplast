<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ordenes_trabajo', 'token_seguimiento')) {
            Schema::table('ordenes_trabajo', function (Blueprint $table) {
                $table->string('token_seguimiento', 64)->nullable()->unique()->after('codigo_ot');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ordenes_trabajo', 'token_seguimiento')) {
            Schema::table('ordenes_trabajo', function (Blueprint $table) {
                $table->dropUnique(['token_seguimiento']);
                $table->dropColumn('token_seguimiento');
            });
        }
    }
};
