<?php

namespace App\Services;

use App\DTO\CartLine;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Persiste las compras confirmadas como reservas.
 *
 * El carrito vive en la sesión (ADR-0002) y se vacía al confirmar; antes de
 * eso tomamos una foto de sus líneas para que el historial del cliente no
 * dependa de cambios posteriores en el catálogo.
 */
class ReservationService
{
    /**
     * Crea la reserva y su detalle de productos.
     *
     * @param  array<string, mixed>  $data  Resumen del checkout.
     * @param  Collection<int, CartLine>  $lines  Líneas del carrito.
     */
    public function create(User $user, array $data, Collection $lines): Reservation
    {
        $reservation = $user->reservations()->create([
            'number' => $this->generateNumber(),
            'status' => 'reservada',
            'payment_method' => $data['payment_method'],
            'payment_label' => $data['payment_label'],
            'shipping_method' => $data['shipping_method'],
            'shipping_summary' => $data['shipping_summary'],
            'subtotal' => $data['subtotal'],
            'taxes' => $data['taxes'],
            'shipping_cost' => $data['shipping_cost'],
            'discount' => $data['discount'],
            'total' => $data['total'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'billing_invoice_type' => $data['billing_invoice_type'] ?? null,
            'billing_tax_condition' => $data['billing_tax_condition'] ?? null,
            'billing_tax_id' => $data['billing_tax_id'] ?? null,
        ]);

        foreach ($lines as $line) {
            $reservation->items()->create([
                'product_id' => $line->product->id,
                'product_name' => $line->product->name,
                'unit_price' => $line->product->price,
                'quantity' => $line->quantity,
                'subtotal' => $line->subtotal,
            ]);
        }

        return $reservation;
    }

    /**
     * Número de orden único con formato ORD-AAAAMMDD-XXXX.
     */
    private function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Reservation::query()->where('number', $number)->exists());

        return $number;
    }
}
