# ADR-0008: Caché del catálogo con claves versionadas, sin "cache tags"

* **Estado:** Reemplazado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

> **Reemplazado por [ADR-0010](0010-cache-del-catalogo-con-datos-planos.md)** (2026-10-22).
> Se conserva el mecanismo de claves versionadas y sin *cache tags*, pero se
> corrige el formato de lo que se guarda en caché: en Laravel 13 la caché no
> deserializa clases PHP, por lo que no se pueden cachear modelos Eloquent.

## Contexto

El catálogo es de lectura intensive: la lista de categorías y el listado de productos se piden mucho más de lo que se modifican. El requisito de performance pide **caching de catálogo**.

El driver de caché del proyecto es `database` (`CACHE_STORE=database`). La documentación de Laravel 13 es explícita sobre un límite: **los "cache tags" no están soportados por los drivers `file`, `database`, `dynamodb` ni `storage`**. Los tags son la forma idiomática de invalidar por grupo (`Cache::tags(['catalogo'])->flush()`), y aquí no están disponibles.

Alternativas evaluadas:

- **Redis + tags:** tags nativos e invalidación por grupo. Costo: un servicio de infraestructura adicional que hay que instalar, configurar y monitorear, para un proyecto que corre en SQLite.
- **TTL corto (`Cache::remember` con 5 minutos):** simple, pero durante la ventana de TTL el catálogo sirve datos viejos después de un cambio de precio o de stock.
- **Claves versionadas:** la clave del caché incluye un número de versión (`catalogo:v{N}`). Para invalidar, se incrementa `N` y las entradas viejas quedan huérfanas (se limpian por TTL).
- **Invalidación directa por clave:** `Cache::forget('categorias')` en cada alta de producto. Frágil: cada nueva clave cacheada tiene que acordarse de olvidarse.

## Decisión

Se usa **`Cache::remember` con claves versionadas**, sin tags:

- **Categorías:** `Cache::remember('catalogo.categorias.v'.$version, $ttl, fn () => Category::active()->orderBy('name')->get())`.
- **Listado de productos:** se cachea por *combinación de filtros*, con la versión como parte de la clave: `catalogo.productos.v{N}.{hash-de-filtros}`.
- La versión vive en la caché bajo la clave `catalogo.version` (default `1`). Un job o un listener de modelo la incrementa cuando cambia el catálogo.
- `Cache::flexible()` (stale-while-revalidate, disponible en Laravel 13) se usa donde el costo de recomputar es alto: el cliente recibe la versión cacheada aunque esté vencida y la renovación ocurre en segundo plano.
- TTL por defecto: 1 hora para las categorías y 5 minutos para los listados de productos.
- El driver sigue siendo `database`, sin servicios nuevos.

La invalidación se dispara desde el modelo mediante **observadores de Eloquent** (`ProductObserver`, `CategoryObserver`) que incrementan la versión cuando se crea, actualiza o elimina un registro, en lugar de que cada consumidor recuerde qué clave invalidar.

## Consecuencias

### Positivas

- No requiere Redis ni ninguna infraestructura nueva: se mantiene el stack actual.
- La invalidación es **explícita y centralizada**: un observador incrementa la versión y todo el catálogo queda invalidado de una vez, sin recordar claves.
- `Cache::flexible` elimina la ventana de latencia cuando el TTL vence: el usuario nunca espera a la recomputación.
- El versionado permite invalidar de forma selectiva en el futuro (por ejemplo, una versión por categoría).

### Limitaciones / Riesgos

- **Las entradas viejas quedan huérfanas** en la caché hasta que vence su TTL. Con el driver `database` eso son filas en la tabla `cache` que se limpian solas; con volúmenes altos conviene un `php artisan cache:prune` periódico.
- Sin tags no se puede invalidar "todo lo relacionado con la categoría X" de forma granular: la invalidación es de catálogo completo. Es aceptable para un catálogo que cambia poco.
- El hash de filtros en la clave multiplica el número de entradas: cada combinación visitada genera una fila. Con TTL corto y poda automática el costo se acota.
- El versionado depende de que los observadores estén registrados: si alguien inserta productos con `DB::table()` (sin Eloquent), la versión no se incrementa y el catálogo queda desactualizado hasta que vence el TTL. Se mitiga documentando que toda escritura pasa por Eloquent.
