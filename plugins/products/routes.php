<?php

use App\Providers\plugins\products\ProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
        Route::get('/products/categories', [ProductsController::class, 'categories'])->name('products.categories.index');
        Route::post('/products/categories', [ProductsController::class, 'storeCategory'])->name('products.categories.store');
        Route::put('/products/categories/{id}', [ProductsController::class, 'updateCategory'])->name('products.categories.update');
        Route::post('/products', [ProductsController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{id}', [ProductsController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [ProductsController::class, 'destroyProduct'])->name('products.destroy');
        Route::post('/products/settings', [ProductsController::class, 'updateSettings'])->name('products.settings.update');
    });

    Route::get('/user/{username}/products', [ProductsController::class, 'publicCatalog'])->name('products.catalog');
    Route::get('/user/{username}/products/customer', [ProductsController::class, 'customerLookup'])->name('products.customer.lookup');
    Route::post('/user/{username}/products/orders', [ProductsController::class, 'storeOrder'])->name('products.orders.store');
});
