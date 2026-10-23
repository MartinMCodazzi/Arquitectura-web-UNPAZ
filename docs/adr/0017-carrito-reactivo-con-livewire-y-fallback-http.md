# ADR-0017: Carrito reactivo con Livewire (fallback HTTP sin JavaScript)

* **Estado:** Aceptado
* **Fecha:** 2026-10-23
* **Autor:** OpenCode Agent

## Contexto

El ADR-0012 definió que el carrito se manipulaba únicamente con formularios
HTML contra endpoints HTTP: agregar, cambiar cantidad, remover y vaciar eran
peticiones POST/PATCH/DELETE con CSRF. Con la decisión del ADR-0016 de
introducir reactividad en la web entera, ese modelo de interacción queda
superado: cada acción obligaba a recargar la página y el contador del header
sólo reflejaba el estado al recargar.

Restricciones que debía preservar la nueva solución:

* el carrito sigue viviendo en la sesión y su única frontera de escritura es
  `CartService` (ADR-0004);
* los precios siguen en céntimos y el feedback usa es_AR/ARS (ADRs 0002/0003);
* el sitio debe seguir funcionando para un usuario sin JavaScript (requisito
  original del trabajo práctico).

## Decisión

El carrito se administra con **Livewire**:

* `App\Livewire\Cart\CartCounter` — contador reactivo del header; escucha el
  evento `carrito-actualizado` y relee `CartService::count()`.
* `App\Livewire\Cart\AddToCartButton` — botón "Agregar al carrito" que usan la
  card del catálogo y la ficha de producto; agrega vía `CartService::add()` y
  emite feedback local (sin recargar).
* `App\Livewire\Cart\CartSummary` — página del carrito: cantidades
  (`wire:model.live.debounce`), quitar línea, vaciar y totales, siempre vía
  `CartService`.

Complementario (no excluyente) queda el fallback sin JavaScript:

* los endpoints HTTP de `CartController` (`carrito.agregar`, `carrito.actualizar`,
  `carrito.eliminar`, `carrito.vaciar`) siguen existiendo y funcionando: son la
  ruta que usan los `<form>` alternativos dentro de bloques `<noscript>`, cuando
  el navegador no corre JavaScript;
* el backend (`CartService`, la sesión, la validación) es único para las dos
  rutas: Livewire y los endpoints HTTP escriben por el mismo servicio, así que
  no hay dos fuentes de verdad.

Reemplaza el ADR-0012, que queda marcado como `Reemplazado`.

## Consecuencias

### Positivas

- Agregar, cambiar cantidad, quitar y vaciar se reflejan al instante, sin
  recargar la página; el contador del header y los totales se recalculan con
  el evento `carrito-actualizado`.
- Un solo backend (`CartService`) para el modo reactivo y el fallback HTTP:
  el modo sin JavaScript sigue garantizado con `<noscript>` y endpoints.
- Las tests HTTP existentes del carrito se mantienen válidos (los endpoints
  no cambian).

### Limitaciones / Riesgos

- Hay dos caminos por acción (Livewire y HTTP); comparten backend, pero el
  marcado es doble: las piezas Livewire añaden sus formularios dentro de
  `<noscript>` para conservar el modo sin JS. En el caso de `CartSummary`,
  que renderiza la tabla, la replicación sin JS queda reducida a
  acciones puntuales con `<noscript>`.
- Con JavaScript la UX es sensible al debounce: cambio drástico de cantidad
  dispara muchos writes a sesión; se encarga con `debounce` de ~300 ms.
- El header necesita `@livewireScripts` en el layout público (antes no carga
  bundle `app.js`, y sigue sin cargarlo: Livewire sirve su propio asset).
