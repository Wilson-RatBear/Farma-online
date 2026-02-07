<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); // id Primaria int(11) AUTO_INCREMENT
            $table->string('nombre', 100); // nombre varchar(100)
            $table->string('slug', 100)->nullable(); // slug varchar(100)
            $table->text('descripcion')->nullable(); // descripcion text
            $table->string('imagen', 500)->nullable(); // imagen varchar(500)
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('categorias');
    }
};