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

    /*
    |--------------------------------------------------------------------------
    | Checkout: envío
    |--------------------------------------------------------------------------
    |
    | Los importes están en céntimos, igual que los precios del catálogo. El
    | checkout muestra esta lista tal cual; agregar un correo o un punto de
    | entrega nuevo es sólo sumar una entrada acá.
    |
    */

    'shipping' => [

        'couriers' => [
            'oca' => [
                'name' => 'OCA',
                'price' => 450000,
                'eta' => '3 a 5 días hábiles',
            ],
            'andreani' => [
                'name' => 'Andreani',
                'price' => 520000,
                'eta' => '2 a 4 días hábiles',
            ],
            'correo-argentino' => [
                'name' => 'Correo Argentino',
                'price' => 380000,
                'eta' => '4 a 7 días hábiles',
            ],
            'moto' => [
                'name' => 'Moto mensajería (CABA y GBA)',
                'price' => 300000,
                'eta' => '24 a 48 hs',
            ],
        ],

        'pickup_points' => [
            'central' => [
                'name' => 'Punto de entrega Central',
                'address' => 'Av. Corrientes 1234, CABA',
                'price' => 0,
                'eta' => 'Listo en 24 hs',
            ],
            'norte' => [
                'name' => 'Punto de entrega Norte',
                'address' => 'Av. Maipú 2500, Vicente López',
                'price' => 0,
                'eta' => 'Listo en 48 hs',
            ],
            'oeste' => [
                'name' => 'Punto de entrega Oeste',
                'address' => 'Av. Rivadavia 8500, Liniers',
                'price' => 50000,
                'eta' => 'Listo en 48 hs',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout: medios de pago
    |--------------------------------------------------------------------------
    |
    | `discount` es una fracción del subtotal de productos que se descuenta
    | en el resumen (0.10 = 10 %). Los medios sin descuento usan 0.
    |
    */

    'payment_methods' => [

        'transferencia' => [
            'name' => 'Transferencia bancaria',
            'description' => '10 % OFF sobre el subtotal de productos.',
            'discount' => 0.10,
        ],

        'tarjeta_credito' => [
            'name' => 'Tarjeta de crédito',
            'description' => 'Hasta 3 cuotas sin interés.',
            'discount' => 0,
        ],

        'tarjeta_debito' => [
            'name' => 'Tarjeta de débito',
            'description' => 'Débito inmediato al confirmar la compra.',
            'discount' => 0,
        ],

        'mercadopago' => [
            'name' => 'Mercado Pago',
            'description' => 'Dinero en cuenta, QR o tarjeta guardada.',
            'discount' => 0,
        ],

        'efectivo' => [
            'name' => 'Efectivo contra entrega',
            'description' => 'Sólo disponible al retirar en un punto de entrega.',
            'discount' => 0,
        ],

    ],

];
