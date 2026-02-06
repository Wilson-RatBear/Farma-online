<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->integer('rating')->unsigned();
            $table->text('comentario')->nullable();
            $table->timestamps();
            
            // Un usuario solo puede hacer una reseña por producto
            $table->unique(['usuario_id', 'producto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resenas');
    }
};