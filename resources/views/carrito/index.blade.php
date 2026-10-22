@use(Illuminate\Support\Number)

<x-store-layout
    title="Carrito de compras"
    description="Revisá los productos de tu carrito de compras antes de finalizar la compra."
    :canonical="route('carrito.index')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Carrito de compras
        </h1>

        @if ($lines->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-base font-medium text-gray-900 dark:text-gray-100">
                    Tu carrito está vacío
                </p>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Todavía no agregaste productos. Explorá el catálogo y encontrá lo que buscás.
                </p>

                <a href="{{ route('productos.index') }}"
                   class="mt-6 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ver el catálogo
                </a>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Producto</th>
                                    <th scope="col" class="px-4 py-3 text-right">Precio</th>
                                    <th scope="col" class="px-4 py-3 text-center">Cantidad</th>
                                    <th scope="col" class="px-4 py-3 text-right">Subtotal</th>
                                    <th scope="col" class="px-4 py-3"><span class="sr-only">Quitar</span></th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($lines as $line)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md bg-gray-100 dark:bg-gray-700">
                                                    @if ($line->product->image_url_small)
                                                        <img src="{{ $line->product->image_url_small }}"
                                                             alt="{{ $line->product->name }}"
                                                             loading="lazy"
                                                             width="64"
                                                             height="64"
                                                             class="h-full w-full object-cover">
                                                    @else
                                                        <span class="px-2 text-center text-xs text-gray-400 dark:text-gray-500">
                                                            {{ $line->product->name }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div>
                                                    <a href="{{ route('productos.show', $line->product) }}"
                                                       class="font-medium text-gray-900 hover:underline dark:text-gray-100">
                                                        {{ $line->product->name }}
                                                    </a>

                                                    @if ($line->product->category)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $line->product->category->name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 text-right text-gray-600 dark:text-gray-300">
                                            {{ $line->product->formatted_price }}
                                        </td>

                                        <td class="px-4 py-4">
                                            <form method="POST"
                                                  action="{{ route('carrito.actualizar', $line->product) }}"
                                                  class="flex items-center justify-center gap-2">
                                                @csrf
                                                @method('PATCH')

                                                <label for="cantidad-{{ $line->product->id }}" class="sr-only">
                                                    Cantidad de {{ $line->product->name }}
                                                </label>

                                                <input id="cantidad-{{ $line->product->id }}"
                                                       type="number"
                                                       name="quantity"
                                                       value="{{ $line->quantity }}"
                                                       min="1"
                                                       max="{{ $line->product->stock }}"
                                                       class="w-20 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                                                <button type="submit"
                                                        class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    Actualizar
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">
                                            {{ $line->formattedSubtotal() }}
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            <form method="POST"
                                                  action="{{ route('carrito.eliminar', $line->product) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">
                                                    Quitar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <a href="{{ route('productos.index') }}"
                           class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            ← Seguir comprando
                        </a>

                        <form method="POST" action="{{ route('carrito.vaciar') }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">
                                Vaciar carrito
                            </button>
                        </form>
                    </div>
                </div>

                <aside class="lg:col-span-1">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Resumen del pedido
                        </h2>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Subtotal</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ Number::currency($totals['subtotal'] / 100, config('store.currency'), config('store.locale')) }}
                                </dd>
                            </div>

                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">
                                    IVA ({{ (int) round(config('store.tax_rate') * 100) }} %)
                                </dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ Number::currency($totals['taxes'] / 100, config('store.currency'), config('store.locale')) }}
                                </dd>
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                                <dt class="font-semibold text-gray-900 dark:text-gray-100">Total</dt>
                                <dd class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ Number::currency($totals['total'] / 100, config('store.currency'), config('store.locale')) }}
                                </dd>
                            </div>
                        </dl>

                        <button type="button"
                                disabled
                                class="mt-6 w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            Finalizar compra (fuera de alcance)
                        </button>

                        <p class="mt-4 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            Tu carrito se guarda en la sesión del navegador (cookie de sesión): no hace falta
                            iniciar sesión y se conserva mientras la sesión esté activa.
                        </p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</x-store-layout>
