# ADR-0006: Los filtros del catálogo se implementan como scopes locales de Eloquent

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

## Contexto

El catálogo tiene que filtrar por **categoría**, **rango de precios** (mínimo y máximo), **disponibilidad** (con stock) y **término de búsqueda** (nombre o descripción), además de ordenar por precio ascendente/descendente, fecha o nombre.

La pregunta es dónde vive esa lógica de filtrado. Alternativas evaluadas:

- **Pipeline (paquetes de referencia de Laravel):** cada filtro es una clase propia (`FilterByCategory`, `FilterByPrice`...). Es muy legible y muy testeable, pero para cuatro filtros añade cuatro clases, un `FilterPipeline` y un contrato; el equipo de mantenimiento del proyecto tiene que aprender una convención extra.
- **Query Builder propio (`ProductFilter`):** una clase que envuelve el builder y encadena `when()`. Es básicamente una reimplementación de los scopes con más indirección.
- **Scopes locales de Eloquent:** cada filtro es un método del modelo, encadenable y reutilizable desde cualquier consulta (incluidas relaciones).

Además hay una razón de **SEO**: los filtros se aplican sobre la query string (`?categoria=electronica&precio_max=5000000`), no sobre JavaScript. El HTML que se sirve ya viene filtrado, lo que permite que cada combinación de filtros sea indexable y cacheable.

## Decisión

Los filtros se implementan como **scopes locales del modelo `Product`**, con el atributo `#[Scope]` de Laravel 13 (equivalente moderno de los métodos `scopeXxx`):

| Scope                | Firma                                  | Comportamiento                                            |
| -------------------- | -------------------------------------- | --------------------------------------------------------- |
| `active()`           | —                                      | `is_active = true`                                        |
| `inStock()`          | —                                      | `stock > 0`                                               |
| `inCategory()`       | `int $categoryId`                      | `category_id = ?`                                          |
| `priceBetween()`     | `?int $minPrice, ?int $maxPrice`       | `price >= ?` / `price <= ?` (ignora nulos)                |
| `search()`           | `?string $term`                        | `name LIKE %t%` OR `description LIKE %t%`                 |
| `sortedBy()`         | `string $sort`                        | `price_asc`, `price_desc`, `newest`, `oldest`, `name`      |

Reglas de diseño:

- Los scopes son **puros**: no tocan la sesión, no leen el request, no devuelven modelos.
- La traducción de la query string a scopes ocurre en una capa aparte (un DTO `App\DTO\ProductFilters` en la Tarea 2), que valida y normaliza los parámetros. El modelo no conoce el HTTP.
- `sortedBy()` siempre añade un **desempate por `id`**: sin él, el orden entre filas con el mismo precio es indefinido y la paginación puede repetir o saltar productos.
- `search()` elimina los comodines `%` y `_` del término para que el usuario no altere el comportamiento del `LIKE`.
- La búsqueda se hace con `whereLike()` de Laravel, que es portable entre SQLite, MySQL y PostgreSQL.

## Consecuencias

### Positivas

- Cero clases extra: los filtros son métodos del modelo, descubribles por autocompletado y por el propio IDE.
- Los scopes son reutilizables en cualquier contexto: `Product::active()->inStock()->inCategory($id)`, `$category->products()->active()`, `Product::query()->...`.
- Encadenables y combinables sin boilerplate; el `where` anidado de la búsqueda se resuelve solo con `where(function () { ... })`.
- La URL con query string hace que el catálogo sea renderizado en servidor, indexable y cacheable: los crawlers ven el contenido filtrado, no una página vacía con JavaScript.
- Coherente con el resto del framework: un revisor que conoce Laravel conoce los scopes.

### Limitaciones / Riesgos

- Los scopes se mezclan en el modelo: si el catálogo creciera mucho, `Product` se volvería grande. Hoy el conjunto es acotado y cohesivo.
- El `LIKE '%término%'` **no puede usar índices**: con volúmenes grandes la búsqueda se degrada a un escaneo completo de la tabla. Es el punto débil conocido de esta decisión (deuda técnica documentada).
- Un scope mal parametrizado puede filtrar de más si no se valida en la capa de DTO. Se mitiga con tests sobre la capa de filtros.
- El ordenamiento por precio con un desempate por `id` no es el "más relevantes" de un buscador commercial: no hay ranking por popularidad. Se acepta en el alcance mínimo.
