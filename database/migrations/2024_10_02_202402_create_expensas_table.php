<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expensas', function (Blueprint $table) {
            $table->id('idExpensa');
            $table->unsignedBigInteger('idPropiedad');
            $table->foreign('idPropiedad')->references('idPropiedad')->on('propiedades');
            $table->decimal('monto', 8, 2)->default(0);
            $table->decimal('montoPagado', 8, 2)->default(0);
            $table->decimal('montoPendiente', 8, 2)->default(0);
            $table->decimal('montoAhorro', 8, 2)->default(0);
            $table->date('mes');
            $table->enum('estado', ['Pendiente', 'Pagado'])->default('Pendiente');
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
        Schema::dropIfExists('expensas');
    }
}
