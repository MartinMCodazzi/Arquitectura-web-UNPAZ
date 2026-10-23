<a href="{{ route('carrito.index') }}"
   class="relative flex items-center gap-2 text-sm font-medium {{ request()->routeIs('carrito.*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
   aria-label="Carrito de compras con {{ $count }} {{ $count === 1 ? 'unidad' : 'unidades' }}">
    <span>Carrito</span>

    @if ($count > 0)
        <span wire:key="cart-counter-badges" class="inline-flex min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-semibold text-white">
            {{ $count }}
        </span>
    @endif
</a>
