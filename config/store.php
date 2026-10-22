<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Moneda y formato regional
    |--------------------------------------------------------------------------
    |
    | Los precios se almacenan SIEMPRE como enteros en céntimos (ver la regla
    | R2 de DECISIONES_DISENO.txt). Estos valores sólo controlan cómo se
    | presenta ese importe al usuario final.
    |
    */

    'currency' => env('STORE_CURRENCY', 'ARS'),

    'locale' => env('STORE_LOCALE', 'es_AR'),

    /*
    |--------------------------------------------------------------------------
    | Impuestos
    |--------------------------------------------------------------------------
    |
    | Tasa de IVA aplicada en el carrito (21 % en Argentina). El valor es una
    | fracción decimal: 0.21 equivale al 21 %.
    |
    */

    'tax_rate' => (float) env('STORE_TAX_RATE', 0.21),

    /*
    |--------------------------------------------------------------------------
    | Catálogo
    |--------------------------------------------------------------------------
    |
    | Cantidad de productos por página en el listado del catálogo.
    |
    */

    'products_per_page' => (int) env('STORE_PRODUCTS_PER_PAGE', 12),

    /*
    |--------------------------------------------------------------------------
    | Imágenes de producto
    |--------------------------------------------------------------------------
    |
    | Los anchos de las miniaturas que genera App\Services\ImageService y la
    | calidad de la recompresión. La calidad es un compromiso: valores muy
    | altos producen archivos pesados y muy bajos destruyen los bordes de las
    | imágenes de producto (ver ADR-0007).
    |
    */

    'image_widths' => [200, 400, 800],

    'image_quality' => (int) env('STORE_IMAGE_QUALITY', 80),

    /*
    | Formatos generados, en orden de preferencia. El primero es el que se
    | usa en las vistas; el resto quedan disponibles para un <picture>.
    |
    */

    'image_formats' => ['webp', 'avif'],

    /*
    | Tiempo de cacheo de las miniaturas, en segundos. Un año: el nombre del
    | archivo incluye la versión de la imagen original, así que una imagen
    | nueva se publica con una URL nueva.
    |
    */

    'image_cache_seconds' => (int) env('STORE_IMAGE_CACHE_SECONDS', 31536000),

];
