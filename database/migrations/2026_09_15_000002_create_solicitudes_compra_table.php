<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_solicitud', 50)->unique();
            $table->unsignedBigInteger('repuesto_id');
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_sugerida');
            $table->string('motivo', 50)->default('STOCK_MINIMO'); // STOCK_MINIMO, REPOSICION_URGENTE, MANUAL
            $table->unsignedBigInteger('solicitado_por')->nullable();
            $table->string('estado', 50)->default('PENDIENTE'); // PENDIENTE, APROBADO, RECHAZADO, COMPRADO
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('repuesto_id')->references('id')->on('repuestos')->onDelete('cascade');
            $table->foreign('solicitado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_compra');
    }
};
