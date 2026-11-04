<div class="mt-3">
    @if ($feedback)
        <p class="{{ $feedbackType === 'success' ? 'text-sm font-medium text-green-600 dark:text-green-400' : ($feedbackType === 'warning' ? 'text-sm font-medium text-yellow-600 dark:text-yellow-400' : 'text-sm font-medium text-red-600 dark:text-red-400') }}"
           wire:key="feedback-{{ $productId }}">
            {{ $feedback }}
        </p>
    @endif

    <div class="@unless ($feedback) mt-3 @endunless @if ($withQuantity) flex flex-wrap items-end gap-3 @endif">
        @if ($withQuantity)
            <div>
                <label for="cantidad-{{ $productId }}" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Cantidad
                </label>

                <input id="cantidad-{{ $productId }}"
                       type="number"
                       wire:model.live="quantity"
                       min="1"
                       max="{{ max($stock, 1) }}"
                       class="mt-1 w-24 rounded-lg border-gray-700 bg-gray-800 text-center text-sm font-bold text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
            </div>
        @endif

        <div class="flex @if ($withQuantity) flex-1 flex-wrap gap-2 @else w-full flex-col gap-1.5 @endif">
            <button type="button"
                    wire:click="addToCart"
                    @if ($withQuantity)
                        class="flex-1 rounded-lg border border-orange-500/60 bg-gray-800 px-4 py-2.5 text-sm font-bold text-orange-400 transition hover:bg-orange-500/10 focus:outline-none"
                    @else
                        class="w-full rounded-lg border border-gray-700 bg-gray-800/90 px-3 py-2 text-xs font-semibold text-gray-200 transition hover:border-orange-500 hover:text-orange-400"
                    @endif>
                🛒 Agregar al carrito
            </button>

            @if ($withQuantity)
                <button type="button"
                        wire:click="buyNow"
                        class="flex-1 rounded-lg bg-orange-500 px-4 py-2.5 text-center text-sm font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400">
                    ⚡ Comprar ahora
                </button>
            @endif
        </div>
    </div>

    {{-- Fallback sin JavaScript (ADR-0017): el mismo POST contra el endpoint
         HTTP del carrito, ejecutado con formularios HTML normales. --}}
    <noscript>
        <form method="POST" action="{{ route('carrito.agregar') }}" class="mt-3">
            @csrf

            <input type="hidden" name="product_id" value="{{ $productId }}">

            @if ($withQuantity)
                <input type="hidden" name="quantity" value="{{ $quantity }}">
            @else
                <input type="hidden" name="quantity" value="1">
            @endif

            <button type="submit"
                    class="@if ($withQuantity) rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white @else w-full rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 @endif">
                Agregar al carrito (sin JavaScript)
            </button>
        </form>
    </noscript>
</div>
