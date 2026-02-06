<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // id Primaria int(11) AUTO_INCREMENT
            $table->string('nombre', 255); // nombre varchar(255)
            $table->text('descripcion')->nullable(); // descripcion text
            $table->decimal('precio', 10, 2); // precio decimal(10,2)
            $table->decimal('precio_original', 10, 2)->nullable(); // precio_original decimal(10,2)
            $table->integer('stock')->default(0); // stock int(11) default 0
            $table->string('imagen', 500)->nullable(); // imagen varchar(500)
            $table->foreignId('categoria_id')->constrained('categorias'); // categoria_id int(11)
            $table->enum('badge', ['mas-vendido', 'oferta', 'nuevo', 'esencial'])->nullable(); // badge enum
            $table->boolean('activo')->default(true); // activo tinyint(1) default 1
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};