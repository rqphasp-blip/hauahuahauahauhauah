<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\instagramstories\InstagramStoriesImportController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/stories/import/instagram', [InstagramStoriesImportController::class, 'create'])->name('stories.instagram.import');
    Route::post('/stories/import/instagram', [InstagramStoriesImportController::class, 'store'])->name('stories.instagram.store');
});