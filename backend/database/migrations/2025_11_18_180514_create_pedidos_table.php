<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('numero_orden')->unique();
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
            $table->text('direccion_envio');
            $table->string('ciudad_envio');
            $table->string('telefono_contacto');
            $table->enum('metodo_pago', ['efectivo', 'pago_movil', 'transferencia', 'zelle', 'paypal', 'tarjeta']);
            $table->string('metodo_pago_detalle')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->string('banco')->nullable();
            $table->string('telefono_pago')->nullable();
            $table->decimal('monto_pagado', 10, 2)->nullable();
            $table->string('estado_pago')->default('pendiente');
            $table->timestamp('fecha_pago')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};