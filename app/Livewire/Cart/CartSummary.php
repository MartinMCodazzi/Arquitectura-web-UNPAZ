<?php

namespace App\Livewire\Cart;

use App\Livewire\Forms\LoginForm;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Página del carrito (ADR-0017).
 *
 * Las cantidades se editan con `wire:model.live.debounce` sobre el array
 * `quantities` (producto id => cantidad); quitar una línea y vaciar el
 * carrito son acciones directas. Toda mutación pasa por CartService, que
 * re-clava cantidades al stock disponible; después de escribir se vuelve a
 * sincronizar `quantities` con `CartService::items()` para que el input
 * nunca muestre una cantidad que la sesión no guarda.
 */
class CartSummary extends Component
{
    /**
     * Cantidad editable por producto: `product_id => cantidad`.
     *
     * @var array<int, int>
     */
    public array $quantities = [];

    /**
     * Mensaje de feedback del panel.
     */
    public ?string $feedback = null;

    public string $feedbackType = 'success';

    /**
     * Formulario de login que se muestra a los invitados antes del checkout.
     */
    public LoginForm $form;

    /**
     * Modal de login/registro para invitados.
     */
    public bool $showLoginModal = false;

    /**
     * Modal de confirmación de compra y checkout simulado.
     */
    public bool $showCheckoutModal = false;

    public string $checkoutName = '';

    public string $checkoutEmail = '';

    /**
     * Fase del checkout: `loading` muestra "Estamos preparando todo para tu
     * compra"; `wizard` habilita los 4 pasos.
     */
    public string $checkoutPhase = 'loading';

    /**
     * Paso actual: 1 envío, 2 facturación, 3 pago, 4 revisión.
     */
    public int $checkoutStep = 1;

    // Paso 1: envío.
    public string $shippingMethod = 'domicilio';

    public string $shippingName = '';

    public string $shippingPhone = '';

    public string $shippingStreet = '';

    public string $shippingNumber = '';

    public string $shippingFloor = '';

    public string $shippingCity = '';

    public string $shippingProvince = '';

    public string $shippingPostalCode = '';

    public string $shippingNotes = '';

    public string $shippingCourier = '';

    public string $shippingPickupPoint = '';

    // Paso 2: facturación (Factura B a nombre del comprador).
    public string $billingInvoiceType = 'B';

    public string $billingTaxCondition = 'consumidor_final';

    public string $billingTaxId = '';

    // Paso 3: pago.
    public string $checkoutPayment = 'transferencia';

    // Datos de tarjeta (sólo para la simulación; no se almacenan).
    public string $cardNumber = '';

    public string $cardHolder = '';

    public string $cardExpiry = '';

    public string $cardCvv = '';

    public string $cardInstallments = '1';

    // Paso 4: revisión.
    public bool $acceptedTerms = false;

    public ?string $orderPlacedMessage = null;

    public function mount(): void
    {
        $this->syncQuantities();

        // El pago con Mercado Pago termina en otra pantalla; al volver, el
        // mensaje de la orden viaja por sesión.
        if ($message = session()->pull('order_placed_message')) {
            $this->orderPlacedMessage = $message;
        }

        // Tras loguearse, registrarse o volver de Mercado Pago se reabre el
        // checkout para no perder el hilo de la compra.
        if (session()->pull('open_checkout') && auth()->check() && ! app(CartService::class)->isEmpty()) {
            if ($state = session()->pull('mp_checkout_state')) {
                $this->restoreCheckoutState($state);
                $this->checkoutStep = 3;
                $this->checkoutPhase = 'wizard';
                $this->showCheckoutModal = true;
            } else {
                $this->prepareCheckout();
            }
        }
    }

    /**
     * Cambio de cantidad en vivo (input con debounce).
     *
     * @param  string  $property  Nombre de la propiedad actualizada (`quantities.<id>`).
     * @param  mixed  $value  Nuevo valor enviado por el cliente.
     */
    public function updated(string $property, $value = null): void
    {
        if (str_starts_with($property, 'quantities.')) {
            $productId = (int) substr($property, strlen('quantities.'));

            $this->setQuantity($productId, (int) ($this->quantities[$productId] ?? 0));
        }
    }

    /**
     * Cambia la cantidad de una línea; el servicio interpreta `<= 0` como quitar.
     */
    public function setQuantity(int $productId, int $quantity): void
    {
        app(CartService::class)->update($this->resolvable($productId), $quantity);

        $this->notify('Carrito actualizado.', 'success');
    }

