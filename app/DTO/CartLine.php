<?php

namespace App\DTO;

use App\Models\Product;
use Illuminate\Support\Number;

/**
 * Línea del carrito: un producto con la cantidad elegida y su subtotal.
 */
final readonly class CartLine
{
    public function __construct(
        public Product $product,
        public int $quantity,
        public int $subtotal,
    ) {}

    /**
     * Subtotal de la línea formateado como moneda.
     */
    public function formattedSubtotal(): string
    {
        return Number::currency(
            $this->subtotal / 100,
            config('store.currency'),
            config('store.locale'),
        );
    }
}
