# Tienda Online — Trabajo práctico de arquitectura web

Tienda online mínima construida con Laravel, con el catálogo renderizado íntegramente en servidor y **sin JavaScript** en el camino crítico: los filtros viajan en la URL y el carrito se manipula con formularios HTML contra endpoints HTTP.

El proyecto no es un boilerplate de Laravel: casi todo el código de negocio es propio (catálogo, filtros, caché, carrito, imágenes, SEO) y cada decisión técnica relevante está documentada como ADR.

## Stack

| Pieza | Versión |
| ----- | ------- |
| Laravel | 13.38 |
| PHP | 8.3 (extensión `gd` requerida) |
| Livewire + Volt | 3.8 + 1.11 (sólo autenticación y panel) |
| Pest | 4.7 |
| Tailwind CSS | 3 |
| Base de datos | SQLite |

Dependencias de PHP directas: `laravel/framework`, `laravel/breeze`, `laravel/boost`, `intervention/image`, `livewire/livewire`, `livewire/volt`, `pestphp/pest`, `laravel/pint`.

## Puesta en marcha

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed       # 7 categorías y 46 productos de demostración
php artisan products:images      # genera la imagen original y las miniaturas de cada producto

npm run build                     # (o `npm run dev` durante el desarrollo)
php artisan serve
```

El sitio queda disponible en `http://127.0.0.1:8000/productos`.

### Comandos útiles

```bash
php artisan products:images --force   # regenera las imágenes de demostración
php artisan products:images --queued  # procesa las miniaturas con un job de cola
php artisan cache:clear               # vacía la caché del catálogo
php artisan test --compact            # suite completa de tests
vendor/bin/pint --dirty
```

## Funcionalidades

- **Catálogo público** con filtros por categoría, precio (en céntimos dentro de la URL), texto y disponibilidad, y ordenamiento por precio, fecha o nombre. Cada combinación de filtros es una URL indexable y cacheable.
- **Ficha de producto** con imagen, precio, stock, productos relacionados y alta al carrito.
- **Carrito en sesión** con alta, cambio de cantidad, borrado de líneas y vaciado, con subtotal, IVA y total.
- **Imágenes de producto** generadas con intervention/image en WebP y AVIF, versionadas en disco y servidas con `Cache-Control: immutable`.
- **SEO**: sitemap XML, `robots.txt`, URL canónica por página y metadatos OpenGraph y Twitter Card.
- **Caché del catálogo** con claves versionadas: el camino caliente del listado no consulta la tabla de productos.

## Rutas públicas

| Ruta | Descripción |
| ---- | ----------- |
| `GET /` | Redirección 301 a `/productos` |
| `GET /productos` | Catálogo con filtros en la query string |
| `GET /productos/{slug}` | Ficha de producto |
| `GET /categorias/{slug}` | Redirección 301 a la categoría filtrada |
| `GET /carrito` | Carrito de compras |
| `POST /carrito/agregar` | Agrega una línea |
| `PATCH /carrito/{producto}` | Cambia la cantidad |
| `DELETE /carrito/{producto}` | Saca la línea |
| `DELETE /carrito` | Vacía el carrito |
| `GET /imagenes/{ruta}` | Miniaturas versionadas |
| `GET /sitemap.xml` | Sitemap XML del catálogo |
| `GET /robots.txt` | Reglas para robots y referencia al sitemap |

## Estructura

```
app/
├── Console/Commands/GenerateProductImages.php   # imágenes de demostración
├── DTO/           CategoryOption, ProductFilters, ProductSummary, CartLine
├── Http/Controllers/  ProductController, CartController, ImageController,
│                      SitemapController, RobotsController
├── Jobs/GenerateProductThumbnails.php
├── Models/        Product, Category, User
├── Observers/     ProductObserver, CategoryObserver (invalidan la caché)
├── Services/      CartService, ImageService, SitemapService
├── Support/CatalogCache.php   # caché versionada del catálogo
└── View/Components/StoreLayout.php
```

## Reglas del dominio

- **Los precios se guardan como enteros en céntimos** (`unsignedBigInteger`). El formato a moneda es responsabilidad de la presentación.
- **El carrito vive en la sesión** (array plano `{ id => cantidad }`); no hay tablas `carts` ni `cart_items`.
- **El sitio público no carga JavaScript**: los filtros se aplican en el servidor y el carrito usa formularios HTML con `@csrf` y `@method`.
- **La caché sólo guarda datos planos** (arrays): en Laravel 13 la caché no deserializa clases PHP, así que los DTO se rehidratan en memoria.

## Documentación de las decisiones

- `docs/adr/` — un ADR por decisión técnica, con índice en [`docs/adr/README.md`](docs/adr/README.md).
- `DECISIONES_DISENO.txt` — bitácora de sesión: qué se hizo en cada tarea y por qué.

## Tests

```bash
php artisan test --compact
```

La suite cubre el catálogo y sus filtros, la caché, el carrito, las imágenes, el SEO y el renderizado en servidor.