    /**
     * Quita un producto del carrito.
     */
    public function removeProduct(int $productId): void
    {
        app(CartService::class)->remove($this->resolvable($productId));

        $this->notify('Producto eliminado del carrito.', 'success');
    }

    /**
     * Producto resuelto por id; si ya no existe en la base, se construye un
     * modelo mínimo con `isAvailable()` en falso para que CartService purgue
     * la línea de la sesión (ADR-0004).
     */
    private function resolvable(int $productId): Product
    {
        return Product::query()->find($productId) ?? new Product(['id' => $productId]);
    }

    /**
     * Vacía el carrito entero.
     */
    public function clearCart(): void
    {
        app(CartService::class)->clear();

        $this->quantities = [];

        $this->notify('Carrito vaciado.', 'success');
    }

    /**
     * Abre el checkout. Si el visitante no está autenticado, primero se le
     * pide que ingrese o se registre: sólo un cliente registrado puede comprar.
     */
    public function openCheckout(): void
    {
        if (app(CartService::class)->isEmpty()) {
            $this->notify('El carrito está vacío.', 'warning');

            return;
        }

        if (! auth()->check()) {
            $this->form->reset();
            $this->resetValidation();
            $this->showCheckoutModal = false;
            $this->showLoginModal = true;

            return;
        }

        $this->prepareCheckout();
    }

    /**
     * Autentica al cliente desde el modal del carrito y lo devuelve al carrito
     * con el checkout abierto.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->form->reset();
        $this->resetValidation();
        $this->showLoginModal = false;

        session()->flash('open_checkout', true);

        $this->redirect(route('carrito.index'), navigate: false);
    }

    /**
     * Cierra el modal de login sin autenticar.
     */
    public function closeLoginModal(): void
    {
        $this->showLoginModal = false;

        $this->form->reset();
        $this->resetValidation();
    }

    /**
     * Carga los datos del cliente autenticado y abre el modal de checkout.
     */
    private function prepareCheckout(): void
    {
        $user = auth()->user();

        $this->checkoutName = $user?->name ?? '';
        $this->checkoutEmail = $user?->email ?? '';
        $this->shippingName = $user?->name ?? '';

        $this->checkoutPhase = 'loading';
        $this->checkoutStep = 1;
        $this->checkoutPayment = 'transferencia';
        $this->billingInvoiceType = 'B';
        $this->billingTaxCondition = 'consumidor_final';
        $this->shippingMethod = 'domicilio';
        $this->shippingCourier = '';
        $this->shippingPickupPoint = '';
        $this->acceptedTerms = false;
        $this->resetCardData();

        $this->resetValidation();

        $this->showCheckoutModal = true;

        session()->forget('mp_checkout_state');
    }

    /**
     * Pasa de la pantalla de preparación al asistente de 4 pasos.
     */
    public function startCheckout(): void
    {
        if (! $this->showCheckoutModal || app(CartService::class)->isEmpty()) {
            return;
        }

        $this->checkoutPhase = 'wizard';
    }

    /**
     * Cierra el modal de checkout.
     */
    public function closeCheckout(): void
    {
        $this->showCheckoutModal = false;
        $this->resetValidation();
    }

    /**
     * Avanza un paso validando sólo los datos de ese paso.
     */
    public function nextStep(): void
    {
        $this->normalizeCardData();

        $this->validate($this->stepRules($this->checkoutStep), $this->checkoutMessages());

        if ($this->checkoutStep === 3 && $this->checkoutPayment === 'efectivo' && $this->shippingMethod !== 'retiro') {
            $this->addError('checkoutPayment', 'El pago en efectivo sólo está disponible para retiro en punto de entrega.');

            return;
        }

        if ($this->checkoutStep === 3 && $this->checkoutPayment === 'mercadopago') {
            $this->redirectToMercadoPago();

            return;
        }

        if ($this->checkoutStep < 4) {
            $this->checkoutStep++;
        }
    }

    /**
     * Retrocede un paso sin perder lo que el cliente ya completó.
     */
    public function previousStep(): void
    {
        $this->resetValidation();

        $this->checkoutStep = max(1, $this->checkoutStep - 1);
    }

