@use(Illuminate\Support\Str)

@props(['product'])

{{-- Card reutilizable del catálogo: los dos consumidores son el listado del
     catálogo y los productos relacionados de la ficha. `product` es un
     `ProductSummary` (listado, ADR-0008) o un modelo `Product` (ficha): los
     dos exponen los mismos atributos públicos usados acá. --}}

<article class="group relative flex flex-col overflow-hidden rounded-xl border border-gray-800 bg-gray-900/90 shadow-lg transition duration-200 hover:-translate-y-1 hover:border-orange-500/50 hover:shadow-orange-500/10 hover:shadow-2xl">
    {{-- Badge de cuotas estilo gaming --}}
    <div class="absolute top-2.5 left-2.5 z-10 flex flex-col gap-1">
        <span class="rounded bg-orange-500 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow">
            3 Cuotas fijas
        </span>
    </div>

    <a href="{{ route('productos.show', $product->slug) }}" class="relative flex aspect-square items-center justify-center overflow-hidden bg-gray-950 p-4">
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
                     class="h-full w-full object-contain transition duration-300 group-hover:scale-105">
            </picture>
        @else
            <div class="flex flex-col items-center justify-center p-4 text-center">
                <span class="text-3xl text-gray-700">⚡</span>
                <span class="mt-2 text-xs font-semibold text-gray-500">
                    {{ Str::limit($product->name, 40) }}
                </span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-2 p-4">
        @if ($product->category)
            <a href="{{ route('productos.index', ['categoria' => $product->category->slug]) }}"
               class="text-[11px] font-bold uppercase tracking-wider text-orange-400 hover:text-orange-300 transition">
                {{ $product->category->name }}
            </a>
        @endif

        <h3 class="text-sm font-bold text-gray-100 line-clamp-2 min-h-[2.5rem]">
            <a href="{{ route('productos.show', $product->slug) }}" class="hover:text-orange-400 transition">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto pt-2 border-t border-gray-800/80">
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="block text-[10px] uppercase font-bold text-gray-400">Precio especial</span>
                    <x-store.price :cents="$product->price" class="text-lg font-black text-white" />
                </div>
                <x-store.stock-badge :in-stock="$product->is_in_stock" :stock="$product->stock" />
            </div>

            <div class="mt-1 flex items-center justify-between text-[11px] text-gray-400">
                <span>Transferencia (10% OFF):</span>
                <span class="font-bold text-green-400">
                    <x-store.price :cents="(int) round($product->price * 0.9)" />
                </span>
            </div>
        </div>

        @if ($product->is_in_stock)
            <div class="pt-2">
                <livewire:cart.add-to-cart-button
                    :product-id="$product->id"
                    :stock="$product->stock"
                    :key="'card-producto-'.$product->id" />
            </div>
        @else
            <div class="pt-2">
                <button type="button" disabled class="w-full rounded-lg bg-gray-800/60 py-2 text-center text-xs font-semibold text-gray-500 cursor-not-allowed">
                    Sin stock
                </button>
            </div>
        @endif
    </div>
</article>
