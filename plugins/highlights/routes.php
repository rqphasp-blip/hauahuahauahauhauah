<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\highlights\HighlightsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/highlights', [HighlightsController::class, 'index'])->name('highlights.index');
    Route::get('/highlights/create', [HighlightsController::class, 'create'])->name('highlights.create');
    Route::post('/highlights', [HighlightsController::class, 'store'])->name('highlights.store');
    Route::get('/highlights/{id}', [HighlightsController::class, 'show'])->name('highlights.show');
    Route::delete('/highlights/{id}', [HighlightsController::class, 'destroy'])->name('highlights.destroy');
});