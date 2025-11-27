<?php

use App\Providers\plugins\linktreeimport\LinktreeImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/importar-perfil', [LinktreeImportController::class, 'index'])->name('profile-import.index');
    Route::post('/importar-perfil', [LinktreeImportController::class, 'store'])->name('profile-import.store');
    Route::post('/importar-perfil/limpar', [LinktreeImportController::class, 'clear'])->name('profile-import.clear');
});
