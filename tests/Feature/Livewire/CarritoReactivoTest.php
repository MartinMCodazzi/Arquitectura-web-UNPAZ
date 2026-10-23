<?php

use App\Livewire\Cart\AddToCartButton;
use App\Livewire\Cart\CartCounter;
use App\Livewire\Cart\CartSummary;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Carrito reactivo con Livewire (ADR-0017)
|--------------------------------------------------------------------------
|
| La única frontera de escritura del carrito sigue siendo CartService
| (ADR-0004); estos tests garantizan que las piezas Livewire respetan esa
| frontera, que el feedback correcto se muestra y que el evento
| `carrito-actualizado` conecta el contador del header.
|
*/

test('el botón agregar guarda el producto en la sesión vía CartService', function () {
    $producto = Product::factory()->withStock(5)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 5])
        ->call('addToCart')
        ->assertDispatched('carrito-actualizado')
        ->assertSee('Producto agregado al carrito.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 1]);
});

test('el botón agregar respeta la cantidad elegida en la ficha', function () {
    $producto = Product::factory()->withStock(5)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 5, 'withQuantity' => true])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertSee('Producto agregado al carrito.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 2]);
});

test('el botón agregar avisa cuando el stock no alcanza', function () {
    $producto = Product::factory()->withStock(3)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 3, 'withQuantity' => true])
        ->set('quantity', 10)
        ->call('addToCart')
        ->assertSee('sólo hay 3');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('el botón agregar no bloquea productos sin stock: sólo no agrega', function () {
    $producto = Product::factory()->outOfStock()->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 0])
        ->call('addToCart')
        ->assertSee('No hay stock suficiente');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('el contador del header refleja el carrito y se refresca con el evento', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 2);

    Livewire::test(CartCounter::class)
        ->assertSee('>2</span>');

    Livewire::test(CartCounter::class)
        ->dispatch('carrito-actualizado')
        ->assertSee('>2</span>');
});

test('el carrito muestra el estado vacío cuando no hay líneas', function () {
    Livewire::test(CartSummary::class)->assertSee('Tu carrito está vacío');
});

test('cambiar la cantidad desde el input actualiza el carrito', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->set("quantities.{$producto->id}", 3)
        ->assertSee('Carrito actualizado.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('quitar una línea la elimina de la sesión', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 2);

    Livewire::test(CartSummary::class)
        ->assertSee($producto->name)
        ->call('removeProduct', $producto->id)
        ->assertSee('Producto eliminado del carrito.');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('vaciar el carrito borra todas las líneas', function () {
    $primero = Product::factory()->withStock(5)->create();
    $segundo = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($primero, 1);
    app(CartService::class)->add($segundo, 1);

    Livewire::test(CartSummary::class)
        ->assertSee($primero->name)
        ->assertSee($segundo->name)
        ->call('clearCart')
        ->assertSee('Carrito vaciado.')
        ->assertSee('Tu carrito está vacío');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('la cantidad no puede superar el stock disponible', function () {
    $producto = Product::factory()->withStock(3)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->set("quantities.{$producto->id}", 99)
        ->assertDispatched('carrito-actualizado');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});
