<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\CatalogCache;

/**
 * Invalida la caché del catálogo cuando cambia un producto (ver ADR-0006).
 */
class ProductObserver
{
    public function saved(Product $product): void
    {
        CatalogCache::bump();
    }

    public function deleted(Product $product): void
    {
        CatalogCache::bump();
    }
}
