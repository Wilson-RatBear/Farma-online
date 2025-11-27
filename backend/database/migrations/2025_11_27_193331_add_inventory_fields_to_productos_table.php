<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('stock_minimo')->default(10);
            $table->integer('stock_maximo')->default(100);
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->decimal('costo', 8, 2)->nullable();
            $table->boolean('alertar_stock_bajo')->default(true);
            $table->timestamp('ultima_reposicion')->nullable();
            
            // Clave foránea para proveedor (sin constraint por ahora)
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn([
                'stock_minimo', 
                'stock_maximo', 
                'proveedor_id', 
                'costo', 
                'alertar_stock_bajo',
                'ultima_reposicion'
            ]);
        });
    }
};