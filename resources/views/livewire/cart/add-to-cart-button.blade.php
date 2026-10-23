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
                <label for="cantidad-{{ $productId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cantidad
                </label>

                <input id="cantidad-{{ $productId }}"
                       type="number"
                       wire:model.live="quantity"
                       min="1"
                       max="{{ max($stock, 1) }}"
                       class="mt-1 w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
            </div>
        @endif

        <button type="button"
                wire:click="addToCart"
                @if ($withQuantity)
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                @else
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                @endif>
            Agregar al carrito
        </button>
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
