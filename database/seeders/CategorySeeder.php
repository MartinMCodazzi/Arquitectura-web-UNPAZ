<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Catálogo base de categorías gamer y tecnología de la tienda.
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
            'name' => 'Componentes de PC',
            'slug' => 'componentes-pc',
            'description' => 'Placas de video, procesadores, memorias RAM, discos SSD y fuentes para armar o mejorar tu setup.',
            'is_active' => true,
        ],
        [
            'name' => 'Periféricos Gamer',
            'slug' => 'perifericos-gamer',
            'description' => 'Teclados mecánicos, mouses ópticos de alta precisión, auriculares 7.1 y accesorios para competir.',
            'is_active' => true,
        ],
        [
            'name' => 'Monitores Gamer',
            'slug' => 'monitores-gamer',
            'description' => 'Pantallas de alta tasa de refresco, paneles IPS rápidos y curvaturas inmersivas para no perderte ningún fotograma.',
            'is_active' => true,
        ],
        [
            'name' => 'Notebooks & PCs Gamer',
            'slug' => 'notebooks-y-pcs',
            'description' => 'Equipos portátiles de alto rendimiento y PCs armadas listas para jugar y transmitir en directo.',
            'is_active' => true,
        ],
        [
            'name' => 'Consolas y Videojuegos',
            'slug' => 'consolas-y-gaming',
            'description' => 'Consolas de última generación, joysticks inalámbricos y mandos de precisión para todas las plataformas.',
            'is_active' => true,
        ],
        [
            'name' => 'Sillas y Setup Gamer',
            'slug' => 'sillas-y-setup',
            'description' => 'Sillas ergonómicas de competición, escritorios gaming y soportes para tu estación de batalla.',
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
