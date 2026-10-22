<?php

use App\DTO\CategoryOption;
use App\Models\Category;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // El driver por defecto en los tests es `array`: para comprobar la
    // serialización real hay que usar el driver `database`.
    config(['cache.default' => 'database']);

    Cache::forget(CatalogCache::VERSION_KEY);
    Cache::forget(CatalogCache::categoriesKey(CatalogCache::version()));
});

test('la caché del catálogo guarda datos planos y no modelos Eloquent', function () {
    Category::factory()->create(['name' => 'Electrónica', 'slug' => 'electronica']);

    $categories = CatalogCache::categories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first())->toBeInstanceOf(CategoryOption::class)
        ->and($categories->first()->slug)->toBe('electronica');

    // Laravel 13 no deserializa clases PHP desde la caché
    // (config cache.serializable_classes = false): lo guardado debe ser un
    // array plano, o al releerlo quedaría un __PHP_Incomplete_Class.
    $payload = Cache::get(CatalogCache::categoriesKey(CatalogCache::version()));

    expect($payload)->toBeArray()
        ->and($payload[0])->toHaveKeys(['id', 'name', 'slug']);
});

test('guardar una categoría invalida la caché del catálogo', function () {
    Category::factory()->create();

    expect(CatalogCache::categories())->toHaveCount(1);

    Category::factory()->create();

    expect(CatalogCache::categories())->toHaveCount(2);
});

test('las categorías cacheadas respetan el filtro de activas y el orden alfabético', function () {
    Category::factory()->create(['name' => 'Zapatillas', 'slug' => 'zapatillas']);
    Category::factory()->create(['name' => 'Accesorios', 'slug' => 'accesorios']);
    Category::factory()->inactive()->create(['name' => 'Oculta', 'slug' => 'oculta']);

    $categories = CatalogCache::categories();

    expect($categories)->toHaveCount(2)
        ->and($categories->first()->slug)->toBe('accesorios')
        ->and($categories->last()->slug)->toBe('zapatillas');
});
