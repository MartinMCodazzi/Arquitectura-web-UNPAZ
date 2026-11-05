@php
    $pageName = $paginator->getPageName();
    $currentPage = (int) $paginator->currentPage();
    $lastPage = (int) $paginator->lastPage();

    // Ventana corta alrededor de la página actual (máximo 5 números).
    $windowStart = max(1, $currentPage - 2);
    $windowEnd = min($lastPage, $currentPage + 2);
    $pages = range($windowStart, $windowEnd);

    // La primera y la última se muestran aparte cuando quedan fuera de la ventana.
    $showFirst = ! in_array(1, $pages, true);
    $showLast = ! in_array($lastPage, $pages, true);

    $baseClasses = 'inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition';

    $anchorClasses = $baseClasses.' border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';

    // La página actual se arma con sus propios colores: así no compite con los
    // del enlace base y el número siempre queda visible.
    $currentClasses = $baseClasses.' cursor-default border-orange-500 bg-orange-500 font-bold text-white shadow-md';

    $disabledClasses = $baseClasses.' cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-600';
@endphp

{{-- Paginación híbrida: los anchors hacen funcionar los links sin JavaScript
     (GET con los filtros en la query string) y `wire:click.prevent` intercepta
     el click para navegar entre páginas sin recargar (ADR-0016). --}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación del catálogo" class="flex flex-wrap items-center justify-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="{{ $disabledClasses }}" aria-disabled="true" onclick="return false">← Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               wire:click.prevent="previousPage('{{ $pageName }}')"
               rel="prev"
               aria-label="Página anterior"
               class="{{ $anchorClasses }}">← Anterior</a>
        @endif

        @if ($showFirst)
            <a href="{{ $paginator->url(1) }}"
               wire:click.prevent="gotoPage(1, '{{ $pageName }}')"
               class="{{ $anchorClasses }}">1</a>

            @if ($windowStart > 2)
                <span class="px-1 text-sm text-gray-400 dark:text-gray-600">…</span>
            @endif
        @endif

        @foreach ($pages as $page)
            @if ($page === $currentPage)
                <span aria-current="page" class="{{ $currentClasses }}">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}"
                   wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')"
                   class="{{ $anchorClasses }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($showLast)
            @if ($windowEnd < $lastPage - 1)
                <span class="px-1 text-sm text-gray-400 dark:text-gray-600">…</span>
            @endif

            <a href="{{ $paginator->url($lastPage) }}"
               wire:click.prevent="gotoPage({{ $lastPage }}, '{{ $pageName }}')"
               class="{{ $anchorClasses }}">{{ $lastPage }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               wire:click.prevent="nextPage('{{ $pageName }}')"
               rel="next"
               aria-label="Página siguiente"
               class="{{ $anchorClasses }}">Siguiente →</a>
        @else
            <span class="{{ $disabledClasses }}" aria-disabled="true" onclick="return false">Siguiente →</span>
        @endif
    </nav>
@endif
