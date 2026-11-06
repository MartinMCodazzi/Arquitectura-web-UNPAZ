<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Generación y publicación de las miniaturas de producto (ver ADR-0005).
 *
 * El servicio trabaja sobre el disco `public`: la imagen original queda en
 * `products/{id}/original.{ext}` y las miniaturas en
 * `products/{id}/{ancho}-{version}.{formato}`. La versión (hash corto del
 * original) va en el nombre del archivo para que el navegador pueda cachear
 * las miniaturas durante un año y, al mismo tiempo, una imagen nueva se
 * publique siempre con una URL nueva.
 */
class ImageService
{
    /**
     * Extensiones admitidas para la imagen original.
     *
     * @var list<string>
     */
    public const SOURCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    /**
     * Genera las miniaturas de un producto.
     *
     * Es idempotente: si la miniatura ya existe en disco, no se vuelve a
     * generar. Devuelve las rutas relativas de las miniaturas disponibles.
     *
     * @return array<string, string> Formato => ruta relativa en el disco `public`.
     */
    public function generateThumbnails(Product $product): array
    {
        if ($product->image_path === null || ! $this->sourceExists($product)) {
            return [];
        }

        $image = $this->manager->decodePath(Storage::disk('public')->path($product->image_path));

        $generated = [];

        foreach (config('store.image_widths') as $width) {
            foreach (config('store.image_formats') as $format) {
                $path = $this->thumbnailPath($product, $width, $format);

                if ($path === null) {
                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    $this->writeThumbnail($image, $path, $width, $format);
                }

                $generated[$format] = $path;
            }
        }

        return $generated;
    }

    /**
     * Ruta relativa de la miniatura de un producto en el ancho y formato dados.
     *
     * Devuelve `null` si el producto no tiene imagen original.
     */
    public function thumbnailPath(Product $product, int $width, string $format = 'webp'): ?string
    {
        if ($product->image_path === null) {
            return null;
        }

        return $this->thumbnailPathFor($product, $width, $this->version($product), $format);
    }

    /**
     * Información de las miniaturas publicadas de un producto.
     *
     * La versión identifica al archivo publicado: si el original cambia, la
     * versión cambia y con ella la URL de la miniatura. Los formatos son los
     * que están efectivamente en disco (el AVIF depende de que el servidor
     * pueda codificarlo).
     *
     * @return array{version: string, formats: list<string>}|null
     */
    public function thumbnailInfo(Product $product, int $width = 400): ?array
    {
        if (! $this->sourceExists($product)) {
            return null;
        }

        $version = $this->version($product);

        $formats = array_values(array_filter(
            (array) config('store.image_formats'),
            fn (mixed $format): bool => is_string($format) && Storage::disk('public')->exists(
                $this->thumbnailPathFor($product, $width, $version, $format)
            ),
        ));

        return $formats === [] ? null : ['version' => $version, 'formats' => $formats];
    }

    /**
     * URL pública de la miniatura de un producto.
     *
     * Devuelve `null` si el producto no tiene imagen o si la miniatura todavía
     * no fue generada: en ese caso la vista muestra el texto alternativo.
     */
    public function thumbnailUrl(Product $product, int $width = 400, string $format = 'webp'): ?string
    {
        $path = $this->thumbnailPath($product, $width, $format);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('imagenes.show', ['path' => $path]);
    }

    /**
     * ¿Existe la imagen original del producto en el disco?
     */
    public function sourceExists(Product $product): bool
    {
        if ($product->image_path === null) {
            return false;
        }

        return Storage::disk('public')->exists($product->image_path);
    }

    /**
     * Versión de la imagen original: hash corto que cambia cuando el archivo
     * original cambia. Va en el nombre de la miniatura para cachearla.
     */
    private function version(Product $product): string
    {
        $path = Storage::disk('public')->path($product->image_path);

        if (! is_file($path)) {
            return substr(sha1($path), 0, 10);
        }

        return substr(sha1($path.'|'.filemtime($path).'|'.filesize($path)), 0, 10);
    }

    /**
     * Ruta relativa de una miniatura con la versión dada.
     */
    private function thumbnailPathFor(Product $product, int $width, string $version, string $format): string
    {
        return sprintf(
            'products/%d/%d-%s.%s',
            $product->id,
            $width,
            $version,
            $format,
        );
    }

    /**
     * Escribe una miniatura en el disco `public`.
     */
    private function writeThumbnail(ImageInterface $image, string $path, int $width, string $format): void
    {
        $target = Storage::disk('public')->path($path);

        Storage::disk('public')->makeDirectory(dirname($path));

        $thumbnail = $image->cover($width, $width);

        $thumbnail->encode($this->encoder($format))->save($target);
    }

    /**
     * Codificador del formato pedido.
     */
    private function encoder(string $format): WebpEncoder|AvifEncoder
    {
        $quality = (int) config('store.image_quality');

        return $format === 'avif'
            ? new AvifEncoder(quality: $quality)
            : new WebpEncoder(quality: $quality);
    }
}
