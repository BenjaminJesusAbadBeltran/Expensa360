<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCajaChicaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caja_chica', function (Blueprint $table) {
            $table->id('idCajaChica');
            $table->decimal('saldoInicial', 15, 2);
            $table->decimal('saldoActual', 15, 2);
            $table->decimal('saldoFinal', 15, 2);
            $table->date('fecha_Inicial');
            $table->date('fecha_Final')->nullable();
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
        Schema::dropIfExists('caja_chica');
    }
}
