<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTareasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('conductor_id')->unsigned();
            $table->integer('orden_id')->unsigned();
            $table->string('nombre_contacto');
            $table->string('direccion');
            $table->integer('telefono');
            $table->string('detalles');
            $table->decimal('lat', 30, 25);
            $table->decimal('lng', 30, 25);
            $table->double('cobrado', 11, 2);
            $table->integer('estado_tarea_id')->default(1);
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
        Schema::dropIfExists('tareas');
    }
}
