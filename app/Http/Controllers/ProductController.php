<?php

namespace App\Http\Controllers;

use App\DTO\ProductFilters;
use App\DTO\ProductSummary;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    /**
     * Cantidad de productos relacionados que se muestran en la ficha.
     */
    private const RELATED_PRODUCTS_LIMIT = 4;

    /**
     * Catálogo público con filtros aplicados desde la query string.
     *
     * El listado se renderiza siempre en servidor: los filtros viajan en la
     * URL para que cada combinación sea indexable y cacheable (ADR-0006).
     *
     * El resultado se guarda en la caché del catálogo como datos planos y se
     * rehidrata en `ProductSummary` (ADR-0010): la vista nunca ve un modelo
     * Eloquent en el listado.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // El formulario de filtros escribe los precios en pesos (legibles para
        // el usuario); la URL canónica los lleva en céntimos (ADR-0002). Se
        // redirige para tener una sola URL por combinación de filtros.
        if ($this->hasPriceFormFields($request)) {
            return redirect()->route('productos.index', $this->canonicalQuery($request));
        }

        $filters = ProductFilters::fromRequest($request);

        $perPage = (int) config('store.products_per_page');
        $page = Paginator::resolveCurrentPage();

        [$summaries, $total] = $this->summaries($filters, $page, $perPage);

        $products = new LengthAwarePaginator(
            $summaries,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('products.index', [
            'products' => $products,
            'categories' => CatalogCache::categories(),
            'filters' => $filters,
            'canonical' => $this->catalogCanonical($filters, $page),
            'image' => $summaries->first()?->thumbnailUrl(400, 'webp'),
        ]);
    }

    /**
     * Ficha de producto.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        // Eager loading explícito: el guardia de lazy loading de Laravel 13
        // no cubre el modelo único (ADR-0009).
        $product->load('category');

        $relatedProducts = Product::query()
            ->active()
            ->inStock()
            ->with('category')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->limit(self::RELATED_PRODUCTS_LIMIT)
            ->get();

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'canonical' => route('productos.show', $product->slug),
            'image' => $product->image_url_large,
        ]);
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

    /**
     * URL canónica del catálogo con los filtros normalizados.
     *
     * La página 1 se declara sin el parámetro `page` para que exista una sola
     * URL por combinación de filtros (ADR-0006).
     */
    private function catalogCanonical(ProductFilters $filters, int $page): string
    {
        $query = $filters->toQuery();

        if ($page > 1) {
            $query['page'] = $page;
        }

        return route('productos.index', $query);
    }

    /**
     * ¿El request viene del formulario de filtros (precios en pesos)?
     */
    private function hasPriceFormFields(Request $request): bool
    {
        return $request->filled('precio_min_pesos') || $request->filled('precio_max_pesos');
    }

    /**
     * Traduce los parámetros del formulario a la query string canónica.
     *
     * Los precios del formulario van en pesos y los de la URL en céntimos
     * (ADR-0002); el resto de los parámetros ya usa los nombres canónicos.
     *
     * @return array<string, mixed>
     */
    private function canonicalQuery(Request $request): array
    {
        $query = $request->query();
        unset($query['precio_min_pesos'], $query['precio_max_pesos']);

        foreach (['min' => 'precio_min', 'max' => 'precio_max'] as $suffix => $key) {
            $pesos = $request->query('precio_'.$suffix.'_pesos');

            if (is_numeric($pesos) && (int) $pesos > 0) {
                $query[$key] = (int) $pesos * 100;
            }
        }

        return array_filter(
            $query,
            fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
