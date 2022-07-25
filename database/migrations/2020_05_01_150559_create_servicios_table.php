<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label',50);
            $table->double('precio_base', 11, 2)->default(0.0);
            $table->integer('horas_base')->default(1);
            $table->integer('km_base');
            $table->integer('porcentaje_comision');
            $table->double('precio_km', 11, 2)->default(0.0);
            $table->double('precio_hora', 11, 2)->default(0.0);
            $table->integer('qty_conductores')->default(1);
            $table->boolean('activo');
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
        Schema::dropIfExists('servicios');
    }
}
