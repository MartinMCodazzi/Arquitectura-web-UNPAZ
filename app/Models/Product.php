<?php

namespace App\Models;

use App\Services\ImageService;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'price', 'stock', 'image_path', 'category_id', 'is_active'])]
#[RouteKey('slug')]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * `price` se almacena en céntimos (entero) y `stock` en unidades enteras.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Categoría a la que pertenece el producto.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Limita la consulta a los productos visibles en el catálogo público.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Limita la consulta a los productos con existencias disponibles.
     */
    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * Limita la consulta a los productos de una categoría.
     */
    #[Scope]
    protected function inCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    /**
     * Limita la consulta a los productos dentro de un rango de precios (céntimos).
     */
    #[Scope]
    protected function priceBetween(Builder $query, ?int $minPrice, ?int $maxPrice): void
    {
        $query->when(
            $minPrice !== null,
            fn (Builder $q) => $q->where('price', '>=', $minPrice),
        )->when(
            $maxPrice !== null,
            fn (Builder $q) => $q->where('price', '<=', $maxPrice),
        );
    }

    /**
     * Busca productos por nombre o descripción.
     *
     * Los comodines `%` y `_` se eliminan del término para que el usuario no
     * pueda alterar el comportamiento del LIKE.
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = Str::of((string) $term)->replace(['%', '_'], '')->trim()->toString();

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query->whereLike('name', "%{$term}%")
                ->orWhereLike('description', "%{$term}%");
        });
    }

    /**
     * Ordena el catálogo de forma determinista (con desempate por id).
     */
    #[Scope]
    protected function sortedBy(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'name' => $query->orderBy('name')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * Precio formateado como moneda (presentación, nunca lógica de negocio).
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => Number::currency(
            $this->price / 100,
            config('store.currency'),
            config('store.locale'),
        ));
    }

    /**
     * Indica si el producto tiene existencias.
     */
    protected function isInStock(): Attribute
    {
        return Attribute::get(fn (): bool => $this->stock > 0);
    }

    /**
     * URL de la miniatura del producto (400 px), si tiene.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnailUrl(400));
    }

    /**
     * URL de la miniatura grande del producto (800 px), si tiene.
     */
    protected function imageUrlLarge(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnailUrl(800));
    }

    /**
     * URL de la miniatura chica del producto (200 px), si tiene.
     */
    protected function imageUrlSmall(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnailUrl(200));
    }

    /**
     * URL de la miniatura del producto en el ancho y formato pedidos.
     *
     * Devuelve `null` cuando el producto no tiene imagen o cuando la
     * miniatura todavía no fue generada: en ese caso las vistas muestran el
     * texto alternativo.
     */
    public function thumbnailUrl(int $width = 400, string $format = 'webp'): ?string
    {
        return app(ImageService::class)->thumbnailUrl($this, $width, $format);
    }

    /**
     * El producto puede comprarse: está activo y hay existencias.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Verifica que haya existencias suficientes para la cantidad pedida.
     */
    public function hasStock(int $quantity): bool
    {
        return $quantity > 0 && $this->stock >= $quantity;
    }
}
