<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\stories\StoriesController;

// Rotas para o gerenciamento de stories
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/stories', [StoriesController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [StoriesController::class, 'create'])->name('stories.create');
    Route::post('/stories', [StoriesController::class, 'store'])->name('stories.store');
    Route::get('/stories/{id}', [StoriesController::class, 'show'])->name('stories.show');
    Route::delete('/stories/{id}', [StoriesController::class, 'destroy'])->name('stories.destroy');
    Route::get('/user/{username}/stories', [StoriesController::class, 'userStories'])->name('stories.user');
});
