# ADR-0001: Arquitectura y stack tecnológico del proyecto

* **Estado:** Aceptado
* **Fecha:** 2026-11-05
* **Autor:** Martin Munoz Codazzi

## Contexto

El proyecto es una tienda online desarrollada como trabajo práctico de arquitectura web. Antes de escribir código había que decidir con qué tecnologías se construye y cómo se organiza el código: lenguaje y framework, acceso a datos, forma de renderizar el catálogo, manera de agregar interactividad sin duplicar la lógica y estrategia de pruebas.

Se evaluaron estas alternativas:

- **PHP puro con PDO:** da control total, pero obliga a reimplementar ruteo, validación, sesiones, protección CSRF, migraciones y cacheo.
- **Otro framework MVC (Symfony, CodeIgniter):** son maduros, pero requieren más configuración inicial y ofrecen menos integración con el ecosistema elegido.
- **SPA con API (React o Vue + backend Laravel):** mejora la interacción, pero duplica el modelado, exige mantener dos aplicaciones y perjudica el SEO del catálogo.
- **Laravel + Blade + Livewire:** un único monolito, renderizado en servidor y reactividad puntual sin escribir una API.

Se eligió la última opción porque cumple los requisitos del trabajo práctico con una sola base de código y sin renunciar al SEO.

## Decisión

El proyecto es un **monolito Laravel** organizado en capas, con **renderizado en servidor** como camino principal y **Livewire** como capa de reactividad. No hay API REST ni SPA: el navegador recibe HTML y, cuando JavaScript está disponible, Livewire actualiza fragmentos de la página contra el mismo backend.

### Stack tecnológico

| Pieza | Tecnología / versión | Rol en el proyecto |
| ----- | -------------------- | ------------------ |
| Lenguaje | PHP 8.3 (`^8.3`) | Lenguaje de toda la aplicación. Requiere la extensión `gd` para las imágenes. |
| Framework | Laravel 13.38 (`laravel/framework`) | Ruteo, contenedor de dependencias, validación, sesiones, caché, colas, migraciones y HTTP. |
| ORM | Eloquent | Acceso a la base de datos: modelos, relaciones, *scopes*, *casts* y observadores. |
| Reactividad | Livewire 3.8 (`livewire/livewire`) | Componentes interactivos del catálogo y del carrito. |
| Componentes de página | Volt 1.11 (`livewire/volt`) | Páginas de autenticación y perfil escritas como componentes de un solo archivo. |
| Vistas | Blade | Plantillas del sitio público, layouts y componentes reutilizables. |
| JavaScript | Alpine.js (incluido en Livewire) | Comportamiento de UI dentro de los componentes Livewire; no se usa como framework global. |
| CSS | Tailwind CSS 3 + PostCSS/Autoprefixer | Estilos utilitarios y diseño visual de la tienda. |
| Bundler | Vite 8 + `laravel-vite-plugin` | Compilación de assets y *hot reload* en desarrollo. |
| Base de datos | SQLite | Persistencia local y base de datos de los tests (`:memory:`). |
| Sesiones | Driver `database` | Persistencia del carrito y del estado de autenticación. |
| Caché | Driver `database` | Caché versionada del catálogo (`CatalogCache`). |
| Colas | Driver `database` | Procesamiento de las miniaturas de producto. |
| Imágenes | `intervention/image` 4.4 + GD | Generación de miniaturas WebP/AVIF. |
| Autenticación | Laravel Breeze 2.4 | *Scaffolding* de registro, login, recuperación de contraseña y perfil. |
| Tests | Pest 4.7 sobre PHPUnit | Suite unitaria y de integración. |
| Calidad | Laravel Pint | Formato de código PSR-12. |
| Utilidades de desarrollo | Laravel Pail, Collision, Faker, Laravel Boost | Logs en vivo, errores legibles, datos de prueba y asistencia local. |

### Organización en capas

El código sigue el flujo `ruta → controlador o componente Livewire → servicio → modelo/caché → vista`. Cada capa tiene una responsabilidad acotada:

