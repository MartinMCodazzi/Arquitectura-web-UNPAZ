<x-store-layout
    :title="$product->name"
    :description="$product->description"
    :canonical="$canonical"
    :image="$image"
    type="product">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm" aria-label="Migas de pan">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('productos.index') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                        Catálogo
                    </a>
                </li>
                @if ($product->category)
                    <li aria-hidden="true">/</li>
                    <li>
                        <a href="{{ route('productos.index', ['categoria' => $product->category->slug]) }}"
                           class="hover:text-gray-700 dark:hover:text-gray-300">
                            {{ $product->category->name }}
                        </a>
                    </li>
                @endif
                <li aria-hidden="true">/</li>
                <li class="truncate text-gray-900 dark:text-gray-100" aria-current="page">
                    {{ $product->name }}
                </li>
            </ol>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            <div class="flex aspect-square items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                @if ($product->image_url_large)
                    <picture>
                        @if ($avif = $product->thumbnailUrl(800, 'avif'))
                            <source type="image/avif" srcset="{{ $avif }}">
                        @endif

                        <img src="{{ $product->image_url_large }}"
                             alt="{{ $product->name }}"
                             loading="eager"
                             fetchpriority="high"
                             width="800"
                             height="800"
                             class="h-full w-full object-cover">
                    </picture>
                @else
                    <span class="px-6 text-center text-sm font-medium text-gray-400 dark:text-gray-500">
                        {{ $product->name }}
                    </span>
                @endif
            </div>

            <div class="flex flex-col gap-6">
                <div>
                    @if ($product->category)
                        <a href="{{ route('productos.index', ['categoria' => $product->category->slug]) }}"
                           class="text-xs font-medium uppercase tracking-wide text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            {{ $product->category->name }}
                        </a>
                    @endif

                    <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $product->name }}
                    </h1>
                </div>

                <div class="flex items-baseline gap-4">
                    <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $product->formatted_price }}
                    </span>

                    @if ($product->is_in_stock)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                            {{ $product->stock }} unidades disponibles
                        </span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                            Sin stock
                        </span>
                    @endif
                </div>

                @if ($product->description)
                    <div class="prose max-w-none text-gray-600 dark:text-gray-300">
                        <p class="whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    @if ($product->is_in_stock)
                        <form method="POST" action="{{ route('carrito.agregar') }}" class="flex flex-wrap items-end gap-3">
                            @csrf

                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div>
                                <label for="cantidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Cantidad
                                </label>

                                <input id="cantidad"
                                       type="number"
                                       name="quantity"
                                       value="1"
                                       min="1"
                                       max="{{ $product->stock }}"
                                       class="mt-1 w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                            </div>

                            <button type="submit"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Agregar al carrito
                            </button>
                        </form>
                    @else
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">
                            Este producto no tiene existencias disponibles.
                        </p>
                    @endif

                    <a href="{{ route('productos.index') }}"
                       class="mt-4 inline-block text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Volver al catálogo
                    </a>
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Productos relacionados</h2>

                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('products.partials.card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-store-layout>
