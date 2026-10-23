@php
    $pageName = $paginator->getPageName();
    $lastPage = (int) $paginator->lastPage();

    // Ventana corta alrededor de la página actual (máximo 7 páginas).
    $start = max(2, $paginator->currentPage() - 2);
    $end = min($lastPage - 1, $paginator->currentPage() + 2);

    $pages = range($start, $end);

    $anchorClasses = 'inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
@endphp

{{-- Paginación híbrida: los anchors hacen funcionar los links sin JavaScript
     (GET con los filtros en la query string) y `wire:click.prevent` intercepta
     el click para navegar entre páginas sin recargar (ADR-0016). --}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación del catálogo" class="flex items-center justify-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="{{ $anchorClasses }} cursor-not-allowed text-gray-400 dark:text-gray-600" aria-disabled="true" onclick="return false">← Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               wire:click.prevent="previousPage('{{ $pageName }}')"
               rel="prev"
               aria-label="Página anterior"
               class="{{ $anchorClasses }} text-gray-400 dark:text-gray-600">← Anterior</a>
        @endif

        @if ($paginator->currentPage() !== 1)
            <a href="{{ $paginator->url(1) }}"
               wire:click.prevent="gotoPage(1, '{{ $pageName }}')"
               class="{{ $anchorClasses }}">1</a>
        @endif

        @if ($start > 2)
            <span class="px-1 text-sm text-gray-400 dark:text-gray-600">…</span>
        @endif

        @foreach ($pages as $page)
            @if ($page === $paginator->currentPage())
                <span aria-current="page" class="{{ $anchorClasses }} cursor-default border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-600 dark:border-indigo-600 dark:bg-indigo-600">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}"
                   wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')"
                   class="{{ $anchorClasses }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($end < $lastPage - 1)
            <span class="px-1 text-sm text-gray-400 dark:text-gray-600">…</span>
        @endif

        @if ($lastPage > 1 && $paginator->currentPage() !== $lastPage)
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
            <span class="{{ $anchorClasses }} cursor-not-allowed text-gray-400 dark:text-gray-600" aria-disabled="true" onclick="return false">Siguiente →</span>
        @endif
    </nav>
@endif
