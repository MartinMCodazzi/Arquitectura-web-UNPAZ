<?php

use App\DTO\ProductFilters;
use Illuminate\Http\Request;

function filtros(array $query = []): ProductFilters
{
    return ProductFilters::fromRequest(Request::create('/productos', 'GET', $query));
}

test('normaliza el slug de la categoría', function () {
    expect(filtros(['categoria' => 'Electrónica'])->category)->toBe('electronica')
        ->and(filtros(['categoria' => '   '])->category)->toBeNull()
        ->and(filtros(['categoria' => ['array']])->category)->toBeNull();
});

test('descarta precios no numéricos o no positivos', function () {
    expect(filtros(['precio_min' => 'abc'])->minPrice)->toBeNull()
        ->and(filtros(['precio_min' => 0])->minPrice)->toBeNull()
        ->and(filtros(['precio_min' => -500])->minPrice)->toBeNull()
        ->and(filtros(['precio_max' => ['array']])->maxPrice)->toBeNull();
});

test('redondea los precios a pesos enteros', function () {
    expect(filtros(['precio_min' => 500_050])->minPrice)->toBe(500_000)
        ->and(filtros(['precio_max' => 500_050])->maxPrice)->toBe(500_100)
        ->and(filtros(['precio_min' => 500_000])->minPrice)->toBe(500_000);
});

test('normaliza el término de búsqueda', function () {
    expect(filtros(['buscar' => '  audífonos  '])->search)->toBe('audífonos')
        ->and(filtros(['buscar' => '%_'])->search)->toBeNull()
        ->and(filtros(['buscar' => ''])->search)->toBeNull();
});

test('normaliza el criterio de ordenamiento', function () {
    expect(filtros(['orden' => 'price_asc'])->sort)->toBe('price_asc')
        ->and(filtros(['orden' => 'drop table'])->sort)->toBe(ProductFilters::DEFAULT_SORT)
        ->and(filtros([])->sort)->toBe(ProductFilters::DEFAULT_SORT);
});

test('la query derivada sólo contiene los filtros activos', function () {
    expect(filtros([])->toQuery())->toBe([])
        ->and(filtros([])->hasActiveFilters())->toBeFalse()
        ->and(filtros(['categoria' => 'electronica', 'orden' => 'price_asc'])->toQuery())->toBe([
            'categoria' => 'electronica',
            'orden' => 'price_asc',
        ]);
});

test('los precios en pesos enteros reflejan el valor de la URL', function () {
    $filtros = filtros(['precio_min' => 500_000, 'precio_max' => 1_200_000]);

    expect($filtros->minPriceInPesos())->toBe(5000)
        ->and($filtros->maxPriceInPesos())->toBe(12000);
});
