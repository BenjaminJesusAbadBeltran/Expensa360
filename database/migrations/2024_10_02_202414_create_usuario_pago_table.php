<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioPagoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_pago', function (Blueprint $table) {
            $table->id('idUsuario_Pago');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idPago');
            $table->foreign('idUsuario')->references('idUsuario')->on('users')->onDelete('cascade');
            $table->foreign('idPago')->references('idPago')->on('pagos')->onDelete('cascade');
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
        Schema::dropIfExists('usuario_pago');
    }
}
