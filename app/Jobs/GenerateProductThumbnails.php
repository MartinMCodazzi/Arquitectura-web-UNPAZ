<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Genera las miniaturas de un producto fuera del ciclo de la request.
 *
 * El producto se referencia por id y no por modelo para no serializarlo en
 * la cola: el job siempre lee el estado actual de la base de datos.
 */
class GenerateProductThumbnails implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $productId) {}

    /**
     * Genera las miniaturas del producto.
     */
    public function handle(ImageService $images): void
    {
        $product = Product::find($this->productId);

        if (! $product instanceof Product) {
            return;
        }

        $images->generateThumbnails($product);
    }
}
