# ADR-0007: intervention/image para el tratamiento de imágenes de producto

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

## Contexto

El catálogo tiene que mostrar imágenes de producto. El requisito de performance pide **miniaturas optimizadas en formatos modernos (WebP/AVIF)**, cacheadas en disco, y `loading="lazy"` nativo en las vistas.

El catálogo actual no tiene imágenes reales (`image_path` es nullable y los seeders lo dejan en `null`), pero la infraestructura de imágenes hay que decidirla antes de que exista el primer upload.

Alternativas evaluadas:

- **Spatie Laravel Media Library:** gestor completo de archivos: tabla `media`, conversiones, rutas por modelo, sprites. Es potente y es la opción por defecto del ecosistema, pero para un catálogo con **una sola imagen por producto** arrastra una tabla extra, un modelo extra y un flujo de subida que hay que aprender.
- **intervention/image:** librería de procesamiento de imágenes. No gestiona el almacenamiento (eso lo hace el `Storage` de Laravel), sólo transforma: recortar, redimensionar, convertir a WebP/AVIF. Se integra con el `Storage` de Laravel y con los *jobs* de cola de forma natural.
- **GD directo de PHP:** cero dependencias, pero es una API de bajo nivel (reencodificar, manejando recursos) sin las abstracciones de tamaño, calidad y formatos modernos.

Restricción del entorno: **la extensión `ext-gd` no estaba instalada** en PHP 8.3.36 de este servidor (tampoco `ext-imagick`). Se instaló `php8.3-gd` (8.3.36, desde el PPA ondrej, versión compatible con el PHP en ejecución). intervention/image v4 requiere PHP ^8.3, `ext-mbstring` y un driver GD o Imagick.

## Decisión

Se utiliza **`intervention/image` v4** con el driver **GD** como único motor de transformación de imágenes.

- Se agrega como dependencia de Composer (`composer require intervention/image`), con la aprobación explícita del usuario.
- `ext-gd` queda como requisito de instalación documentado.
- El procesamiento vive en un servicio de dominio (`App\Services\ImageService`, Tarea 4) que:
  - genera miniaturas de anchos fijos (por ejemplo 200, 400, 800 px) desde la imagen original;
  - las guarda en el disco `public` bajo `products/{id}/{ancho}.webp` (y `.avif` cuando la imagen lo soporte);
  - es idempotente: si la miniatura ya existe, no se regenera.
- La transformación se ejecuta en un **job de cola** (`GenerateProductThumbnails`), no en el ciclo de la request, para que la carga y el procesamiento no bloqueen al usuario.
- Las miniaturas se sirven con `Cache-Control` largo y nombre de archivo versionado, de modo que el navegador y el CDN puedan cachearlas.
- Las vistas usan `loading="lazy"` nativo en las imágenes del listado y `loading="eager"` + `fetchpriority="high"` en la imagen principal de la ficha de producto.

## Consecuencias

### Positivas

- Se evita la tabla `media` y toda la complejidad de un gestor de archivos: para una imagen por producto es la opción más simple de mantener.
- WebP y AVIF reducen el peso de las imágenes entre un 30 % y un 70 % frente a JPEG/PNG, lo que mejora el Core Web Vitals del catálogo.
- El servicio es idempotente y cacheable en disco: regenerar miniaturas no cuesta I/O si ya existen.
- Al ser un job de cola, el procesamiento no suma latencia a la respuesta HTTP.
- Se mantiene el `Storage` de Laravel: los archivos se sirven con `php artisan storage:link` y el `public` disk, sin configuración adicional.

### Limitaciones / Riesgos

- **Añade una dependencia de Composer y una extensión de PHP** (`ext-gd`) que debe estar presente en cualquier entorno de despliegue. Si falta, la aplicación no arranca el job de imágenes.
- Sin gestor de archivos: no hay variantado de imágenes por producto, ni asociar el archivo a un modelo con metadatos, ni conversiones declarativas. Si el catálogo crece a varias imágenes, hay que revisar esta decisión.
- AVIF requiere que el build de PHP tenga el soporte correspondiente; con GD puro la conversión a AVIF no siempre está disponible. Por eso el plan es servir WebP como formato principal y AVIF sólo cuando la librería lo soporta.
- La calidad de la recompresión es un compromiso: hay que elegir un factor de calidad (≈80) que no destruya los bordes de las imágenes de producto.
