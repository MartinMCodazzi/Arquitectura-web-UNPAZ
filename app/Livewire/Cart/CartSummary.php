<?php

namespace App\Livewire\Cart;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
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

    public function mount(): void
    {
        $this->syncQuantities();
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
        ]);
    }
}
