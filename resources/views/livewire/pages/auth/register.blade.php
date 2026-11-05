<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.store', ['title' => 'Crear cuenta', 'description' => 'Creá tu cuenta en la tienda para finalizar tu compra.'])] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Origen del registro. Vale `carrito` cuando se llegó desde el checkout
     * para volver allí al terminar.
     */
    #[Url(as: 'redirect')]
    public string $redirectTo = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        // Si el registro se inició desde el carrito, se vuelve allí con el
        // checkout abierto para que el cliente termine la compra.
        if ($this->redirectTo === 'carrito') {
            session()->flash('open_checkout', true);

            $this->redirect(route('carrito.index', absolute: false), navigate: false);

            return;
        }

        $this->redirect(route('productos.index', absolute: false), navigate: false);
    }
}; ?>

<div class="mx-auto w-full max-w-md px-4 py-12 sm:px-6">
    <div class="rounded-2xl border border-gray-800 bg-gray-900/90 p-6 shadow-2xl sm:p-8">
        <div class="text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 text-2xl font-black text-white shadow-md">
                ⚡
            </span>

            <h1 class="mt-4 text-2xl font-black uppercase tracking-tight text-white">
                Crear cuenta
            </h1>

            <p class="mt-2 text-sm text-gray-400">
                Registrate para comprar y hacer seguimiento de tus pedidos.
            </p>
        </div>

        @if ($redirectTo === 'carrito')
            <div class="mt-5 rounded-lg border border-orange-500/30 bg-orange-500/10 p-3 text-xs leading-relaxed text-orange-300">
                Estás a un paso de terminar tu compra. Creá tu cuenta y tu carrito se conserva.
            </div>
        @endif

        <form wire:submit="register" class="mt-6 space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Nombre y apellido
                </label>
                <input id="name"
                       type="text"
                       wire:model="name"
                       required
                       autofocus
                       autocomplete="name"
                       placeholder="Ej. Juan Pérez"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                @error('name')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Correo electrónico
                </label>
                <input id="email"
                       type="email"
                       wire:model="email"
                       required
                       autocomplete="username"
                       placeholder="tu@correo.com"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                @error('email')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Contraseña
                </label>
                <input id="password"
                       type="password"
                       wire:model="password"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                @error('password')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                    Repetir contraseña
                </label>
                <input id="password_confirmation"
                       type="password"
                       wire:model="password_confirmation"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="mt-1 w-full rounded-lg border border-gray-700 bg-gray-800 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                @error('password_confirmation')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-orange-500 py-2.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400">
                Crear cuenta
            </button>
        </form>

        <p class="mt-6 border-t border-gray-800 pt-4 text-center text-xs text-gray-400">
            ¿Ya tenés cuenta?
            <a href="{{ route('login') }}" class="font-semibold text-orange-400 hover:underline">
                Iniciá sesión
            </a>
        </p>
    </div>
</div>