1. **Entrada HTTP (`routes/` y `public/index.php`).** `routes/web.php` declara las rutas públicas y el *route model binding* por `slug`; `routes/auth.php` las rutas de autenticación; `routes/console.php` los comandos. `bootstrap/app.php` configura el framework, el ruteo y el manejo de excepciones.
2. **Controladores HTTP (`app/Http/Controllers/`).** `ProductController`, `CartController`, `ImageController`, `SitemapController` y `RobotsController` son delgados: validan la entrada y delegan en los servicios. No contienen reglas de negocio.
3. **Componentes Livewire (`app/Livewire/`).** `Catalog\Catalogo`, `Cart\AddToCartButton`, `Cart\CartCounter` y `Cart\CartSummary` manejan el estado de la interfaz y llaman a los mismos servicios que los controladores. `Actions\Logout` y `Forms\LoginForm` completan el flujo de autenticación.
4. **Servicios de aplicación (`app/Services/`).** `CatalogService` resuelve el listado combinando caché y base de datos; `CartService` es la única frontera que escribe el carrito en la sesión; `ImageService` genera y publica miniaturas; `SitemapService` arma el sitemap. Acá vive la lógica de negocio.
5. **Soporte e infraestructura (`app/Support/`).** `CatalogCache` centraliza las claves versionadas, los TTL y la invalidación de la caché del catálogo.
6. **DTOs (`app/DTO/`).** `ProductFilters`, `ProductSummary`, `CartLine` y `CategoryOption` transportan datos tipados entre capas y evitan pasar arrays sueltos o modelos completos a las vistas.
7. **Modelos Eloquent (`app/Models/`).** `Product`, `Category` y `User` representan las tablas y concentran relaciones, *scopes* locales, *casts* y accesores de presentación.
8. **Persistencia (`database/`).** Migraciones para el esquema, *seeders* para los datos de demostración y *factories* para los tests.
9. **Vistas (`resources/views/`).** Layouts (`layouts/app`, `layouts/guest`, `layouts/store`), componentes Blade reutilizables (`components/store/*`) y vistas de Livewire/Volt.
10. **Procesos en segundo plano (`app/Jobs/` y `app/Console/Commands/`).** El job `GenerateProductThumbnails` genera miniaturas en cola; el comando `products:images` genera las imágenes de demostración.
11. **Configuración (`config/`).** `config/store.php` agrupa la configuración de negocio (moneda, locale, IVA, paginación y parámetros de imágenes); el resto son los archivos estándar de Laravel.

### Eloquent como ORM

- **Migraciones** versionadas para `users`, `categories`, `products`, `cache` y `jobs`. El esquema de productos incluye índices compuestos para los filtros y el ordenamiento del catálogo.
- **Modelos** con atributos de PHP 8: `#[Fillable]` para asignación masiva, `#[RouteKey('slug')]` para las URLs amigables y `#[Scope]` para los filtros reutilizables (`active`, `inStock`, `inCategory`, `priceBetween`, `search`, `sortedBy`).
- **Relaciones**: `Category hasMany Product` y `Product belongsTo Category`, con *eager loading* explícito para evitar consultas N+1.
- **Casts y accesores**: `price` y `stock` como enteros, `is_active` como booleano y accesores de presentación para el precio formateado y las URLs de las miniaturas.
- **Observadores**: `ProductObserver` y `CategoryObserver` invalidan la caché del catálogo cuando cambian los datos.
- **Seeders y factories**: datos de demostración y objetos de prueba reproducibles.

No se agrega una capa de repositorios: Eloquent ya es el ORM y la abstracción de negocio vive en los servicios. Sumar repositorios sobre Eloquent duplicaría consultas sin aportar aislamiento real.

### Persistencia, caché y colas

- **Base de datos**: SQLite en desarrollo y tests, con migraciones y *seeders*. El acceso siempre pasa por Eloquent.
- **Sesión**: el carrito se guarda como un array plano `{ product_id: cantidad }` en la clave `carrito`. Sólo `CartService` lee y escribe esa clave.
- **Caché**: `CatalogCache` usa claves versionadas (`catalogo.categorias.v{N}` y `catalogo.productos.v{N}.{huella}`) porque el driver `database` no soporta *cache tags*. La versión se incrementa desde los observadores. Lo que se guarda son arrays planos; los DTO se rehidratan en memoria.
- **Colas**: el job `GenerateProductThumbnails` procesa las miniaturas con el driver `database`. El comando `products:images --queued` lo encola; sin ese flag, el procesamiento es sincrónico.
- **Archivos**: las imágenes originales y sus miniaturas viven en el disco `public` (`storage/app/public/products/...`) y se sirven a través de `ImageController` con `Cache-Control` inmutable y nombre de archivo versionado.

