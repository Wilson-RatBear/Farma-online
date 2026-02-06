<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@farmacia.com'], [
            'name' => 'Administrador',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        User::updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Usuario Test',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $cat1 = Categoria::updateOrCreate(['slug' => 'medicamentos'], ['nombre' => 'Medicamentos', 'descripcion' => 'Analgésicos y más']);
        $cat2 = Categoria::updateOrCreate(['slug' => 'cuidado-personal'], ['nombre' => 'Cuidado Personal', 'descripcion' => 'Higiene diaria']);

        Producto::updateOrCreate(['nombre' => 'Paracetamol 500mg'], [
            'descripcion' => 'Eficaz contra el dolor',
            'precio' => 5.50,
            'stock' => 100,
            'categoria_id' => $cat1->id,
            'activo' => true,
            'imagen' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500',
        ]);

        Producto::updateOrCreate(['nombre' => 'Champú Suave'], [
            'descripcion' => 'Para todo tipo de cabello',
            'precio' => 12.00,
            'stock' => 50,
            'categoria_id' => $cat2->id,
            'activo' => true,
            'imagen' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=500',
        ]);

        Promocion::updateOrCreate(['nombre' => 'Oferta de Verano'], [
            'descripcion' => '20% off en todo',
            'descuento_porcentaje' => 20,
            'activo' => true,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(30),
        ]);
    }
}
