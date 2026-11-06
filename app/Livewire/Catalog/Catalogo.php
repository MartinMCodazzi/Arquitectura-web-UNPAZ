<?php

namespace App\Livewire\Catalog;

use App\DTO\ProductFilters;
use App\Services\CatalogService;
use App\Support\CatalogCache;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Catálogo público reactivo (ADR-0009).
 *
 * Los filtros viven en la query string como en la versión server-rendered
 * (ADR-0004): son propiedades `#[Url]` con los nombres canónicos
 * (`categoria`, `buscar`, `precio_min`, `precio_max`, `orden`, `solo_stock`),
 * de modo que cada combinación sigue siendo compartible e indexable.
 *
 * Los precios viajan y se normalizan en céntimos canónicos; los
 * inputs del formulario muestran pesos enteros, así que cada precio tiene dos
 * propiedades sincronizadas: `precioMin/precioMax` (URL, céntimos) y
 * `precioMinPesos/precioMaxPesos` (UI). Sólo las de la UI reciben updates del
 * cliente, por eso no hay loops de reactividad.
 *
 * El listado sale de CatalogService (mismo código que la versión sin
 * JavaScript), que reutiliza CatalogCache y ProductSummary (ADR-0008).
 */
class Catalogo extends Component
{
    use WithPagination;

    /**
     * Texto de búsqueda (nombre o descripción).
     */
    #[Url]
    public string $buscar = '';

    /**
     * Categoría por slug, o '' para todas.
     */
    #[Url]
    public string $categoria = '';

    /**
     * Precio mínimo en céntimos canónicos (múltiplo de 100).
     */
    #[Url(as: 'precio_min')]
    public ?int $precioMin = null;

    /**
     * Precio máximo en céntimos canónicos (múltiplo de 100).
     */
    #[Url(as: 'precio_max')]
    public ?int $precioMax = null;

    /**
     * Criterio de ordenamiento admitido por ProductFilters.
     */
    #[Url]
    public string $orden = ProductFilters::DEFAULT_SORT;

    /**
     * Listar solamente productos con stock.
     */
    #[Url]
    public bool $soloStock = false;

    /**
     * Precio mínimo del input, en pesos (UI); sincronizado con `precioMin`.
     */
    public ?int $precioMinPesos = null;

    /**
     * Precio máximo del input, en pesos (UI); sincronizado con `precioMax`.
     */
    public ?int $precioMaxPesos = null;

    public function mount(): void
    {
        // La URL viaja en céntimos y éstos normalizan a múltiplos
        // de peso (ADR-0004), así que la división es exacta.
        $this->precioMinPesos = $this->precioMin !== null ? intdiv($this->precioMin, 100) : null;
        $this->precioMaxPesos = $this->precioMax !== null ? intdiv($this->precioMax, 100) : null;
    }

    /**
     * Los updates que llegan del cliente son los de la UI: texto, categoría,
     * orden y "solo stock". Al cambiar un filtro se vuelve a la primera página.
     *
     * @param  string  $property  Propiedad actualizada.
     * @param  mixed  $value  Valor enviado por el cliente.
     */
    public function updated(string $property, $value = null): void
    {
        if (in_array($property, ['buscar', 'categoria', 'orden', 'soloStock'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Precio mínimo en pesos (UI) → céntimos canónicos.
     *
     * @param  mixed  $value  Input crudo (puede venir strings vacíos).
     */
    public function updatedPrecioMinPesos($value = null): void
    {
        $this->precioMin = $this->pesosToCents($value);

        $this->resetPage();
    }

    /**
     * Precio máximo en pesos (UI) → céntimos canónicos.
     *
     * @param  mixed  $value  Input crudo (puede venir strings vacíos).
     */
    public function updatedPrecioMaxPesos($value = null): void
    {
        $this->precioMax = $this->pesosToCents($value);

        $this->resetPage();
    }

    /**
     * "Limpiar filtros": vuelve a la vista por defecto del catálogo.
     */
    public function clearFilters(): void
    {
        $this->buscar = '';
        $this->categoria = '';
        $this->orden = ProductFilters::DEFAULT_SORT;
        $this->soloStock = false;
        $this->precioMinPesos = null;
        $this->precioMaxPesos = null;
        $this->precioMin = null;
        $this->precioMax = null;

        $this->resetPage();
    }

    public function render(): View
    {
        $filters = $this->filters();

        $products = app(CatalogService::class)->page(
            $filters,
            $this->getPage(),
            (int) config('store.products_per_page'),
        );

        return view('livewire.catalog.catalogo', [
            'products' => $products,
            'categories' => CatalogCache::categories(),
            'filters' => $filters,
        ]);
    }

    /**
     * Filtros canónicos para CatalogService.
     */
    private function filters(): ProductFilters
    {
        return new ProductFilters(
            category: $this->categoria !== '' ? $this->categoria : null,
            minPrice: $this->precioMin,
            maxPrice: $this->precioMax,
            search: $this->buscar !== '' ? $this->buscar : null,
            inStockOnly: $this->soloStock,
            sort: in_array($this->orden, ProductFilters::SORT_OPTIONS, true)
                ? $this->orden
                : ProductFilters::DEFAULT_SORT,
        );
    }

    /**
     * Input UI (pesos, admite vacío) → céntimos canónicos (o null).
     */
    private function pesosToCents(mixed $value): ?int
    {
        $pesos = is_string($value) ? trim($value) : $value;

        if ($pesos === null || $pesos === '') {
            return null;
        }

        $pesos = (int) $pesos;

        return $pesos > 0 ? $pesos * 100 : null;
    }
}
