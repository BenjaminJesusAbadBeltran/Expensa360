<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioExpensaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_expensa', function (Blueprint $table) {
            $table->id('idUsuario_Expensa');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idExpensa');
            $table->foreign('idUsuario')->references('idUsuario')->on('users')->onDelete('cascade');
            $table->foreign('idExpensa')->references('idExpensa')->on('expensas')->onDelete('cascade');
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
        Schema::dropIfExists('usuario_expensa');
    }
}
