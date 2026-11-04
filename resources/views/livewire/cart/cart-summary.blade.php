<div>
    @if ($orderPlacedMessage)
        <div class="mb-8 rounded-xl border border-green-500/40 bg-gradient-to-r from-gray-900 to-gray-800 p-6 text-center shadow-lg dark:border-green-500/50">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-500/20 text-green-400">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="mt-4 text-xl font-bold text-white">¡Compra finalizada con éxito!</h3>
            <p class="mt-2 text-sm text-gray-300">{{ $orderPlacedMessage }}</p>
            <div class="mt-6">
                <a href="{{ route('productos.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-orange-600 focus:outline-none">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Seguir explorando productos
                </a>
            </div>
        </div>
    @endif

    @if ($feedback)
        <div class="mb-4">
            <x-store.alert :type="$feedbackType">{{ $feedback }}</x-store.alert>
        </div>
    @endif

    @if ($lines->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
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
        <div class="grid gap-8 lg:grid-cols-3">
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
                                <tr wire:key="linea-{{ $line->product->id }}">
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
                                                <a href="{{ route('productos.show', $line->product->slug) }}"
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
                                        <x-store.price :cents="$line->product->price" />
                                    </td>

                                    <td class="px-4 py-4">
                                        <label for="cantidad-{{ $line->product->id }}" class="sr-only">
                                            Cantidad de {{ $line->product->name }}
                                        </label>

                                        <input id="cantidad-{{ $line->product->id }}"
                                               type="number"
                                               wire:model.live.debounce.350ms="quantities.{{ $line->product->id }}"
                                               min="1"
                                               max="{{ $line->product->stock }}"
                                               class="w-20 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                                    </td>

                                    <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">
                                        <x-store.price :cents="$line->subtotal" />
                                    </td>

                                    <td class="px-4 py-4 text-right">
                                        <button type="button"
                                                wire:click="removeProduct({{ $line->product->id }})"
                                                class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">
                                            Quitar
                                        </button>
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

                    <button type="button"
                            wire:click="clearCart"
                            wire:confirm="¿Seguro que querés vaciar el carrito?"
                            class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">
                        Vaciar carrito
                    </button>
                </div>

                {{-- Fallback sin JavaScript (ADR-0017): las mismas operaciones
                     contra los endpoints HTTP del carrito (ADR-0012), ahora en
                     modo compacto. --}}
                <noscript>
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Modo sin JavaScript
                        </p>

                        <div class="mt-3 space-y-3">
                            @foreach ($lines as $line)
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $line->product->name }}
                                    </span>

                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('carrito.actualizar', $line->product) }}"
                                              class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <input type="number"
                                                   name="quantity"
                                                   value="{{ $line->quantity }}"
                                                   min="1"
                                                   max="{{ $line->product->stock }}"
                                                   aria-label="Cantidad de {{ $line->product->name }}"
                                                   class="w-20 rounded-md border-gray-300 text-right text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                            <button type="submit"
                                                    class="rounded-md border border-gray-300 px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                                                Actualizar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('carrito.eliminar', $line->product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md border border-red-200 px-2 py-1.5 text-xs text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/40">
                                                Quitar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('carrito.vaciar') }}" class="mt-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">
                                Vaciar carrito
                            </button>
                        </form>
                    </div>
                </noscript>
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
                                <x-store.price :cents="$totals['subtotal']" />
                            </dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">
                                IVA ({{ (int) round(config('store.tax_rate') * 100) }} %)
                            </dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                <x-store.price :cents="$totals['taxes']" />
                            </dd>
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                            <dt class="font-semibold text-gray-900 dark:text-gray-100">Total</dt>
                            <dd class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                <x-store.price :cents="$totals['total']" />
                            </dd>
                        </div>
                    </dl>

                    <button type="button"
                            wire:click="openCheckout"
                            class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 py-3 text-center text-sm font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Finalizar compra
                    </button>

                    <p class="mt-4 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                        ⚡ Garantía oficial y envío asegurado en todas tus compras gamer.
                    </p>
                </div>
            </aside>
        </div>
    @endif

    {{-- Modal Checkout / Confirmación de Compra --}}
    @if ($showCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm animate-fade-in">
            <div class="relative w-full max-w-lg rounded-2xl border border-gray-700 bg-gray-900 p-6 text-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/20 text-orange-400">
                            🎮
                        </span>
                        <h3 class="text-lg font-bold text-white">Finalizar tu Pedido</h3>
                    </div>
                    <button type="button" wire:click="closeCheckout" class="text-gray-400 hover:text-white">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="processCheckout" class="mt-4 space-y-4">
                    <div>
                        <label for="checkoutName" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Nombre y Apellido *
                        </label>
                        <input id="checkoutName"
                               type="text"
                               wire:model="checkoutName"
                               placeholder="Ej. Juan Pérez"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('checkoutName') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="checkoutEmail" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Correo Electrónico (para comprobante) *
                        </label>
                        <input id="checkoutEmail"
                               type="email"
                               wire:model="checkoutEmail"
                               placeholder="juan@ejemplo.com"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('checkoutEmail') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Método de Pago
                        </label>
                        <div class="mt-2 space-y-2">
                            <label class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-700 bg-gray-800/80 p-3 hover:border-orange-500/60 {{ $checkoutPayment === 'transferencia' ? 'border-orange-500 ring-1 ring-orange-500' : '' }}">
                                <div class="flex items-center gap-3">
                                    <input type="radio" wire:model.live="checkoutPayment" value="transferencia" class="text-orange-500 focus:ring-orange-500">
                                    <span class="text-sm font-medium">Transferencia Bancaria</span>
                                </div>
                                <span class="rounded bg-orange-500/20 px-2 py-0.5 text-xs font-bold text-orange-400">10% OFF Especial</span>
                            </label>

                            <label class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-700 bg-gray-800/80 p-3 hover:border-orange-500/60 {{ $checkoutPayment === 'tarjeta' ? 'border-orange-500 ring-1 ring-orange-500' : '' }}">
                                <div class="flex items-center gap-3">
                                    <input type="radio" wire:model.live="checkoutPayment" value="tarjeta" class="text-orange-500 focus:ring-orange-500">
                                    <span class="text-sm font-medium">Tarjeta de Débito / Crédito</span>
                                </div>
                                <span class="text-xs text-gray-400">Hasta 3 cuotas</span>
                            </label>

                            <label class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-700 bg-gray-800/80 p-3 hover:border-orange-500/60 {{ $checkoutPayment === 'efectivo' ? 'border-orange-500 ring-1 ring-orange-500' : '' }}">
                                <div class="flex items-center gap-3">
                                    <input type="radio" wire:model.live="checkoutPayment" value="efectivo" class="text-orange-500 focus:ring-orange-500">
                                    <span class="text-sm font-medium">Efectivo contra entrega</span>
                                </div>
                                <span class="text-xs text-gray-400">Retiro en local</span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-800 p-3.5 text-sm">
                        <div class="flex justify-between text-gray-300">
                            <span>Total a pagar:</span>
                            <span class="text-base font-bold text-orange-400">
                                <x-store.price :cents="$totals['total']" />
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" wire:click="closeCheckout" class="rounded-lg px-4 py-2 text-sm text-gray-400 hover:text-white">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg hover:bg-orange-600 focus:outline-none">
                            Confirmar y Comprar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
