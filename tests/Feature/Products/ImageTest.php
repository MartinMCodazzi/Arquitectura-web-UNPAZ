<?php

use App\Jobs\GenerateProductThumbnails;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/**
 * Escribe una imagen original de prueba en el disco `public` y devuelve su
 * ruta relativa.
 */
function crearImagenOriginal(Product $producto): string
{
    $path = sprintf('products/%d/original.jpg', $producto->id);

    $imagen = imagecreatetruecolor(600, 400);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 200, 40, 90));

    Storage::disk('public')->makeDirectory(dirname($path));
    imagejpeg($imagen, Storage::disk('public')->path($path), 90);
    imagedestroy($imagen);

    return $path;
}

test('el servicio genera una miniatura por ancho y formato', function () {
    $producto = Product::factory()->create();
    $producto->update(['image_path' => crearImagenOriginal($producto)]);

    $generadas = app(ImageService::class)->generateThumbnails($producto);

    expect($generadas)->toHaveKeys(['webp', 'avif']);

    $servicio = app(ImageService::class);

    foreach (config('store.image_widths') as $width) {
        foreach (config('store.image_formats') as $format) {
            $path = $servicio->thumbnailPath($producto, $width, $format);

            Storage::disk('public')->assertExists($path);

            $dimensiones = getimagesize(Storage::disk('public')->path($path));

            expect($dimensiones[0])->toBe($width)
                ->and($dimensiones[1])->toBe($width);
        }
    }
});

test('el servicio no regenera una miniatura que ya existe', function () {
    $producto = Product::factory()->create();
    $producto->update(['image_path' => crearImagenOriginal($producto)]);

    $servicio = app(ImageService::class);
    $servicio->generateThumbnails($producto);

    $path = $servicio->thumbnailPath($producto, 400, 'webp');
    $antes = filemtime(Storage::disk('public')->path($path));

    clearstatcache();

    $servicio->generateThumbnails($producto);

    expect(filemtime(Storage::disk('public')->path($path)))->toBe($antes);
});

test('el producto expone la URL de la miniatura del ancho pedido', function () {
    $producto = Product::factory()->create();
    $producto->update(['image_path' => crearImagenOriginal($producto)]);

    app(ImageService::class)->generateThumbnails($producto);

    expect($producto->image_url)->toContain('/imagenes/products/'.$producto->id.'/400-')
        ->and($producto->image_url_large)->toContain('/imagenes/products/'.$producto->id.'/800-')
        ->and($producto->image_url_small)->toContain('/imagenes/products/'.$producto->id.'/200-');
});

test('un producto sin imagen no expone URL de miniatura', function () {
    $producto = Product::factory()->create();

    expect($producto->image_url)->toBeNull()
        ->and($producto->image_url_large)->toBeNull();
});

test('la ruta de imágenes sirve la miniatura con cacheo largo', function () {
    $producto = Product::factory()->create();
    $producto->update(['image_path' => crearImagenOriginal($producto)]);

    app(ImageService::class)->generateThumbnails($producto);

    $path = app(ImageService::class)->thumbnailPath($producto, 400, 'webp');

    $response = $this->get(route('imagenes.show', ['path' => $path]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp');

    $cacheControl = (string) $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('public')
        ->and($cacheControl)->toContain('max-age='.config('store.image_cache_seconds'))
        ->and($cacheControl)->toContain('immutable');
});

test('la ruta de imágenes responde 404 si la miniatura no existe', function () {
    $this->get(route('imagenes.show', ['path' => 'products/999/400-inexistente.webp']))
        ->assertNotFound();
});

test('la ruta de imágenes rechaza el path traversal', function () {
    $this->get('/imagenes/../../.env')->assertNotFound();
});

test('el job de miniaturas genera los archivos del producto', function () {
    $producto = Product::factory()->create();
    $producto->update(['image_path' => crearImagenOriginal($producto)]);

    GenerateProductThumbnails::dispatchSync($producto->id);

    Storage::disk('public')->assertExists(
        app(ImageService::class)->thumbnailPath($producto, 400, 'webp')
    );
});
