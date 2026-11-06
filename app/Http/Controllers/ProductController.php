<?php

namespace App\Http\Controllers;

use App\DTO\ProductFilters;
use App\DTO\ProductSummary;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    /**
     * Cantidad de productos relacionados que se muestran en la ficha.
     */
    private const RELATED_PRODUCTS_LIMIT = 4;

    /**
     * Catálogo público con filtros aplicados desde la query string.
     *
     * Desde ADR-0009 (reactividad con Livewire) el listado en sí lo renderiza
     * el componente `App\Livewire\Catalog\Catalogo`, que consume el mismo
     * CatalogService que este controlador. Acá quedan las responsabilidades
     * de página: el redirect canónico del formulario de filtros y los
     * metadatos SEO del `<head>`.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // El formulario de filtros escribe los precios en pesos (legibles para
        // el usuario); la URL canónica los lleva en céntimos. Se
        // redirige para tener una sola URL por combinación de filtros.
        if ($this->hasPriceFormFields($request)) {
            return redirect()->route('productos.index', $this->canonicalQuery($request));
        }

        $filters = ProductFilters::fromRequest($request);
        $page = Paginator::resolveCurrentPage();
        $perPage = (int) config('store.products_per_page');

        return view('products.index', [
            'canonical' => $this->catalogCanonical($filters, $page),
            'image' => $this->ogImage($filters, $perPage),
        ]);
    }

    /**
     * Ficha de producto.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        // Eager loading explícito: el guardia de lazy loading de Laravel 13
        // no cubre el modelo único.
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
     * Imagen destacada para OpenGraph: el primer producto de la primera página
     * del listado, leído directamente de la caché del catálogo (sin consultar
     * la base cuando la entrada ya está cacheada, ADR-0008).
     */
    private function ogImage(ProductFilters $filters, int $perPage): ?string
    {
        $cached = CatalogCache::products($filters, 1, $perPage);
        $first = $cached['items'][0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        return ProductSummary::fromArray($first)->thumbnailUrl(400, 'webp');
    }

    /**
     * URL canónica del catálogo con los filtros normalizados.
     *
     * La página 1 se declara sin el parámetro `page` para que exista una sola
     * URL por combinación de filtros (ADR-0004).
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
     * Los precios del formulario van en pesos y los de la URL en céntimos;
     * el resto de los parámetros ya usa los nombres canónicos.
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
