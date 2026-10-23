@props(['inStock', 'stock' => 0, 'variant' => 'text'])

{{-- Disponibilidad de stock con dos variantes:
     - `text`: una línea corta ("Disponible" / "Sin stock") para la card.
     - `pill`: una insignia con la cantidad exacta para la ficha. --}}

@if ($inStock)
    <span @if ($variant === 'pill')
            class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
        @else
            class="text-xs font-medium text-green-600 dark:text-green-400"
        @endif>
        @if ($variant === 'pill')
            {{ (int) $stock }} unidades disponibles
        @else
            Disponible
        @endif
    </span>
@else
    <span @if ($variant === 'pill')
            class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
        @else
            class="text-xs font-medium text-red-600 dark:text-red-400"
        @endif>
        Sin stock
    </span>
@endif
