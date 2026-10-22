<?php

use App\Models\Product;

/*
|--------------------------------------------------------------------------
| Renderizado en servidor
|--------------------------------------------------------------------------
|
| El sitio público se renderiza entero en servidor y tiene que funcionar
| con el JavaScript deshabilitado: los filtros viajan en la URL (ADR-0011) y
| el carrito se manipula con formularios HTML (ADR-0012). Estos tests son la
| garantía de que nada de eso se rompa por un descuido en el layout.
|
*/

test('el catálogo no carga el bundle de JavaScript', function () {
    $this->get(route('productos.index'))
        ->assertOk()
        ->assertDontSee('type="module"', false);
});

test('la ficha de producto no carga el bundle de JavaScript', function () {
    $producto = Product::factory()->create();

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertDontSee('type="module"', false);
});

test('el carrito no carga el bundle de JavaScript', function () {
    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertDontSee('type="module"', false);
});

test('las páginas públicas no usan directivas de Alpine ni de Livewire', function () {
    $producto = Product::factory()->create();

    $paginas = [
        $this->get(route('productos.index')),
        $this->get(route('productos.show', $producto)),
        $this->get(route('carrito.index')),
    ];

    foreach ($paginas as $pagina) {
        $pagina->assertOk()
            ->assertDontSee('x-data', false)
            ->assertDontSee('x-on:', false)
            ->assertDontSee('wire:', false)
            ->assertDontSee('livewire', false);
    }
});

test('el filtro de precio se aplica en el servidor y devuelve la URL canónica', function () {
    $this->get(route('productos.index', ['precio_min_pesos' => 1000]))
        ->assertRedirect(route('productos.index', ['precio_min' => 100000]));
});

test('el carrito se puede usar con formularios HTML contra endpoints HTTP', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 2,
    ])->assertRedirect();

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee($producto->name);

    $this->patch(route('carrito.actualizar', $producto), ['quantity' => 4])
        ->assertRedirect();

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('value="4"', false);

    $this->delete(route('carrito.eliminar', $producto))
        ->assertRedirect();

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Tu carrito está vacío');
});
