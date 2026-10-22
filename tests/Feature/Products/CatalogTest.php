<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

test('el catálogo muestra sólo los productos activos', function () {
    $visible = Product::factory()->create(['name' => 'Producto visible']);
    $oculto = Product::factory()->inactive()->create(['name' => 'Producto oculto']);

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee($visible->name)
        ->assertDontSee($oculto->name);
});

test('el catálogo filtra por categoría usando el slug de la query string', function () {
    $electronica = Category::factory()->create(['slug' => 'electronica', 'name' => 'Electrónica']);
    $hogar = Category::factory()->create(['slug' => 'hogar', 'name' => 'Hogar']);

    $auriculares = Product::factory()->forCategory($electronica)->create(['name' => 'Auriculares']);
    $sartén = Product::factory()->forCategory($hogar)->create(['name' => 'Sartén']);

    $this->get(route('productos.index', ['categoria' => 'electronica']))
        ->assertOk()
        ->assertSee($auriculares->name)
        ->assertDontSee($sartén->name);
});

test('el catálogo filtra por rango de precios en céntimos', function () {
    $barato = Product::factory()->pricedBetween(100_00, 200_00)->create(['name' => 'Producto barato']);
    $caro = Product::factory()->pricedBetween(900_00, 1_000_00)->create(['name' => 'Producto caro']);

    $this->get(route('productos.index', ['precio_min' => 500_00]))
        ->assertOk()
        ->assertSee($caro->name)
        ->assertDontSee($barato->name);

    $this->get(route('productos.index', ['precio_max' => 500_00]))
        ->assertOk()
        ->assertSee($barato->name)
        ->assertDontSee($caro->name);
});

test('el catálogo filtra por término de búsqueda', function () {
    $coincidencia = Product::factory()->create([
        'name' => 'Auriculares Bluetooth',
        'description' => 'Cancelación activa de ruido',
    ]);
    $otro = Product::factory()->create([
        'name' => 'Sartén antiadherente',
        'description' => 'Aluminio reforzado',
    ]);

    $this->get(route('productos.index', ['buscar' => 'auriculares']))
        ->assertOk()
        ->assertSee($coincidencia->name)
        ->assertDontSee($otro->name);
});

test('el catálogo filtra por disponibilidad de stock', function () {
    $conStock = Product::factory()->withStock(5)->create(['name' => 'Producto con stock']);
    $sinStock = Product::factory()->outOfStock()->create(['name' => 'Producto sin stock']);

    $this->get(route('productos.index', ['solo_stock' => 1]))
        ->assertOk()
        ->assertSee($conStock->name)
        ->assertDontSee($sinStock->name);
});

test('el catálogo ordena por precio ascendente cuando se lo pide', function () {
    $caro = Product::factory()->create(['name' => 'Producto caro', 'price' => 900_00]);
    $barato = Product::factory()->create(['name' => 'Producto barato', 'price' => 100_00]);

    $response = $this->get(route('productos.index', ['orden' => 'price_asc']))
        ->assertOk()
        ->assertSeeInOrder([$barato->name, $caro->name]);
});

test('el formulario de filtros redirige a la URL canónica con los precios en céntimos', function () {
    $this->get(route('productos.index', [
        'precio_min_pesos' => 5000,
        'categoria' => 'electronica',
    ]))
        ->assertRedirect(route('productos.index', [
            'categoria' => 'electronica',
            'precio_min' => 500_000,
        ]));
});

test('el formulario de filtros sin precios no genera redirección', function () {
    $categoria = Category::factory()->create(['slug' => 'electronica']);
    $producto = Product::factory()->forCategory($categoria)->create(['name' => 'Producto visible']);

    $this->get(route('productos.index', [
        'precio_min_pesos' => '',
        'precio_max_pesos' => '',
        'categoria' => 'electronica',
    ]))
        ->assertOk()
        ->assertSee($producto->name);
});

test('los parámetros inválidos de la query string se descartan', function () {
    $producto = Product::factory()->create(['name' => 'Producto visible']);

    $this->get(route('productos.index', [
        'categoria' => ['array'],
        'precio_min' => 'no-es-un-numero',
        'buscar' => '%_',
        'orden' => 'sql-injection',
    ]))
        ->assertOk()
        ->assertSee($producto->name);
});

test('la ficha de producto muestra el detalle y los productos relacionados', function () {
    $categoria = Category::factory()->create();
    $producto = Product::factory()->forCategory($categoria)->create(['name' => 'Producto destacado']);
    $relacionado = Product::factory()->forCategory($categoria)->withStock(3)->create(['name' => 'Producto relacionado']);

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertSee($producto->name)
        ->assertSee($producto->formatted_price)
        ->assertSee($relacionado->name);
});

test('la ficha de producto responde 404 cuando el producto está desactivado', function () {
    $producto = Product::factory()->inactive()->create();

    $this->get(route('productos.show', $producto))->assertNotFound();
});

test('la ficha de producto responde 404 cuando el producto no existe', function () {
    $this->get('/productos/producto-inexistente')->assertNotFound();
});

test('la ficha de producto no genera consultas N+1', function () {
    $categoria = Category::factory()->create();
    $producto = Product::factory()->forCategory($categoria)->create();

    Product::factory()->count(3)->forCategory($categoria)->create();

    DB::enableQueryLog();
    DB::flushQueryLog();

    $this->get(route('productos.show', $producto))->assertOk();

    // Producto + categoría + productos relacionados + sus categorías.
    expect(DB::getQueryLog())->toHaveCount(4);
});

test('la URL de una categoría redirige al catálogo filtrado', function () {
    $categoria = Category::factory()->create(['slug' => 'electronica']);

    $this->get(route('categorias.show', $categoria))
        ->assertRedirect(route('productos.index', ['categoria' => 'electronica']));
});

test('la URL de una categoría inactiva responde 404', function () {
    $categoria = Category::factory()->inactive()->create();

    $this->get(route('categorias.show', $categoria))->assertNotFound();
});

test('la raíz redirige al catálogo', function () {
    $this->get('/')->assertRedirect('/productos');
});