    /**
     * Permite volver a editar un paso desde la revisión final.
     */
    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 4) {
            return;
        }

        $this->resetValidation();

        $this->checkoutStep = $step;
    }

    /**
     * Reglas de validación del paso actual.
     *
     * @return array<string, mixed>
     */
    private function stepRules(int $step): array
    {
        return match ($step) {
            1 => $this->shippingRules(),
            2 => $this->billingRules(),
            3 => $this->paymentRules(),
            default => ['acceptedTerms' => ['accepted']],
        };
    }

    /**
     * Reglas de todos los pasos, para la validación final del servidor.
     *
     * @return array<string, mixed>
     */
    private function allCheckoutRules(): array
    {
        return array_merge(
            $this->shippingRules(),
            $this->billingRules(),
            $this->paymentRules(),
            ['acceptedTerms' => ['accepted']],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function shippingRules(): array
    {
        $rules = [
            'shippingMethod' => ['required', Rule::in(['domicilio', 'retiro'])],
            'shippingName' => ['required', 'string', 'min:3', 'max:100'],
            'shippingPhone' => ['required', 'string', 'min:6', 'max:30'],
        ];

        if ($this->shippingMethod === 'retiro') {
            $rules['shippingPickupPoint'] = ['required', Rule::in(array_keys($this->pickupPoints()))];

            return $rules;
        }

        return array_merge($rules, [
            'shippingStreet' => ['required', 'string', 'max:120'],
            'shippingNumber' => ['required', 'string', 'max:20'],
            'shippingFloor' => ['nullable', 'string', 'max:40'],
            'shippingCity' => ['required', 'string', 'max:80'],
            'shippingProvince' => ['required', 'string', 'max:80'],
            'shippingPostalCode' => ['required', 'string', 'max:12'],
            'shippingNotes' => ['nullable', 'string', 'max:300'],
            'shippingCourier' => ['required', Rule::in(array_keys($this->couriers()))],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function billingRules(): array
    {
        return [
            'checkoutName' => ['required', 'string', 'min:3', 'max:100'],
            'checkoutEmail' => ['required', 'email', 'max:150'],
            'billingInvoiceType' => ['required', Rule::in(['B'])],
            'billingTaxCondition' => ['required', Rule::in(['consumidor_final', 'monotributo', 'responsable_inscripto', 'exento'])],
            'billingTaxId' => ['required', 'string', 'min:7', 'max:13', 'regex:/^[0-9.\- ]+$/'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentRules(): array
    {
        $rules = [
            'checkoutPayment' => ['required', Rule::in(array_keys($this->paymentMethods()))],
        ];

        if (! $this->isCardPayment()) {
            return $rules;
        }

        $rules['cardNumber'] = [
            'required',
            'string',
            function ($attribute, $value, $fail): void {
                $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

                if (strlen($digits) < 13 || strlen($digits) > 19) {
                    $fail('Ingresá un número de tarjeta válido (entre 13 y 19 dígitos).');
                }
            },
        ];

        $rules['cardHolder'] = ['required', 'string', 'min:3', 'max:100'];

        $rules['cardExpiry'] = [
            'required',
            'string',
            'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            function ($attribute, $value, $fail): void {
                if (! preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', (string) $value, $matches)) {
                    return;
                }

                $expiry = now()
                    ->setDate(2000 + (int) $matches[2], (int) $matches[1], 1)
                    ->endOfMonth();

                if ($expiry->isPast()) {
                    $fail('La tarjeta está vencida.');
                }
            },
        ];

        $rules['cardCvv'] = ['required', 'string', 'regex:/^\d{3,4}$/'];

        if ($this->checkoutPayment === 'tarjeta_credito') {
            $rules['cardInstallments'] = ['required', 'in:1,3,6'];
        }

        return $rules;
    }

    /**
     * Mensajes de validación en español para todo el checkout.
     *
     * @return array<string, string>
     */
    private function checkoutMessages(): array
    {
        return [
            'shippingMethod.required' => 'Elegí cómo querés recibir tu compra.',
            'shippingName.required' => 'Ingresá el nombre de quien recibe.',
            'shippingName.min' => 'El nombre debe tener al menos 3 caracteres.',
            'shippingPhone.required' => 'Ingresá un teléfono de contacto.',
            'shippingPhone.min' => 'El teléfono es demasiado corto.',
            'shippingStreet.required' => 'Ingresá la calle de la dirección.',
            'shippingNumber.required' => 'Ingresá el número de la dirección.',
            'shippingCity.required' => 'Ingresá la ciudad.',
            'shippingProvince.required' => 'Ingresá la provincia.',
            'shippingPostalCode.required' => 'Ingresá el código postal.',
            'shippingCourier.required' => 'Seleccioná un correo para el envío.',
            'shippingCourier.in' => 'El correo seleccionado no es válido.',
            'shippingPickupPoint.required' => 'Seleccioná un punto de entrega.',
            'shippingPickupPoint.in' => 'El punto de entrega seleccionado no es válido.',
            'checkoutName.required' => 'Ingresá el nombre y apellido del comprador.',
            'checkoutName.min' => 'El nombre debe tener al menos 3 caracteres.',
            'checkoutEmail.required' => 'Ingresá el correo electrónico.',
            'checkoutEmail.email' => 'El formato del correo no es válido.',
            'billingTaxId.required' => 'Ingresá tu DNI o CUIT.',
            'billingTaxId.min' => 'El DNI o CUIT es demasiado corto.',
            'billingTaxId.max' => 'El DNI o CUIT es demasiado largo.',
            'billingTaxId.regex' => 'El DNI o CUIT sólo puede contener números, puntos y guiones.',
            'billingTaxCondition.required' => 'Seleccioná la condición frente al IVA.',
            'billingTaxCondition.in' => 'La condición frente al IVA no es válida.',
            'checkoutPayment.required' => 'Seleccioná un medio de pago.',
            'checkoutPayment.in' => 'El medio de pago seleccionado no es válido.',
            'cardNumber.required' => 'Ingresá el número de la tarjeta.',
            'cardHolder.required' => 'Ingresá el nombre como figura en la tarjeta.',
            'cardHolder.min' => 'El nombre del titular es demasiado corto.',
            'cardExpiry.required' => 'Ingresá el vencimiento de la tarjeta.',
            'cardExpiry.regex' => 'Usá el formato MM/AA para el vencimiento.',
            'cardCvv.required' => 'Ingresá el código de seguridad (CVV).',
            'cardCvv.regex' => 'El CVV debe tener 3 o 4 dígitos.',
            'cardInstallments.in' => 'Elegí una cantidad de cuotas válida.',
            'acceptedTerms.accepted' => 'Tenés que aceptar los términos y condiciones para comprar.',
        ];
    }

    /**
     * Correos disponibles con precio en céntimos.
     *
     * @return array<string, array{name: string, price: int, eta: string}>
     */
    public function couriers(): array
    {
        return config('store.shipping.couriers', []);
    }

    /**
     * Puntos de entrega disponibles con precio en céntimos.
     *
     * @return array<string, array{name: string, address: string, price: int, eta: string}>
     */
    public function pickupPoints(): array
    {
        return config('store.shipping.pickup_points', []);
    }

    /**
     * Medios de pago disponibles.
     *
     * @return array<string, array{name: string, description: string, discount: float}>
     */
    public function paymentMethods(): array
    {
        return config('store.payment_methods', []);
    }

    /**
     * ¿El medio de pago elegido es tarjeta?
     */
    private function isCardPayment(): bool
    {
        return in_array($this->checkoutPayment, ['tarjeta_credito', 'tarjeta_debito'], true);
    }

    /**
     * Normaliza los datos de la tarjeta antes de validar.
     */
    private function normalizeCardData(): void
    {
        if (! $this->isCardPayment()) {
            return;
        }

        $this->cardNumber = preg_replace('/\D+/', '', $this->cardNumber) ?? '';
        $this->cardHolder = trim($this->cardHolder);
        $this->cardCvv = preg_replace('/\D+/', '', $this->cardCvv) ?? '';

        $expiry = preg_replace('/\D+/', '', $this->cardExpiry) ?? '';

        if (strlen($expiry) === 4) {
            $this->cardExpiry = substr($expiry, 0, 2).'/'.substr($expiry, 2, 2);
        }
    }

    /**
     * Limpia los datos sensibles de la tarjeta.
     */
    private function resetCardData(): void
    {
        $this->cardNumber = '';
        $this->cardHolder = '';
        $this->cardExpiry = '';
        $this->cardCvv = '';
        $this->cardInstallments = '1';
    }

    /**
     * Guarda el estado del asistente para poder retomarlo al volver de
     * Mercado Pago, y redirige a la pantalla de pago.
     */
    private function redirectToMercadoPago(): void
    {
        session()->put('mp_checkout_state', $this->checkoutState());

        $data = $this->reservationData();

        session()->put('mp_checkout', array_merge($data, [
            'checkout_name' => $data['customer_name'],
            'checkout_email' => $data['customer_email'],
            'amount' => $data['total'],
        ]));

        $this->redirect(route('checkout.mercadopago'), navigate: false);
    }

    /**
     * Estado serializable del asistente (sin datos de tarjeta).
     *
     * @return array<string, string>
     */
    private function checkoutState(): array
    {
        return [
            'shippingMethod' => $this->shippingMethod,
            'shippingName' => $this->shippingName,
            'shippingPhone' => $this->shippingPhone,
            'shippingStreet' => $this->shippingStreet,
            'shippingNumber' => $this->shippingNumber,
            'shippingFloor' => $this->shippingFloor,
            'shippingCity' => $this->shippingCity,
            'shippingProvince' => $this->shippingProvince,
            'shippingPostalCode' => $this->shippingPostalCode,
            'shippingNotes' => $this->shippingNotes,
            'shippingCourier' => $this->shippingCourier,
            'shippingPickupPoint' => $this->shippingPickupPoint,
            'checkoutName' => $this->checkoutName,
            'checkoutEmail' => $this->checkoutEmail,
            'billingInvoiceType' => $this->billingInvoiceType,
            'billingTaxCondition' => $this->billingTaxCondition,
            'billingTaxId' => $this->billingTaxId,
            'checkoutPayment' => $this->checkoutPayment,
        ];
    }

    /**
     * Restaura el estado guardado al volver de Mercado Pago.
     *
     * @param  array<string, mixed>  $state
     */
    private function restoreCheckoutState(array $state): void
    {
        foreach (array_keys($this->checkoutState()) as $property) {
            if (array_key_exists($property, $state)) {
                $this->{$property} = (string) $state[$property];
            }
        }
    }

    /**
     * Costo de envío según el método elegido (céntimos).
     */
    public function shippingCost(): int
    {
        if ($this->shippingMethod === 'retiro') {
            return (int) ($this->pickupPoints()[$this->shippingPickupPoint]['price'] ?? 0);
        }

        return (int) ($this->couriers()[$this->shippingCourier]['price'] ?? 0);
    }

    /**
     * Descuento del medio de pago sobre el subtotal de productos (céntimos).
     */
    public function discount(): int
    {
        $rate = (float) ($this->paymentMethods()[$this->checkoutPayment]['discount'] ?? 0);

        return (int) round(app(CartService::class)->subtotal() * $rate);
    }

    /**
     * Total final: productos + IVA + envío - descuento.
     */
    public function grandTotal(): int
    {
        return max(0, app(CartService::class)->total() + $this->shippingCost() - $this->discount());
    }

    /**
     * Texto del envío para la revisión y el mensaje de confirmación.
     */
    public function shippingSummary(): string
    {
        if ($this->shippingMethod === 'retiro') {
            $point = $this->pickupPoints()[$this->shippingPickupPoint] ?? null;

            return $point
                ? "Retiro en {$point['name']} ({$point['address']})"
                : 'Retiro en punto de entrega';
        }

        $courier = $this->couriers()[$this->shippingCourier] ?? null;
        $address = trim("{$this->shippingStreet} {$this->shippingNumber}");
        $address .= $this->shippingFloor !== '' ? " ({$this->shippingFloor})" : '';
        $place = trim("{$this->shippingCity}, {$this->shippingProvince} (CP {$this->shippingPostalCode})");

        $base = $courier
            ? "Envío a domicilio con {$courier['name']}"
            : 'Envío a domicilio';

        return "{$base} a {$address}, {$place}";
    }

    /**
     * Nombre del medio de pago elegido.
     */
    public function paymentLabel(): string
    {
        return $this->paymentMethods()[$this->checkoutPayment]['name'] ?? 'Medio de pago';
    }

    /**
     * Etiqueta legible de la condición frente al IVA.
     */
    public function billingTaxConditionLabel(): string
    {
        return match ($this->billingTaxCondition) {
            'monotributo' => 'Monotributo',
            'responsable_inscripto' => 'Responsable Inscripto',
            'exento' => 'Exento',
            default => 'Consumidor Final',
        };
    }

    /**
     * Resumen que se guarda como reserva al confirmar la compra.
     *
     * @return array<string, mixed>
     */
    private function reservationData(): array
    {
        $cart = app(CartService::class);

        return [
            'payment_method' => $this->checkoutPayment,
            'payment_label' => $this->paymentLabel(),
            'shipping_method' => $this->shippingMethod,
            'shipping_summary' => $this->shippingSummary(),
            'subtotal' => $cart->subtotal(),
            'taxes' => $cart->taxes(),
            'shipping_cost' => $this->shippingCost(),
            'discount' => $this->discount(),
            'total' => $this->grandTotal(),
            'customer_name' => $this->checkoutName,
            'customer_email' => $this->checkoutEmail,
            'billing_invoice_type' => $this->billingInvoiceType,
            'billing_tax_condition' => $this->billingTaxCondition,
            'billing_tax_id' => $this->billingTaxId,
        ];
    }

    /**
     * Procesa y confirma la orden simulada.
     */
    public function processCheckout(): void
    {
        // Defensa en el servidor: aunque el modal se fuerce desde el navegador,
        // un invitado nunca puede confirmar una compra.
        if (! auth()->check()) {
            $this->showCheckoutModal = false;
            $this->showLoginModal = true;

            $this->notify('Iniciá sesión o creá una cuenta para finalizar la compra.', 'warning');

            return;
        }

        $cart = app(CartService::class);

        if ($cart->isEmpty()) {
            $this->showCheckoutModal = false;
            $this->notify('Tu carrito está vacío.', 'error');

            return;
        }

        $this->normalizeCardData();

        $this->validate($this->allCheckoutRules(), $this->checkoutMessages());

        if ($this->checkoutPayment === 'efectivo' && $this->shippingMethod !== 'retiro') {
            $this->checkoutStep = 3;
            $this->addError('checkoutPayment', 'El pago en efectivo sólo está disponible para retiro en punto de entrega.');

            return;
        }

        if ($this->checkoutPayment === 'mercadopago') {
            $this->redirectToMercadoPago();

            return;
        }

        // Guardamos la compra como reserva antes de vaciar el carrito.
        $reservation = app(ReservationService::class)->create(
            auth()->user(),
            $this->reservationData(),
            $cart->lines(),
        );

        $orderNumber = $reservation->number;
        $paymentLabel = $this->paymentLabel();
        $shippingSummary = $this->shippingSummary();
        $total = Number::currency(
            $this->grandTotal() / 100,
            config('store.currency'),
            config('store.locale'),
        );

        // Vaciamos el carrito tras procesar con éxito
        $cart->clear();
        $this->quantities = [];
        $this->showCheckoutModal = false;
        $this->checkoutPhase = 'loading';
        $this->checkoutStep = 1;
        $this->acceptedTerms = false;
        $this->resetCardData();
        session()->forget('mp_checkout_state');

        $this->orderPlacedMessage = "¡Felicitaciones {$this->checkoutName}! Tu orden {$orderNumber} fue registrada con éxito. Pagás con {$paymentLabel}. {$shippingSummary}. Total: {$total}. Te enviamos los detalles a {$this->checkoutEmail}.";
        $this->dispatch('carrito-actualizado');
    }

    /**
     * Notifica al usuario y sincroniza el resto de la página (contador header).
     */
    private function notify(string $message, string $type): void
    {
        $this->feedback = $message;
        $this->feedbackType = $type;

        $this->syncQuantities();

        $this->dispatch('carrito-actualizado');
    }

    /**
     * Relee las cantidades vigentes de la sesión (fuente de verdad: CartService).
     */
    private function syncQuantities(): void
    {
        $this->quantities = app(CartService::class)->items();
    }

    public function render(): View
    {
        $cart = app(CartService::class);

        return view('livewire.cart.cart-summary', [
            'lines' => $cart->lines(),
            'totals' => $cart->totals(),
            'couriers' => $this->couriers(),
            'pickupPoints' => $this->pickupPoints(),
            'paymentMethods' => $this->paymentMethods(),
            'shippingCost' => $this->shippingCost(),
            'discount' => $this->discount(),
            'grandTotal' => $this->grandTotal(),
            'shippingSummary' => $this->shippingSummary(),
            'paymentLabel' => $this->paymentLabel(),
            'billingTaxConditionLabel' => $this->billingTaxConditionLabel(),
        ]);
    }
}
