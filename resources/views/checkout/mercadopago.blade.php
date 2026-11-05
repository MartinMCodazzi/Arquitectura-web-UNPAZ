<x-store-layout
    title="Mercado Pago"
    description="Completá el pago de tu compra con Mercado Pago."
    :canonical="route('checkout.mercadopago')">

    <div class="mx-auto max-w-lg px-4 py-10 sm:px-6">
        <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="bg-[#009EE3] px-6 py-5 text-white">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-2xl font-black tracking-tight">Mercado Pago</p>
                        <p class="text-xs text-white/80">Pago seguro</p>
                    </div>
                    <span class="text-3xl">💳</span>
                </div>
            </div>

            <div class="p-6 text-gray-900">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total a pagar</p>
                <p class="mt-1 text-3xl font-black text-gray-900">
                    <x-store.price :cents="$checkout['amount'] ?? 0" />
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Compra a nombre de {{ $checkout['checkout_name'] ?? 'cliente' }}
                </p>

                <div class="mt-5 space-y-2 border-t border-gray-200 pt-4 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Productos</span>
                        <span><x-store.price :cents="$checkout['subtotal'] ?? 0" /></span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>IVA</span>
                        <span><x-store.price :cents="$checkout['taxes'] ?? 0" /></span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Envío</span>
                        <span>
                            @if (($checkout['shipping_cost'] ?? 0) > 0)
                                <x-store.price :cents="$checkout['shipping_cost']" />
                            @else
                                Gratis
                            @endif
                        </span>
                    </div>

                    @if (($checkout['discount'] ?? 0) > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Descuento</span>
                            <span>- <x-store.price :cents="$checkout['discount']" /></span>
                        </div>
                    @endif
                </div>

                <div class="mt-4 rounded-lg bg-gray-50 p-3 text-xs leading-relaxed text-gray-500">
                    {{ $checkout['shipping_summary'] ?? '' }}
                </div>

                <form method="POST" action="{{ route('checkout.mercadopago.confirm') }}" class="mt-5">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-lg bg-[#009EE3] py-3 text-sm font-bold uppercase tracking-wide text-white shadow-md transition hover:bg-[#008FCC] focus:outline-none focus:ring-2 focus:ring-[#009EE3] focus:ring-offset-2">
                        Pagar con Mercado Pago
                    </button>
                </form>

                <form method="POST" action="{{ route('checkout.mercadopago.cancel') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-lg border border-gray-300 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                        Cancelar y volver a la tienda
                    </button>
                </form>

                <p class="mt-4 text-center text-[11px] text-gray-400">
                    Simulación de pago: no se realiza un cobro real.
                </p>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">
            Al finalizar volvés automáticamente a Tienda Online.
        </p>
    </div>

</x-store-layout>
