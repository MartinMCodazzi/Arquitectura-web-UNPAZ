<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cuenta bancaria o billetera virtual del cliente.
 */
#[Fillable([
    'label',
    'type',
    'bank_name',
    'holder_name',
    'holder_dni',
    'cbu_alias',
    'is_default',
])]
class CustomerAccount extends Model
{
    /**
     * Cliente dueño de la cuenta.
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
     * Nombre legible del tipo de cuenta.
     */
    public function typeLabel(): string
    {
        return $this->type === 'billetera' ? 'Billetera virtual' : 'Cuenta bancaria';
    }
}
