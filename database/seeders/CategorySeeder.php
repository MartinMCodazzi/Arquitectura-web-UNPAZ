<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Catálogo base de categorías de la tienda.
 *
 * El seeder es idempotente: se puede ejecutar varias veces sin duplicar filas
 * porque usa updateOrCreate sobre el slug (clave natural de la categoría).
 */
class CategorySeeder extends Seeder
{
    /**
     * @var list<array<string, mixed>>
     */
    private const CATEGORIES = [
        [
            'name' => 'Electrónica',
            'slug' => 'electronica',
            'description' => 'Audio, wearables y accesorios para acompañar tu día a día conectado.',
            'is_active' => true,
        ],
        [
            'name' => 'Hogar y Cocina',
            'slug' => 'hogar-y-cocina',
            'description' => 'Utensilios, organización y todo lo que hace mejor funcionar una casa.',
            'is_active' => true,
        ],
        [
            'name' => 'Moda',
            'slug' => 'moda',
            'description' => 'Indumentaria, calzado y accesorios con estilo para cada ocasión.',
            'is_active' => true,
        ],
        [
            'name' => 'Deportes',
            'slug' => 'deportes',
            'description' => 'Equipamiento para entrenar, competir y moverse con más energía.',
            'is_active' => true,
        ],
        [
            'name' => 'Libros',
            'slug' => 'libros',
            'description' => 'Novela, ensayo y material de estudio seleccionados por nuestro equipo editorial.',
            'is_active' => true,
        ],
        [
            'name' => 'Juguetes',
            'slug' => 'juguetes',
            'description' => 'Juegos educativos y de entretenimiento para todas las edades.',
            'is_active' => true,
        ],
        [
            'name' => 'Belleza y Cuidado Personal',
            'slug' => 'belleza-y-cuidado-personal',
            'description' => 'Cuidado de la piel, el cabello y el bienestar diario.',
            'is_active' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
