<?php

use App\DTO\ProductFilters;
use App\DTO\ProductSummary;
use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

test('el listado del catálogo se sirve desde la caché sin consultar los productos', function () {
    Product::factory()->withStock(5)->create(['name' => 'Producto visible']);

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee('Producto visible');

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee('Producto visible');

    $consultas = collect(DB::getQueryLog())->pluck('query');

    expect($consultas->filter(fn (string $sql): bool => str_contains($sql, 'from "products"')))->toBeEmpty();
});

test('la caché del listado guarda datos planos y no modelos Eloquent', function () {
    Product::factory()->withStock(5)->create(['name' => 'Producto cacheado']);

    $this->get(route('productos.index'))->assertOk();

    $filters = ProductFilters::fromRequest(Request::create('/productos'));

    $payload = Cache::get(CatalogCache::productsKey(
        CatalogCache::version(),
        $filters,
        1,
        (int) config('store.products_per_page'),
    ));

    expect($payload)->toBeArray()
        ->and($payload)->toHaveKeys(['total', 'items'])
        ->and($payload['items'][0])->toHaveKeys([
            'id', 'name', 'slug', 'price', 'stock', 'category', 'image_version', 'image_formats',
        ]);
});

test('el listado cacheado se rehidrata en resúmenes de producto', function () {
    $categoria = Category::factory()->create(['name' => 'Electrónica', 'slug' => 'electronica']);

    Product::factory()->forCategory($categoria)->withStock(5)->create(['name' => 'Auriculares Pro']);

    $this->get(route('productos.index'))->assertOk()->assertSee('Auriculares Pro');

    $filters = ProductFilters::fromRequest(Request::create('/productos'));

    $payload = Cache::get(CatalogCache::productsKey(
        CatalogCache::version(),
        $filters,
        1,
        (int) config('store.products_per_page'),
    ));

    $resumen = ProductSummary::fromArray($payload['items'][0]);

    expect($resumen)->toBeInstanceOf(ProductSummary::class)
        ->and($resumen->name)->toBe('Auriculares Pro')
        ->and($resumen->category?->slug)->toBe('electronica')
        ->and($resumen->is_in_stock)->toBeTrue()
        ->and($resumen->formatted_price)->toContain('$');
});

test('un cambio en el catálogo invalida el listado cacheado', function () {
    $producto = Product::factory()->withStock(5)->create(['name' => 'Producto activo']);

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee('Producto activo');

    $producto->update(['is_active' => false]);

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertDontSee('Producto activo');
});

test('la paginación del catálogo se conserva cuando el listado sale de la caché', function () {
    Product::factory()->count(15)->create(['stock' => 5]);

    $primera = $this->get(route('productos.index'))->assertOk();

    expect($primera->viewData('products')->total())->toBe(15)
        ->and($primera->viewData('products')->count())->toBe(12);

    $segunda = $this->get(route('productos.index', ['page' => 2]))->assertOk();

    expect($segunda->viewData('products')->total())->toBe(15)
        ->and($segunda->viewData('products')->count())->toBe(3)
        ->and($segunda->viewData('products')->currentPage())->toBe(2);
});

test('cada combinación de filtros tiene su propia entrada de caché', function () {
    Product::factory()->withStock(5)->create();

    $this->get(route('productos.index'))->assertOk();
    $this->get(route('productos.index', ['categoria' => 'electronica']))->assertOk();

    $filtros = ProductFilters::fromRequest(Request::create('/productos'));
    $filtrosCategoria = ProductFilters::fromRequest(Request::create('/productos?categoria=electronica'));

    $perPage = (int) config('store.products_per_page');

    expect(CatalogCache::productsKey(CatalogCache::version(), $filtros, 1, $perPage))
        ->not->toBe(CatalogCache::productsKey(CatalogCache::version(), $filtrosCategoria, 1, $perPage))
        ->and(CatalogCache::products($filtros, 1, $perPage))->not->toBeNull()
        ->and(CatalogCache::products($filtrosCategoria, 1, $perPage))->not->toBeNull();
});
