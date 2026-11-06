<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Contador de carrito del header (ADR-0009, ADR-0010).
 *
 * Refleja cuántas unidades hay en la sesión mediante CartService (única
 * frontera de escritura del carrito, ADR-0002). Se refresca al recibir el
 * evento `carrito-actualizado` que emiten las acciones del carrito.
 */
class CartCounter extends Component
{
    /**
     * Cantidad total de unidades en el carrito.
     */
    public int $count = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    /**
     * Recalcula el contador desde el servicio (sin toques directos a sesión).
     */
    #[On('carrito-actualizado')]
    public function refresh(): void
    {
        $this->count = app(CartService::class)->count();
    }

    public function render(): View
    {
        return view('livewire.cart.cart-counter');
    }
}
