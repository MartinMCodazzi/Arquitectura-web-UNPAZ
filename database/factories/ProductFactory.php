<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Product>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->paragraph(),
            // Precio en céntimos: entre ARS 5.000 y ARS 250.000.
            'price' => fake()->numberBetween(500_000, 25_000_000),
            'stock' => fake()->numberBetween(0, 60),
            'image_path' => null,
            'category_id' => Category::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Producto sin existencias.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Producto desactivado (no aparece en el catálogo público).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Producto con existencias iniciales garantizadas.
     */
    public function withStock(int $stock = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
        ]);
    }

    /**
     * Producto dentro de un rango de precios (en céntimos).
     */
    public function pricedBetween(int $minPrice, int $maxPrice): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->numberBetween($minPrice, $maxPrice),
        ]);
    }

    /**
     * Producto de una categoría concreta.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Producto con una imagen asociada.
     */
    public function withImage(string $imagePath = 'products/ejemplo.webp'): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => $imagePath,
        ]);
    }
}
