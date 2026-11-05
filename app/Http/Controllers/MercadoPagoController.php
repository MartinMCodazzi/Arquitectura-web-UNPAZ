<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

/**
 * Pantalla de pago simulada de Mercado Pago.
 *
 * El proyecto no tiene credenciales de Mercado Pago, así que este controlador
 * reproduce el ida y vuelta del checkout: recibe el resumen guardado por el
 * asistente, permite confirmar o cancelar, y vuelve al carrito con el
 * resultado. Cuando se configuren credenciales reales, este es el punto que
 * se reemplaza por la creación de una preferencia y la redirección a
 * `init_point`.
 */
class MercadoPagoController extends Controller
{
    /**
     * Muestra el resumen de pago.
     */
    public function show(CartService $cart): View|RedirectResponse
    {
        $checkout = session('mp_checkout');

        if (! is_array($checkout) || $cart->isEmpty()) {
            return redirect()
                ->route('carrito.index')
                ->with('warning', 'No hay un pago pendiente de Mercado Pago.');
        }

        return view('checkout.mercadopago', [
            'checkout' => $checkout,
        ]);
    }

    /**
     * Confirma el pago simulado, vacía el carrito y vuelve a la tienda.
     */
    public function confirm(Request $request, CartService $cart): RedirectResponse
    {
        $checkout = session()->pull('mp_checkout');

        if (! is_array($checkout)) {
            return redirect()
                ->route('carrito.index')
                ->with('open_checkout', true)
                ->with('warning', 'No encontramos el pago pendiente. Intentá nuevamente.');
        }

        session()->forget('mp_checkout_state');

        if ($cart->isEmpty()) {
            return redirect()
                ->route('carrito.index')
                ->with('error', 'Tu carrito está vacío. No se pudo procesar el pago.');
        }

        $data = [
            'payment_method' => $checkout['payment_method'] ?? 'mercadopago',
            'payment_label' => $checkout['payment_label'] ?? 'Mercado Pago',
            'shipping_method' => $checkout['shipping_method'] ?? 'domicilio',
            'shipping_summary' => $checkout['shipping_summary'] ?? '',
            'subtotal' => (int) ($checkout['subtotal'] ?? 0),
            'taxes' => (int) ($checkout['taxes'] ?? 0),
            'shipping_cost' => (int) ($checkout['shipping_cost'] ?? 0),
            'discount' => (int) ($checkout['discount'] ?? 0),
            'total' => (int) ($checkout['amount'] ?? 0),
            'customer_name' => $checkout['customer_name'] ?? $checkout['checkout_name'] ?? 'Cliente',
            'customer_email' => $checkout['customer_email'] ?? $checkout['checkout_email'] ?? '',
            'billing_invoice_type' => $checkout['billing_invoice_type'] ?? null,
            'billing_tax_condition' => $checkout['billing_tax_condition'] ?? null,
            'billing_tax_id' => $checkout['billing_tax_id'] ?? null,
        ];

        $reservation = app(ReservationService::class)->create($request->user(), $data, $cart->lines());

        $orderNumber = $reservation->number;

        $total = Number::currency(
            (int) ($checkout['amount'] ?? 0) / 100,
            config('store.currency'),
            config('store.locale'),
        );

        $cart->clear();

        $message = "¡Felicitaciones {$data['customer_name']}! Tu orden {$orderNumber} fue registrada con éxito. Pagás con Mercado Pago. {$data['shipping_summary']}. Total: {$total}. Te enviamos los detalles a {$data['customer_email']}.";

        return redirect()
            ->route('carrito.index')
            ->with('success', 'Pago aprobado con Mercado Pago.')
            ->with('order_placed_message', $message);
    }

    /**
     * Cancela el pago y devuelve al cliente al paso de pago del asistente.
     */
    public function cancel(): RedirectResponse
    {
        session()->forget('mp_checkout');

        return redirect()
            ->route('carrito.index')
            ->with('open_checkout', true)
            ->with('warning', 'Cancelaste el pago con Mercado Pago. Podés intentarlo de nuevo.');
    }
}
