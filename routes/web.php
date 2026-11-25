<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NovaPoshtaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalog', CatalogController::class)->name('catalog');
Route::get('/product/{product:slug}', ProductController::class)->name('product');

Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
// лимит — защита от спама заказами (каждый заказ уходит в dropshop-core)
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/checkout/done', [CheckoutController::class, 'done'])->name('checkout.done');

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/np/cities', [NovaPoshtaController::class, 'cities'])->name('np.cities');
    Route::get('/np/points', [NovaPoshtaController::class, 'points'])->name('np.points');
});

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
