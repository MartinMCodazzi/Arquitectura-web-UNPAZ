<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Account\AccountPage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas de la tienda
|--------------------------------------------------------------------------
|
| El catálogo se renderiza siempre en servidor y los filtros viajan en la
| query string, de modo que cada combinación es indexable y cacheable
| (ver ADR-0006). El detalle de producto se direcciona por *slug*.
|
*/

Route::redirect('/', '/productos', 301);

Route::get('/productos', [ProductController::class, 'index'])
    ->name('productos.index');

Route::get('/productos/{product:slug}', [ProductController::class, 'show'])
    ->name('productos.show');

Route::get('/categorias/{category:slug}', function (Category $category) {
    abort_unless($category->is_active, 404);

    return redirect()->route('productos.index', ['categoria' => $category->slug], 301);
})->name('categorias.show');

/*
|--------------------------------------------------------------------------
| Carrito de compras
|--------------------------------------------------------------------------
|
| El carrito vive en la sesión (ver ADR-0004) y se manipula con formularios
| HTML contra endpoints HTTP: el sitio público no depende de JavaScript.
|
*/

Route::get('/carrito', [CartController::class, 'index'])
    ->name('carrito.index');

Route::post('/carrito/agregar', [CartController::class, 'store'])
    ->name('carrito.agregar');

Route::patch('/carrito/{product}', [CartController::class, 'update'])
    ->name('carrito.actualizar');

Route::delete('/carrito/{product}', [CartController::class, 'destroy'])
    ->name('carrito.eliminar');

Route::delete('/carrito', [CartController::class, 'clear'])
    ->name('carrito.vaciar');

/*
|--------------------------------------------------------------------------
| Pago simulado con Mercado Pago
|--------------------------------------------------------------------------
|
| Sin credenciales de Mercado Pago, el flujo reproduce la redirección y la
| vuelta al sitio. El resumen del checkout viaja en la sesión.
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/checkout/mercadopago', [MercadoPagoController::class, 'show'])
        ->name('checkout.mercadopago');

    Route::post('/checkout/mercadopago/pagar', [MercadoPagoController::class, 'confirm'])
        ->name('checkout.mercadopago.confirm');

    Route::post('/checkout/mercadopago/cancelar', [MercadoPagoController::class, 'cancel'])
        ->name('checkout.mercadopago.cancel');
});

/*
|--------------------------------------------------------------------------
| Miniaturas de producto
|--------------------------------------------------------------------------
|
| Las miniaturas se sirven por HTTP (y no como archivos estáticos) para poder
| enviar un Cache-Control largo: el nombre del archivo incluye la versión de
| la imagen original, así que una imagen nueva publica una URL nueva.
|
*/

Route::get('/imagenes/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('imagenes.show');

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
|
| El sitemap XML y el robots.txt se generan en la aplicación: el sitemap se
| arma con el catálogo cacheado y se invalida con la versión del catálogo.
|
*/

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::get('/robots.txt', RobotsController::class)
    ->name('robots');

Route::get('/mi-cuenta', AccountPage::class)
    ->middleware('auth')
    ->name('mi-cuenta');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('productos.index');
})->middleware('auth')->name('logout');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
