<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\CatalogCache;

/**
 * Invalida la caché del catálogo cuando cambia una categoría (ver ADR-0006).
 */
class CategoryObserver
{
    public function saved(Category $category): void
    {
        CatalogCache::bump();
    }

    public function deleted(Category $category): void
    {
        CatalogCache::bump();
    }
}
