@use(App\DTO\ProductFilters)
@use(Illuminate\Support\Str)

<x-store-layout
    title="Catálogo de productos"
    description="Explorá el catálogo completo de productos disponibles, con filtros por categoría, precio y disponibilidad."
    :canonical="$canonical"
    :image="$image">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Catálogo</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $products->total() }}
                    {{ Str::plural('producto', $products->total()) }}
                    @if ($filters->hasActiveFilters())
                        <span>con los filtros aplicados</span>
                    @endif
                </p>
            </div>

            @if ($filters->hasActiveFilters())
                <a href="{{ route('productos.index') }}"
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                    Limpiar filtros
                </a>
            @endif
        </div>

        {{-- Filtros: se envían por query string para que el catálogo filtrado sea indexable (ADR-0006).
             Los precios del formulario van en pesos; el controlador redirige a la URL canónica,
             que los lleva en céntimos (ADR-0002). --}}
        <form method="GET"
              action="{{ route('productos.index') }}"
              class="mt-6 grid gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-700 dark:bg-gray-800">

            <div class="sm:col-span-2 lg:col-span-1">
                <label for="buscar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                <input id="buscar"
                       name="buscar"
                       type="search"
                       value="{{ $filters->search }}"
                       placeholder="Nombre o descripción"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>

            <div>
                <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                <select id="categoria"
                        name="categoria"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters->category === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="precio_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio mínimo ($)</label>
                <input id="precio_min"
                       name="precio_min_pesos"
                       type="number"
                       min="0"
                       step="1"
                       value="{{ $filters->minPriceInPesos() }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>

            <div>
                <label for="precio_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio máximo ($)</label>
                <input id="precio_max"
                       name="precio_max_pesos"
                       type="number"
                       min="0"
                       step="1"
                       value="{{ $filters->maxPriceInPesos() }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>

            <div>
                <label for="orden" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por</label>
                <select id="orden"
                        name="orden"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    @foreach (ProductFilters::SORT_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($filters->sort === $option)>
                            {{ ProductFilters::sortLabelFor($option) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between gap-4 sm:col-span-2 lg:col-span-5">
                <label for="solo_stock" class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input id="solo_stock"
                           name="solo_stock"
                           type="checkbox"
                           value="1"
                           @checked($filters->inStockOnly)
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900">
                    Mostrar sólo productos con stock
                </label>

                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Filtrar
                </button>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="mt-10 rounded-lg border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
                <p class="text-gray-500 dark:text-gray-400">
                    No encontramos productos con esos filtros.
                </p>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @include('products.partials.card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-store-layout>
