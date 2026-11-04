# ADR-0016: Livewire como capa de reactividad del sitio

* **Estado:** Aceptado
* **Fecha:** 2026-10-23
* **Autor:** Martin Munoz Codazzi

## Contexto

Hasta ahora el sitio público funcionaba 100 % server-rendered sin JavaScript
(ADRs 0011 y 0012): los filtros viajaran por query string con un redirect
canónico y el carrito se manipulaba con formularios HTML. Ese diseño garantiza
indexabilidad y simplicidad, pero penaliza la experiencia interactiva:

* cada cambio de filtro o de cantidad es un round-trip completo de página,
* el contador del carrito del header sólo se actualiza al recargar,
* el feedback ("producto agregado") requiere recargar la página.

Se puso como requisito del proyecto tener reactividad en la web entera,
conservando la arquitectura existente (servicios, DTOs, caché del catálogo y
reglas de dominio como precios en céntimos y eager loading).

Alternativas evaluadas:

1. **Inertia.js / SPA** — requiere introducir un runtime frontend (Vue/React)
   y duplicar la lógica de estado; sobredimensionado para una tienda pequeña
   y contrario al carácter server-rendered del proyecto.
2. **JavaScript ad-hoc + fetch contra los endpoints HTTP actuales** — ligero,
   pero duplica validación y estado, sin sistema de plantillas
   compartido entre cliente y servidor.
3. **Alpine.js puro** — perfecto para la UI local pero no tiene estado en
   servidor: filtros y carrito necesitarían igualmente llamadas manuales.

## Decisión

Toda la interactividad dinámica del sitio se implementa con **Livewire 3**
(`livewire/livewire` ya instalado, sin dependencias nuevas):

* componentes **class-based** en `app/Livewire` (mismo estilo que ya genera
  Breeze), con vistas Blade en `resources/views/livewire`;
* la lógica de negocio no vive en el componente: se delega en los servicios
  existentes (`CartService`, `CatalogService`, `CatalogCache`), de modo que el
  carrito sigue escribiendo sólo en `CartService` (ADR-0004) y el listado
  sigue consumiendo la caché de datos planos (ADRs 0008, 0010, 0015);
* la comunicación entre componentes (contador del header, alerts) se hace con
  events (`$this->dispatch('carrito-actualizado')`, `#[On(...)]`), no con
  estado global;
* Alpine.js queda disponible sólo en detalles menores de UI (p. ej. dropdowns
  de la zona privada); el bundle `resources/js/app.js` no se vuelve a cargar
  en el sitio público: Livewire sirve sus propios assets con `@livewireScripts`.

Piezas reactivas en esta fase: contador del carrito del header, botón
"Agregar al carrito", página del carrito y catálogo con filtros y paginación
(que conservan la query string, ver ADR-0017 y ADR-0011).

### Componentes reutilizables

Como política general, antes de crear una vista nueva se revisan los
componentes existentes y las piezas repetidas se extraen a componentes Blade
reutilizables en `resources/views/components/store/` (`price`, `stock-badge`,
`alert`, `product-card`); el partial `products/partials/card.blade.php` se
absorbe en `x-store.product-card`.

## Consecuencias

### Positivas

- Reactividad real (filtros, carrito, contador) sin SPA ni bundle extra: el
  estado del sitio sigue viviendo en el servidor (sesión PHP).
- Reutilización del stack: los servicios y reglas de dominio (precios en
  céntimos, eager loading, caché) son los mismos de siempre.
- Mantenibilidad: la lógica queda testeable en PHP (`Livewire::test`) con Pest,
  sin tests de navegador.
- La suite de Breeze ya estaba construida con Livewire: unifica el estilo.

### Limitaciones / Riesgos

- El sitio público ya no funciona sin JavaScript en las piezas reactivas: se
  conservan los endpoints HTTP y fallas declaradas en `<noscript>` como
  degeneración sin JS (ver ADR-0017).
- Cada interacción es una request HTTP (Livewire hace round-trips): en
  hardware lento la latencia se siente; se mitiga con `debounce` en los inputs.
- El contador del carrito y los filtros sincronizan la URL vía Livewire; el
  canonical del `<head>` se renderiza en servidor y no se recalcula con cada
  commit de Livewire (aceptable: canonical se define por URL cargada).
- Convive con `Model::preventLazyLoading()`: las propiedades públicas de los
  componentes no deben ser modelos Eloquent con relaciones pendientes; se
  usan DTOs (`ProductSummary`, `CartLine`) o ids + rehidratación en el
  servicio.
