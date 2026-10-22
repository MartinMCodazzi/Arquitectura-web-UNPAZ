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

        <!-- El sitio público se renderiza entero en servidor y funciona sin
             JavaScript (ADR-0011 y ADR-0012): acá sólo se carga el CSS. -->
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
            <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center gap-8">
                            <a href="{{ route('productos.index') }}" class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ config('app.name') }}
                            </a>

                            <nav class="hidden space-x-6 sm:flex">
                                <a href="{{ route('productos.index') }}"
                                   class="text-sm font-medium {{ request()->routeIs('productos.*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                    Catálogo
                                </a>
                            </nav>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('carrito.index') }}"
                               class="relative flex items-center gap-2 text-sm font-medium {{ request()->routeIs('carrito.*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                <span>Carrito</span>

                                @if ($cartCount > 0)
                                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('productos.index') }}" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Iniciar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            @if (session('success') || session('warning') || session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="space-y-2">
                        @if (session('success'))
                            <div role="status"
                                 class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/40 dark:text-green-200">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('warning'))
                            <div role="status"
                                 class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 dark:border-yellow-900 dark:bg-yellow-900/40 dark:text-yellow-200">
                                {{ session('warning') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div role="alert"
                                 class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-900/40 dark:text-red-200">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ config('app.name') }} — Trabajo práctico de arquitectura web en Laravel.
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
