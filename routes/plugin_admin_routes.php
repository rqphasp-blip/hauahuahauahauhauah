<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PluginController;

Route::middleware(['web', 'auth', 'admin']) // Supondo middlewares comuns para admin. Ajuste conforme sua configuração.
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('plugins', PluginController::class)->except(['show']);
        Route::post('plugins/{plugin}/activate', [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
    });

