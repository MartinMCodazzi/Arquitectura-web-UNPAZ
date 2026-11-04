# ADR-0011: El formulario de filtros envía precios en pesos y redirige a la URL canónica

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

*Complementa a [ADR-0002](0002-precios-como-enteros-en-centimos.md) (los precios se almacenan y se comunican en céntimos) y a [ADR-0006](0006-filtros-con-scopes-locales-de-eloquent.md) (los filtros viajan en la query string). No reemplaza a ninguno: los aplica.*

## Contexto

ADR-0002 fija que los precios se almacenan en céntimos y que **los umbrales de los filtros de precio viajan en la URL en céntimos** (`?precio_min=500000`). ADR-0006 fija que el estado del catálogo vive en la query string, para que cada combinación de filtros sea indexable y compartible.

El formulario de filtros, en cambio, lo escribe una persona: nadie piensa en céntimos. Si el campo "Precio mínimo ($)" enviara `500000` para $5.000, el usuario tendría que hacer la cuenta a mano y equivocarse con facilidad. Hay que decidir cómo se traduce "lo que la persona escribe" a "lo que viaja en la URL".

Alternativas evaluadas:

- **Convertir en el navegador (JavaScript):** un `onsubmit` que multiplica los valores por 100 antes de enviar el formulario. Es lo más directo, pero el proyecto **no tiene Alpine.js en `package.json`** (sólo Livewire, que lo empaqueta dentro de su propio bundle) y el layout público del catálogo no monta ningún componente de Livewire: el `onsubmit` no se ejecutaría y el formulario enviaría pesos donde la URL espera céntimos, filtrando mal sin avisar.
- **Escribir los céntimos en el campo:** el formulario sería técnicamente correcto pero obligaría al usuario a calcular mentalmente el factor 100.
- **Cambiar la URL para que viaje en pesos:** contradice ADR-0002 y reparte la conversión entre la URL y la consulta, que es justamente la fuente clásica de errores de factor 100.
- **Redirección canónica:** el formulario manda pesos en parámetros con sufijo explícito (`precio_min_pesos`) y el controlador responde con un 302 a la URL canónica, que ya lleva los céntimos (`precio_min=500000`).

## Decisión

**El formulario de filtros escribe precios en pesos; la URL canónica los lleva en céntimos, y el paso de una a otra lo hace una redirección 302 del servidor.**

- Los campos del formulario se llaman `precio_min_pesos` y `precio_max_pesos`. El sufijo `_pesos` documenta la unidad en la propia URL y evita que alguien confunda ambos formatos.
- `ProductController@index()` detecta la presencia de esos parámetros (`hasPriceFormFields()`) y, si hay alguno con valor, responde `302` hacia `route('productos.index', canonicalQuery($request))`, donde los precios ya vienen convertidos a céntimos y el resto de los parámetros conserva sus nombres canónicos.
- Si el formulario se envía con los campos de precio vacíos, **no hay redirección**: los demás filtros ya viajan con nombres canónicos y la respuesta es `200` directa. Así el catálogo no paga un salto extra en cada cambio de filtro que no toque el precio.
- La conversión ocurre en un único lugar (`canonicalQuery()`), y la lectura de la URL canónica sigue haciéndola el DTO `App\DTO\ProductFilters`, que normaliza los umbrales a múltiplos de 100 (pesos enteros) para que el formulario y la URL nunca discrepen.
- La redirección es **302 (temporal)**: la URL canónica es la que se indexa y la que se comparte; la forma del formulario no es contenido distinto.

Consecuencia para las vistas: el formulario no necesita JavaScript en absoluto. Es un `GET` plano contra `/productos`, y el sitio funciona completo sin scripts (el catálogo entero se renderiza en servidor).

## Consecuencias

### Positivas

- El usuario escribe y ve pesos; la URL, la base de datos y el DTO trabajan siempre en céntimos: una sola unidad en cada capa.
- **Cero JavaScript** en el camino crítico del catálogo: no depende de Alpine.js ni de que Livewire esté montado en la página.
- Una sola URL por combinación de filtros (la canónica): no se duplica contenido ni se generan dos direcciones indexables para el mismo listado.
- La conversión está en un único método y es verificable con dos tests: uno del 302 y otro de la URL canónica ya resuelta.
- El DTO sigue siendo la única puerta de entrada de los filtros a la consulta: la redirección no agrega lógica de filtrado, sólo normaliza la URL.

### Limitaciones / Riesgos

- Un clic en "Filtrar" con precio cargado genera **dos respuestas** (302 + 200). Es un salto local y barato, pero es un salto.
- Hay dos nombres de parámetro para lo mismo (`precio_min_pesos` y `precio_min`): si alguien edita el formulario y no conoce la convención, puede romper el filtro en silencio. Mitigación: los parámetros del formulario llevan el sufijo `_pesos` y el controlador sólo actúa sobre esos dos nombres exactos.
- La URL canónica expone céntimos (`precio_min=500000`) en lugar del número que el usuario escribió: es menos legible, pero es el formato que exige ADR-0002 y evita ambigüedad de separador decimal entre visitantes.
- Si en el futuro se agrega JavaScript al catálogo (por ejemplo, un filtro por precio con slider), esta redirección debería revisarse: la conversión del lado del cliente haría innecesario el salto.
