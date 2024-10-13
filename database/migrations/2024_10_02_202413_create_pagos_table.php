<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('idPago');
            $table->unsignedBigInteger('idMetodoPago');
            $table->unsignedBigInteger('idCajaChica');
            $table->decimal('monto', 8, 2);
            $table->date('fechaPago');
            $table->integer('idStatus');
            $table->timestamps();

            $table->foreign('idMetodoPago')->references('idMetodo')->on('metodos_pago');
            $table->foreign('idCajaChica')->references('idCajaChica')->on('caja_chica');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos');
    }
}
