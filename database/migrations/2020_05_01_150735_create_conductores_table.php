<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConductoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname',50);
            $table->string('lastname',50);
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->string('telefono',16);
            $table->string('direccion',90);
            $table->string('nit',30);
            $table->integer('ciudad_id')->nullable();
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
        Schema::dropIfExists('conductores');
    }
}