### Frontend

- **Blade** renderiza el HTML en el servidor: catálogo, ficha de producto, carrito, sitemap y páginas de error.
- **Componentes Blade** (`components/store/product-card`, `price`, `stock-badge`, `alert`) encapsulan la presentación reutilizable.
- **Livewire** se monta en el catálogo y en el carrito para filtros, contador del *header* y edición de cantidades. Los componentes están basados en clases y delegan toda la lógica en los servicios.
- **Volt** se usa para las páginas de autenticación y perfil, montadas desde `resources/views/livewire/pages` y `resources/views/livewire/profile`.
- **Vite + Tailwind** compilan los estilos y el JavaScript. El sitio funciona sin JavaScript en el camino crítico: los filtros viajan en la URL y el carrito conserva *endpoints* HTTP como *fallback*.

### Autenticación y autorización

Laravel Breeze aporta el *scaffolding* de registro, login, verificación de email, recuperación y actualización de contraseña y eliminación de cuenta. Las pantallas se implementan con Volt; las rutas protegidas usan los *middlewares* `auth` y `verified`. El catálogo y el carrito son públicos.

### Pruebas y calidad

- **Pest 4** sobre PHPUnit, con suites `Unit` y `Feature` (`tests/Unit` y `tests/Feature`).
- Los tests usan SQLite en memoria, caché y sesión en `array`, cola `sync` y mail `array`, de modo que no dependen de servicios externos.
- Cubren catálogo, filtros, caché, carrito, imágenes, SEO, autenticación, perfil y renderizado en servidor.
- **Laravel Pint** mantiene el formato PSR-12. `composer test` limpia la configuración y ejecuta la suite completa.

### Entornos y configuración

La configuración sensible vive en `.env` y se expone a través de `config/*.php`. Los valores por defecto del proyecto son `APP_LOCALE=es_AR`, `APP_TIMEZONE=America/Argentina/Buenos_Aires`, `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `CACHE_STORE=database` y `QUEUE_CONNECTION=database`. La moneda, el IVA, la paginación y los parámetros de imágenes se administran en `config/store.php`.

## Consecuencias

### Positivas

- **Una sola base de código y un solo despliegue:** frontend y backend comparten lenguaje, rutas, validaciones y modelos.
- **Buen SEO por defecto:** el HTML se genera en el servidor y cada combinación de filtros tiene su propia URL indexable.
- **Menos duplicación:** los controladores y los componentes Livewire consumen los mismos servicios y DTOs, así que el modo reactivo y el modo sin JavaScript no divergen.
- **Eloquent acelera el desarrollo:** relaciones, *scopes*, *casts* y observadores cubren el modelo de datos sin SQL manual.
- **Caché simple y efectiva:** las claves versionadas funcionan con el driver `database` y se invalidan solas al cambiar el catálogo.
- **Stack estándar y verificable:** Laravel, Livewire, Pest y Tailwind tienen documentación abundante y son fáciles de evaluar por un docente o revisor.
- **Tests rápidos y aislados:** SQLite en memoria y drivers `array`/`sync` permiten correr la suite sin infraestructura externa.

### Limitaciones / Riesgos

- **Monolito acoplado al framework:** gran parte del comportamiento depende de Laravel; migrar a otro framework implicaría reescribir ruteo, sesiones, caché y autenticación.
- **Livewire requiere JavaScript para la parte reactiva:** sin JS se conserva el *fallback* HTTP, pero la experiencia es menos fluida y hay que mantener ambos caminos.
- **SQLite no es una base para alta concurrencia:** sirve para el trabajo práctico y los tests, pero un despliegue real con escrituras concurrentes debería migrar a MySQL o PostgreSQL.
- **Sesión, caché y colas sobre la misma base:** simplifica la puesta en marcha, pero agrega carga a la base de datos y no escala horizontalmente sin cambiarlas de driver.
- **La caché puede quedar desactualizada entre la escritura y el incremento de versión:** la ventana es muy corta porque la invalidación ocurre en los observadores, pero existe.
- **`ext-gd` es obligatoria:** sin esa extensión no se generan las miniaturas y el catálogo pierde imágenes.
- **Los DTO deben mantenerse sincronizados con el esquema:** agregar o renombrar columnas exige actualizar el modelo, el DTO y las vistas que lo consumen.
