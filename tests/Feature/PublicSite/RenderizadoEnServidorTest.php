<?php

use App\Models\Product;

/*
|--------------------------------------------------------------------------
| Renderizado en servidor y modo sin JavaScript
|--------------------------------------------------------------------------
|
| ADR-0009 introduce Livewire como capa de reactividad del sitio, así que las
| garantías originales de "cero JavaScript" ya no aplican en forma literal y
| esos tests fueron descartados. Lo que se sigue garantizando acá es:
|
|   1. El listado y el carrito se renderizan en servidor (el SSR inicial de
|      Livewire: el HTML que llega al navegador tiene los datos ya resueltos).
|   2. El flujo sin JavaScript sobrevive: los endpoints HTTP del carrito y el
|      redirect canónico del formulario de filtros siguen funcionando detrás
|      de bloques <noscript>.
|
*/

test('las páginas públicas montan componentes Livewire (SSR inicial)', function () {
    $producto = Product::factory()->create();

    $paginas = [
        $this->get(route('productos.index')),
        $this->get(route('productos.show', $producto)),
        $this->get(route('carrito.index')),
    ];

    foreach ($paginas as $pagina) {
        $pagina->assertOk()
            ->assertSee('wire:snapshot')
            ->assertSee('livewire/livewire.js', false);
    }
});

test('el catálogo publica la variante sin JavaScript (formulario GET)', function () {
    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee('<noscript>', false)
        ->assertSee('precio_min_pesos', false)
        ->assertSee('precio_max_pesos', false);
});

test('el carrito publica la variante sin JavaScript (endpoints HTTP)', function () {
    $producto = Product::factory()->withStock(5)->create();

    $this->post(route('carrito.agregar'), [
        'product_id' => $producto->id,
        'quantity' => 1,
    ])->assertRedirect();

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('<noscript>', false)
        ->assertSee(route('carrito.actualizar', $producto), false)
        ->assertSee(route('carrito.eliminar', $producto), false);
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

    $this->patch(route('carrito.actualizar', $producto), ['quantity' => 3])
        ->assertRedirect();

    expect(session('carrito'))->toBe([$producto->id => 3]);
});

test('las páginas públicas no cargan el bundle vite de JavaScript', function () {
    $producto = Product::factory()->create();

    $paginas = [
        $this->get(route('productos.index')),
        $this->get(route('productos.show', $producto)),
        $this->get(route('carrito.index')),
    ];

    foreach ($paginas as $pagina) {
        $pagina->assertOk()->assertDontSee('/build/assets/', false);
    }
});
