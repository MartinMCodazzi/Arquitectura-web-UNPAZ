<?php

namespace App\View\Components;

use App\Services\CartService;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout público de la tienda (sin autenticación).
 */
class StoreLayout extends Component
{
    /**
     * Título de la página.
     */
    public ?string $title;

    /**
     * Meta descripción de la página.
     */
    public ?string $description;

    /**
     * Título completo (incluye el nombre de la tienda).
     */
    public string $fullTitle;

    /**
     * URL canónica de la página.
     */
    public string $canonical;

    /**
     * Imagen para OpenGraph (imagen destacada).
     */
    public ?string $image;

    /**
     * Tipo de contenido OpenGraph (`website` o `product`).
     */
    public string $type;

    /**
     * @param  string|null  $title  Título de la página.
     * @param  string|null  $description  Meta descripción de la página.
     * @param  string|null  $canonical  URL canónica; por defecto, la URL actual.
     * @param  string|null  $image  Imagen destacada para OpenGraph.
     * @param  string  $type  Tipo de contenido OpenGraph.
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $canonical = null,
        ?string $image = null,
        string $type = 'website',
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->fullTitle = $title ? $title.' · '.config('app.name') : config('app.name');
        $this->canonical = $canonical ?? request()->url();
        $this->image = $image;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.store', [
            'cartCount' => app(CartService::class)->count(),
        ]);
    }
}
