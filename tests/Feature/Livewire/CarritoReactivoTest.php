<?php

use App\Livewire\Cart\AddToCartButton;
use App\Livewire\Cart\CartCounter;
use App\Livewire\Cart\CartSummary;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Services\CartService;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Carrito reactivo con Livewire (ADR-0017)
|--------------------------------------------------------------------------
|
| La única frontera de escritura del carrito sigue siendo CartService
| (ADR-0004); estos tests garantizan que las piezas Livewire respetan esa
| frontera, que el feedback correcto se muestra y que el evento
| `carrito-actualizado` conecta el contador del header.
|
*/

/**
 * Recorre el asistente de checkout completo con datos válidos.
 */
function completarCheckout($component, bool $acceptTerms = true)
{
    $component
        ->call('startCheckout')
        ->set('shippingMethod', 'domicilio')
        ->set('shippingName', 'Ana Gamer')
        ->set('shippingPhone', '1123456789')
        ->set('shippingStreet', 'Av. Corrientes')
        ->set('shippingNumber', '1234')
        ->set('shippingCity', 'CABA')
        ->set('shippingProvince', 'Buenos Aires')
        ->set('shippingPostalCode', '1043')
        ->set('shippingCourier', 'oca')
        ->call('nextStep')
        ->set('checkoutName', 'Ana Gamer')
        ->set('checkoutEmail', 'ana@example.com')
        ->set('billingTaxId', '20123456789')
        ->set('billingTaxCondition', 'consumidor_final')
        ->call('nextStep')
        ->set('checkoutPayment', 'transferencia')
        ->call('nextStep');

    if ($acceptTerms) {
        $component->set('acceptedTerms', true);
    }

    return $component;
}

test('el botón agregar guarda el producto en la sesión vía CartService', function () {
    $producto = Product::factory()->withStock(5)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 5])
        ->call('addToCart')
        ->assertDispatched('carrito-actualizado')
        ->assertSee('Producto agregado al carrito.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 1]);
});

test('el botón agregar respeta la cantidad elegida en la ficha', function () {
    $producto = Product::factory()->withStock(5)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 5, 'withQuantity' => true])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertSee('Producto agregado al carrito.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 2]);
});

test('el botón agregar avisa cuando el stock no alcanza', function () {
    $producto = Product::factory()->withStock(3)->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 3, 'withQuantity' => true])
        ->set('quantity', 10)
        ->call('addToCart')
        ->assertSee('sólo hay 3');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('el botón agregar no bloquea productos sin stock: sólo no agrega', function () {
    $producto = Product::factory()->outOfStock()->create();

    Livewire::test(AddToCartButton::class, ['productId' => $producto->id, 'stock' => 0])
        ->call('addToCart')
        ->assertSee('No hay stock suficiente');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('el contador del header refleja el carrito y se refresca con el evento', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 2);

    Livewire::test(CartCounter::class)
        ->assertSee('>2</span>');

    Livewire::test(CartCounter::class)
        ->dispatch('carrito-actualizado')
        ->assertSee('>2</span>');
});

test('el carrito muestra el estado vacío cuando no hay líneas', function () {
    Livewire::test(CartSummary::class)->assertSee('Tu carrito está vacío');
});

test('cambiar la cantidad desde el input actualiza el carrito', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->set("quantities.{$producto->id}", 3)
        ->assertSee('Carrito actualizado.');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('quitar una línea la elimina de la sesión', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 2);

    Livewire::test(CartSummary::class)
        ->assertSee($producto->name)
        ->call('removeProduct', $producto->id)
        ->assertSee('Producto eliminado del carrito.');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('vaciar el carrito borra todas las líneas', function () {
    $primero = Product::factory()->withStock(5)->create();
    $segundo = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($primero, 1);
    app(CartService::class)->add($segundo, 1);

    Livewire::test(CartSummary::class)
        ->assertSee($primero->name)
        ->assertSee($segundo->name)
        ->call('clearCart')
        ->assertSee('Carrito vaciado.')
        ->assertSee('Tu carrito está vacío');

    expect(session(CartService::SESSION_KEY))->toBeNull();
});

test('la cantidad no puede superar el stock disponible', function () {
    $producto = Product::factory()->withStock(3)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->set("quantities.{$producto->id}", 99)
        ->assertDispatched('carrito-actualizado');

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 3]);
});

