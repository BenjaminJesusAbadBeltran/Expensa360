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
            $table->unsignedBigInteger('idUsuario');
            $table->foreign('idUsuario')->references('idUsuario')->on('users');
            $table->unsignedBigInteger('idPropiedad');
            $table->foreign('idPropiedad')->references('idPropiedad')->on('propiedades');
            $table->unsignedBigInteger('idMetodoPago');
            $table->foreign('idMetodoPago')->references('idMetodo')->on('metodos_pago');
            $table->decimal('montoTotal', 8, 2);
            $table->timestamp('fechaPago')->format('Y-m-d H:i');
            $table->string('observaciones')->nullable();
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
        Schema::dropIfExists('pagos');
    }
}
