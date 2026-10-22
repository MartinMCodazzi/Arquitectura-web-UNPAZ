<?php

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Number;

test('agregar un producto lo guarda en el carrito de la sesión', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 2,
    ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 2]);
});

test('agregar un producto sin stock no lo suma al carrito', function () {
    $producto = Product::factory()->outOfStock()->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('agregar un producto desactivado no lo suma al carrito', function () {
    $producto = Product::factory()->inactive()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('la cantidad agregada se limita al stock disponible', function () {
    $producto = Product::factory()->withStock(3)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 10,
    ])
        ->assertRedirect()
        ->assertSessionHas('warning');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('el carrito vacío muestra el estado vacío', function () {
    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Tu carrito está vacío');
});

test('el carrito muestra las líneas agregadas', function () {
    $producto = Product::factory()->withStock(5)->create([
        'name' => 'Sartén de hierro',
        'price' => 10_000,
    ]);

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 2,
    ]);

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Sartén de hierro')
        ->assertSee(Number::currency(10_000 / 100, 'ARS', 'es_AR'))
        ->assertSee(Number::currency(20_000 / 100, 'ARS', 'es_AR'));
});

test('los totales del carrito aplican el IVA configurado', function () {
    $producto = Product::factory()->withStock(5)->create(['price' => 10_000]);

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 2,
    ]);

    $totals = app(CartService::class)->totals();

    expect($totals['subtotal'])->toBe(20_000)
        ->and($totals['taxes'])->toBe(4_200)
        ->and($totals['total'])->toBe(24_200);

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee(Number::currency(24_200 / 100, 'ARS', 'es_AR'));
});

test('se puede cambiar la cantidad de una línea del carrito', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ]);

    $this->patch(route('carrito.actualizar', $producto), ['quantity' => 3])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('actualizar la cantidad a cero quita el producto del carrito', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ]);

    $this->patch(route('carrito.actualizar', $producto), ['quantity' => 0])
        ->assertRedirect();

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('se puede quitar un producto del carrito', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ]);

    $this->delete(route('carrito.eliminar', $producto))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('se puede vaciar el carrito completo', function () {
    $primero = Product::factory()->withStock(5)->create();
    $segundo = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), ['product_id' => $primero->id, 'quantity' => 1]);
    $this->post(route('carrito.agregar'), ['product_id' => $segundo->id, 'quantity' => 1]);

    $this->delete(route('carrito.vaciar'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('el carrito descarta los productos que fueron desactivados', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ]);

    $producto->update(['is_active' => false]);

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Tu carrito está vacío');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('el carrito descarta los productos que fueron borrados', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ]);

    $producto->delete();

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Tu carrito está vacío');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('el encabezado muestra la cantidad de unidades del carrito', function () {
    $producto = Product::factory()->withStock(9)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 7,
    ]);

    expect(app(CartService::class)->count())->toBe(7);

    $response = $this->get(route('productos.index'))->assertOk();

    expect($response->getContent())->toMatch('/bg-indigo-600[^>]*>\s*7\s*<\/span>/');
});
