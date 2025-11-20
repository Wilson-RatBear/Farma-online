<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SOLO cambiar el tipo de dato sin tocar foreign keys
        DB::statement('ALTER TABLE pedidos MODIFY usuario_id BIGINT UNSIGNED');
    }

    public function down(): void
    {
        // Revertir tipo de dato
        DB::statement('ALTER TABLE pedidos MODIFY usuario_id INT');
    }
};