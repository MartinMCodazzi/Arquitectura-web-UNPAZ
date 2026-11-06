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
                                               class="w-20 rounded-lg border border-gray-300 bg-white text-right text-sm font-semibold text-black shadow-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
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

                {{-- Fallback sin JavaScript (ADR-0010): las mismas operaciones
                     contra los endpoints HTTP del carrito, ahora en modo
                     compacto. --}}
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
                                                   class="w-20 rounded-lg border border-gray-300 bg-white text-right text-sm font-semibold text-black shadow-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
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

                    @guest
                        <p class="mt-3 text-center text-xs leading-relaxed text-gray-400">
                            Para comprar tenés que
                            <a href="{{ route('login') }}" class="font-semibold text-orange-400 hover:underline">iniciar sesión</a>
                            o
                            <a href="{{ route('register', ['redirect' => 'carrito']) }}" class="font-semibold text-orange-400 hover:underline">registrarte</a>.
                        </p>
                    @endguest

                    <p class="mt-4 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                        ⚡ Garantía oficial y envío asegurado en todas tus compras gamer.
                    </p>
                </div>
            </aside>
        </div>
    @endif

    {{-- Checkout en 4 pasos (envío, facturación, pago y revisión) --}}
    @if ($showCheckoutModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/70 p-3 backdrop-blur-sm animate-fade-in">
            <div class="relative mx-auto my-3 flex max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-700 bg-gray-900 text-white shadow-2xl">
                <div class="flex shrink-0 items-center justify-between border-b border-gray-800 px-4 py-3 sm:px-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/20 text-orange-400">
                            🎮
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-white">Finalizar tu pedido</h3>
                            <p class="text-xs text-gray-400">Compra protegida · Tienda Online</p>
                        </div>
                    </div>

                    <button type="button"
                            wire:click="closeCheckout"
                            aria-label="Cerrar"
                            class="text-gray-400 hover:text-white">
                        ✕
                    </button>
                </div>

                @if ($checkoutPhase === 'loading')
                    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-5 py-10 text-center"
                         wire:key="checkout-loading"
                         x-init="setTimeout(() => $wire.startCheckout(), 1600)">
                        <div class="relative flex h-16 w-16 items-center justify-center">
                            <div class="absolute inset-0 animate-spin rounded-full border-4 border-orange-500/20 border-t-orange-500"></div>
                            <span class="text-2xl">⚡</span>
                        </div>

                        <h4 class="mt-5 text-lg font-black text-white">Estamos preparando todo para tu compra</h4>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-gray-400">
                            Verificamos stock, precios y opciones de envío. En unos segundos vas a completar 4 pasos: envío, facturación, pago y revisión.
                        </p>

                        <div class="mt-5 h-1.5 w-48 overflow-hidden rounded-full bg-gray-800">
                            <div class="h-full w-1/2 animate-pulse rounded-full bg-orange-500"></div>
                        </div>
                    </div>
                @else
                    <form wire:submit.prevent="nextStep" class="flex min-h-0 flex-1 flex-col">
                        <div class="shrink-0 border-b border-gray-800 px-4 py-3 sm:px-5">
                            @php
                                $checkoutSteps = [1 => 'Envío', 2 => 'Facturación', 3 => 'Pago', 4 => 'Revisión'];
                            @endphp

                            <div class="flex items-center">
                                @foreach ($checkoutSteps as $number => $label)
                                    <div class="flex flex-1 items-center">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $checkoutStep > $number ? 'bg-green-500 text-white' : ($checkoutStep === $number ? 'bg-orange-500 text-white' : 'bg-gray-800 text-gray-500') }}">
                                            @if ($checkoutStep > $number)
                                                ✓
                                            @else
                                                {{ $number }}
                                            @endif
                                        </div>

                                        <span class="ml-2 hidden text-xs font-semibold sm:inline {{ $checkoutStep === $number ? 'text-white' : 'text-gray-500' }}">
                                            {{ $label }}
                                        </span>

                                        @if (! $loop->last)
                                            <div class="mx-3 h-px flex-1 {{ $checkoutStep > $number ? 'bg-green-500/60' : 'bg-gray-800' }}"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5" wire:key="checkout-step-{{ $checkoutStep }}">
                            @if ($checkoutStep === 1)
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-white">¿Cómo querés recibir tu compra?</h4>
                                        <p class="mt-1 text-sm text-gray-400">Elegí envío a domicilio o retiro en un punto de entrega.</p>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3 transition {{ $shippingMethod === 'domicilio' ? 'border-orange-500 bg-orange-500/10 ring-1 ring-orange-500' : 'border-gray-700 bg-gray-800/60 hover:border-orange-500/50' }}">
                                            <div class="flex items-start gap-3">
                                                <input type="radio" wire:model.live="shippingMethod" value="domicilio" class="mt-1 text-orange-500 focus:ring-orange-500">
                                                <div>
                                                    <p class="text-sm font-bold text-white">Envío a domicilio</p>
                                                    <p class="mt-1 text-xs text-gray-400">Recibilo en tu casa con el correo que elijas.</p>
                                                </div>
                                            </div>
                                            <span class="text-xl">🚚</span>
                                        </label>

                                        <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3 transition {{ $shippingMethod === 'retiro' ? 'border-orange-500 bg-orange-500/10 ring-1 ring-orange-500' : 'border-gray-700 bg-gray-800/60 hover:border-orange-500/50' }}">
                                            <div class="flex items-start gap-3">
                                                <input type="radio" wire:model.live="shippingMethod" value="retiro" class="mt-1 text-orange-500 focus:ring-orange-500">
                                                <div>
                                                    <p class="text-sm font-bold text-white">Retiro en punto de entrega</p>
                                                    <p class="mt-1 text-xs text-gray-400">Retiralo cuando esté listo, sin esperar al correo.</p>
                                                </div>
                                            </div>
                                            <span class="text-xl">🏪</span>
                                        </label>
                                    </div>
                                    @error('shippingMethod') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="shippingName" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                Nombre de quien recibe *
                                            </label>
                                            <input id="shippingName"
                                                   type="text"
                                                   wire:model="shippingName"
                                                   placeholder="Ej. Juan Pérez"
                                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                            @error('shippingName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="shippingPhone" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                Teléfono de contacto *
                                            </label>
                                            <input id="shippingPhone"
                                                   type="tel"
                                                   wire:model="shippingPhone"
                                                   placeholder="Ej. 11 2345 6789"
                                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                            @error('shippingPhone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    @if ($shippingMethod === 'domicilio')
                                        <div class="space-y-4 rounded-xl border border-gray-800 bg-gray-800/30 p-4">
                                            <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Dirección de entrega</p>

                                            <div class="grid gap-4 sm:grid-cols-3">
                                                <div class="sm:col-span-2">
                                                    <label for="shippingStreet" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Calle *
                                                    </label>
                                                    <input id="shippingStreet"
                                                           type="text"
                                                           wire:model="shippingStreet"
                                                           placeholder="Ej. Av. Corrientes"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('shippingStreet') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label for="shippingNumber" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Número *
                                                    </label>
                                                    <input id="shippingNumber"
                                                           type="text"
                                                           wire:model="shippingNumber"
                                                           placeholder="1234"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('shippingNumber') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-3">
                                                <div>
                                                    <label for="shippingFloor" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Piso / Depto
                                                    </label>
                                                    <input id="shippingFloor"
                                                           type="text"
                                                           wire:model="shippingFloor"
                                                           placeholder="Ej. 4° B"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                </div>

                                                <div>
                                                    <label for="shippingCity" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Ciudad *
                                                    </label>
                                                    <input id="shippingCity"
                                                           type="text"
                                                           wire:model="shippingCity"
                                                           placeholder="Ej. CABA"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('shippingCity') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label for="shippingPostalCode" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Código postal *
                                                    </label>
                                                    <input id="shippingPostalCode"
                                                           type="text"
                                                           wire:model="shippingPostalCode"
                                                           placeholder="Ej. 1043"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('shippingPostalCode') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <div>
                                                    <label for="shippingProvince" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Provincia *
                                                    </label>
                                                    <input id="shippingProvince"
                                                           type="text"
                                                           wire:model="shippingProvince"
                                                           placeholder="Ej. Buenos Aires"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('shippingProvince') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label for="shippingNotes" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Referencias (opcional)
                                                    </label>
                                                    <input id="shippingNotes"
                                                           type="text"
                                                           wire:model="shippingNotes"
                                                           placeholder="Entre calles, timbre, etc."
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-orange-400">Elegí el correo</p>

                                            <div class="space-y-2">
                                                @foreach ($couriers as $courierId => $courier)
                                                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3 transition {{ $shippingCourier === $courierId ? 'border-orange-500 bg-orange-500/10 ring-1 ring-orange-500' : 'border-gray-700 bg-gray-800/60 hover:border-orange-500/50' }}">
                                                        <div class="flex items-center gap-3">
                                                            <input type="radio" wire:model.live="shippingCourier" value="{{ $courierId }}" class="text-orange-500 focus:ring-orange-500">
                                                            <div>
                                                                <p class="text-sm font-semibold text-white">{{ $courier['name'] }}</p>
                                                                <p class="text-xs text-gray-400">{{ $courier['eta'] }}</p>
                                                            </div>
                                                        </div>

                                                        <span class="text-sm font-bold text-orange-400">
                                                            @if ($courier['price'] > 0)
                                                                <x-store.price :cents="$courier['price']" />
                                                            @else
                                                                Gratis
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('shippingCourier') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                    @else
                                        <div>
                                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-orange-400">Elegí el punto de entrega</p>

                                            <div class="space-y-2">
                                                @foreach ($pickupPoints as $pointId => $point)
                                                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3 transition {{ $shippingPickupPoint === $pointId ? 'border-orange-500 bg-orange-500/10 ring-1 ring-orange-500' : 'border-gray-700 bg-gray-800/60 hover:border-orange-500/50' }}">
                                                        <div class="flex items-center gap-3">
                                                            <input type="radio" wire:model.live="shippingPickupPoint" value="{{ $pointId }}" class="text-orange-500 focus:ring-orange-500">
                                                            <div>
                                                                <p class="text-sm font-semibold text-white">{{ $point['name'] }}</p>
                                                                <p class="text-xs text-gray-400">{{ $point['address'] }} · {{ $point['eta'] }}</p>
                                                            </div>
                                                        </div>

                                                        <span class="text-sm font-bold text-orange-400">
                                                            @if ($point['price'] > 0)
                                                                <x-store.price :cents="$point['price']" />
                                                            @else
                                                                Gratis
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('shippingPickupPoint') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                    @endif
                                </div>
                            @elseif ($checkoutStep === 2)
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-white">Datos de facturación</h4>
                                        <p class="mt-1 text-sm text-gray-400">La factura se emite a nombre del comprador.</p>
                                    </div>

                                    <div class="rounded-xl border border-orange-500/30 bg-orange-500/10 p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wider text-orange-300">Comprobante</p>
                                                <p class="mt-1 text-sm font-semibold text-white">Factura B</p>
                                            </div>
                                            <span class="rounded-lg bg-orange-500 px-3 py-1 text-xs font-black uppercase tracking-wider text-white">
                                                B
                                            </span>
                                        </div>
                                        <p class="mt-2 text-xs leading-relaxed text-gray-300">
                                            Se emitirá con los datos que ingreses a continuación. Si necesitás Factura A, contactá a atención al cliente antes de comprar.
                                        </p>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="checkoutName" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                Nombre y apellido / Razón social *
                                            </label>
                                            <input id="checkoutName"
                                                   type="text"
                                                   wire:model="checkoutName"
                                                   placeholder="Ej. Juan Pérez"
                                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                            @error('checkoutName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="checkoutEmail" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                Correo electrónico *
                                            </label>
                                            <input id="checkoutEmail"
                                                   type="email"
                                                   wire:model="checkoutEmail"
                                                   placeholder="juan@ejemplo.com"
                                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                            @error('checkoutEmail') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="billingTaxId" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                DNI / CUIT *
                                            </label>
                                            <input id="billingTaxId"
                                                   type="text"
                                                   wire:model="billingTaxId"
                                                   placeholder="Ej. 20.123.456-7"
                                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                            @error('billingTaxId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="billingTaxCondition" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                Condición frente al IVA *
                                            </label>
                                            <select id="billingTaxCondition"
                                                    wire:model="billingTaxCondition"
                                                    class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                <option value="consumidor_final">Consumidor Final</option>
                                                <option value="monotributo">Monotributo</option>
                                                <option value="responsable_inscripto">Responsable Inscripto</option>
                                                <option value="exento">Exento</option>
                                            </select>
                                            @error('billingTaxCondition') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Dirección de facturación</p>
                                        <p class="mt-1 text-sm text-gray-200">{{ $shippingSummary }}</p>
                                        <p class="mt-1 text-xs text-gray-500">Si la dirección no coincide con la de envío, podés corregirla volviendo al paso anterior.</p>
                                    </div>
                                </div>
                            @elseif ($checkoutStep === 3)
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-white">Elegí cómo querés pagar</h4>
                                        <p class="mt-1 text-sm text-gray-400">Todos los medios de pago disponibles para tu compra.</p>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach ($paymentMethods as $methodId => $method)
                                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3 transition {{ $checkoutPayment === $methodId ? 'border-orange-500 bg-orange-500/10 ring-1 ring-orange-500' : 'border-gray-700 bg-gray-800/60 hover:border-orange-500/50' }}">
                                                <div class="flex items-start gap-3">
                                                    <input type="radio" wire:model.live="checkoutPayment" value="{{ $methodId }}" class="mt-1 text-orange-500 focus:ring-orange-500">
                                                    <div>
                                                        <p class="text-sm font-bold text-white">{{ $method['name'] }}</p>
                                                        <p class="mt-1 text-xs text-gray-400">{{ $method['description'] }}</p>
                                                    </div>
                                                </div>

                                                @if (($method['discount'] ?? 0) > 0)
                                                    <span class="rounded bg-orange-500/20 px-2 py-0.5 text-xs font-bold text-orange-400">
                                                        {{ (int) round($method['discount'] * 100) }} % OFF
                                                    </span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('checkoutPayment') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                                    @if ($checkoutPayment === 'transferencia')
                                        <div class="rounded-xl border border-green-500/30 bg-green-500/10 p-4 text-sm text-gray-200">
                                            <p class="font-semibold text-green-400">Datos para transferir</p>
                                            <p class="mt-1">Alias: <span class="font-mono font-bold text-white">gamerstore.unpaz</span></p>
                                            <p class="text-xs text-gray-400">Te enviamos el CBU y el comprobante por correo al confirmar la compra.</p>
                                        </div>
                                    @endif

                                    @if (in_array($checkoutPayment, ['tarjeta_credito', 'tarjeta_debito'], true))
                                        <div class="rounded-xl border border-gray-700 bg-gray-800/40 p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Datos de la tarjeta</p>
                                                    <p class="mt-1 text-xs text-gray-400">Ingresá los datos tal como figuran en el plástico.</p>
                                                </div>
                                                <span class="text-xl">💳</span>
                                            </div>

                                            <div class="mt-3 space-y-3">
                                                <div>
                                                    <label for="cardNumber" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Número de tarjeta *
                                                    </label>
                                                    <input id="cardNumber"
                                                           type="text"
                                                           wire:model="cardNumber"
                                                           inputmode="numeric"
                                                           autocomplete="cc-number"
                                                           maxlength="23"
                                                           placeholder="0000 0000 0000 0000"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('cardNumber') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label for="cardHolder" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                        Nombre como figura en la tarjeta *
                                                    </label>
                                                    <input id="cardHolder"
                                                           type="text"
                                                           wire:model="cardHolder"
                                                           autocomplete="cc-name"
                                                           maxlength="100"
                                                           placeholder="JUAN PEREZ"
                                                           class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                    @error('cardHolder') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                </div>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label for="cardExpiry" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                            Vencimiento *
                                                        </label>
                                                        <input id="cardExpiry"
                                                               type="text"
                                                               wire:model="cardExpiry"
                                                               inputmode="numeric"
                                                               autocomplete="cc-exp"
                                                               maxlength="5"
                                                               placeholder="MM/AA"
                                                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                        @error('cardExpiry') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                    </div>

                                                    <div>
                                                        <label for="cardCvv" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                            CVV *
                                                        </label>
                                                        <input id="cardCvv"
                                                               type="password"
                                                               wire:model="cardCvv"
                                                               inputmode="numeric"
                                                               autocomplete="cc-csc"
                                                               maxlength="4"
                                                               placeholder="123"
                                                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                        @error('cardCvv') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                    </div>
                                                </div>

                                                @if ($checkoutPayment === 'tarjeta_credito')
                                                    <div>
                                                        <label for="cardInstallments" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                                                            Cuotas
                                                        </label>
                                                        <select id="cardInstallments"
                                                                wire:model="cardInstallments"
                                                                class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                                            <option value="1">1 pago</option>
                                                            <option value="3">3 cuotas sin interés</option>
                                                            <option value="6">6 cuotas</option>
                                                        </select>
                                                        @error('cardInstallments') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                                    </div>
                                                @endif
                                            </div>

                                            <p class="mt-3 flex items-center gap-1.5 text-[11px] text-gray-500">
                                                🔒 Pago simulado. Los datos de la tarjeta no se almacenan.
                                            </p>
                                        </div>
                                    @endif

                                    @if ($checkoutPayment === 'mercadopago')
                                        <div class="rounded-xl border border-sky-500/40 bg-sky-500/10 p-4 text-sm text-gray-200">
                                            <p class="font-semibold text-sky-400">Pago con Mercado Pago</p>
                                            <p class="mt-1 text-xs leading-relaxed text-gray-300">
                                                Al continuar te redirigimos a Mercado Pago para completar el pago de forma segura.
                                                Cuando se apruebe, volvés a la tienda con tu compra confirmada.
                                            </p>
                                        </div>
                                    @endif

                                    @if ($checkoutPayment === 'efectivo' && $shippingMethod !== 'retiro')
                                        <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 p-4 text-sm text-amber-200">
                                            El pago en efectivo sólo está disponible si elegís retiro en un punto de entrega. Volvé al paso de envío para cambiarlo.
                                        </div>
                                    @endif

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <div class="flex items-center justify-between text-sm text-gray-300">
                                            <span>Total a pagar</span>
                                            <span class="text-lg font-bold text-orange-400">
                                                <x-store.price :cents="$grandTotal" />
                                            </span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Incluye envío y descuentos aplicados.</p>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-white">Revisá tu pedido</h4>
                                        <p class="mt-1 text-sm text-gray-400">Verificá que todo esté bien antes de confirmar la compra.</p>
                                    </div>

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Envío</p>
                                                <p class="mt-1 text-sm text-white">{{ $shippingSummary }}</p>
                                                <p class="mt-1 text-xs text-gray-400">{{ $shippingName }} · {{ $shippingPhone }}</p>
                                                @if ($shippingMethod === 'domicilio' && $shippingNotes !== '')
                                                    <p class="mt-1 text-xs text-gray-500">Referencias: {{ $shippingNotes }}</p>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="goToStep(1)" class="text-xs font-semibold text-orange-400 hover:underline">
                                                Editar
                                            </button>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Facturación · Factura {{ $billingInvoiceType }}</p>
                                                <p class="mt-1 text-sm text-white">{{ $checkoutName }}</p>
                                                <p class="mt-1 text-xs text-gray-400">DNI / CUIT: {{ $billingTaxId }} · {{ $billingTaxConditionLabel }}</p>
                                                <p class="mt-1 text-xs text-gray-400">{{ $checkoutEmail }}</p>
                                            </div>
                                            <button type="button" wire:click="goToStep(2)" class="text-xs font-semibold text-orange-400 hover:underline">
                                                Editar
                                            </button>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Pago</p>
                                                <p class="mt-1 text-sm text-white">{{ $paymentLabel }}</p>
                                                <p class="mt-1 text-xs text-gray-400">{{ $paymentMethods[$checkoutPayment]['description'] ?? '' }}</p>
                                            </div>
                                            <button type="button" wire:click="goToStep(3)" class="text-xs font-semibold text-orange-400 hover:underline">
                                                Editar
                                            </button>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-800 bg-gray-800/40 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-orange-400">Productos</p>

                                        <div class="mt-3 space-y-2">
                                            @foreach ($lines as $line)
                                                <div class="flex items-start justify-between gap-3 text-sm" wire:key="review-line-{{ $line->product->id }}">
                                                    <span class="text-gray-200">
                                                        {{ $line->quantity }}× {{ $line->product->name }}
                                                    </span>
                                                    <span class="shrink-0 font-semibold text-white">
                                                        <x-store.price :cents="$line->subtotal" />
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-4 space-y-2 border-t border-gray-800 pt-3 text-sm">
                                            <div class="flex justify-between text-gray-400">
                                                <span>Subtotal</span>
                                                <x-store.price :cents="$totals['subtotal']" />
                                            </div>
                                            <div class="flex justify-between text-gray-400">
                                                <span>IVA</span>
                                                <x-store.price :cents="$totals['taxes']" />
                                            </div>
                                            <div class="flex justify-between text-gray-400">
                                                <span>Envío</span>
                                                <span>
                                                    @if ($shippingCost > 0)
                                                        <x-store.price :cents="$shippingCost" />
                                                    @else
                                                        Gratis
                                                    @endif
                                                </span>
                                            </div>
                                            @if ($discount > 0)
                                                <div class="flex justify-between text-green-400">
                                                    <span>Descuento ({{ $paymentLabel }})</span>
                                                    <span>- <x-store.price :cents="$discount" /></span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between border-t border-gray-800 pt-3 text-base font-bold text-white">
                                                <span>Total</span>
                                                <x-store.price :cents="$grandTotal" />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-700 bg-gray-800/60 p-4">
                                            <input type="checkbox"
                                                   wire:model.live="acceptedTerms"
                                                   class="mt-0.5 rounded border-gray-700 bg-gray-800 text-orange-500 focus:ring-orange-500">
                                            <span class="text-sm leading-relaxed text-gray-300">
                                                Acepto los <span class="font-semibold text-white underline">términos y condiciones</span> y la
                                                <span class="font-semibold text-white underline">política de privacidad</span>, y confirmo que los datos ingresados son correctos.
                                            </span>
                                        </label>
                                        @error('acceptedTerms') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-800 px-4 py-3 sm:px-5">
                            <button type="button"
                                    wire:click="previousStep"
                                    @disabled($checkoutStep === 1)
                                    class="rounded-lg border border-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:border-gray-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-40">
                                ← Anterior
                            </button>

                            <div class="flex items-center gap-3">
                                <button type="button"
                                        wire:click="closeCheckout"
                                        class="rounded-lg px-3 py-2.5 text-sm text-gray-400 hover:text-white">
                                    Cancelar
                                </button>

                                @if ($checkoutStep < 4)
                                    <button type="submit"
                                            class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-50">
                                        @if ($checkoutStep === 3 && $checkoutPayment === 'mercadopago')
                                            Ir a Mercado Pago →
                                        @else
                                            Siguiente →
                                        @endif
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="processCheckout"
                                            @disabled(! $acceptedTerms)
                                            class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-50">
                                        Confirmar compra
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal de login para invitados: sólo un cliente registrado puede
         finalizar la compra. El carrito se conserva en la sesión. --}}
    @if ($showLoginModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm animate-fade-in">
            <div class="relative w-full max-w-md rounded-2xl border border-gray-700 bg-gray-900 p-6 text-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/20 text-orange-400">
                            🔐
                        </span>
                        <h3 class="text-lg font-bold text-white">Ingresá para finalizar tu compra</h3>
                    </div>

                    <button type="button"
                            wire:click="closeLoginModal"
                            aria-label="Cerrar"
                            class="text-gray-400 hover:text-white">
                        ✕
                    </button>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-gray-300">
                    Para completar el pedido necesitás iniciar sesión con tu cuenta o registrarte.
                    Tu carrito se conserva.
                </p>

                <form wire:submit.prevent="login" class="mt-5 space-y-4">
                    <div>
                        <label for="loginEmail" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Correo electrónico
                        </label>
                        <input id="loginEmail"
                               type="email"
                               wire:model="form.email"
                               autocomplete="username"
                               placeholder="tu@correo.com"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('form.email')
                            <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="loginPassword" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Contraseña
                        </label>
                        <input id="loginPassword"
                               type="password"
                               wire:model="form.password"
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('form.password')
                            <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-xs text-gray-400">
                            <input type="checkbox"
                                   wire:model="form.remember"
                                   class="rounded border-gray-700 bg-gray-800 text-orange-500 focus:ring-orange-500">
                            Recordarme
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-gray-400 underline hover:text-orange-400">
                                Olvidé mi contraseña
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-orange-500 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400">
                        Iniciar sesión
                    </button>
                </form>

                <div class="mt-5 border-t border-gray-800 pt-4 text-center">
                    <p class="text-xs text-gray-400">¿Todavía no tenés cuenta?</p>

                    <a href="{{ route('register', ['redirect' => 'carrito']) }}"
                       class="mt-2 inline-flex w-full items-center justify-center rounded-lg border border-orange-500/60 bg-orange-500/10 px-4 py-2.5 text-sm font-bold text-orange-400 transition hover:bg-orange-500/20">
                        Crear una cuenta
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
