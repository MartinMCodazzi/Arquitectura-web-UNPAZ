# ADR-0004: El carrito de compras se persiste en la sesión

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

## Contexto

La tienda es mínima y opera con **visitantes no autenticados**: no hay checkout, no hay pasarela de pago y no hay historial de pedidos en el alcance del proyecto. Aun así, el carrito tiene que sobrevivir a la navegación entre páginas y, con `SESSION_DRIVER=database`, también al cierre del navegador.

La decisión es dónde vive el estado del carrito. Alternativas evaluadas:

- **Tablas `carts` y `cart_items` en base de datos:** persistencia real, historial, carrito compartible entre dispositivos y consulta analítica de "productos más agregados al carrito". El costo: dos tablas más, un modelo de datos que hay que mantener, y el problema de reconciliar el carrito de sesión con el carrito de un usuario que acaba de iniciar sesión.
- **Cookie:** el carrito viaja al cliente. Límite de 4 KB y exposición de cantidades e importes, sin ninguna garantía de integridad (el cliente puede editar el contenido).
- **Redis:** rápido, pero agrega una dependencia de infraestructura para un estado de duración corta.
- **Sesión:** el proyecto ya usa el driver `database`; no requiere ninguna tabla nueva ni ningún servicio adicional.

## Decisión

El carrito vive en la **sesión**, encapsulado en un servicio de dominio `App\Services\CartService` (Tarea 3). Decisiones concretas:

- La sesión guarda un array plano `{ product_id: quantity }`. No hay objetos serializados: sólo identificadores y cantidades enteras.
- El servicio es el **único** que escribe en la clave de sesión. Los controladores y los componentes Livewire nunca tocan `Session::get('cart')` directamente.
- El servicio resuelve los datos faltantes al leer: cuando un producto del carrito fue borrado o desactivado, la línea se descarta y se limpia la sesión.
- La validación de stock y de cantidad vive en el servicio, no en la vista ni en el controlador.
- `SESSION_DRIVER=database` ya está configurado en `.env`, por lo que el carrito sobrevive al cierre del navegador sin trabajo adicional.

## Consecuencias

### Positivas

- Cero tablas nuevas y cero migraciones para el carrito: el modelo de datos del proyecto sigue siendo `categories` + `products`.
- La estructura `{ id => cantidad }` hace triviales las operaciones de agregar, quitar y cambiar cantidad, y el cálculo de totales es una suma sobre el array.
- El cliente no puede manipular el carrito: el estado vive en el servidor.
- La dependencia de stock se valida en un único lugar y es fácil de testear de forma aislada (el servicio no necesita HTTP).
- Migrar más adelante a persistencia en base de datos es acotado: el `CartService` es la única frontera que habría que reescribir.

### Limitaciones / Riesgos

- El carrito se pierde al cerrar la sesión o al expirar la cookie de sesión. Es el comportamiento esperado para una tienda sin cuenta, pero conviene explicarlo en la interfaz.
- No hay historial: no se puede saber qué productos se agregaron al carrito ni con qué frecuencia.
- Con el driver `database`, cada escritura del carrito es un `UPDATE` sobre la tabla `sessions`. A alto volumen de tráfico sería preferible Redis o el driver `file`.
- Si más adelante se agrega inicio de sesión, hay que decidir qué pasa con el carrito de invitado (recomendación: conservarlo y fusionarlo).
