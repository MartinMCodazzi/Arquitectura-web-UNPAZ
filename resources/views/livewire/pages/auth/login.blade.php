<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.store', ['title' => 'Iniciar sesión', 'description' => 'Ingresá a tu cuenta para finalizar tu compra.'])] class extends Component
{
    public LoginForm $form;

    /**
     * Origen del login. Vale `carrito` cuando se llegó desde el checkout para
     * volver allí y terminar la compra.
     */
    #[Url(as: 'redirect')]
    public string $redirectTo = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Si el login se inició desde el carrito, se vuelve allí con el
        // checkout abierto para que el cliente complete la compra.
        if ($this->redirectTo === 'carrito') {
            session()->flash('open_checkout', true);

            $this->redirect(route('carrito.index', absolute: false), navigate: false);

            return;
        }

        $intended = session()->pull('url.intended');

        if (! $intended || str_contains($intended, '/dashboard')) {
            $intended = route('productos.index', absolute: false);
        }

        $this->redirect($intended, navigate: false);
    }
}; ?>

<div class="mx-auto w-full max-w-md px-4 py-12 sm:px-6">
    <div class="rounded-2xl border border-gray-800 bg-gray-900/90 p-6 shadow-2xl sm:p-8">
        <div class="text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 text-2xl font-black text-white shadow-md">
                ⚡
            </span>

            <h1 class="mt-4 text-2xl font-black uppercase tracking-tight text-white">
                Iniciar sesión
            </h1>

            <p class="mt-2 text-sm text-gray-400">
                Ingresá con tu cuenta para comprar y seguir tus pedidos.
            </p>
        </div>

        @if ($redirectTo === 'carrito')
            <div class="mt-5 rounded-lg border border-orange-500/30 bg-orange-500/10 p-3 text-xs leading-relaxed text-orange-300">
                Estás a un paso de terminar tu compra. Ingresá y tu carrito se conserva.
            </div>
        @endif

        <x-auth-session-status class="mt-4 text-sm text-green-400" :status="session('status')" />

        <form wire:submit="login" class="mt-6 space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Correo electrónico
                </label>
                <input id="email"
                       type="email"
                       wire:model="form.email"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="tu@correo.com"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                @error('form.email')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Contraseña
                </label>
                <input id="password"
                       type="password"
                       wire:model="form.password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
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
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-gray-400 underline hover:text-orange-400">
                        Olvidé mi contraseña
                    </a>
                @endif
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-orange-500 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400">
                Iniciar sesión
            </button>
        </form>

        <p class="mt-6 border-t border-gray-800 pt-4 text-center text-xs text-gray-400">
            ¿Todavía no tenés cuenta?
            <a href="{{ route('register', $redirectTo ? ['redirect' => $redirectTo] : []) }}"
               class="font-semibold text-orange-400 hover:underline">
                Registrate
            </a>
        </p>
    </div>
</div>
