<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Muestra el carrito con sus líneas y los totales.
     */
    public function index(CartService $cart): View
    {
        return view('carrito.index', [
            'lines' => $cart->lines(),
            'totals' => $cart->totals(),
        ]);
    }

    /**
     * Agrega un producto al carrito.
     */
    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);

        if (! $product->isAvailable()) {
            return back()->with('error', 'El producto no está disponible.');
        }

        $added = $cart->add($product, $quantity);

        if ($added === 0) {
            return back()->with('error', 'No se pudo agregar el producto: no hay stock suficiente.');
        }

        if ($added < $quantity) {
            return back()->with('warning', "Sólo hay {$product->stock} unidades disponibles: se agregaron {$added}.");
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    /**
     * Cambia la cantidad de un producto del carrito.
     */
    public function update(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $cart->update($product, (int) $validated['quantity']);

        return back()->with('success', 'Carrito actualizado.');
    }

    /**
     * Quita un producto del carrito.
     */
    public function destroy(Product $product, CartService $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('success', 'Producto eliminado del carrito.');
    }

    /**
     * Vacía el carrito.
     */
    public function clear(CartService $cart): RedirectResponse
    {
        $cart->clear();

        return back()->with('success', 'Carrito vaciado.');
    }
}
