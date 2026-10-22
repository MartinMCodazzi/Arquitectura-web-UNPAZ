<?php

namespace App\Console\Commands;

use App\Jobs\GenerateProductThumbnails;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Genera las imágenes de demostración de los productos.
 *
 * El catálogo de ejemplo no usa fotografías reales: para que el proyecto sea
 * reproducible y no dependa de servicios externos, cada producto recibe una
 * imagen original generada localmente (un cuadro con el nombre del producto
 * sobre el color de su categoría) que luego se procesa con intervention/image
 * para producir las miniaturas WebP y AVIF.
 */
class GenerateProductImages extends Command
{
    /**
     * @var string
     */
    protected $signature = 'products:images
        {--force : Regenera también las imágenes que ya existen}
        {--queued : Encola el trabajo de miniaturas en vez de procesarlo en el momento}';

    /**
     * @var string
     */
    protected $description = 'Genera la imagen original y las miniaturas de cada producto';

    /**
     * Ejecuta el comando.
     */
    public function handle(ImageService $images): int
    {
        $products = Product::query()->with('category')->orderBy('id')->get();

        if ($products->isEmpty()) {
            $this->components->info('No hay productos para los que generar imágenes.');

            return self::SUCCESS;
        }

        $processed = 0;

        foreach ($products as $product) {
            if ($this->option('force') || $product->image_path === null || ! $images->sourceExists($product)) {
                $product->update(['image_path' => $this->createPlaceholder($product, $images)]);
            }

            $this->generateThumbnails($product, $images);

            $processed++;
        }

        $this->components->info(sprintf('Imágenes listas para %d producto(s).', $processed));

        return self::SUCCESS;
    }

    /**
     * Genera (o encola) las miniaturas de un producto.
     */
    private function generateThumbnails(Product $product, ImageService $images): void
    {
        if ($this->option('queued')) {
            GenerateProductThumbnails::dispatch($product->id);

            return;
        }

        $images->generateThumbnails($product);
    }

    /**
     * Crea la imagen original de un producto y devuelve su ruta relativa.
     */
    private function createPlaceholder(Product $product, ImageService $images): string
    {
        $path = sprintf('products/%d/original.jpg', $product->id);

        $disk = Storage::disk('public');
        $disk->makeDirectory(dirname($path));

        $this->drawPlaceholder($disk->path($path), $product);

        return $path;
    }

    /**
     * Dibuja la imagen original del producto con GD.
     *
     * Es un generador de datos de demostración, no parte del pipeline de
     * publicación: por eso usa GD directamente en lugar de intervention/image.
     */
    private function drawPlaceholder(string $target, Product $product): void
    {
        $size = 1200;

        $image = imagecreatetruecolor($size, $size);

        [$r, $g, $b] = $this->color($product);
        $light = imagecolorallocate($image, (int) ($r * 0.6), (int) ($g * 0.6), (int) ($b * 0.6));

        // Degradado diagonal suave para que no sea un cuadro plano.
        for ($y = 0; $y < $size; $y++) {
            $shade = imagecolorallocate(
                $image,
                $this->clamp($r - ($y / $size) * 70),
                $this->clamp($g - ($y / $size) * 70),
                $this->clamp($b - ($y / $size) * 70),
            );
            imageline($image, 0, $y, $size, $y, $shade);
        }

        imagesetthickness($image, 6);
        imagerectangle($image, 40, 40, $size - 40, $size - 40, $light);
        imagesetthickness($image, 1);

        $white = imagecolorallocate($image, 255, 255, 255);
        $font = $this->font();

        $this->drawText($image, $font, $white, (int) ($size / 2), (int) ($size / 2) - 60, $product->name, 54, 1400);
        $this->drawText(
            $image,
            $font,
            $light,
            (int) ($size / 2),
            (int) ($size / 2) + 60,
            $product->category?->name ?? 'Tienda Online',
            30,
            1200,
        );

        imagejpeg($image, $target, 85);
        imagedestroy($image);
    }

    /**
     * Dibuja un texto centrado, repartido en varias líneas si hace falta.
     */
    private function drawText(
        \GdImage $image,
        string $font,
        int $color,
        int $centerX,
        int $startY,
        string $text,
        int $size,
        int $maxWidth,
    ): void {
        $words = explode(' ', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($this->textWidth($font, $candidate, $size) > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;

                continue;
            }

            $current = $candidate;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        $lineHeight = (int) ($size * 1.4);
        $y = $startY - (count($lines) - 1) * (int) ($lineHeight / 2);

        foreach ($lines as $line) {
            $width = $this->textWidth($font, $line, $size);
            imagettftext($image, $size, 0, $centerX - (int) ($width / 2), $y, $color, $font, $line);
            $y += $lineHeight;
        }
    }

    /**
     * Ancho en píxeles de un texto.
     */
    private function textWidth(string $font, string $text, int $size): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return (int) (abs($box[2] - $box[0]));
    }

    /**
     * Archivo de la fuente TrueType a usar.
     */
    private function font(): string
    {
        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

        return is_file($font) ? $font : IMAGEPATH.'DejaVuSans.ttf';
    }

    /**
     * Ajusta un valor de color al rango válido de GD.
     */
    private function clamp(float $value): int
    {
        return (int) max(0, min(255, round($value)));
    }

    /**
     * Color estable derivado de la categoría del producto.
     *
     * @return array{int, int, int}
     */
    private function color(Product $product): array
    {
        $palette = [
            [37, 99, 235],
            [13, 148, 136],
            [190, 24, 93],
            [124, 58, 237],
            [217, 119, 6],
            [2, 132, 199],
            [22, 163, 74],
            [220, 38, 38],
        ];

        $index = ($product->category_id ?? 0) % count($palette);

        return $palette[$index];
    }
}
