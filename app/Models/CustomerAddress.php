<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domicilio de entrega guardado por un cliente.
 */
#[Fillable([
    'label',
    'recipient_name',
    'phone',
    'street',
    'number',
    'floor',
    'city',
    'province',
    'postal_code',
    'notes',
    'is_default',
])]
class CustomerAddress extends Model
{
    /**
     * Cliente dueño del domicilio.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Dirección completa en una sola línea.
     */
    public function fullAddress(): string
    {
        $parts = array_filter([
            trim($this->street.' '.$this->number),
            $this->floor,
            $this->city,
            $this->province,
            $this->postal_code ? 'CP '.$this->postal_code : null,
        ]);

        return implode(', ', $parts);
    }
}
