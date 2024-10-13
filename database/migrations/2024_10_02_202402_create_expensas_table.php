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
            $table->decimal('montoPagar', 8, 2)->default(0);
            $table->date('fechaVencimiento');
            $table->integer('idStatus');
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
