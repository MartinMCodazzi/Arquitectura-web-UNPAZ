# ADR-0013: Las miniaturas se versionan en disco y se sirven por HTTP con cacheo inmutable

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

*Complementa a [ADR-0007](0007-intervention-image-para-imagenes-de-producto.md) (intervention/image como motor de transformación). No reemplaza a ninguno: lo implementa.*

## Contexto

[ADR-0007](0007-intervention-image-para-imagenes-de-producto.md) ya decidió qué librería se usa (intervention/image con GD), qué anchos se generan (200, 400 y 800 px), en qué formatos (WebP y AVIF) y que las miniaturas se sirven con `Cache-Control` largo y nombre de archivo versionado. Quedaba definir **cómo se materializa eso en el proyecto**.

El catálogo de demostración tampoco tiene fotografías: `products.image_path` es nullable y los seeders lo dejan en `null`. Las imágenes tienen que existir para que el catálogo se vea como se espera en una entrega.

Alternativas evaluadas:

- **Servir las miniaturas como archivos estáticos del disco `public`:** es lo más simple (`php artisan storage:link` y el servidor web entrega el archivo). El problema es que las cabeceras de cacheo quedan en la configuración del servidor web (Apache o Nginx), fuera del repositorio: cada entorno de despliegue tiene que acordarse de configurar `Cache-Control` y `expires` a mano, y si se olvida, la miniatura se vuelve a descargar en cada visita.
- **Servirlas por una ruta de Laravel:** la ruta devuelve el archivo con las cabeceras que la aplicación decide. El costo es que el archivo lo sirve PHP en lugar del servidor web (más lento para archivos grandes, irrelevante para miniaturas de 3 a 6 KB) y que hay que blindar la ruta contra el path traversal.
- **Meter las imágenes en la base de datos:**también, permitiría versionar la URL sin tocar el disco, pero mete bytes en la base de datos y complica las migraciones.
- **Fotos reales descargadas de un servicio externo:** el catálogo se vería mejor, pero el proyecto no sería reproducible (depende de un tercero, de su disponibilidad y de su licencia) y la entrega quedaría atada a un servicio que puede caerse.

## Decisión

**El original de cada producto vive en el disco `public`; las miniaturas se generan en ese mismo disco con el formato, el ancho y la versión en el nombre del archivo; y se sirven por una ruta de la aplicación que manda un `Cache-Control` inmutable de un año.**

- **Rutas en disco.** El original queda en `products/{id}/original.jpg` (columna `image_path`). Las miniaturas en `products/{id}/{ancho}-{version}.{formato}`, por ejemplo `products/12/400-9f1c2a7b3d.webp`.
- **Versionado por hash.** La versión es los primeros 10 caracteres del SHA-1 de la ruta, la fecha de modificación y el tamaño del original. Si la imagen original cambia, el nombre de la miniatura cambia: la URL nueva es distinta de la anterior y el navegador no puede estar mostrando la versión vieja.
- **Servido por la aplicación.** `GET /imagenes/{path}` (`ImageController`) devuelve el archivo con `Cache-Control: public, max-age=31536000, immutable`. La ruta normaliza la ruta y rechaza `..` (path traversal) antes de tocar el disco.
- **Formatos.** Se generan WebP (principal) y AVIF. Las vistas usan `<picture>` con `<source type="image/avif">` y un `<img>` WebP de respaldo: el navegador elige AVIF cuando lo soporta y cae en WebP cuando no.
- **El pipeline de transformación es intervention/image** (ADR-0007), en `App\Services\ImageService`, que es idempotente: si la miniatura ya existe, no la vuelve a generar. El procesamiento por producto vive en el job de cola `GenerateProductThumbnails`.
- **Las imágenes de demostración se generan localmente.** El comando `php artisan products:images` dibuja con GD un cuadro con degradado, el nombre del producto y el nombre de la categoría (el color depende de la categoría), lo guarda como original y genera todas sus miniaturas. Es un generador de datos de demostración, no parte del pipeline de publicación: por eso usa GD directamente en lugar de intervention/image.
- **Anchos en configuración.** `config/store.php` define `image_widths`, `image_quality` (80), `image_formats` y `image_cache_seconds`.

## Consecuencias

### Positivas

- El cacheo de las imágenes queda **en el repositorio**: no depende de que alguien configure el servidor web.
- El versionado por hash resuelve de una vez el "cache busting" sin pedirle al usuario que recargue ni usar query strings en la URL de la imagen.
- WebP y AVIF con `<picture>` cumplen con el requisito de formatos modernos: el peso de las miniaturas del catálogo es de 3 a 6 KB.
- `loading="lazy"` en el listado y `loading="eager"` + `fetchpriority="high"` en la ficha completan el trabajo de performance (ADR-0007).
- El proyecto queda **reproducible**: `php artisan migrate:fresh --seed && php artisan products:images` levanta el catálogo con imágenes sin ningún servicio externo.
- El servicio es idempotente y el comando acepta `--force` y `--queued`: regenerar todo el catálogo de imágenes no cuesta trabajo si los archivos ya existen.

### Limitaciones / Riesgos

- **Las miniaturas no se sirven como archivos estáticos:** PHP lee y entrega cada archivo. Para una tienda con mucho tráfico conviene mover el `public` a un CDN o a almacenamiento de objetos y dejar de pasar por la ruta.
- Requiere `ext-gd` en el servidor: sin la extensión, el catálogo con imágenes no arranca (ver ADR-0007).
- Los placeholders generados no son fotografías reales: el catálogo se ve "de muestra". Es una decisión consciente para no depender de servicios externos ni de licencias de terceros.
- El hash de versión depende de la fecha de modificación del archivo: si el original se copia preservando la fecha y el tamaño, la URL no cambiaría (caso poco probable en la práctica).
- Cada miniatura generada ocupa disco: con 46 productos, 3 anchos y 2 formatos, son 276 archivos (y el original). No hay limpieza automática de las miniaturas viejas cuando cambia el original.
