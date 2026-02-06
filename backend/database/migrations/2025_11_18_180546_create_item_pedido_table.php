<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_pedido', function (Blueprint $table) {
            $table->id(); // id Primaria int(11) AUTO_INCREMENT
            $table->foreignId('pedido_id')->constrained('pedidos'); // pedido_id int(11)
            $table->foreignId('producto_id')->constrained('productos'); // producto_id int(11)
            $table->integer('cantidad'); // cantidad int(11)
            $table->decimal('precio_unitario', 10, 2); // precio_unitario decimal(10,2)
            $table->timestamps(); // created_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_pedido');
    }
};