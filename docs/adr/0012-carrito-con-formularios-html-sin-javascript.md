# ADR-0012: El carrito se manipula con formularios HTML contra endpoints HTTP

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

*Complementa a [ADR-0004](0004-carrito-persistido-en-sesion.md) (dónde vive el carrito) y a [ADR-0011](0011-redireccion-canonica-del-formulario-de-filtros.md) (el catálogo público no depende de JavaScript). No reemplaza a ninguno: los aplica.*

## Contexto

El carrito necesita cuatro operaciones: agregar un producto, cambiar la cantidad de una línea, quitar una línea y vaciar el carrito. La decisión pendiente es **cómo se realizan estas operaciones desde la interfaz**.

El proyecto tiene Livewire 3 y Volt instalados (dependencias PHP), pero **no tiene Alpine.js en `package.json`**: Alpine viaja dentro del bundle de Livewire y sólo está disponible en las páginas que montan un componente de Livewire. El layout público del catálogo (`x-store-layout`) no monta ninguno, y eso fue una decisión explícita de ADR-0011.

Alternativas evaluadas:

- **Componentes Livewire para el carrito:** la alternativa natural, porque el proyecto ya trae Livewire. Aporta interactividad (actualizar la cantidad sin recargar, contador del encabezado al instante) y su propio Alpine. El costo: cada página del catálogo que monta un componente Livewire carga su bundle de JavaScript, se pierde el renderizado íntegramente en servidor y el carrito deja de funcionar sin JavaScript.
- **Formularios HTML contra endpoints HTTP:** cada operación es un `POST`/`PATCH`/`DELETE` con `@csrf` y el verbo HTTP true (`@method`). El servidor responde con un `302` y un mensaje flash. No hay JavaScript, no hay estado en el cliente y el resultado es el mismo con o sin scripts.
- **`fetch` desde el cliente:** actualizar el carrito sin recargar, pero es JavaScript a mano, sin protección CSRF por defecto, y vuelve a depender del JavaScript en el camino crítico.

## Decisión

**El carrito se manipula con formularios HTML contra endpoints HTTP de `CartController`. El sitio público completo, catálogo y carrito incluidos, se renderiza en servidor y funciona sin JavaScript.**

- Endpoints (`routes/web.php`): `GET /carrito`, `POST /carrito/agregar`, `PATCH /carrito/{product}`, `DELETE /carrito/{product}` y `DELETE /carrito/vaciar`.
- Los formularios viajan con `@csrf` y `@method('PATCH' | 'DELETE')`, que Laravel traduce al verbo HTTP real. Son formularios `POST` reales, no enlaces.
- Cada endpoint responde `302` con un mensaje flash (`success`, `warning` o `error`) que el layout público muestra sobre el contenido. No hay JSON ni AJAX.
- El estado del carrito sigue siendo responsabilidad exclusiva de `App\Services\CartService` (ADR-0004): los controladores validan la entrada y delegan; el servicio es el único que escribe en la clave de sesión.
- El contador de unidades del encabezado no es un componente reactivo: `StoreLayout::render()` lee el conteo del `CartService` en cada render del servidor. Como el carrito se modifica por formularios, el badge ya está al día en la respuesta siguiente.
- La cantidad es un campo de texto (number input) con `min`/`max`: el servidor vuelve a validar (`min:1`, `max:100`) y el servicio la limita al stock. La validación del cliente es una ayuda visual, nunca la garantía.

## Consecuencias

### Positivas

- **Cero JavaScript en todo el sitio público:** no se carga el bundle de Livewire ni Alpine, no hay estado en el cliente que pueda desincronizarse del servidor y el carrito funciona con JavaScript deshabilitado.
- El flujo es HTTP puro y comprobable con `curl`: cualquier operación se puede auditar en los logs del servidor.
- La protección CSRF y la validación del servidor se aplican de forma estándar, sin código propio.
- El badge del carrito, los mensajes y las líneas se renderizan siempre con el estado real del servidor: no hay "fantasmas" de estado en el cliente.
- Consistente con ADR-0011: una sola regla para todo el sitio público (renderizado en servidor).

### Limitaciones / Riesgos

- Cada operación del carrito recarga la página: agregar un producto vuelve al catálogo y el contador del encabezado se actualiza recién en esa respuesta. Para un carrito de este tamaño es aceptable, pero es más lento que una actualización sin recarga.
- El token CSRF debe estar disponible en el HTML de cada página con formularios: el layout público ya lo expone en `<meta name="csrf-token">`.
- Si en el futuro se necesita un carrito que se actualice en vivo (por ejemplo, un panel de administración), la respuesta natural sería un componente Livewire: habría que revisar esta decisión en ese momento.
- La validación con `number input` depende del navegador para la usability (flechitas, validación nativa); el servidor sigue siendo la autoridad.
