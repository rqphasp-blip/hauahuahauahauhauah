<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\gallery\GalleryController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/create', [GalleryController::class, 'create'])->name('gallery.create');
    Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
    Route::get('/gallery/{id}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::get('/user/{username}/gallery', [GalleryController::class, 'userGallery'])->name('gallery.user');
});