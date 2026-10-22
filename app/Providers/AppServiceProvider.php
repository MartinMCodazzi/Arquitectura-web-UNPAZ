<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En desarrollo falla la petición ante cualquier relación cargada de forma
        // perezosa: es la forma más barata de detectar problemas de N+1.
        // OJO: en Laravel 13 este guardia no cubre el modelo único (ADR-0009),
        // por eso el eager loading se hace siempre de forma explícita.
        Model::preventLazyLoading(! $this->app->isProduction());

        // La caché del catálogo se invalida desde los observadores (ADR-0008).
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
    }
}
