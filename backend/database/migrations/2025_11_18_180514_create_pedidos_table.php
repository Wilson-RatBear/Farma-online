<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // id Primaria int(11) AUTO_INCREMENT
            $table->foreignId('usuario_id')->constrained('users'); // usuario_id int(11)
            $table->string('numero_orden', 50); // numero_orden varchar(50)
            $table->decimal('total', 10, 2); // total decimal(10,2)
            $table->enum('estado', ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'])->default('pendiente'); // estado enum
            $table->text('direccion_envio'); // direccion_envio text
            $table->string('ciudad_envio', 100); // ciudad_envio varchar(100)
            $table->string('telefono_contacto', 20); // telefono_contacto varchar(20)
            $table->enum('metodo_pago', ['tarjeta', 'efectivo']); // metodo_pago enum
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};