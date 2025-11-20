<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();
            
            // Índices sin foreign keys por ahora
            $table->index('usuario_id');
            $table->index('producto_id');
            
            // Evitar duplicados
            $table->unique(['usuario_id', 'producto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('carritos');
    }
};