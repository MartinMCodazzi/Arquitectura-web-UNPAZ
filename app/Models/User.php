<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Domicilios guardados por el cliente.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Cuentas bancarias o billeteras del cliente.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(CustomerAccount::class);
    }

    /**
     * Compras reservadas por el cliente.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Primer nombre del usuario (sin apellido) para mostrar en la interfaz.
     */
    protected function firstName(): Attribute
    {
        return Attribute::get(function (): string {
            $name = trim($this->name ?? '');

            return $name !== '' ? explode(' ', $name)[0] : '';
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