test('un invitado que finaliza la compra recibe el pedido de login y registro', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->assertSet('showLoginModal', true)
        ->assertSet('showCheckoutModal', false)
        ->assertSee('Ingresá para finalizar tu compra')
        ->assertSee('Crear una cuenta');
});

test('un invitado no puede confirmar la compra aunque fuerce el checkout', function () {
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->set('showCheckoutModal', true)
        ->call('processCheckout')
        ->assertSet('showCheckoutModal', false)
        ->assertSet('showLoginModal', true)
        ->assertSet('orderPlacedMessage', null);

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 1]);
});

test('un cliente autenticado abre el checkout con los datos de su cuenta', function () {
    $user = User::factory()->create([
        'name' => 'Ana Gamer',
        'email' => 'ana@example.com',
    ]);

    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->assertSet('showLoginModal', false)
        ->assertSet('showCheckoutModal', true)
        ->assertSet('checkoutName', 'Ana Gamer')
        ->assertSet('checkoutEmail', 'ana@example.com');
});

test('el login desde el carrito autentica y vuelve al carrito con el checkout pendiente', function () {
    $user = User::factory()->create([
        'email' => 'cliente@example.com',
        'password' => 'password',
    ]);

    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->set('form.email', 'cliente@example.com')
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('carrito.index'));

    $this->assertAuthenticatedAs($user);

    expect(session('open_checkout'))->toBeTrue();
});

test('el login desde el carrito muestra el error con credenciales incorrectas', function () {
    $user = User::factory()->create([
        'email' => 'cliente@example.com',
        'password' => 'password',
    ]);

    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->set('form.email', 'cliente@example.com')
        ->set('form.password', 'incorrecta')
        ->call('login')
        ->assertHasErrors(['form.email'])
        ->assertSet('showLoginModal', true)
        ->assertSet('showCheckoutModal', false);

    $this->assertGuest();
});

test('al volver al carrito después del login el checkout se abre automáticamente', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user)->withSession(['open_checkout' => true]);

    Livewire::test(CartSummary::class)
        ->assertSet('showLoginModal', false)
        ->assertSet('showCheckoutModal', true);
});

test('la página del carrito renderiza la preparación de la compra al volver del login', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user)
        ->withSession(['open_checkout' => true])
        ->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Estamos preparando todo para tu compra');
});

test('al finalizar la compra se muestra la pantalla de preparación antes de los pasos', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->assertSet('checkoutPhase', 'loading')
        ->assertSet('checkoutStep', 1)
        ->assertSee('Estamos preparando todo para tu compra')
        ->call('startCheckout')
        ->assertSet('checkoutPhase', 'wizard')
        ->assertSee('¿Cómo querés recibir tu compra?');
});

test('el paso de envío exige los datos de la dirección antes de avanzar', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->call('startCheckout')
        ->call('nextStep')
        ->assertHasErrors([
            'shippingPhone',
            'shippingStreet',
            'shippingNumber',
            'shippingCity',
            'shippingProvince',
            'shippingPostalCode',
            'shippingCourier',
        ])
        ->assertSet('checkoutStep', 1);
});

test('el retiro en punto de entrega avanza sin pedir dirección y no cobra envío', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create(['price' => 10_000]);

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = Livewire::test(CartSummary::class)
        ->call('openCheckout')
        ->call('startCheckout')
        ->set('shippingMethod', 'retiro')
        ->set('shippingPickupPoint', 'norte')
        ->set('shippingPhone', '1123456789')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('checkoutStep', 2);

    expect($component->instance()->shippingCost())->toBe(0)
        ->and($component->instance()->grandTotal())->toBe(11_100);
});

test('el paso de facturación exige DNI o CUIT del comprador', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = Livewire::test(CartSummary::class)->call('openCheckout');

    $component
        ->call('startCheckout')
        ->set('shippingPhone', '1123456789')
        ->set('shippingStreet', 'Av. Corrientes')
        ->set('shippingNumber', '1234')
        ->set('shippingCity', 'CABA')
        ->set('shippingProvince', 'Buenos Aires')
        ->set('shippingPostalCode', '1043')
        ->set('shippingCourier', 'oca')
        ->call('nextStep')
        ->assertSet('checkoutStep', 2)
        ->call('nextStep')
        ->assertHasErrors(['billingTaxId'])
        ->assertSet('checkoutStep', 2);
});

