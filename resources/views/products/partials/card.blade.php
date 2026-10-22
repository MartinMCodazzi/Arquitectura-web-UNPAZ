@use(Illuminate\Support\Str)

@props(['product'])

<article class="group flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
    <a href="{{ route('productos.show', $product->slug) }}" class="flex aspect-square items-center justify-center overflow-hidden bg-gray-100 dark:bg-gray-700">
        @if ($product->image_url)
            <picture>
                @if ($avif = $product->thumbnailUrl(400, 'avif'))
                    <source type="image/avif" srcset="{{ $avif }}">
                @endif

                <img src="{{ $product->image_url }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     width="400"
                     height="400"
                     class="h-full w-full object-cover transition group-hover:scale-105">
            </picture>
        @else
            <span class="px-4 text-center text-sm font-medium text-gray-400 dark:text-gray-500">
                {{ Str::limit($product->name, 40) }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-2 p-4">
        @if ($product->category)
            <a href="{{ route('productos.index', ['categoria' => $product->category->slug]) }}"
               class="text-xs font-medium uppercase tracking-wide text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                {{ $product->category->name }}
            </a>
        @endif

        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
            <a href="{{ route('productos.show', $product->slug) }}" class="hover:underline">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto flex items-center justify-between pt-2">
            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ $product->formatted_price }}
            </span>

            @if ($product->is_in_stock)
                <span class="text-xs font-medium text-green-600 dark:text-green-400">Disponible</span>
            @else
                <span class="text-xs font-medium text-red-600 dark:text-red-400">Sin stock</span>
            @endif
        </div>

        @if ($product->is_in_stock)
            <form method="POST" action="{{ route('carrito.agregar') }}">
                @csrf

                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">

                <button type="submit"
                        class="mt-3 w-full rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Agregar al carrito
                </button>
            </form>
        @endif
    </div>
</article>
