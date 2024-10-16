<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresoCajaChicaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingreso_caja_chica', function (Blueprint $table) {
            $table->id('idIngresoCajaChica');
            $table->unsignedBigInteger('idCajaChica');
            $table->foreign('idCajaChica')->references('idCajaChica')->on('caja_chica');
            $table->unsignedBigInteger('idPago');
            $table->foreign('idPago')->references('idPago')->on('pagos');
            $table->decimal('montoIngreso', 15, 2); // Monto que se ingresa a la caja chica
            $table->date('fechaIngreso');
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
        Schema::dropIfExists('ingreso_caja_chica');
    }
}
