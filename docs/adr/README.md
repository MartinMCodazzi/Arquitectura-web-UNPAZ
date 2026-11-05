# Architecture Decision Records (ADR)

Registro de decisiones de arquitectura del proyecto **Tienda Online**.

Cada decisión técnica relevante (modelado de datos, librerías, patrones de diseño, estrategias de caché, filtros, SEO) se documenta en un archivo propio con la plantilla estándar de ADR y se agrega a este índice.

---

## Plantilla

Todo ADR nuevo debe seguir esta estructura **estrictamente**:

```markdown
# ADR-XXXX: [Título corto de la decisión]

* **Estado:** [Propuesto | Aceptado | Reemplazado]
* **Fecha:** YYYY-MM-DD
* **Autor:** [Nombre]

## Contexto
[Problema, necesidad de negocio o requerimiento universitario, junto con las alternativas evaluadas]

## Decisión
[Descripción clara de la solución o tecnología seleccionada]

## Consecuencias

### Positivas
- [Ventaja 1]
- [Ventaja 2]

### Limitaciones / Riesgos
- [Desventaja o compromiso aceptado]
```

---

## Regla permanente

> **REGLA PERSISTENTE:** toda decisión técnica relevante DEBE registrarse como un nuevo archivo `.md` dentro de `docs/adr/` con formato correlativo (`0001-nombre-decision.md`) y actualizarse en este índice.

Reglas complementarias:

1. Los archivos son **append-only en su número**: un ADR no se edita para cambiar una decisión; se marca como `Reemplazado` y se crea uno nuevo que lo referencia.
2. El índice se actualiza **en el mismo commit** en que se agrega un ADR.
3. El nombre del archivo es `NNNN-nombre-decision.md`, con 4 dígitos y *kebab-case* en español.
4. `DECISIONES_DISENO.txt` (raíz del repositorio) es la **bitácora de sesión** —qué se hizo en cada tarea— y no la fuente de verdad de las decisiones técnicas. Esa fuente es este directorio.

---

## Índice de decisiones