test('el pago en efectivo se rechaza con envío a domicilio y se acepta con retiro', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $domicilio = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'domicilio',
    );

    $domicilio
        ->set('checkoutPayment', 'efectivo')
        ->call('nextStep')
        ->assertHasErrors(['checkoutPayment'])
        ->assertSet('checkoutStep', 3);

    $retiro = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'retiro',
    );

    $retiro
        ->set('checkoutPayment', 'efectivo')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('checkoutStep', 4);
});

test('la revisión muestra los datos de envío, facturación y pago', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarCheckout(
        Livewire::test(CartSummary::class)->call('openCheckout'),
    );

    $component
        ->assertSet('checkoutStep', 4)
        ->assertSee('Av. Corrientes 1234')
        ->assertSee('Factura B')
        ->assertSee('20123456789')
        ->assertSee('Transferencia bancaria')
        ->assertSee('Acepto los');
});

test('el resumen final suma el envío y aplica el descuento por transferencia', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create(['price' => 10_000]);

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarCheckout(
        Livewire::test(CartSummary::class)->call('openCheckout'),
    );

    // Productos 10.000 + IVA 2.100 + envío OCA 4.500 - 10 % de descuento 1.000.
    expect($component->instance()->shippingCost())->toBe(450_000)
        ->and($component->instance()->discount())->toBe(1_000)
        ->and($component->instance()->grandTotal())->toBe(461_100);
});

test('no se puede confirmar la compra sin aceptar los términos y condiciones', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarCheckout(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        acceptTerms: false,
    );

    $component
        ->call('processCheckout')
        ->assertHasErrors(['acceptedTerms'])
        ->assertSet('showCheckoutModal', true);

    expect(session(CartService::SESSION_KEY))->toBe([$producto->id => 1]);
});

test('al pagar con tarjeta se piden los datos y se validan', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'domicilio',
    );

    $component
        ->set('checkoutPayment', 'tarjeta_credito')
        ->call('nextStep')
        ->assertHasErrors(['cardNumber', 'cardHolder', 'cardExpiry', 'cardCvv'])
        ->assertSet('checkoutStep', 3);
});

test('una tarjeta vencida no permite avanzar', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'domicilio',
    );

    $component
        ->set('checkoutPayment', 'tarjeta_debito')
        ->set('cardNumber', '4111111111111111')
        ->set('cardHolder', 'ANA GAMER')
        ->set('cardExpiry', '01/20')
        ->set('cardCvv', '123')
        ->call('nextStep')
        ->assertHasErrors(['cardExpiry'])
        ->assertSet('checkoutStep', 3);
});

test('una tarjeta válida permite avanzar y sus datos se limpian al confirmar', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'domicilio',
    );

    $component
        ->set('checkoutPayment', 'tarjeta_credito')
        ->set('cardNumber', '4111 1111 1111 1111')
        ->set('cardHolder', 'ANA GAMER')
        ->set('cardExpiry', '12/30')
        ->set('cardCvv', '123')
        ->set('cardInstallments', '3')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('checkoutStep', 4)
        ->assertSet('cardNumber', '4111111111111111')
        ->set('acceptedTerms', true)
        ->call('processCheckout')
        ->assertSet('showCheckoutModal', false)
        ->assertSet('cardNumber', '');

    expect($component->get('orderPlacedMessage'))->not->toContain('4111');
});

test('Mercado Pago redirige a su pantalla y guarda el resumen del pago', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarHastaPago(
        Livewire::test(CartSummary::class)->call('openCheckout'),
        'domicilio',
    );

    $component
        ->set('checkoutPayment', 'mercadopago')
        ->call('nextStep')
        ->assertRedirect(route('checkout.mercadopago'));

    expect(session('mp_checkout'))->toBeArray()
        ->and(session('mp_checkout')['amount'])->toBeInt()
        ->and(session('mp_checkout_state')['checkoutPayment'])->toBe('mercadopago');
});

