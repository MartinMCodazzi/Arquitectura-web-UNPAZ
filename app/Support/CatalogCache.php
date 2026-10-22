<?php

namespace App\Support;

use App\DTO\CategoryOption;
use App\DTO\ProductFilters;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Caché del catálogo con claves versionadas (ver ADR-0008 y ADR-0010).
 *
 * El driver `database` no soporta "cache tags", por lo que la invalidación
 * se hace incrementando una versión global del catálogo: las claves viejas
 * quedan huérfanas y se limpian solas al vencer su TTL.
 *
 * Lo que se guarda en caché son **datos planos** (arrays), nunca modelos
 * Eloquent: en Laravel 13 la caché no deserializa clases PHP por defecto
 * (`cache.serializable_classes => false`) y los modelos se convertían en
 * `__PHP_Incomplete_Class`.
 */
final class CatalogCache
{
    /**
     * Clave de la versión vigente del catálogo.
     */
    public const VERSION_KEY = 'catalogo.version';

    /**
     * Tiempo de vida de la lista de categorías (segundos).
     */
    public const CATEGORIES_TTL = 3600;

    /**
     * Tiempo de vida del listado filtrado (segundos).
     */
    public const PRODUCTS_TTL = 600;

    /**
     * Versión vigente del catálogo.
     */
    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Invalida el catálogo: incrementa la versión y borra la entrada anterior.
     */
    public static function bump(): void
    {
        $current = self::version();

        Cache::forever(self::VERSION_KEY, $current + 1);
        Cache::forget(self::categoriesKey($current));
    }

    /**
     * Categorías visibles del catálogo, ordenadas alfabéticamente.
     *
     * @return Collection<int, CategoryOption>
     */
    public static function categories(): Collection
    {
        $key = self::categoriesKey(self::version());

        $cached = Cache::get($key);

        // Una entrada que no sea un array plano está obsoleta (por ejemplo, una
        // versión anterior que guardaba modelos Eloquent): se descarta y se
        // vuelve a construir.
        if (! is_array($cached)) {
            Cache::forget($key);

            $cached = Cache::remember($key, self::CATEGORIES_TTL, fn () => self::categoryRows());
        }

        return collect($cached)
            ->map(fn (array $category): CategoryOption => CategoryOption::fromArray($category))
            ->values();
    }

    /**
     * Listado filtrado del catálogo, cacheado como datos planos.
     *
     * Devuelve `null` cuando todavía no está en caché (y descarta entradas
     * obsoletas que no sean arrays planos).
     *
     * @return array{total: int, items: list<array<string, mixed>>}|null
     */
    public static function products(ProductFilters $filters, int $page, int $perPage): ?array
    {
        $key = self::productsKey(self::version(), $filters, $page, $perPage);

        $cached = Cache::get($key);

        if (! is_array($cached) || ! isset($cached['total'], $cached['items']) || ! is_array($cached['items'])) {
            Cache::forget($key);

            return null;
        }

        return $cached;
    }

    /**
     * Guarda el listado filtrado del catálogo en la caché.
     *
     * @param  array{total: int, items: list<array<string, mixed>>}  $payload
     */
    public static function putProducts(ProductFilters $filters, int $page, int $perPage, array $payload): void
    {
        Cache::put(
            self::productsKey(self::version(), $filters, $page, $perPage),
            $payload,
            self::PRODUCTS_TTL,
        );
    }

    /**
     * Clave versionada del listado filtrado.
     *
     * La huella (`fingerprint`) resume la combinación de filtros, la página y
     * el tamaño de página: dos combinaciones distintas nunca comparten clave.
     */
    public static function productsKey(int $version, ProductFilters $filters, int $page, int $perPage): string
    {
        $fingerprint = sha1(json_encode([
            $filters->toQuery(),
            $page,
            $perPage,
        ], JSON_THROW_ON_ERROR));

        return 'catalogo.productos.v'.$version.'.'.$fingerprint;
    }

    /**
     * Categorías del catálogo como datos planos, listos para cachear.
     *
     * @return list<array{id: int, name: string, slug: string}>
     */
    private static function categoryRows(): array
    {
        return Category::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();
    }

    /**
     * Clave versionada de la lista de categorías.
     */
    public static function categoriesKey(int $version): string
    {
        return 'catalogo.categorias.v'.$version;
    }
}
