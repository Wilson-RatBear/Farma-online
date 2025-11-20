<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar tipo de dato a bigint
        DB::statement('ALTER TABLE pedidos MODIFY usuario_id BIGINT UNSIGNED');
        
        // Agregar foreign key correcta
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });
        
        // Revertir tipo de dato
        DB::statement('ALTER TABLE pedidos MODIFY usuario_id INT');
    }
};