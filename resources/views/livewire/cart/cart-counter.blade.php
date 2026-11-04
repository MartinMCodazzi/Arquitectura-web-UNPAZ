<a href="{{ route('carrito.index') }}"
   class="relative flex items-center gap-2.5 px-3.5 py-2 rounded-xl border border-gray-800 bg-gray-900/80 hover:border-orange-500/60 hover:bg-gray-800 text-sm font-bold text-gray-200 transition group shadow-sm"
   aria-label="Carrito de compras con {{ $count }} {{ $count === 1 ? 'unidad' : 'unidades' }}">
    <div class="relative">
        <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>

        @if ($count > 0)
            <span wire:key="cart-counter-badges" class="absolute -top-2 -right-2.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-orange-500 px-1 text-[11px] font-black text-white shadow-md animate-pulse">
                {{ $count }}
            </span>
        @endif
    </div>

    <span class="hidden sm:inline group-hover:text-white transition">Carrito</span>
</a>
