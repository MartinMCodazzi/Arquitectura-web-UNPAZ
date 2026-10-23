@use(App\DTO\ProductFilters)
@use(Illuminate\Support\Str)

{{-- Catálogo reactivo (ADR-0016). El server render inicial ya muestra el
     listado completo con los filtros de la URL; después, cada cambio de
     filtro actualiza la grilla sin recargar. --}}

<div>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $products->total() }}
            {{ Str::plural('producto', $products->total()) }}
            @if ($filters->hasActiveFilters())
                <span>con los filtros aplicados</span>
            @endif
        </p>

        @if ($filters->hasActiveFilters())
            <button type="button"
                    wire:click="clearFilters"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                Limpiar filtros
            </button>
        @endif
    </div>

    {{-- Filtros reactivos: la URL canónica (céntimos, ADR-0002/0011) se
         sincroniza al vuelo vía `#[Url]`; los valores de input son pesos. --}}
    <div class="mt-6 grid gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="sm:col-span-2 lg:col-span-1">
            <label for="buscar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
            <input id="buscar"
                   type="text"
                   wire:model.live.debounce.500ms="buscar"
                   placeholder="Nombre o descripción"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        </div>

        <div>
            <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
            <select id="categoria"
                    wire:model.live="categoria"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Todas las categorías</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="precio_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio mínimo ($)</label>
            <input id="precio_min"
                   type="number"
                   wire:model.live.debounce.700ms="precioMinPesos"
                   min="0"
                   step="1"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        </div>

        <div>
            <label for="precio_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio máximo ($)</label>
            <input id="precio_max"
                   type="number"
                   wire:model.live.debounce.700ms="precioMaxPesos"
                   min="0"
                   step="1"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        </div>

        <div>
            <label for="orden" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por</label>
            <select id="orden"
                    wire:model.live="orden"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                @foreach (ProductFilters::SORT_OPTIONS as $option)
                    <option value="{{ $option }}">{{ ProductFilters::sortLabelFor($option) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-4 sm:col-span-2 lg:col-span-5">
            <label for="solo_stock" class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input id="solo_stock"
                       type="checkbox"
                       wire:model.live="soloStock"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900">
                Mostrar sólo productos con stock
            </label>
        </div>
    </div>

    {{-- Fallback sin JavaScript: formulario GET clásico con el redirect
         canónico de ADR-0011 (precios en pesos → URL en céntimos). --}}
    <noscript>
        <form method="GET"
              action="{{ route('productos.index') }}"
              class="mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Modo sin JavaScript
            </p>

            <div class="mt-3 flex flex-wrap items-end gap-3">
                <input type="search" name="buscar" value="{{ $filters->search }}"
                       placeholder="Buscar"
                       class="rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">

                <select name="categoria" class="rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters->category === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <input type="number" name="precio_min_pesos" min="0" step="1"
                       value="{{ $filters->minPriceInPesos() }}"
                       aria-label="Precio mínimo"
                       class="w-28 rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">

                <input type="number" name="precio_max_pesos" min="0" step="1"
                       value="{{ $filters->maxPriceInPesos() }}"
                       aria-label="Precio máximo"
                       class="w-28 rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">

                <select name="orden" class="rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    @foreach (ProductFilters::SORT_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($filters->sort === $option)>
                            {{ ProductFilters::sortLabelFor($option) }}
                        </option>
                    @endforeach
                </select>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="solo_stock" value="1" @checked($filters->inStockOnly)
                           class="rounded border-gray-300 text-indigo-600 shadow-sm dark:border-gray-600 dark:bg-gray-900">
                    Sólo con stock
                </label>

                <button type="submit"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Filtrar
                </button>
            </div>
        </form>
    </noscript>

    @if ($products->isEmpty())
        <div class="mt-10 rounded-lg border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
            <p class="text-gray-500 dark:text-gray-400">
                No encontramos productos con esos filtros.
            </p>
        </div>
    @else
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-store.product-card :product="$product" wire:key="producto-{{ $product->id }}" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links('pagination.catalogo') }}
        </div>
    @endif
</div>
