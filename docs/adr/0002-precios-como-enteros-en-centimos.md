# ADR-0002: Los precios se almacenan como enteros en céntimos

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

## Contexto

El catálogo necesita dos operaciones exactas sobre el precio: **filtrar por rango** (slider de precio mínimo y máximo) y **sumar** los importes del carrito. Ambas operaciones se ejecutan miles de veces por día y cualquier error de redondeo se propaga a los totales que ve el cliente.

La pregunta de diseño es cómo representar un precio monetario en la base de datos y en PHP. Alternativas evaluadas:

- `decimal(10,2)` en SQL y `float`/`double` en PHP: es la opción por defecto en muchos-tutoriales, pero las coma flotante acumulan error. Sumar $ 0,10 + $ 0,20 en IEEE-754 no da $ 0,30, sino $ 0,30000000000000004. Con dos productos por carrito el error es invisible; con un reporte de ventas diario ya no lo es.
- `decimal(10,2)` en SQL y `decimal:2` en el cast de Eloquent: el cast devuelve **string**, no número. Cada comparación y cada suma necesita un cast explícito a entero o float, y es fácil olvidarlo en uno de los muchos puntos de uso.
- `float` en PHP: rechazo frontal, misma razón que arriba.

La tienda está orientada a Argentina (moneda ARS) y el precio es un dato de negocio, no un texto de presentación: la decisión de cómo se *muestra* (símbolo, separador de miles, decimales) pertenece a la capa de presentación, no al modelo de datos.

## Decisión

La columna `products.price` es un **`unsignedBigInteger` que representa céntimos de pesos argentinos (ARS)**.

- $ 12.345,67 se almacena como `1234567`.
- El modelo `Product` declara el cast `'price' => 'integer'`, de modo que el valor que llega a PHP siempre es un entero.
- El formato a moneda se resuelve exclusivamente en la capa de presentación, mediante el accessor `formatted_price`, que usa `Number::currency()` con la moneda y el locale de `config/store.php` (`ARS` / `es_AR`).
- Los umbrales de los filtros de precio viajan por la URL en **céntimos** (ej. `?precio_min=500000`) para no depender de la configuración regional del navegador del visitante.
- La tasa de IVA (21 %) vive en `config/store.php` como `store.tax_rate` y se aplica sobre el subtotal en céntimos.

## Consecuencias

### Positivas

- Las sumas del carrito y los filtros de rango son **exactos**: `1234567 + 250000 = 1484567`, sin deriva.
- No hace falta castear en cada operación: el tipo entero se propaga solo.
- El rango del tipo es holgado (2^63) para cualquier catálogo: el límite real es el de la constraint de la base, mucho mayor que un `unsignedInteger`.
- La lógica de negocio queda independiente del formato: cambiar la moneda o el locale no toca el modelo.
- Es el estándar de la industria (Stripe, Shopify), lo que resulta comprensible para cualquier evaluador.

### Limitaciones / Riesgos

- El valor almacenado no es el que ve el usuario: hay que respetar la convención de "céntimos" en todo el código o aparecen errores de factor 100.
- El accessor `formatted_price` convierte a `float` para delegar en `Number::currency`. La conversión es exacta para múltiplos de 0,01 dentro del rango de `double`, pero el formateo no debe usarse nunca para comparar precios: para eso está el entero.
- Cambiar la moneda obligaría a migrar la columna (multiplicar por el factor de conversión) o a sumar una tabla de histórico de precios.