test('la pantalla de Mercado Pago muestra el total y confirma el pago', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $this->withSession(['mp_checkout' => mercadoPagoPayload()])
        ->get(route('checkout.mercadopago'))
        ->assertOk()
        ->assertSee('Mercado Pago')
        ->assertSee('Pagar con Mercado Pago');

    $this->post(route('checkout.mercadopago.confirm'))
        ->assertRedirect(route('carrito.index'))
        ->assertSessionHas('order_placed_message')
        ->assertSessionHas('success');

    expect(session(CartService::SESSION_KEY))->toBeNull();

    $reservation = Reservation::query()->where('user_id', $user->id)->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->payment_method)->toBe('mercadopago')
        ->and($reservation->status)->toBe('reservada')
        ->and($reservation->items)->toHaveCount(1);

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('¡Compra finalizada con éxito!');
});

test('cancelar Mercado Pago vuelve al paso de pago con los datos cargados', function () {
    $user = User::factory()->create();
    $producto = Product::factory()->withStock(5)->create();

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $this->withSession([
        'mp_checkout' => mercadoPagoPayload(),
        'mp_checkout_state' => [
            'shippingMethod' => 'domicilio',
            'shippingName' => 'Ana Gamer',
            'shippingPhone' => '1123456789',
            'shippingStreet' => 'Av. Corrientes',
            'shippingNumber' => '1234',
            'shippingFloor' => '',
            'shippingCity' => 'CABA',
            'shippingProvince' => 'Buenos Aires',
            'shippingPostalCode' => '1043',
            'shippingNotes' => '',
            'shippingCourier' => 'oca',
            'shippingPickupPoint' => '',
            'checkoutName' => 'Ana Gamer',
            'checkoutEmail' => 'ana@example.com',
            'billingInvoiceType' => 'B',
            'billingTaxCondition' => 'consumidor_final',
            'billingTaxId' => '20123456789',
            'checkoutPayment' => 'mercadopago',
        ],
    ])->post(route('checkout.mercadopago.cancel'))
        ->assertRedirect(route('carrito.index'))
        ->assertSessionHas('open_checkout', true);

    $this->get(route('carrito.index'))
        ->assertOk()
        ->assertSee('Elegí cómo querés pagar')
        ->assertSee('Mercado Pago');
});

test('un cliente autenticado puede confirmar la compra y el carrito se vacía', function () {
    $user = User::factory()->create();

    $producto = Product::factory()->withStock(5)->create(['price' => 10_000]);

    app(CartService::class)->add($producto, 1);

    $this->actingAs($user);

    $component = completarCheckout(
        Livewire::test(CartSummary::class)->call('openCheckout'),
    );

    $component
        ->call('processCheckout')
        ->assertSet('showCheckoutModal', false)
        ->assertSee('¡Compra finalizada con éxito!');

    expect(session(CartService::SESSION_KEY))->toBeNull();

    $reservation = Reservation::query()->where('user_id', $user->id)->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->status)->toBe('reservada')
        ->and($reservation->total)->toBeInt()
        ->and($reservation->items)->toHaveCount(1)
        ->and($reservation->items->first()->product_name)->toBe($producto->name);
});

/**
 * Completa envío y facturación y deja el asistente en el paso de pago.
 */
function completarHastaPago($component, string $shippingMethod)
{
    $component
        ->call('startCheckout')
        ->set('shippingMethod', $shippingMethod)
        ->set('shippingPhone', '1123456789');

    if ($shippingMethod === 'retiro') {
        $component->set('shippingPickupPoint', 'central');
    } else {
        $component
            ->set('shippingStreet', 'Av. Corrientes')
            ->set('shippingNumber', '1234')
            ->set('shippingCity', 'CABA')
            ->set('shippingProvince', 'Buenos Aires')
            ->set('shippingPostalCode', '1043')
            ->set('shippingCourier', 'oca');
    }

    return $component
        ->call('nextStep')
        ->set('billingTaxId', '20123456789')
        ->call('nextStep');
}

/**
 * Resumen que el asistente guarda antes de redirigir a Mercado Pago.
 *
 * @return array<string, mixed>
 */
function mercadoPagoPayload(): array
{
    return [
        'checkout_name' => 'Ana Gamer',
        'checkout_email' => 'ana@example.com',
        'shipping_summary' => 'Envío a domicilio con OCA a Av. Corrientes 1234, CABA, Buenos Aires (CP 1043)',
        'subtotal' => 10_000,
        'taxes' => 2_100,
        'shipping_cost' => 450_000,
        'discount' => 0,
        'amount' => 462_100,
    ];
}
