# ADR-0014: SEO del sitio público: sitemap XML, robots.txt y metadatos OpenGraph

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

## Contexto

El requisito de la tienda incluye SEO: metadatos OpenGraph, sitemap y meta tags por página. El sitio público se renderiza en servidor (ADR-0011) y cada combinación de filtros tiene su propia URL indexable (ADR-0006), así que hay que decidir:

- Cómo se genera el sitemap XML y cómo se mantiene al día con el catálogo.
- Qué URL es la canónica de cada página (sobre todo del catálogo filtrado y paginado).
- Qué metadatos de OpenGraph y Twitter Card se emiten y desde dónde.
- Cómo se declara `robots.txt` y dónde vive el `robots.txt`.

Alternativas evaluadas:

- **`spatie/laravel-sitemap`:** el paquete de referencia del ecosistema, con vistas Blade para el XML y soporte de sitemap index. El costo: una dependencia nueva (y el proyecto no puede sumar dependencias sin aprobación) para un XML que son treinta líneas.
- **Sitemap estático en `public/`:** no requiere código, pero se queda viejo en cuanto cambia el catálogo: hay que regenerarlo y publicarlo a mano en cada despliegue.
- **Sitemap generado por la aplicación:** se arma con los datos del catálogo en el momento de la request y se cachea con la versión del catálogo (ADR-0008): se actualiza solo cuando un producto o una categoría cambian, porque los observadores invalidan la caché.
- **`robots.txt` estático en `public/` (el del skeleton de Laravel):** lo sirve el servidor web sin pasar por PHP, pero su contenido no puede depender del host: la línea `Sitemap:` quedaría con un dominio fijo o ausente.
- **`robots.txt` como ruta de la aplicación:** el contenido se arma con el host real, así que el `Sitemap:` siempre apunta al sitemap correcto.

## Decisión

**El sitemap, el robots.txt y los metadatos de cada página los genera la aplicación, sin dependencias nuevas.**

- **Sitemap.** `GET /sitemap.xml` responde con un documento XML (`application/xml`) generado por `App\Services\SitemapService`. Incluye el catálogo (`/productos`), las páginas de categoría en su URL canónica (`/productos?categoria={slug}`) y las fichas de los productos activos (`/productos/{slug}`) con su `lastmod`. Las categorías inactivas y los productos desactivados quedan fuera. El resultado se cachea con la versión del catálogo, así que los observadores lo invalidan solos.
- **robots.txt.** `GET /robots.txt` responde `text/plain` con `User-agent: *`, `Allow: /` y la línea `Sitemap:` con la URL absoluta del sitemap. Se eliminó el `public/robots.txt` del skeleton de Laravel: el servidor web sirve los archivos estáticos de `public/` antes de llegar al framework, así que ese archivo tapaba la ruta y además apuntaba a ningún sitemap.
- **Metadatos.** El layout público (`x-store-layout`) emite, además del `<title>` y la meta descripción: `<link rel="canonical">`, `og:site_name`, `og:title`, `og:type`, `og:url`, `og:locale`, `og:description`, `og:image` (cuando la página tiene imagen) y las etiquetas de Twitter Card (`twitter:card`, `twitter:title`, `twitter:description`). El componente `StoreLayout` recibe `title`, `description`, `canonical`, `image` y `type`.
- **URL canónica.** Cada página declara la suya: la ficha, su propia URL; el carrito, su propia URL; el catálogo, la URL con los filtros normalizados (`ProductFilters::toQuery()`), que es la misma combinación que usa la redirección canónica del formulario (ADR-0011). La página 1 se declara sin el parámetro `page` para que exista una sola URL por combinación de filtros; las páginas siguientes sí lo incluyen.
- **Tipo e imagen.** El catálogo usa `og:type=website` y la miniatura del primer producto como `og:image`; la ficha usa `og:type=product` y su propia miniatura de 800 px. Cuando no hay imagen, la tarjeta de Twitter degrada a `summary`.

## Consecuencias

### Positivas

- Cero dependencias nuevas: el XML se construye a mano con los datos del catálogo.
- El sitemap no puede quedar viejo: se regenera cuando cambia el catálogo (los observadores incrementan la versión de la caché).
- Cada página declara una única URL canónica, así que los filtros equivalentes (`?categoria=x` y `/categorias/x`, que es una redirección 301) no compiten por el mismo contenido.
- El `robots.txt` siempre apunta al sitemap del host real, sin depender de la configuración del servidor web.
- Los metadatos de OpenGraph hacen que el catálogo se vea bien al compartirse en redes sociales, con la imagen del producto.

### Limitaciones / Riesgos

- El sitemap se arma con una consulta al catálogo por versión: con un catálogo muy grande habría que paginarlo o generar un sitemap index (el paquete `spatie/laravel-sitemap` ya lo resuelve).
- `og:image` apunta a una miniatura cuadrada de 400 u 800 px: las plataformas sociales prefieren imágenes de 1200 x 630. Con fotos reales en formato horizontal el resultado mejora sin cambiar el código.
- El `robots.txt` ahora pasa por PHP en vez de servirse como archivo estático: es una diferencia de milisegundos en una consulta que los robots hacen una sola vez por visita.
- La meta descripción del catálogo es fija: no se genera a partir de los filtros aplicados, así que dos listados filtrados distintos comparten la misma descripción.
- El sitemap no declara `hreflang` ni URLs con filtros de precio: el catálogo filtrado por precio queda fuera del sitemap (a propósito: son páginas de bajo valor para el buscador).
