<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\leads01\Leads01Controller;

Route::prefix('leads01')
    ->name('leads01.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', [Leads01Controller::class, 'index'])->name('index');
        Route::get('/create', [Leads01Controller::class, 'create'])->name('create');
        Route::post('/', [Leads01Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [Leads01Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [Leads01Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [Leads01Controller::class, 'destroy'])->name('destroy');

        Route::get('/{id}/leads', [Leads01Controller::class, 'leads'])->name('leads');
        Route::get('/{id}/leads/{entryId}', [Leads01Controller::class, 'showLead'])->name('leads.show');
		 Route::post('/leads01/{id}/toggle-visible', [Leads01Controller::class, 'toggleVisible'])
        ->name('leads01.campaign.toggle-visible');
    });

