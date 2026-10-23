<x-store-layout
    title="Carrito de compras"
    description="Revisá los productos de tu carrito de compras antes de finalizar la compra."
    :canonical="route('carrito.index')">

    {{-- Carrito reactivo (ADR-0017): líneas, cantidades y totales se
         actualizan sin recargar; el fallback sin JavaScript vive dentro del
         componente (endpoints HTTP de ADR-0012). --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Carrito de compras
        </h1>

        <div class="mt-8">
            <livewire:cart.cart-summary />
        </div>
    </div>
</x-store-layout>
