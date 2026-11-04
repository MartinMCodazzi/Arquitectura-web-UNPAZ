# ADR-0010: La caché del catálogo guarda datos planos, no modelos Eloquent

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

*Reemplaza a [ADR-0008](0008-cache-del-catalogo-con-claves-versionadas.md), cuyo mecanismo de claves versionadas y sin *cache tags* se mantiene; lo que cambia es el formato de lo que se guarda en caché.*

## Contexto

Al implementar la caché de categorías prevista en ADR-0008, el sitio falló con:

```
App\Support\CatalogCache::categories(): Return value must be of type
Illuminate\Support\Collection, __PHP_Incomplete_Class returned
```

La causa está en `config/cache.php` de Laravel 13:

```php
'serializable_classes' => false,
```

El valor por defecto es `false` y la documentación del framework lo explica así: *"no se deserializará ninguna clase PHP desde la caché, para prevenir ataques de cadena de gadgets si se filtra la APP_KEY"*. En la práctica, `DatabaseStore::unserialize()` termina en `unserialize($value, ['allowed_classes' => false])`, y **todo objeto** guardado en la caché vuelve como `__PHP_Incomplete_Class`: los modelos Eloquent, las colecciones e incluso los `stdClass`.

El fallo no apareció en la suite de tests porque en el entorno de testing el driver de caché es `array` (`CACHE_STORE=array` en `phpunit.xml`), que no serializa nada. Sólo se manifiesta con el driver `database`, que es el configurado en la aplicación.

Alternativas evaluadas:

- **Habilitar `cache.serializable_classes => true`:** permite cachear modelos, pero desactiva la protección contra gadget chains justo por el motivo que Laravel 13 la activa: si se filtra la `APP_KEY`, un atacante podría construir una cadena de deserialización con las clases permitidas. Es una degradación de seguridad que no compensa la comodidad.
- **Cachear modelos y confiar en que el problema no aparece:** descartado: ya apareció, y además ata la caché a la forma del modelo (si mañana `Category` gana una relación, el objeto cacheado queda desactualizado en estructura).
- **Cachear datos planos y rehidratar en memoria:** lo que se guarda en caché es un array de arrays con sólo los datos necesarios; al leer, se reconstruyen objetos de valor (`App\DTO\CategoryOption`) en memoria, que nunca se serializan.

## Decisión

**En la caché sólo se guardan datos planos (arrays y escalares).** Los modelos Eloquent nunca se cachean.

- `App\Support\CatalogCache` es el único punto de acceso a la caché del catálogo. Mantiene el mecanismo de ADR-0008: claves versionadas (`catalogo.categorias.v{N}`), TTL e invalidación por observadores de Eloquent (`ProductObserver` y `CategoryObserver` incrementan la versión).
- La versión se guarda con `Cache::forever('catalogo.version', ...)`, que es un entero y por lo tanto es seguro serializar.
- Las categorías se guardan como `list<array{id: int, name: string, slug: string}>`. Al leerlas se rehidratan en `App\DTO\CategoryOption` (objeto *value object* de sólo lectura) con `CategoryOption::fromArray()`.
- `CatalogCache::categories()` **valida la forma de lo que lee**: si la entrada cacheada no es un array (por ejemplo, una entrada vieja escrita por una versión anterior del código que guardaba modelos), la descarta y la vuelve a construir. Esto hace que el despliegue de este cambio no requiera borrar la caché a mano.
- La clave `categoriesKey()` es pública para poder inspeccionar y probar la caché desde los tests.

Consecuencia práctica para las vistas: los datos cacheados no son modelos, así que la vista del catálogo trabaja con `CategoryOption` (`$category->name`, `$category->slug`) y no con `Category`.

El caché del listado de productos filtrado (combinación de filtros como parte de la clave) queda pendiente y se implementará en la Tarea 5, aplicando la misma regla: se cachea el *payload* serializable, no la paginación.

## Consecuencias

### Positivas

- La caché funciona con la configuración segura por defecto de Laravel 13: no hace falta abrir la puerta a las gadget chains.
- El payload cacheado es estable: si el modelo cambia (nuevos atributos, nuevas relaciones), la caché no queda desactualizada en estructura, porque sólo guarda los campos que la vista necesita.
- El formato plano es portable entre drivers (`database`, `file`, `redis`): no depende de cómo serialice cada driver.
- La lectura es defensiva: una entrada con formato inesperado se descarta sola en lugar de romper la página.
- Se aprenda una restricción real del framework, verificada empíricamente y documentada para que nadie vuelva a cachear un modelo sin saberlo.

### Limitaciones / Riesgos

- Hay que convertir a mano entre modelo y array al cachear y rehidratar al leer: es código explícito, pero es código que hay que mantener.
- `CategoryOption` duplica (en parte) la forma de `Category`: si la vista necesita un campo nuevo, hay que agregarlo a los tres lugares (modelo, DTO y array serializable). Se acepta: son tres campos y el beneficio es la estabilidad del payload.
- La caché no puede almacenar modelos con relaciones precargadas: el cacheo de listados de productos con su categoría resuelta queda fuera de alcance por esta decisión (el listado se arma en la vista o en un DTO plano).
- El driver `array` en testing **no reproduce** el problema: sin un test explícito con el driver `database`, esta clase de bug puede reaparecer. Mitigación: `tests/Feature/Products/CatalogCacheTest.php` fuerza el driver `database`.
