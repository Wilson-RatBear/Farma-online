<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    try {
        // Solo intentar eliminar si existe
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign('pedidos_ibfk_1');
        });
    } catch (\Exception $e) {
        // Si no existe, continuar sin error
    }

    // Cambiar tipo de dato
    DB::statement('ALTER TABLE pedidos MODIFY usuario_id BIGINT UNSIGNED');
    
    // Crear nueva foreign key
    Schema::table('pedidos', function (Blueprint $table) {
        $table->foreign('usuario_id')->references('id')->on('users');
    });
}
};