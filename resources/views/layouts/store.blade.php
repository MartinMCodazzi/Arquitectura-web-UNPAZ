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
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('productos.*') ? 'bg-orange-500/15 text-orange-400 border border-orange-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                                    Catálogo Gamer
                                </a>
                                <a href="{{ route('productos.index', ['categoria' => 'electronica']) }}"
                                   class="px-3 py-1.5 rounded-lg text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800 transition">
                                    Hardware & Accesorios
                                </a>
                            </nav>
                        </div>

                        <div class="flex items-center gap-4">
                            {{-- Contador de carrito reactivo con estilo gamer --}}
                            <livewire:cart.cart-counter />

                            @auth
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-lg border border-gray-700 transition">
                                    Mi Cuenta
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-bold uppercase tracking-wider text-orange-400 hover:text-orange-300 bg-orange-500/10 hover:bg-orange-500/20 px-3.5 py-2 rounded-lg border border-orange-500/30 transition">
                                    Ingresar
                                </a>
                            @endauth
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
