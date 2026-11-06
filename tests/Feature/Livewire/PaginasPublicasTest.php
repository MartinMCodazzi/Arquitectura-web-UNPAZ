<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Integración de componentes Livewire en las páginas públicas (ADR-0009)
|--------------------------------------------------------------------------
|
| El layout público monta el contador del carrito; el catálogo, el
| componente reactivo; la ficha, el botón de agregar; y la página del
| carrito, el componente CartSummary. Estos tests verifican que todas las
| piezas estén presentes en el HTML server-rendered.
|
*/

test('el catálogo monta los componentes de catálogo y contador', function () {
    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSeeLivewire('catalog.catalogo')
        ->assertSeeLivewire('cart.cart-counter');
});

test('la ficha de producto monta el botón de agregar y el contador', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertSeeLivewire('cart.add-to-cart-button')
        ->assertSeeLivewire('cart.cart-counter');
});

test('los productos relacionados montan su propio botón de agregar', function () {
    $categoria = Category::factory()->create();
    $producto = Product::factory()->forCategory($categoria)->create();
    $relacionado = Product::factory()->forCategory($categoria)->withStock(3)->create();

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertSee($relacionado->name)
        ->assertSee('ficha-producto-'.$producto->id, false);
});

test('la página del carrito monta el resumen reactivo', function () {
    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSeeLivewire('cart.cart-summary')
        ->assertSeeLivewire('cart.cart-counter');
});

test('el contador del header se renderiza en servidor con el estado de sesión', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 3);

    $this->post(route('carrito.agregar'), ['product_id' => $producto->id, 'quantity' => 2]);

    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSeeLivewire('cart.cart-counter')
        ->assertSee('aria-label="Carrito de compras con 5 unidades"');
});
