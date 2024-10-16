<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetallePagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_pagos', function (Blueprint $table) {
            $table->id('idDetallePago');
            $table->unsignedBigInteger('idPago');
            $table->unsignedBigInteger('idExpensa')->nullable();
            $table->foreign('idPago')->references('idPago')->on('pagos');
            $table->unsignedBigInteger('idServicio')->nullable();
            $table->foreign('idServicio')->references('idServicio')->on('servicios_agua');
            $table->foreign('idExpensa')->references('idExpensa')->on('expensas');
            $table->enum('concepto', ['expensa', 'agua', 'garaje', 'baul'])->default('expensa');
            $table->decimal('monto', 10, 2);
            $table->date('mes')->nullable(); // Mes para el que aplica el pago
            $table->enum('status', ['Activo', 'Borrado'])->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_pagos');
    }
}
