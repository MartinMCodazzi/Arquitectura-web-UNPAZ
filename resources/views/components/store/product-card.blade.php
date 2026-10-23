@use(Illuminate\Support\Str)

@props(['product'])

{{-- Card reutilizable del catálogo: los dos consumidores son el listado del
     catálogo y los productos relacionados de la ficha. `product` es un
     `ProductSummary` (listado, ADR-0015) o un modelo `Product` (ficha): los
     dos exponen los mismos atributos públicos usados acá. --}}

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
            <x-store.price :cents="$product->price" class="text-lg font-bold text-gray-900 dark:text-gray-100" />
            <x-store.stock-badge :in-stock="$product->is_in_stock" :stock="$product->stock" />
        </div>

        @if ($product->is_in_stock)
            <livewire:cart.add-to-cart-button
                :product-id="$product->id"
                :stock="$product->stock"
                :key="'card-producto-'.$product->id" />
        @endif
    </div>
</article>
