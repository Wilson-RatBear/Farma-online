<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['usuario_id', 'producto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favoritos');
    }
};