| ADR | Título | Estado | Fecha | Resumen |
| --- | ------ | ------ | ----- | ------- |
| [0001](0001-registro-de-decisiones-en-formato-adr.md) | Arquitectura y stack tecnológico del proyecto | Aceptado | 2026-11-05 | Monolito Laravel 13 sobre PHP 8.3: Eloquent como ORM, Blade + Livewire 3/Volt para la interfaz, SQLite, sesiones/caché/colas sobre base de datos, Vite + Tailwind, Pest 4 y Pint. |
| [0002](0002-precios-como-enteros-en-centimos.md) | Los precios se almacenan como enteros en céntimos | Aceptado | 2026-10-22 | `products.price` es un `unsignedBigInteger` en céntimos de ARS; el formato a moneda es cosa de la presentación. |
| [0003](0003-locale-es-ar-y-moneda-ars.md) | Locale `es_AR`, moneda ARS y rutas en español | Aceptado | 2026-10-22 | Identidad regional de la aplicación: `es_AR`, ARS, `America/Argentina/Buenos_Aires` y configuración de negocio en `config/store.php`. |
| [0004](0004-carrito-persistido-en-sesion.md) | El carrito de compras se persiste en la sesión | Aceptado | 2026-10-22 | `CartService` sobre la sesión, con array plano `{ id => cantidad }`; sin tablas `carts`/`cart_items`. |
| [0005](0005-modelo-de-datos-del-catalogo.md) | Modelo de datos del catálogo (categorías y productos) | Aceptado | 2026-10-22 | Esquema, índices compuestos, baja lógica con `is_active`, `restrictOnDelete` y route key por `slug`. |
| [0006](0006-filtros-con-scopes-locales-de-eloquent.md) | Los filtros del catálogo se implementan como scopes locales de Eloquent | Aceptado | 2026-10-22 | Scopes `#[Scope]` en `Product` + DTO de filtros; los filtros viajan en la query string para que el HTML filtrado sea indexable. |
| [0007](0007-intervention-image-para-imagenes-de-producto.md) | intervention/image para el tratamiento de imágenes de producto | Aceptado | 2026-10-22 | Miniaturas WebP/AVIF generadas en un job de cola, con `ext-gd` como requisito de instalación. |
| [0008](0008-cache-del-catalogo-con-claves-versionadas.md) | Caché del catálogo con claves versionadas, sin "cache tags" | Reemplazado | 2026-10-22 | El driver `database` no soporta tags: se invalida incrementando una versión. Reemplazado por ADR-0010. |
| [0009](0009-eager-loading-explicito-por-limitacion-de-prevent-lazy-loading.md) | Eager loading explícito por limitación de `preventLazyLoading` | Aceptado | 2026-10-22 | En Laravel 13 el guardia de lazy loading no cubre el modelo único: el `with()` es obligatorio por norma. |
| [0010](0010-cache-del-catalogo-con-datos-planos.md) | La caché del catálogo guarda datos planos, no modelos Eloquent | Aceptado | 2026-10-22 | Laravel 13 no deserializa clases PHP desde la caché (`serializable_classes => false`): se cachean arrays y se rehidratan DTOs en memoria. |
| [0011](0011-redireccion-canonica-del-formulario-de-filtros.md) | El formulario de filtros envía precios en pesos y redirige a la URL canónica | Aceptado | 2026-10-22 | El formulario escribe pesos (`precio_min_pesos`) y el servidor responde 302 hacia la URL canónica en céntimos: cero JavaScript y una sola URL indexable por combinación de filtros. |
| [0012](0012-carrito-con-formularios-html-sin-javascript.md) | El carrito se manipula con formularios HTML contra endpoints HTTP | Reemplazado | 2026-10-22 | Agregar, cambiar cantidad, quitar y vaciar son formularios `POST`/`PATCH`/`DELETE` con CSRF. Reemplazado por ADR-0017 (carrito reactivo con Livewire). |
| [0013](0013-miniaturas-versionadas-con-cacheo-inmutable.md) | Las miniaturas se versionan en disco y se sirven por HTTP con cacheo inmutable | Aceptado | 2026-10-22 | Original y miniaturas WebP/AVIF en el disco `public`, con hash de versión en el nombre y `Cache-Control: immutable` desde la aplicación. |
| [0014](0014-seo-sitemap-robots-y-metadatos-opengraph.md) | SEO del sitio público: sitemap XML, robots.txt y metadatos OpenGraph | Aceptado | 2026-10-22 | Sitemap y `robots.txt` generados por la aplicación (sin dependencias nuevas), URL canónica por página y etiquetas OpenGraph/Twitter en el layout. |
| [0015](0015-cache-del-listado-con-resumenes-de-producto.md) | El listado del catálogo se cachea como datos planos y la vista consume resúmenes | Aceptado | 2026-10-22 | El listado filtrado se cachea por combinación de filtros y página; la vista recibe siempre `ProductSummary` y el paginador se reconstruye en memoria. |
| [0016](0016-livewire-como-capa-de-reactividad.md) | Livewire como capa de reactividad del sitio | Aceptado | 2026-10-23 | Toda la interactividad dinámica (filtros, carrito, contador del header) se implementa con Livewire 3 (componentes class-based delegando en los servicios); componentes Blade reutilizables en `resources/views/components/store/`. |
| [0017](0017-carrito-reactivo-con-livewire-y-fallback-http.md) | Carrito reactivo con Livewire (fallback HTTP sin JavaScript) | Aceptado | 2026-10-23 | Reemplaza a ADR-0012: agregar, cambiar cantidad, quitar y vaciar son acciones de Livewire sobre `CartService`, con los endpoints HTTP conservados como fallback `<noscript>`. |

---

## Estado del proyecto

- **Stack:** Laravel 13.38 · PHP 8.3 · Livewire 3 + Volt · Pest 4 · Tailwind 3 · SQLite
- **Migraciones aplicadas:** `categories`, `products` (además de las del framework).
- **Datos de demostración:** 7 categorías y 46 productos (`php artisan db:seed`).
- **Rutas públicas:** `/` → `/productos` · `/productos` · `/productos/{slug}` · `/categorias/{slug}` · `/carrito` · `/imagenes/{path}` · `/sitemap.xml` · `/robots.txt`.
- **Estado:** Tareas 0 a 6 completadas (base del proyecto, esquema del catálogo, catálogo público con filtros y caché, carrito en sesión, imágenes de producto, SEO y performance, suite de tests y cierre) más la fase de reactividad con Livewire (ADR-0016 y ADR-0017): catálogo, filtros, carrito y contador del header son interactivos sin recargar, con fallback HTTP/`<noscript>` para el modo sin JavaScript.
- **Pendiente:** nada dentro del alcance del trabajo práctico. Quedan fuera el checkout y la pasarela de pago, el registro de usuarios para comprar y el panel de administración del catálogo.
