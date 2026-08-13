<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->decimal('presupuesto_anual', 12, 2)->nullable()->default(0.00)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->decimal('presupuesto_anual', 12, 2)->default(0.00)->change();
        });
    }
};
