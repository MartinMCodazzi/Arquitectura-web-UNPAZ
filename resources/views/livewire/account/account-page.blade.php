<div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Datos del cliente --}}
    <section class="rounded-2xl border border-gray-800 bg-gray-900/90 p-6 shadow-xl sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-white shadow-lg">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0" />
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-white">Mi cuenta</h1>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ auth()->user()->name }} · {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            <a href="{{ route('productos.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-300 transition hover:border-orange-500 hover:text-orange-400">
                Seguir comprando
            </a>
        </div>
    </section>

    {{-- Domicilios --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black uppercase tracking-wide text-white">Domicilios</h2>
                <p class="mt-1 text-xs text-gray-500">Guardá tus direcciones de entrega y elegí la predeterminada.</p>
            </div>

            @unless ($showAddressForm)
                <button type="button"
                        wire:click="openAddressForm"
                        class="rounded-lg bg-orange-500 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-md transition hover:bg-orange-600">
                    {{ $addresses->isNotEmpty() ? 'Cambiar o agregar domicilio' : '+ Agregar domicilio' }}
                </button>
            @endunless
        </div>

        @if ($showAddressForm)
            <form wire:submit="saveAddress" class="mt-4 rounded-2xl border border-orange-500/30 bg-gray-900/90 p-5 shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-orange-400">
                        {{ $editingAddressId ? 'Cambiar domicilio' : 'Nuevo domicilio' }}
                    </h3>

                    <button type="button" wire:click="cancelAddressForm" class="text-xs text-gray-400 hover:text-white">
                        Cancelar
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="addressLabel" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Nombre del domicilio *</label>
                        <input id="addressLabel" type="text" wire:model="addressLabel" maxlength="40" placeholder="Casa, Trabajo…"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressLabel') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressRecipient" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Quien recibe *</label>
                        <input id="addressRecipient" type="text" wire:model="addressRecipient" maxlength="100" placeholder="Nombre y apellido"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressRecipient') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressPhone" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Teléfono *</label>
                        <input id="addressPhone" type="text" wire:model="addressPhone" maxlength="30" placeholder="11 2345 6789"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressPhone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label for="addressStreet" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Calle *</label>
                            <input id="addressStreet" type="text" wire:model="addressStreet" maxlength="120" placeholder="Av. Corrientes"
                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            @error('addressStreet') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="addressNumber" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Número *</label>
                            <input id="addressNumber" type="text" wire:model="addressNumber" maxlength="20" placeholder="1234"
                                   class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            @error('addressNumber') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="addressFloor" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Piso / depto (opcional)</label>
                        <input id="addressFloor" type="text" wire:model="addressFloor" maxlength="20" placeholder="3° B"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressFloor') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressCity" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Ciudad *</label>
                        <input id="addressCity" type="text" wire:model="addressCity" maxlength="80" placeholder="CABA"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressCity') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressProvince" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Provincia *</label>
                        <input id="addressProvince" type="text" wire:model="addressProvince" maxlength="80" placeholder="Buenos Aires"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressProvince') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressPostalCode" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Código postal *</label>
                        <input id="addressPostalCode" type="text" wire:model="addressPostalCode" maxlength="10" placeholder="1043"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('addressPostalCode') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="addressNotes" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Referencias (opcional)</label>
                        <textarea id="addressNotes" wire:model="addressNotes" rows="2" maxlength="500" placeholder="Timbre, entre calles, horarios…"
                                  class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                        @error('addressNotes') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-800 pt-4">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-300">
                        <input type="checkbox" wire:model="addressDefault" class="rounded border-gray-700 bg-gray-800 text-orange-500 focus:ring-orange-500">
                        Usar como domicilio predeterminado
                    </label>

                    <button type="submit"
                            class="rounded-lg bg-orange-500 px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-white shadow-md transition hover:bg-orange-600">
                        Guardar domicilio
                    </button>
                </div>
            </form>
        @endif

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            @forelse ($addresses as $address)
                <article wire:key="address-{{ $address->id }}" class="rounded-xl border border-gray-800 bg-gray-900/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-white">{{ $address->label }}</h3>

                                @if ($address->is_default)
                                    <span class="rounded-full bg-orange-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-orange-400">
                                        Predeterminado
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-gray-300">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                            <p class="mt-1 text-sm text-gray-400">{{ $address->fullAddress() }}</p>

                            @if ($address->notes)
                                <p class="mt-1 text-xs text-gray-500">{{ $address->notes }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-800 pt-3">
                        <button type="button" wire:click="editAddress({{ $address->id }})"
                                class="rounded-md border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-orange-500 hover:text-orange-400">
                            Cambiar
                        </button>

                        @unless ($address->is_default)
                            <button type="button" wire:click="setDefaultAddress({{ $address->id }})"
                                    class="rounded-md border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-orange-500 hover:text-orange-400">
                                Predeterminar
                            </button>
                        @endunless

                        <button type="button" wire:click="deleteAddress({{ $address->id }})"
                                wire:confirm="¿Eliminar el domicilio {{ $address->label }}?"
                                class="rounded-md border border-red-900/60 px-3 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-900/20">
                            Eliminar
                        </button>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 rounded-xl border border-dashed border-gray-700 bg-gray-900/40 p-6 text-center">
                    <p class="text-sm text-gray-400">Todavía no guardaste ningún domicilio.</p>
                    <button type="button" wire:click="openAddressForm" class="mt-2 text-xs font-semibold text-orange-400 hover:underline">
                        Agregar mi primer domicilio
                    </button>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Cuentas --}}
    <section class="mt-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black uppercase tracking-wide text-white">Mis cuentas</h2>
                <p class="mt-1 text-xs text-gray-500">Cuentas bancarias o billeteras para tus pagos y reintegros.</p>
            </div>

            @unless ($showAccountForm)
                <button type="button"
                        wire:click="openAccountForm"
                        class="rounded-lg bg-orange-500 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-md transition hover:bg-orange-600">
                    {{ $accounts->isNotEmpty() ? 'Cambiar o agregar cuenta' : '+ Agregar cuenta' }}
                </button>
            @endunless
        </div>

        @if ($showAccountForm)
            <form wire:submit="saveAccount" class="mt-4 rounded-2xl border border-orange-500/30 bg-gray-900/90 p-5 shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-orange-400">
                        {{ $editingAccountId ? 'Cambiar cuenta' : 'Nueva cuenta' }}
                    </h3>

                    <button type="button" wire:click="cancelAccountForm" class="text-xs text-gray-400 hover:text-white">
                        Cancelar
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="accountLabel" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Nombre de la cuenta *</label>
                        <input id="accountLabel" type="text" wire:model="accountLabel" maxlength="40" placeholder="Cuenta sueldo"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('accountLabel') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="accountType" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Tipo *</label>
                        <select id="accountType" wire:model.live="accountType"
                                class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            <option value="banco">Cuenta bancaria</option>
                            <option value="billetera">Billetera virtual</option>
                        </select>
                        @error('accountType') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="accountBank" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            {{ $accountType === 'billetera' ? 'Billetera' : 'Banco' }}
                        </label>
                        <input id="accountBank" type="text" wire:model="accountBank" maxlength="80" placeholder="Banco / billetera"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('accountBank') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="accountHolder" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Titular *</label>
                        <input id="accountHolder" type="text" wire:model="accountHolder" maxlength="100" placeholder="Nombre y apellido"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('accountHolder') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="accountDni" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">DNI del titular *</label>
                        <input id="accountDni" type="text" wire:model="accountDni" inputmode="numeric" maxlength="9" placeholder="20123456"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('accountDni') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="accountNumber" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">CBU o alias *</label>
                        <input id="accountNumber" type="text" wire:model="accountNumber" maxlength="60" placeholder="mi.alias.banco"
                               class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        @error('accountNumber') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-800 pt-4">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-300">
                        <input type="checkbox" wire:model="accountDefault" class="rounded border-gray-700 bg-gray-800 text-orange-500 focus:ring-orange-500">
                        Usar como cuenta predeterminada
                    </label>

                    <button type="submit"
                            class="rounded-lg bg-orange-500 px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-white shadow-md transition hover:bg-orange-600">
                        Guardar cuenta
                    </button>
                </div>
            </form>
        @endif

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            @forelse ($accounts as $account)
                <article wire:key="account-{{ $account->id }}" class="rounded-xl border border-gray-800 bg-gray-900/70 p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-white">{{ $account->label }}</h3>

                        <span class="rounded-full bg-gray-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ $account->typeLabel() }}
                        </span>

                        @if ($account->is_default)
                            <span class="rounded-full bg-orange-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-orange-400">
                                Predeterminada
                            </span>
                        @endif
                    </div>

                    <dl class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                        @if ($account->bank_name)
                            <dt class="text-gray-500">Banco / billetera</dt>
                            <dd class="text-right text-gray-300">{{ $account->bank_name }}</dd>
                        @endif

                        <dt class="text-gray-500">Titular</dt>
                        <dd class="text-right text-gray-300">{{ $account->holder_name }}</dd>

                        <dt class="text-gray-500">DNI</dt>
                        <dd class="text-right text-gray-300">{{ $account->holder_dni }}</dd>

                        <dt class="text-gray-500">CBU / alias</dt>
                        <dd class="truncate text-right text-gray-300">{{ $account->cbu_alias }}</dd>
                    </dl>

                    <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-800 pt-3">
                        <button type="button" wire:click="editAccount({{ $account->id }})"
                                class="rounded-md border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-orange-500 hover:text-orange-400">
                            Cambiar
                        </button>

                        @unless ($account->is_default)
                            <button type="button" wire:click="setDefaultAccount({{ $account->id }})"
                                    class="rounded-md border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-orange-500 hover:text-orange-400">
                                Predeterminar
                            </button>
                        @endunless

                        <button type="button" wire:click="deleteAccount({{ $account->id }})"
                                wire:confirm="¿Eliminar la cuenta {{ $account->label }}?"
                                class="rounded-md border border-red-900/60 px-3 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-900/20">
                            Eliminar
                        </button>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 rounded-xl border border-dashed border-gray-700 bg-gray-900/40 p-6 text-center">
                    <p class="text-sm text-gray-400">Todavía no guardaste ninguna cuenta.</p>
                    <button type="button" wire:click="openAccountForm" class="mt-2 text-xs font-semibold text-orange-400 hover:underline">
                        Agregar mi primera cuenta
                    </button>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Reservas --}}
    <section class="mt-10">
        <div>
            <h2 class="text-lg font-black uppercase tracking-wide text-white">Mis reservas</h2>
            <p class="mt-1 text-xs text-gray-500">Compras que dejaste reservadas en la tienda.</p>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($reservations as $reservation)
                <article wire:key="reservation-{{ $reservation->id }}" class="rounded-xl border border-gray-800 bg-gray-900/70 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-white">{{ $reservation->number }}</h3>
                                <span class="rounded-full bg-orange-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-orange-400">
                                    {{ $reservation->statusLabel() }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $reservation->created_at->format('d/m/Y H:i') }} · {{ $reservation->payment_label }}
                            </p>
                        </div>

                        <p class="text-lg font-black text-white">
                            <x-store.price :cents="$reservation->total" />
                        </p>
                    </div>

                    <div class="mt-3 space-y-1 border-t border-gray-800 pt-3">
                        @foreach ($reservation->items as $item)
                            <div class="flex items-start justify-between gap-3 text-xs">
                                <span class="text-gray-300">{{ $item->quantity }}× {{ $item->product_name }}</span>
                                <span class="shrink-0 text-gray-400">
                                    <x-store.price :cents="$item->subtotal" />
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-3 text-xs text-gray-500">{{ $reservation->shipping_summary }}</p>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-700 bg-gray-900/40 p-6 text-center">
                    <p class="text-sm text-gray-400">Todavía no tenés reservas.</p>
                    <a href="{{ route('productos.index') }}" class="mt-2 inline-block text-xs font-semibold text-orange-400 hover:underline">
                        Ver catálogo
                    </a>
                </div>
            @endforelse
        </div>
    </section>
</div>
