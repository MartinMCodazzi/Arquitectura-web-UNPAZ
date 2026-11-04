# ADR-0003: Locale es_AR, moneda ARS y rutas en español

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** Martin Munoz Codazzi

## Contexto

El proyecto es una tienda para público argentino y el enunciado está en español. Laravel 13 se instaló con el *locale* por defecto `en` y el nombre de aplicación `Laravel`, lo que produce formatos numéricos norteamericanos (1,234.56) y una interfaz en inglés que no corresponde al destinatario.

Inicialmente se propuso `APP_LOCALE=ar`, por asociar "ar" con Argentina. Es un error: `ar` es el código ISO 639-1 del **árabe**; el locale del español rioplatense es `es_AR` (ISO 639-1 `es` + ISO 3166-1 `AR`).

Alternativas evaluadas:

- `en` / USD: la app funciona, pero muestra precios en dólares y una interfaz en inglés para un público que compra en pesos.
- `es` genérico: funciona, pero pierde el formato argentino (separador de miles con punto, decimal con coma, símbolo `$` y zona horaria `America/Argentina/Buenos_Aires`).
- `es_AR` + ARS: es el locale correcto para el público objetivo.

## Decisión

Configuración de la aplicación:

| Variable                  | Valor                              |
| ------------------------- | ---------------------------------- |
| `APP_NAME`                | `Tienda Online`                    |
| `APP_LOCALE`              | `es_AR`                            |
| `APP_FALLBACK_LOCALE`     | `en`                               |
| `APP_FAKER_LOCALE`        | `es_AR`                            |
| `APP_TIMEZONE`            | `America/Argentina/Buenos_Aires`   |
| `STORE_CURRENCY`          | `ARS`                              |
| `STORE_LOCALE`            | `es_AR`                            |
| `STORE_TAX_RATE`          | `0.21` (IVA 21 %)                  |
| `STORE_PRODUCTS_PER_PAGE` | `12`                               |

Además:

- `config/app.php` pasa a leer `env('APP_TIMEZONE', 'UTC')` (venía con `'UTC'` fijo).
- Los valores de negocio del store viven en `config/store.php`, no en el código: moneda, locale, tasa de impuesto y cantidad de productos por página.
- Las **rutas y los textos de la interfaz están en español**: `/productos`, `/productos/{slug}`, `/categorias/{slug}`, `/carrito`, `/sitemap.xml`.
- El formato de precio usa `Number::currency($centavos / 100, 'ARS', 'es_AR')`, que produce `$ 12.345,67`.

## Consecuencias

### Positivas

- Los precios se muestran en el formato que espera el cliente argentino, sin conversiones ni ambigüedad.
- La zona horaria correcta hace que las fechas de publicación (columna `created_at`) coincidan con el día local.
- `config/store.php` centraliza los parámetros de negocio: cambiar la tasa de IVA o la moneda es editar un archivo, no buscar constantes en el código.
- El `fallback_locale` en inglés evita pantallas vacías si falta alguna traducción.

### Limitaciones / Riesgos

- `es_AR` requiere la extensión `intl` de PHP para el formateo numérico. Está instalada en este entorno, pero es un requisito que debe declararse en la documentación de instalación.
- Si en el futuro hay que vender en varios países, el *locale* por defecto deja de alcanzar: habría que migrar a un paquete de localización por dominio o por sesión.
- Los nombres de ruta en español no son reutilizables tal cual en una versión en inglés: renombrarlos afecta a las URLs ya indexadas.
