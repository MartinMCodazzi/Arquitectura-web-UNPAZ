# ADR-0015: El listado del catálogo se cachea como datos planos y la vista consume resúmenes

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

*Complementa a [ADR-0010](0010-cache-del-catalogo-con-datos-planos.md) (la caché guarda datos planos y los objetos se rehidratan en memoria) y a [ADR-0008](0008-cache-del-catalogo-con-claves-versionadas.md) (invalidación por versión). No reemplaza a ninguno: los aplica al listado de productos.*

## Contexto

Hasta ahora la caché del catálogo guardaba solamente la lista de categorías. El listado de productos, que es la página más solicitada del sitio, consultaba la base de datos en cada request: una consulta de productos con su categoría, el conteo total de la paginación y el renderizado de la vista.

ADR-0010 ya había resuelto el problema de fondo (en Laravel 13 la caché no deserializa clases PHP, así que lo que se guarda son arrays planos y los objetos se rehidratan en memoria), pero faltaba tomar la decisión de **qué se cachea del listado y cómo se reconstruye la paginación**.

Alternativas evaluadas:

- **Cachear los modelos Eloquent:** ya está descartado por ADR-0010: al releerlos quedarían como `__PHP_Incomplete_Class`.
- **Cachear el HTML renderizado del listado (caché de fragmentos):** es lo más simple y la respuesta es idéntica byte a byte. El problema es que el fragmento depende de la URL (cada combinación de filtros y cada página necesita su propia entrada), no se puede invalidar de forma selectiva cuando cambia un producto y el HTML cacheado envejece con datos viejos si el TTL vence sin que los observadores se disparen.
- **Cachear solamente los identificadores y volver a consultar:** no ahorra la consulta principal, que es la que cuesta.
- **Cachear las filas planas del listado y reconstruir el paginador en memoria:** el payload es un array con el total y las filas; al releerlo se rehidratan objetos de presentación y se arma un `LengthAwarePaginator` con esos datos. La consulta a la base de datos desaparece por completo en el camino caliente.

## Decisión

**El listado filtrado del catálogo se cachea por combinación de filtros y página, guardado como datos planos; la vista consume siempre `App\DTO\ProductSummary` y nunca un modelo Eloquent.**

- **Qué se cachea.** El resultado de la consulta paginada: el total de productos que cumplen los filtros y las filas de la página pedida. Cada fila es un array plano con `id`, `name`, `slug`, `price`, `stock`, `category` (array o `null`), `image_version` e `image_formats`.
- **Clave.** `catalogo.productos.v{version}.{huella}`, donde la huella es el SHA-1 de los filtros normalizados, la página y el tamaño de página. La versión es la del catálogo: cuando un producto o una categoría cambian, los observadores la incrementan (ADR-0008) y las entradas viejas quedan huérfanas y se limpian solas al vencer su TTL.
- **TTL.** 600 segundos para el listado y 3600 para las categorías. El TTL es una red de seguridad, no el mecanismo principal de invalidación: ese es la versión.
- **Rehidratación.** `App\DTO\ProductSummary` reconstruye cada fila. Expone los mismos atributos públicos que el modelo (`name`, `formatted_price`, `is_in_stock`, `image_url`, `category`) y el método `thumbnailUrl()`, de modo que la card del catálogo funciona igual con un resumen que con un modelo. La vista del listado nunca ve un modelo Eloquent; la ficha de producto sigue usando el modelo, porque necesita la descripción completa y no se cachea.
- **Paginación.** El paginador se reconstruye con `LengthAwarePaginator` a partir de los datos cacheados, conservando la query string de la request para que los enlaces de paginación mantengan los filtros aplicados.
- **Las imágenes no se consultan en cada request.** La fila cacheada guarda la *versión* de la miniatura (el hash del original, ADR-0013) y los formatos disponibles: con esos dos datos la URL de la miniatura se arma sin tocar el disco. La comprobación de que el archivo exista se hace una sola vez, al llenar la caché.
- **El catálogo y la ficha no comparten tipo de dato.** El catálogo filtrado es una lista repetida y barata de cachear; la ficha es una página por producto y nunca se cachea.

## Consecuencias

### Positivas

- **La página más solicitada no consulta la tabla de productos:** el camino caliente son cuatro lecturas de caché y la sesión.
- El contenido cacheado y el que sale de la base de datos son idénticos: la misma DTO se construye desde una fila o desde un modelo, así que no hay dos versiones de la card que puedan divergir.
- La invalidación es inmediata y no depende del TTL: al desactivar un producto, la versión del catálogo cambia y el listado se recalcula.
- El `total` de la paginación viaja en el payload: la vista puede mostrar "N productos" sin una segunda consulta de conteo.
- La fila cacheada es un array plano: se puede inspeccionar, versionar y migrar sin deserializar clases PHP.

### Limitaciones / Riesgos

- **La caché guarda datos de una versión del catálogo:** si un producto cambia por fuera de la aplicación (una edición manual en la base de datos), el listado puede quedar viejo hasta que venza el TTL de 600 segundos.
- El payload ocupa espacio en la caché por cada combinación de filtros y página: con muchas combinaciones distintas el consumo crece. El TTL es lo que evita que se acumule.
- La DTO `ProductSummary` expone atributos con nombre `snake_case` para parecerse al modelo: es una decisión deliberada para que las vistas sirvan a las dos formas, pero se aparta de la convención habitual de PHP.
- La fila cacheada duplica parte de los datos del producto (nombre, precio): si mañana el catálogo necesita un campo más en la card, hay que tocar el DTO, el payload y la invalidación del catálogo.
- El sitemap, el catálogo y la ficha calculan sus URL por caminos distintos (versión de caché, filtros normalizados y slug): si una URL cambia, hay que revisar los tres.
