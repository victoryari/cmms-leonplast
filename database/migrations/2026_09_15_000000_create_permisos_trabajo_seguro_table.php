<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_trabajo_seguro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_trabajo_id');
            $table->string('tipo_riesgo', 100); // LOTO, ALTURA, ESPACIO_CONFINADO, ALTO_VOLTAJE, CALIENTE
            $table->json('checklist_verificacion')->nullable();
            $table->json('epp_requeridos')->nullable();
            $table->boolean('bloqueo_loto_confirmado')->default(false);
            $table->unsignedBigInteger('aprobado_por')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 50)->default('PENDIENTE'); // PENDIENTE, APROBADO, RECHAZADO
            $table->timestamps();

            $table->foreign('orden_trabajo_id')->references('id')->on('ordenes_trabajo')->onDelete('cascade');
            $table->foreign('aprobado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_trabajo_seguro');
    }
};
