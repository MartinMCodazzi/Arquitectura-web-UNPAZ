<?php

use App\Livewire\Catalog\Catalogo;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Filtros reactivos del catálogo (ADR-0009)
|--------------------------------------------------------------------------
|
| El componente mantiene los filtros en la query string con los nombres
| canónicos en céntimos (ADR-0004); los importes de la UI son pesos.
| El listado consume CatalogService (mismo código que la versión sin JS).
|
*/

test('el catálogo reactivo lista los productos activos', function () {
    $visible = Product::factory()->create(['name' => 'Producto visible']);
    $oculto = Product::factory()->inactive()->create(['name' => 'Producto oculto']);

    Livewire::test(Catalogo::class)
        ->assertSee($visible->name)
        ->assertDontSee($oculto->name);
});

test('el filtro de categoría aplica en el server render y en el componente', function () {
    $electrónica = Category::factory()->create(['slug' => 'electronica', 'name' => 'Electrónica']);
    $hogar = Category::factory()->create(['slug' => 'hogar', 'name' => 'Hogar']);

    $auriculares = Product::factory()->forCategory($electrónica)->create(['name' => 'Auriculares']);
    $sartén = Product::factory()->forCategory($hogar)->create(['name' => 'Sartén']);

    // Server render con la URL canónica.
    $this->get(route('productos.index', ['categoria' => 'electronica']))
        ->assertOk()
        ->assertSee($auriculares->name)
        ->assertDontSee($sartén->name);

    // Reactivo: cambia la categoría y se ve solo la de esa categoría.
    Livewire::test(Catalogo::class)
        ->set('categoria', 'electronica')
        ->assertSee($auriculares->name)
        ->assertDontSee($sartén->name);
});

test('el filtro de precio convierte pesos (UI) a céntimos (URL)', function () {
    $barato = Product::factory()->pricedBetween(100_00, 200_00)->create(['name' => 'Producto barato']);
    $caro = Product::factory()->pricedBetween(900_00, 1_000_00)->create(['name' => 'Producto caro']);

    Livewire::test(Catalogo::class)
        ->set('precioMinPesos', 500)
        ->assertSet('precioMin', 500_00)
        ->assertSet('precioMinPesos', 500)
        ->assertSee($caro->name)
        ->assertDontSee($barato->name);

    Livewire::test(Catalogo::class)
        ->set('precioMaxPesos', 500)
        ->assertSet('precioMax', 500_00)
        ->assertSee($barato->name)
        ->assertDontSee($caro->name);
});

test('el filtro de precio acepta valores vacíos y vuelve a listar todo', function () {
    $producto = Product::factory()->create(['name' => 'Producto visible']);

    Livewire::test(Catalogo::class)
        ->set('precioMinPesos', 500)
        ->set('precioMinPesos', '')
        ->assertSet('precioMin', null)
        ->assertSee($producto->name);
});

test('el filtro de búsqueda filtra por nombre y descripción', function () {
    $coincidencia = Product::factory()->create(['name' => 'Auriculares Bluetooth']);
    $otro = Product::factory()->create(['name' => 'Sartén antiadherente']);

    Livewire::test(Catalogo::class)
        ->set('buscar', 'auriculares')
        ->assertSee($coincidencia->name)
        ->assertDontSee($otro->name);
});

test('el filtro de sólo con stock descarta el stock en cero', function () {
    $conStock = Product::factory()->withStock(5)->create(['name' => 'Producto con stock']);
    $sinStock = Product::factory()->outOfStock()->create(['name' => 'Producto sin stock']);

    Livewire::test(Catalogo::class)
        ->set('soloStock', true)
        ->assertSee($conStock->name)
        ->assertDontSee($sinStock->name);
});

test('limpiar filtros vuelve al listado completo', function () {
    $producto = Product::factory()->create(['name' => 'Producto visible']);

    Livewire::test(Catalogo::class)
        ->set('buscar', 'inexistente')
        ->set('soloStock', true)
        ->call('clearFilters')
        ->assertSet('buscar', '')
        ->assertSet('soloStock', false)
        ->assertSet('precioMin', null)
        ->assertSet('precioMax', null)
        ->assertSet('orden', 'newest')
        ->assertSee($producto->name);
});

test('los criterios de ordenamiento inválidos vuelven al default', function () {
    $producto = Product::factory()->create(['name' => 'Producto visible']);

    $this->get(route('productos.index', ['orden' => 'sql-injection']))
        ->assertOk()
        ->assertSee($producto->name);

    Livewire::test(Catalogo::class)->set('orden', 'sql-injection')->assertOk();
});

test('los parámetros inválidos de la query string se descartan sin romper la página', function () {
    $producto = Product::factory()->create(['name' => 'Producto visible']);

    $this->get(route('productos.index', [
        'categoria' => ['array'],
        'precio_min' => 'no-es-un-numero',
        'buscar' => '%_',
    ]))
        ->assertOk()
        ->assertSee($producto->name);
});
