<x-store-layout
    title="Catálogo de productos"
    description="Explorá el catálogo completo de productos disponibles, con filtros por categoría, precio y disponibilidad."
    :canonical="$canonical"
    :image="$image">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Catálogo</h1>

        {{-- Catálogo reactivo (ADR-0016): filtros, grilla y paginación en el
             componente Livewire; la query string queda canónica y la versión
             sin JavaScript conserva el redirect canónico de ADR-0011. --}}
        <livewire:catalog.catalogo />
    </div>
</x-store-layout>
