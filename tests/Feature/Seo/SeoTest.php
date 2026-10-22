<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

test('el sitemap lista el catálogo, las categorías y los productos activos', function () {
    $categoria = Category::factory()->create(['slug' => 'electronica', 'name' => 'Electrónica']);

    Product::factory()->forCategory($categoria)->create(['slug' => 'auriculares-pro']);
    Product::factory()->inactive()->create(['slug' => 'producto-oculto']);

    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = (string) $response->getContent();

    expect($xml)->toContain('<loc>'.e(route('productos.index')).'</loc>')
        ->and($xml)->toContain('<loc>'.e(route('productos.index', ['categoria' => 'electronica'])).'</loc>')
        ->and($xml)->toContain('<loc>'.e(route('productos.show', 'auriculares-pro')).'</loc>')
        ->and($xml)->not->toContain('producto-oculto');
});

test('el sitemap es un XML válido', function () {
    Product::factory()->create();

    $xml = (string) $this->get(route('sitemap'))->assertOk()->getContent();

    expect(simplexml_load_string($xml))->not->toBeFalse();
});

test('el robots.txt permite indexar el sitio y apunta al sitemap', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Allow: /')
        ->assertSee('Sitemap: '.route('sitemap'));
});

test('el catálogo declara su URL canónica y sus metadatos OpenGraph', function () {
    $this->get(route('productos.index'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e(route('productos.index')).'">', false)
        ->assertSee('<meta property="og:title" content="Catálogo de productos · Tienda Online">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:url" content="'.e(route('productos.index')).'">', false)
        ->assertSee('<meta property="og:locale" content="es-AR">', false)
        ->assertSee('<meta name="twitter:card" content="summary">', false);
});

test('la URL canónica del catálogo conserva los filtros normalizados', function () {
    $canonical = route('productos.index', ['categoria' => 'electronica', 'precio_min' => 10000]);

    $this->get(route('productos.index', ['categoria' => 'electronica', 'precio_min' => 10000]))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($canonical).'">', false);
});

test('la ficha declara su URL canónica y el tipo de contenido producto', function () {
    $producto = Product::factory()->create();

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e(route('productos.show', $producto)).'">', false)
        ->assertSee('<meta property="og:type" content="product">', false)
        ->assertSee('<meta name="twitter:card" content="summary">', false);
});

test('la ficha con imagen declara la imagen destacada para OpenGraph', function () {
    Storage::fake('public');

    $producto = Product::factory()->create();

    $path = sprintf('products/%d/original.jpg', $producto->id);

    Storage::disk('public')->makeDirectory(dirname($path));

    $imagen = imagecreatetruecolor(600, 400);
    imagejpeg($imagen, Storage::disk('public')->path($path), 90);
    imagedestroy($imagen);

    $producto->update(['image_path' => $path]);

    app(ImageService::class)->generateThumbnails($producto);

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.$producto->image_url_large.'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

test('las páginas del carrito y de categorías no repiten contenido canónico', function () {
    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e(route('carrito.index')).'">', false);

    $categoria = Category::factory()->create(['slug' => 'hogar']);

    $this->get(route('categorias.show', $categoria))
        ->assertRedirect(route('productos.index', ['categoria' => 'hogar']), 301);
});
