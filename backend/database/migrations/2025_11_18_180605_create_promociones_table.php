<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id(); // id Primaria int(11) AUTO_INCREMENT
            $table->string('nombre', 255); // nombre varchar(255)
            $table->text('descripcion')->nullable(); // descripcion text
            $table->integer('descuento_porcentaje')->nullable(); // descuento_porcentaje int(11)
            $table->string('imagen', 500)->nullable(); // imagen varchar(500)
            $table->boolean('activo')->default(true); // activo tinyint(1) default 1
            $table->date('fecha_inicio')->nullable(); // fecha_inicio date
            $table->date('fecha_fin')->nullable(); // fecha_fin date
            $table->timestamps(); // created_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('promociones');
    }
};