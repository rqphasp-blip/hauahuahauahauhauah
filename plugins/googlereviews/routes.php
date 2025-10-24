<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\googlereviews\GooglereviewsController;

// Rotas para o gerenciamento de avaliações do Google
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/googlereviews', [GooglereviewsController::class, 'index'])->name('googlereviews.index');
    Route::get('/googlereviews/create', [GooglereviewsController::class, 'create'])->name('googlereviews.create');
    Route::post('/googlereviews', [GooglereviewsController::class, 'store'])->name('googlereviews.store');
    Route::get('/googlereviews/{id}', [GooglereviewsController::class, 'show'])->name('googlereviews.show');
    Route::put('/googlereviews/{id}', [GooglereviewsController::class, 'update'])->name('googlereviews.update');
    Route::delete('/googlereviews/{id}', [GooglereviewsController::class, 'destroy'])->name('googlereviews.destroy');
    Route::get('/googlereviews/widget/{place_id}', [GooglereviewsController::class, 'widget'])->name('googlereviews.widget');
});
