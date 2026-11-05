<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Detalle de una reserva. Guarda una copia del nombre y el precio del
 * producto para que el historial no cambie si el catálogo se modifica.
 */
#[Fillable([
    'product_id',
    'product_name',
    'unit_price',
    'quantity',
    'subtotal',
])]
class ReservationItem extends Model
{
    /**
     * Reserva a la que pertenece el detalle.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Producto original (puede haber sido eliminado).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }
}
