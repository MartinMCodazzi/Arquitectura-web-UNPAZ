<?php

namespace App\Livewire\Cart;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Botón "Agregar al carrito" (ADR-0017).
 *
 * Lo usan la card del catálogo (cantidad fija = 1) y la ficha de producto
 * (con selector de cantidad). La única frontera de escritura es
 * CartService::add(), y el resultado se comunica al resto de la página con el
 * evento `carrito-actualizado` (el contador del header se refresca al instante).
 *
 * Las propiedades que identifican el producto están `Locked`: el cliente envía
 * ids/cantidades, pero nada puede mutarlas desde el navegador.
 */
class AddToCartButton extends Component
{
    #[Locked]
    public int $productId;

    #[Locked]
    public int $stock = 0;

    /**
     * ¿La ficha muestra un selector de cantidad? En la card se agrega de a 1.
     */
    public bool $withQuantity = false;

    /**
     * Cantidad elegida por el usuario (sólo cuando hay selector). Sin tipo
     * declarado a propósito: el input puede estar vacío mientras se tipea.
     */
    public $quantity = 1;

    /**
     * Mensaje de feedback inline (post-agregado).
     */
    public ?string $feedback = null;

    public string $feedbackType = 'success';

    public function mount(int $productId, ?int $stock = null, bool $withQuantity = false): void
    {
        $this->productId = $productId;
        $this->stock = $stock ?? 0;
        $this->withQuantity = $withQuantity;
    }

    /**
     * Agrega el producto y emite feedback; no redirige ni recarga.
     */
    public function addToCart(): void
    {
        $product = Product::query()->find($this->productId);

        if (! $product) {
            $this->feedback('El producto ya no está disponible.', 'error');

            return;
        }

        if (! $product->isAvailable()) {
            $this->feedback('El producto no está disponible.', 'error');

            return;
        }

        $requestedQuantity = $this->withQuantity ? max((int) $this->quantity, 1) : 1;
        $added = app(CartService::class)->add($product, $requestedQuantity);

        if ($added === 0) {
            $this->feedback('No hay stock suficiente para agregar el producto.', 'error');

            return;
        }

        if ($added < $requestedQuantity) {
            $this->feedback("Sólo hay {$product->stock} unidades disponibles: se agregaron {$added}.", 'warning');

            return;
        }

        $this->feedback('Producto agregado al carrito.', 'success');
    }

    /**
     * Agrega el producto al carrito e inmediatamente redirige a /carrito para comprar.
     */
    public function buyNow(): void
    {
        $product = Product::query()->find($this->productId);

        if (! $product || ! $product->isAvailable()) {
            $this->feedback('El producto no está disponible.', 'error');

            return;
        }

        $requestedQuantity = $this->withQuantity ? max((int) $this->quantity, 1) : 1;
        $added = app(CartService::class)->add($product, $requestedQuantity);

        if ($added === 0) {
            $this->feedback('No hay stock suficiente para comprar el producto.', 'error');

            return;
        }

        $this->redirect(route('carrito.index'), navigate: false);
    }

    public function updatedQuantity(): void
    {
        $quantity = max((int) $this->quantity, 1);

        if ($this->stock > 0 && $quantity > $this->stock) {
            $quantity = $this->stock;
        }

        $this->quantity = $quantity;
    }

    /**
     * Guarda el mensaje de feedback y notifica a los demás componentes.
     */
    private function feedback(string $message, string $type): void
    {
        $this->feedback = $message;
        $this->feedbackType = $type;

        $this->dispatch('carrito-actualizado');
    }

    public function render(): View
    {
        return view('livewire.cart.add-to-cart-button');
    }
}
