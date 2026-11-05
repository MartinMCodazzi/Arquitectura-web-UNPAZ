@php
    // Valores por defecto para que este layout también pueda usarse como
    // layout de una página Livewire/Volt (por ejemplo, registro y login).
    $title = $title ?? null;
    $description = $description ?? null;
    $canonical = $canonical ?? url()->current();
    $image = $image ?? null;
    $type = $type ?? 'website';
    $fullTitle = $fullTitle ?? ($title ? $title.' · '.config('app.name') : config('app.name'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $fullTitle }}</title>

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        {{-- URL canónica: una sola URL por página (ADR-0014). --}}
        <link rel="canonical" href="{{ $canonical }}">

        {{-- OpenGraph y Twitter Card: cómo se ve la página cuando se comparte. --}}
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $fullTitle }}">
        <meta property="og:type" content="{{ $type }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif

        @if ($image)
            <meta property="og:image" content="{{ $image }}">
            <meta property="og:image:type" content="image/webp">
            <meta property="og:image:width" content="800">
            <meta property="og:image:height" content="800">
        @endif

        <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $fullTitle }}">

        @if ($description)
            <meta name="twitter:description" content="{{ $description }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- El sitio público no carga resources/js/app.js (ADR-0016): la
             reactividad la aporta Livewire con sus propios assets, que se
             cargan al cierre del body con @livewireScripts. --}}
        @vite(['resources/css/app.css'])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-950 text-gray-100">
        <div class="min-h-screen bg-gray-950 flex flex-col selection:bg-orange-500 selection:text-white">
            {{-- Barra superior de promociones / cuotas estilo gamer --}}
            <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 py-1.5 px-4 text-center text-xs font-bold text-white tracking-wide shadow-inner">
                🚀 ENVÍOS A TODO EL PAÍS · 10% OFF POR TRANSFERENCIA · HASTA 3 CUOTAS SIN INTERÉS
            </div>

            <header class="bg-gray-900/95 backdrop-blur-md border-b border-gray-800 sticky top-0 z-40 shadow-lg">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 gap-4">
                        <div class="flex items-center gap-6">
                            <a href="{{ route('productos.index') }}" class="flex items-center gap-2 group">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 text-white font-black text-xl shadow-md group-hover:scale-105 transition transform">
                                    ⚡
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-xl font-black tracking-wider text-white uppercase group-hover:text-orange-400 transition">
                                        {{ config('app.name', 'GamerStore') }}
                                    </span>
                                    <span class="text-[10px] uppercase font-bold tracking-widest text-orange-500 -mt-1">Hardware & Tech</span>
                                </div>
                            </a>

                            <nav class="hidden md:flex items-center space-x-1">
                                <a href="{{ route('productos.index') }}"
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('productos.*') && ! request('categoria') ? 'bg-orange-500/15 text-orange-400 border border-orange-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                                    Catálogo Completo
                                </a>
                                <a href="{{ route('productos.index', ['categoria' => 'componentes-pc']) }}"
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request('categoria') === 'componentes-pc' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                                    Componentes PC
                                </a>
                                <a href="{{ route('productos.index', ['categoria' => 'perifericos-gamer']) }}"
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request('categoria') === 'perifericos-gamer' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                                    Periféricos
                                </a>
                                <a href="{{ route('productos.index', ['categoria' => 'consolas-y-gaming']) }}"
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request('categoria') === 'consolas-y-gaming' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                                    Consolas
                                </a>
                            </nav>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            @auth
                                {{-- Menú del cliente: icono + nombre con desplegable --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                                    <button type="button"
                                            @click="open = ! open"
                                            :aria-expanded="open.toString()"
                                            aria-haspopup="true"
                                            class="flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-800 px-2.5 py-2 text-xs font-semibold text-gray-200 transition hover:border-orange-500/60 hover:text-white">
                                        <svg class="h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0" />
                                        </svg>

                                        <span class="max-w-[7rem] truncate sm:max-w-[9rem]">{{ auth()->user()->first_name }}</span>

                                        <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         x-transition.origin.top.right
                                         class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-gray-700 bg-gray-900 shadow-2xl">
                                        <div class="border-b border-gray-800 px-4 py-3">
                                            <p class="truncate text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                                            <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                        </div>

                                        <a href="{{ route('mi-cuenta') }}"
                                           class="block px-4 py-2.5 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                            Mi cuenta
                                        </a>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                    class="block w-full px-4 py-2.5 text-left text-sm text-red-400 transition hover:bg-red-500/10">
                                                Cerrar Sesión
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                {{-- Acciones para visitantes: registrarse o iniciar sesión.
                                     El carrito queda siempre a la derecha. --}}
                                <a href="{{ route('register', request()->routeIs('carrito.*') ? ['redirect' => 'carrito'] : []) }}" class="text-xs font-bold uppercase tracking-wider text-white bg-orange-500 hover:bg-orange-600 px-3 py-2 rounded-lg border border-orange-500 shadow-sm transition">
                                    Registrate
                                </a>
                                <a href="{{ route('login', request()->routeIs('carrito.*') ? ['redirect' => 'carrito'] : []) }}" class="text-xs font-bold uppercase tracking-wider text-gray-200 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-lg border border-gray-700 transition">
                                    Inicia Sesión
                                </a>
                            @endauth

                            {{-- Contador de carrito reactivo con estilo gamer --}}
                            <livewire:cart.cart-counter />
                        </div>
                    </div>
                </div>
            </header>

            @if (session('success') || session('warning') || session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="space-y-2">
                        @if (session('success'))
                            <x-store.alert type="success" wire:key="flash-success">{{ session('success') }}</x-store.alert>
                        @endif

                        @if (session('warning'))
                            <x-store.alert type="warning" wire:key="flash-warning">{{ session('warning') }}</x-store.alert>
                        @endif

                        @if (session('error'))
                            <x-store.alert type="error" wire:key="flash-error">{{ session('error') }}</x-store.alert>
                        @endif
                    </div>
                </div>
            @endif

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500 text-white font-black text-lg">⚡</span>
                                <span class="text-lg font-black tracking-wider text-white uppercase">{{ config('app.name', 'GamerStore') }}</span>
                            </div>
                            <p class="mt-3 text-sm text-gray-400 max-w-sm">
                                Tu tienda de tecnología y hardware gamer de confianza. Armado de computadoras, componentes, placas de video y periféricos de alto rendimiento.
                            </p>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-white">Métodos de Pago</h4>
                            <ul class="mt-3 space-y-1 text-sm text-gray-400">
                                <li>💵 Transferencia bancaria (10% OFF)</li>
                                <li>💳 Tarjetas de Débito y Crédito</li>
                                <li>🔒 Checkout Seguro y Garantía oficial</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-white">Atención al Cliente</h4>
                            <ul class="mt-3 space-y-1 text-sm text-gray-400">
                                <li>📍 Envíos a todo el país</li>
                                <li>📦 Despachos en 24hs</li>
                                <li>✉️ soporte@gamerstore.local</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-8 border-t border-gray-800 pt-6 text-center text-xs text-gray-500">
                        {{ config('app.name') }} &copy; {{ date('Y') }} — Arquitectura Web UNPAZ. Precios expresados en moneda nacional ARS con IVA incluido.
                    </div>
                </div>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
