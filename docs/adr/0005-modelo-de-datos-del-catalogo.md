# ADR-0005: Modelo de datos del catálogo (categorías y productos)

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

## Contexto

El catálogo necesita dos entidades: **categorías** (agrupan productos) y **productos** (nombre, descripción, precio, stock, imagen). Hay que decidir además cómo se "da de baja" un producto o una categoría, y qué índices soportan los patrones de consulta del catálogo (listado por categoría, filtro por precio, orden por fecha).

Decisiones de modelado pendientes:

1. **Baja de un producto:** ¿se borra la fila o se desactiva?
2. **Baja de una categoría:** ¿se borra en cascada (y con ella sus productos) o se restringe?
3. **Clave de las URLs:** ¿el `id` o el `slug`?
4. **Índices:** cuáles hacen falta para no degradar el catálogo a medida que crece.

Alternativas evaluadas:

- **SoftDeletes** (`deleted_at`) en lugar de `is_active`: es el patrón por defecto de Laravel y recupera el registro "borrado". Pero mezcla dos conceptos: un producto *desactivado* (decisión comercial: lo escondemos del catálogo pero sigue existiendo) y un producto *eliminado* (ya no existe). Para este proyecto, con un solo flag y sin histórico, `is_active` es suficiente y más explícito.
- **Cascade en `category_id`:** borrar una categoría borraría sus productos en cascada. Es una pérdida de datos silenciosa: un error de administration eliminaría el catálogo de una categoría entera.
- **Route key por `id`:** URLs como `/productos/17` no son legibles ni optimizables para SEO.

## Decisión

### Esquema

**`categories`**

| Columna        | Tipo                | Notas                                   |
| -------------- | ------------------- | --------------------------------------- |
| `id`           | `id`                | PK                                      |
| `name`         | `string`            |                                         |
| `slug`         | `string` unique     | Clave natural y clave de ruta           |
| `description`  | `text` nullable     |                                         |
| `is_active`    | `boolean` default 1 | Visible en el catálogo público           |
| `created_at` / `updated_at` | timestamps |                                |

Índice: `categories_active_name_index` sobre `(is_active, name)`, que cubre el listado público ordenado alfabéticamente.

**`products`**

| Columna        | Tipo                       | Notas                                        |
| -------------- | -------------------------- | -------------------------------------------- |
| `id`           | `id`                       | PK                                           |
| `name`         | `string`                   |                                              |
| `slug`         | `string` unique            | Clave natural y clave de ruta                |
| `description`  | `text` nullable            |                                              |
| `price`        | `unsignedBigInteger`       | Céntimos ARS (ver ADR-0002)                  |
| `stock`        | `unsignedInteger` default 0 | Units disponibles                           |
| `image_path`   | `string` nullable          | Ruta relativa en `storage/app/public`        |
| `category_id`  | `foreignId`                | FK a `categories`, `restrictOnDelete()`      |
| `is_active`    | `boolean` default 1        | Visible en el catálogo público                |
| `created_at` / `updated_at` | timestamps           |                                             |

Índices compuestos que siguen los patrones reales de consulta: `(is_active, category_id)`, `(is_active, price)` y `(is_active, created_at)`.

### Reglas de modelado

- **Baja lógica, no física:** `is_active` desactiva; la fila nunca se borra por la acción de negocio. El catálogo público siempre filtra por `is_active = true`.
- **Restricción de integridad:** `restrictOnDelete()` impide borrar una categoría que todavía tiene productos. Hay que desactivarla o mover sus productos primero.
- **Route key por slug:** `#[RouteKey('slug')]` en `Category` y `Product`. Las URLs son `/productos/auriculares-bluetooth-aurora-pro`.
- **Casts explícitos:** `price` → `integer`, `stock` → `integer`, `is_active` → `boolean` en ambos modelos.
- **Relaciones:** `Product::category()` (`BelongsTo`) y `Category::products()` (`HasMany`), ambas con tipo de retorno declarado.
- **Estilo Laravel 13:** atributos de clase `#[Fillable]`, `#[RouteKey]` y `#[Scope]` (coherente con el `App\Models\User` ya existente en el proyecto).

## Consecuencias

### Positivas

- `is_active` es explícito en la consulta y en la base: no hay que conocer el comportamiento de un global scope para entender por qué un producto no aparece.
- `restrictOnDelete()` convierte un error de administración en un error de base de datos visible, en lugar de una pérdida silenciosa de catálogo.
- El slug como clave de ruta da URLs estables y legibles: cambiar el nombre de un producto no rompe sus enlaces.
- Los tres índices compuestos cubren exactamente los tres accesos hotter del catálogo (por categoría, por precio, por fecha) sin índices redundantes de una sola columna.
- `stock` como `unsignedInteger` con default 0 hace imposible un stock negativo por error de tipeo.

### Limitaciones / Riesgos

- `is_active` no guarda quién desactivó ni cuándo: para auditoría haría falta una tabla de historial.
- Con `is_active` no hay "papelera": un producto desactivado sigue occupying espacio y no se puede purgar. Es aceptable a esta escala.
- La búsqueda por texto (`LIKE '%término%'`) no puede usar índices en SQLite ni MySQL sin un índice FULLTEXT: a escala grande es el principal punto débil del catálogo (ver ADR-0006).
- Si en el futuro se necesitan varias imágenes por producto, `image_path` alcanza: habría que migrar a `product_images`.
