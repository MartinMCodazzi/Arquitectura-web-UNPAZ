<?php

namespace App\DTO;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Estado de los filtros del catálogo, normalizado a partir de la query string.
 *
 * Los precios viajan en la URL en céntimos (ver ADR-0006) y se normalizan a
 * múltiplos de 100 (pesos enteros) para que el formulario y la URL nunca
 * discrepen. La categoría se referencia por *slug*.
 */
final readonly class ProductFilters
{
    /**
     * Criterios de ordenamiento admitidos.
     *
     * @var list<string>
     */
    public const SORT_OPTIONS = [
        'newest',
        'oldest',
        'price_asc',
        'price_desc',
        'name',
    ];

    /**
     * Ordenamiento por defecto.
     */
    public const DEFAULT_SORT = 'newest';

    public function __construct(
        public ?string $category = null,
        public ?int $minPrice = null,
        public ?int $maxPrice = null,
        public ?string $search = null,
        public bool $inStockOnly = false,
        public string $sort = self::DEFAULT_SORT,
    ) {}

    /**
     * Construye los filtros a partir de la request.
     *
     * Todos los valores que llegan desde la URL se validan y normalizan: un
     * parámetro mal formado se descarta en lugar de romper la consulta.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            category: self::normalizeSlug($request->query('categoria')),
            minPrice: self::floorToPeso(self::normalizePrice($request->query('precio_min'))),
            maxPrice: self::ceilToPeso(self::normalizePrice($request->query('precio_max'))),
            search: self::normalizeSearch($request->query('buscar')),
            inStockOnly: $request->boolean('solo_stock'),
            sort: self::normalizeSort($request->query('orden')),
        );
    }

    /**
     * Aplica los filtros a una consulta de productos.
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->category !== null,
                fn (Builder $builder) => $builder->whereHas(
                    'category',
                    fn (Builder $category) => $category->active()->where('slug', $this->category),
                ),
            )
            ->when(
                $this->minPrice !== null || $this->maxPrice !== null,
                fn (Builder $builder) => $builder->priceBetween($this->minPrice, $this->maxPrice),
            )
            ->when(
                $this->search !== null,
                fn (Builder $builder) => $builder->search($this->search),
            )
            ->when(
                $this->inStockOnly,
                fn (Builder $builder) => $builder->inStock(),
            );
    }

    /**
     * Parámetros de la query string derivados de los filtros activos.
     *
     * @return array<string, string|int>
     */
    public function toQuery(): array
    {
        return array_filter([
            'categoria' => $this->category,
            'precio_min' => $this->minPrice,
            'precio_max' => $this->maxPrice,
            'buscar' => $this->search,
            'solo_stock' => $this->inStockOnly ? '1' : null,
            'orden' => $this->sort !== self::DEFAULT_SORT ? $this->sort : null,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * ¿Hay algún filtro activo distinto del ordenamiento por defecto?
     */
    public function hasActiveFilters(): bool
    {
        return $this->toQuery() !== [];
    }

    /**
     * Precio mínimo expresado en pesos enteros (para el formulario).
     */
    public function minPriceInPesos(): ?int
    {
        return $this->minPrice === null ? null : intdiv($this->minPrice, 100);
    }

    /**
     * Precio máximo expresado en pesos enteros (para el formulario).
     */
    public function maxPriceInPesos(): ?int
    {
        return $this->maxPrice === null ? null : intdiv($this->maxPrice, 100);
    }

    /**
     * Etiqueta legible del criterio de ordenamiento.
     */
    public function sortLabel(): string
    {
        return self::sortLabelFor($this->sort);
    }

    /**
     * Etiqueta legible de un criterio de ordenamiento.
     */
    public static function sortLabelFor(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'Más antiguos',
            'price_asc' => 'Precio: menor a mayor',
            'price_desc' => 'Precio: mayor a menor',
            'name' => 'Nombre (A-Z)',
            default => 'Más recientes',
        };
    }

    /**
     * Normaliza el slug de una categoría.
     */
    private static function normalizeSlug(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $slug = Str::slug(trim($value));

        return $slug === '' ? null : Str::limit($slug, 120, '');
    }

    /**
     * Normaliza un importe recibido en la URL (céntimos) a un entero positivo.
     */
    private static function normalizePrice(mixed $value): ?int
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $cents = (int) $value;

        return $cents > 0 ? $cents : null;
    }

    /**
     * Redondea hacia abajo al peso entero más cercano.
     */
    private static function floorToPeso(?int $cents): ?int
    {
        return $cents === null ? null : intdiv($cents, 100) * 100;
    }

    /**
     * Redondea hacia arriba al peso entero más cercano.
     */
    private static function ceilToPeso(?int $cents): ?int
    {
        if ($cents === null) {
            return null;
        }

        return (int) (ceil($cents / 100) * 100);
    }

    /**
     * Normaliza el término de búsqueda.
     */
    private static function normalizeSearch(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $term = Str::of($value)->replace(['%', '_'], '')->trim()->limit(120, '')->toString();

        return $term === '' ? null : $term;
    }

    /**
     * Normaliza el criterio de ordenamiento.
     */
    private static function normalizeSort(mixed $value): string
    {
        return is_string($value) && in_array($value, self::SORT_OPTIONS, true)
            ? $value
            : self::DEFAULT_SORT;
    }
}
