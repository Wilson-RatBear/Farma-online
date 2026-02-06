<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarmaSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador y Usuarios de prueba
        User::updateOrCreate(['email' => 'admin@farmacia.com'], [
            'name' => 'Admin FarmaSalud',
            'password' => bcrypt('admin123'),
            'is_admin' => true,
            'telefono' => '04121234567',
            'direccion' => 'Sede Principal, Caracas'
        ]);

        User::updateOrCreate(['email' => 'cliente@test.com'], [
            'name' => 'Juan Pérez',
            'password' => bcrypt('cliente123'),
            'is_admin' => false,
            'telefono' => '04149876543',
            'direccion' => 'Av. Francisco de Miranda, Edif. Centro, Apto 4B'
        ]);

        // --- CATEGORÍAS ---
        $categorias = [
            [
                'nombre' => 'Medicamentos',
                'slug' => 'medicamentos',
                'descripcion' => 'Analgésicos, antibióticos y tratamientos especializados.',
                'imagen' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500'
            ],
            [
                'nombre' => 'Cuidado Personal',
                'slug' => 'cuidado-personal',
                'descripcion' => 'Todo para tu higiene y cuidado diario.',
                'imagen' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=500'
            ],
            [
                'nombre' => 'Bebés y Mamás',
                'slug' => 'bebes-y-mamas',
                'descripcion' => 'Pañales, fórmulas y accesorios para el cuidado del bebé.',
                'imagen' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500'
            ],
            [
                'nombre' => 'Belleza y SkinCare',
                'slug' => 'belleza',
                'descripcion' => 'Cuidado de la piel, maquillaje y dermocosmética.',
                'imagen' => 'https://images.unsplash.com/photo-1596462502278-27bfdc4033c8?w=500'
            ],
            [
                'nombre' => 'Vitaminas y Fitoterapia',
                'slug' => 'vitaminas',
                'descripcion' => 'Suplementos nutricionales y productos naturales.',
                'imagen' => 'https://images.unsplash.com/photo-1550573995-09775734ec5f?w=500'
            ]
        ];

        $catModels = [];
        foreach ($categorias as $cat) {
            $catModels[$cat['slug']] = Categoria::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // --- PRODUCTOS ---
        $productos = [
            // MEDICAMENTOS
            [
                'nombre' => 'Ibuprofeno 600mg (10 cápsulas)',
                'categoria_slug' => 'medicamentos',
                'precio' => 4.50,
                'precio_original' => 5.20,
                'stock' => 150,
                'descripcion' => 'Analgésico y antiinflamatorio eficaz contra dolores fuertes y fiebre.',
                'imagen' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500',
                'badge' => 'mas-vendido'
            ],
            [
                'nombre' => 'Loratadina 10mg (20 tabletas)',
                'categoria_slug' => 'medicamentos',
                'precio' => 3.00,
                'precio_original' => null,
                'stock' => 80,
                'descripcion' => 'Antihistamínico para el alivio de síntomas de alergias sin causar somnolencia.',
                'imagen' => 'https://images.unsplash.com/photo-1471864190281-ad5f9f81ce4c?w=500',
                'badge' => null
            ],
            [
                'nombre' => 'Amoxicilina 500mg (Suspensión)',
                'categoria_slug' => 'medicamentos',
                'precio' => 8.50,
                'precio_original' => 10.00,
                'stock' => 45,
                'descripcion' => 'Antibiótico de amplio espectro para infecciones bacterianas (Requiere récipe).',
                'imagen' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500',
                'badge' => 'oferta'
            ],

            // CUIDADO PERSONAL
            [
                'nombre' => 'Champú Head & Shoulders Limpieza Renovadora',
                'categoria_slug' => 'cuidado-personal',
                'precio' => 7.50,
                'precio_original' => 9.00,
                'stock' => 60,
                'descripcion' => 'Champú anticaspa para una limpieza profunda y cabello manejable.',
                'imagen' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=500',
                'badge' => 'nuevo'
            ],
            [
                'nombre' => 'Pasta Dental Colgate Total 12 (150g)',
                'categoria_slug' => 'cuidado-personal',
                'precio' => 3.20,
                'precio_original' => null,
                'stock' => 200,
                'descripcion' => 'Protección completa para dientes, lengua, mejillas y encías.',
                'imagen' => 'https://images.unsplash.com/photo-1559591937-e6b7cf037e9d?w=500',
                'badge' => 'esencial'
            ],

            // BEBÉS
            [
                'nombre' => 'Pañales Huggies Active Sec (Etapa 3 - 48 Und)',
                'categoria_slug' => 'bebes-y-mamas',
                'precio' => 15.00,
                'precio_original' => 18.50,
                'stock' => 30,
                'descripcion' => 'Ajuste perfecto y máxima absorción para mantener la piel de tu bebé seca.',
                'imagen' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500',
                'badge' => 'mas-vendido'
            ],
            [
                'nombre' => 'Leche de Fórmula Enfamil Premium 1 (400g)',
                'categoria_slug' => 'bebes-y-mamas',
                'precio' => 22.00,
                'precio_original' => null,
                'stock' => 15,
                'descripcion' => 'Fórmula infantil de inicio con hierro y nutrientes esenciales.',
                'imagen' => 'https://plus.unsplash.com/premium_photo-1678283733971-df96294fd89d?w=500',
                'badge' => 'esencial'
            ],

            // BELLEZA
            [
                'nombre' => 'Protector Solar La Roche-Posay Anthelios 50+',
                'categoria_slug' => 'belleza',
                'precio' => 25.00,
                'precio_original' => 28.00,
                'stock' => 25,
                'descripcion' => 'Fluido invisible de alta protección frente a rayos UVA/UVB para piel sensible.',
                'imagen' => 'https://images.unsplash.com/photo-1555854817-2b22603c7331?w=500',
                'badge' => 'mas-vendido'
            ],
            [
                'nombre' => 'Serum Niacinamida 10% + Zinc 1% (The Ordinary)',
                'categoria_slug' => 'belleza',
                'precio' => 11.50,
                'precio_original' => null,
                'stock' => 40,
                'descripcion' => 'Fórmula de vitaminas y minerales de alta potencia para reducir imperfecciones.',
                'imagen' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=500',
                'badge' => 'nuevo'
            ],

            // VITAMINAS
            [
                'nombre' => 'Vitamina C 1000mg (Efervescente)',
                'categoria_slug' => 'vitaminas',
                'precio' => 6.00,
                'precio_original' => 7.50,
                'stock' => 100,
                'descripcion' => 'Suplemento alimenticio para fortalecer el sistema inmunológico.',
                'imagen' => 'https://images.unsplash.com/photo-1550573995-09775734ec5f?w=500',
                'badge' => 'esencial'
            ],
            [
                'nombre' => 'Magnesio 500mg (60 Cápsulas)',
                'categoria_slug' => 'vitaminas',
                'precio' => 10.00,
                'precio_original' => null,
                'stock' => 50,
                'descripcion' => 'Apoya la función muscular y del sistema nervioso.',
                'imagen' => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=500',
                'badge' => null
            ]
        ];

        foreach ($productos as $prod) {
            Producto::updateOrCreate(
                ['nombre' => $prod['nombre']],
                [
                    'descripcion' => $prod['descripcion'],
                    'precio' => $prod['precio'],
                    'precio_original' => $prod['precio_original'],
                    'stock' => $prod['stock'],
                    'categoria_id' => $catModels[$prod['categoria_slug']]->id,
                    'activo' => true,
                    'imagen' => $prod['imagen'],
                    'badge' => $prod['badge'],
                    'stock_minimo' => 10,
                    'stock_maximo' => 200,
                ]
            );
        }

        // --- PROMOCIONES ---
        Promocion::updateOrCreate(['nombre' => 'Gran Apertura Online'], [
            'descripcion' => 'Recibe un 15% de descuento en tu primera compra usando el código FARMA15.',
            'descuento_porcentaje' => 15,
            'activo' => true,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(2),
            'imagen' => 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?w=800'
        ]);

        Promocion::updateOrCreate(['nombre' => 'Semana de la Belleza'], [
            'descripcion' => 'Hasta 30% de descuento en marcas seleccionadas de SkinCare.',
            'descuento_porcentaje' => 30,
            'activo' => true,
            'fecha_inicio' => now()->addDays(5),
            'fecha_fin' => now()->addDays(12),
            'imagen' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800'
        ]);
    }
}
