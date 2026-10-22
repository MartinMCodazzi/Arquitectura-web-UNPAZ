<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Category>
     */
    protected $model = Category::class;

    /**
     * Categorías realistas de una tienda general con presencia en Argentina.
     *
     * @var list<string>
     */
    public const NAMES = [
        'Electrónica',
        'Hogar y Cocina',
        'Moda',
        'Deportes',
        'Libros',
        'Juguetes',
        'Belleza y Cuidado Personal',
    ];

    /**
     * Descripciones por categoría.
     *
     * @var array<string, string>
     */
    public const DESCRIPTIONS = [
        'Electrónica' => 'Audio, wearables, accessories y todo lo que acompaña tu día a día conectado.',
        'Hogar y Cocina' => 'Utensilios, organización y todo para que tu casa funcione mejor.',
        'Moda' => 'Indumentaria, calzado y accesorios con estilo para cada ocasión.',
        'Deportes' => 'Equipamiento para entrenar, competir y moverse con más energía.',
        'Libros' => 'Novela, ensayo y material de estudio seleccionados por nuestro equipo editorial.',
        'Juguetes' => 'Juegos educativos y de entertainment para todas las edades.',
        'Belleza y Cuidado Personal' => 'Cuidado de la piel, el cabello y el bienestar diario.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(self::NAMES);

        return [
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'description' => self::DESCRIPTIONS[$name] ?? fake()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Categoría desactivada (no aparece en el catálogo público).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Genera un slug único a partir del nombre.
     */
    protected function uniqueSlug(string $name): string
    {
        return Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999);
    }
}
