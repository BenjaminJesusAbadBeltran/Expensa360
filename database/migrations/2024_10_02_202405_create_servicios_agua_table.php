<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiciosAguaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios_agua', function (Blueprint $table) {
            $table->id('idServicio');
            $table->decimal('montoPagar', 8, 2);
            $table->date('fechaMedicion');
            $table->smallInteger('medicion');
            $table->smallInteger('previaMedicion')->nullable();
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
        Schema::dropIfExists('servicios_agua');
    }
}
