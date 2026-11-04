# ADR-0018: RediseÃ±o visual gamer y flujo reactivo de checkout en carrito

* **Estado:** Aceptado
* **Fecha:** 2026-10-30
* **Autor:** Martin Munoz Codazzi

## Contexto

El proyecto contaba con una estructura funcional sÃ³lida (Livewire 3, catÃ¡logo reactivo, carrito en sesiÃ³n, ADRs anteriores), pero la interfaz visual era una plantilla genÃ©rica clara con botones bÃ¡sicos ("Finalizar compra (fuera de alcance)"). Para la entrega y demostraciÃ³n al profesor, se requerÃ­a darle una impronta moderna y atractiva tipo tienda gamer (similar al espÃ­ritu de Compra Gamer: estÃ©tica dark, naranja/Ã¡mbar, promociones de pago en transferencia/cuotas), junto con la capacidad operativa de comprar un producto tanto de forma directa como a travÃ©s del carrito.

## DecisiÃ³n

1. **EstÃ©tica Gamer Dark:**
   - Se renovÃ³ el layout principal `resources/views/layouts/store.blade.php` con modo oscuro inmersivo (`bg-gray-950`, acentos naranja/Ã¡mbar), barra superior de avisos comerciales y cabecera moderna.
   - Las cards del catÃ¡logo (`product-card.blade.php`) y la ficha de detalle (`products/show.blade.php`) ahora exhiben badges de financiaciÃ³n ("3 Cuotas fijas"), cÃ¡lculo dinÃ¡mico de precio especial por transferencia (10% OFF) y botÃ³n de compra rÃ¡pida.
   - La cabecera del catÃ¡logo (`catalogo.blade.php`) incluye un banner tecnolÃ³gico representativo.

2. **Funcionalidad "Comprar ahora" y "Finalizar compra" (Checkout reactivo):**
   - En la ficha de producto se agregÃ³ el mÃ©todo `buyNow()` dentro de `AddToCartButton`, permitiendo aÃ±adir la cantidad deseada e ir directamente al resumen de compra.
   - En `CartSummary`, el botÃ³n deshabilitado se transformÃ³ en una acciÃ³n reactiva `openCheckout()` que levanta un modal de confirmaciÃ³n en Livewire.
   - El checkout solicita nombre, correo y mÃ©todo de pago (transferencia con descuento, tarjeta o efectivo), valida datos, genera un nÃºmero de orden simulado Ãºnico (`ORD-YYYYMMDD-XXXX`), vacÃ­a el carrito a travÃ©s de `CartService` y muestra la pantalla de confirmaciÃ³n exitosa con enlace para seguir explorando.
   - Se respetaron al 100% las decisiones previas: `CartService` como Ãºnica fuente de verdad de la sesiÃ³n, precios en cÃ©ntimos ARS y reactividad con Livewire 3 sin librerÃ­as externas.

## Consecuencias

### Positivas
- Interfaz atractiva y profesional para presentar el avance acadÃ©mico.
- Flujo de compra de punta a punta demostrable en vivo ante el docente.
- No se agregaron dependencias pesadas ni se rompiÃ³ la arquitectura existente.

### Limitaciones / Riesgos
- El proceso de checkout es una simulaciÃ³n transaccional (orden generada y carrito vaciado en sesiÃ³n); no se conecta a pasarelas reales (como MercadoPago o Stripe) ni persiste aÃºn la orden en una tabla `orders` en base de datos.

