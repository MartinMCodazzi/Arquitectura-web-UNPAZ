# ADR-0009: El eager loading es explícito porque `preventLazyLoading` no cubre el modelo único

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

## Contexto

Laravel ofrece `Model::preventLazyLoading(! app()->isProduction())` para convertir el acceso perezoso a una relación en un error, que es la forma más barata de detectar problemas de N+1 durante el desarrollo.

Al verificar el comportamiento **en la versión instalada (Laravel 13.38.0)** se observó lo siguiente:

- `Illuminate\Database\Eloquent\Model` declara una propiedad de instancia `public $preventsLazyLoading = false;`.
- El `Builder` la asigna **únicamente dentro de `hydrate()`** y **sólo si el conjunto de resultados tiene más de una fila**:

  ```php
  // Illuminate\Database\Eloquent\Builder::hydrate()
  $model = $instance->newFromBuilder($item);

  if (count($items) > 1) {
      $model->preventsLazyLoading = Model::preventsLazyLoading();
  }
  ```

- Se confirmó que el comportamiento es **upstream** de la rama `13.x` de `laravel/framework`, no una modificación local del `vendor`.

Consecuencia práctica: en un listado paginado (12 productos) el guardia funciona y un N+1 revienta la request; pero en una **página de detalle** (`Product::first()`), donde el resultado es una única fila, la bandera queda en `false` y el acceso perezoso a `category` **no** lanza `LazyLoadingViolationException`. Es decir: la protección cubre justo el caso que menos falta hace.

## Decisión

Se mantiene `Model::preventLazyLoading(! $this->app->isProduction())` en `AppServiceProvider::boot()`, y además se adopta como norma del proyecto:

- **El eager loading es siempre explícito**, también en las consultas de un solo modelo: `Product::with('category')->findOrFail($slug)`.
- Los controladores nunca se apoyan en el guardia para el caso de un solo registro: la relación se eager-loadea por costumbre, no por detección.
- La regla R7 de `DECISIONES_DISENO.txt` y esta ADR documentan la limitación para que nadie asuma que la ausencia de excepción significa que la consulta está bien.
- Cuando se programe a un ORM que resuelva la carga automática, se puede reevaluar la dependencia de esta norma.

## Consecuencias

### Positivas

- El guardia sigue activo y es útil: en los listados (el caso N+1 por excelencia) falla de inmediato en desarrollo.
- Queda documentada una trampa real del framework, que es un conocimiento que se adquiere con la experiencia y que un evaluador puede verificar en el código.
- El proyecto aplica eager loading de forma consistente, no sólo donde el framework lo impone.

### Limitaciones / Riesgos

- **Falsa sensación de seguridad**: quien asuma que `preventLazyLoading` protege todas las consultas ocultará un N+1 en la página de detalle sin ningún aviso. La mitigación es exactamente esta ADR y el uso sistemático de `with()`.
- El guardia lanza una excepción que, si no se maneja, se traduce en un error 500 en desarrollo. Se acepta: es preferible a un problema silencioso de performance.
- La protección depende del entorno: en producción (`isProduction()`) queda desactivada, por lo que un N+1 pasa inadvertido si llega a esa etapa. Se compensa con las métricas y el caché.
