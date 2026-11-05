<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Compra reservada por un cliente.
 *
 * El checkout actual es simulado, así que cada compra confirmada queda
 * guardada con estado `reservada` y su detalle de productos.
 */
#[Fillable([
    'number',
    'status',
    'payment_method',
    'payment_label',
    'shipping_method',
    'shipping_summary',
    'subtotal',
    'taxes',
    'shipping_cost',
    'discount',
    'total',
    'customer_name',
    'customer_email',
    'billing_invoice_type',
    'billing_tax_condition',
    'billing_tax_id',
])]
class Reservation extends Model
{
    /**
     * Cliente que hizo la reserva.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Productos reservados.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'taxes' => 'integer',
            'shipping_cost' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * Nombre legible del estado de la reserva.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'reservada' => 'Reservada',
            'confirmada' => 'Confirmada',
            'cancelada' => 'Cancelada',
            default => ucfirst($this->status),
        };
    }
}
