<?php

namespace App\Services;

use App\DTO\ProductFilters;
use App\DTO\ProductSummary;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Listado del catálogo: resuelve la página pedida combinando caché y base de
 * datos (ver ADR-0006, ADR-0008).
 *
 * Es el mismo cálculo que consumía la vista server-rendered del catálogo y,
 * desde ADR-0009 (reactividad con Livewire), lo comparten el controlador y el
 * componente `App\Livewire\Catalog\Catalogo`, de modo que el modo sin
 * JavaScript y el modo reactivo salen de exactamente el mismo código.
 */
class CatalogService
{
    /**
     * Página pedida del listado, con resúmenes de producto y paginador listo
     * para generar links (query con los filtros canónicos, ADR-0004).
     */
    public function page(ProductFilters $filters, int $page, int $perPage): LengthAwarePaginator
    {
        [$summaries, $total] = $this->summaries($filters, $page, $perPage);

        return new LengthAwarePaginator(
            $summaries,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => $filters->toQuery(),
            ],
        );
    }

    /**
     * Resúmenes de la página pedida, desde la caché o desde la base de datos.
     *
     * @return array{0: Collection<int, ProductSummary>, 1: int}
     */
    private function summaries(ProductFilters $filters, int $page, int $perPage): array
    {
        $cached = CatalogCache::products($filters, $page, $perPage);

        if ($cached !== null) {
            return [
                collect($cached['items'])->map(fn (array $row): ProductSummary => ProductSummary::fromArray($row)),
                (int) $cached['total'],
            ];
        }

        $paginator = $filters->apply(
            Product::query()->active()->with('category'),
        )
            ->sortedBy($filters->sort)
            ->paginate($perPage);

        $summaries = $paginator->getCollection()
            ->map(fn (Product $product): ProductSummary => ProductSummary::fromModel($product));

        CatalogCache::putProducts($filters, $page, $perPage, [
            'total' => $paginator->total(),
            'items' => $summaries->map(fn (ProductSummary $summary): array => $summary->toArray())->all(),
        ]);

        return [$summaries, $paginator->total()];
    }
}
