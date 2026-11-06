<?php

namespace App\Services;

use App\DTO\CartLine;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Carrito de compras persistido en la sesión (ver ADR-0002).
 *
 * La sesión guarda un array plano `{ product_id: cantidad }`: sólo
 * identificadores y cantidades enteras, nunca objetos. Este servicio es la
 * única frontera que escribe en la clave de sesión; los controladores nunca
 * tocan la sesión del carrito directamente.
 */
class CartService
{
    /**
     * Clave de sesión donde vive el carrito.
     */
    public const SESSION_KEY = 'carrito';

    /**
     * Líneas del carrito: `product_id => cantidad`.
     *
     * @return array<int, int>
     */
    public function items(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        return array_map('intval', $items);
    }

    /**
     * Agrega un producto al carrito.
     *
     * La cantidad se limita al stock disponible: si el carrito ya tenía
     * unidades, la suma no puede superar el stock.
     *
     * @return int Cantidad efectivamente agregada (0 si no se agregó nada).
     */
    public function add(Product $product, int $quantity = 1): int
    {
        if ($quantity < 1 || ! $product->isAvailable()) {
            return 0;
        }

        $items = $this->items();
        $current = $items[$product->id] ?? 0;
        $updated = min($current + $quantity, $product->stock);

        if ($updated === $current) {
            return 0;
        }

        $items[$product->id] = $updated;

        $this->write($items);

        return $updated - $current;
    }

    /**
     * Cambia la cantidad de un producto del carrito.
     *
     * Una cantidad de 0 (o menor) quita el producto del carrito.
     */
    public function update(Product $product, int $quantity): bool
    {
        if ($quantity < 1) {
            return $this->remove($product);
        }

        if (! $product->isAvailable()) {
            return $this->remove($product);
        }

        $items = $this->items();

        if (! array_key_exists($product->id, $items)) {
            return false;
        }

        $items[$product->id] = min($quantity, $product->stock);

        $this->write($items);

        return true;
    }

    /**
     * Quita un producto del carrito.
     */
    public function remove(Product $product): bool
    {
        $items = $this->items();

        if (! array_key_exists($product->id, $items)) {
            return false;
        }

        unset($items[$product->id]);

        $this->write($items);

        return true;
    }

    /**
     * Vacía el carrito.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * ¿El carrito está vacío?
     */
    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    /**
     * Cantidad total de unidades en el carrito.
     */
    public function count(): int
    {
        return array_sum($this->items());
    }

    /**
     * Líneas del carrito con los productos resueltos.
     *
     * Los productos que fueron borrados, desactivados o quedaron sin stock
     * se descartan y se limpian de la sesión (ADR-0002).
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        if ($items === []) {
            return collect();
        }

        $products = Product::query()
            ->with('category')
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $lines = collect();
        $cleaned = [];

        foreach ($items as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product instanceof Product || ! $product->isAvailable()) {
                continue;
            }

            $quantity = min($quantity, $product->stock);
            $cleaned[$productId] = $quantity;

            $lines->push(new CartLine(
                product: $product,
                quantity: $quantity,
                subtotal: $product->price * $quantity,
            ));
        }

        if ($cleaned !== $items) {
            $this->write($cleaned);
        }

        return $lines;
    }

    /**
     * Subtotal del carrito en céntimos.
     */
    public function subtotal(): int
    {
        return (int) $this->lines()->sum(fn (CartLine $line): int => $line->subtotal);
    }

    /**
     * Impuesto del carrito en céntimos.
     */
    public function taxes(): int
    {
        return (int) round($this->subtotal() * (float) config('store.tax_rate'));
    }

    /**
     * Total del carrito en céntimos.
     */
    public function total(): int
    {
        return $this->subtotal() + $this->taxes();
    }

    /**
     * Totales del carrito en céntimos.
     *
     * @return array{subtotal: int, taxes: int, total: int}
     */
    public function totals(): array
    {
        $subtotal = $this->subtotal();
        $taxes = (int) round($subtotal * (float) config('store.tax_rate'));

        return [
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $subtotal + $taxes,
        ];
    }

    /**
     * Escribe las líneas del carrito en la sesión.
     *
     * @param  array<int, int>  $items
     */
    private function write(array $items): void
    {
        if ($items === []) {
            $this->clear();

            return;
        }

        Session::put(self::SESSION_KEY, $items);
    }
}
