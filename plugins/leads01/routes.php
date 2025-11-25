<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\leads01\Leads01Controller;

Route::middleware(['web', 'auth'])->prefix('leads01')->name('leads01.')->group(function () {
    Route::get('/', [Leads01Controller::class, 'index'])->name('index');
    Route::get('/create', [Leads01Controller::class, 'create'])->name('create');
    Route::post('/', [Leads01Controller::class, 'store'])->name('store');
    Route::get('/{id}/edit', [Leads01Controller::class, 'edit'])->name('edit');
    Route::post('/{id}', [Leads01Controller::class, 'update'])->name('update');
    Route::delete('/{id}', [Leads01Controller::class, 'destroy'])->name('destroy');

    Route::get('/{id}/fields', [Leads01Controller::class, 'fields'])->name('fields');
    Route::post('/{id}/fields', [Leads01Controller::class, 'saveFields'])->name('fields.save');

    Route::get('/{id}/leads', [Leads01Controller::class, 'leads'])->name('leads');
    Route::get('/{id}/leads/{entryId}', [Leads01Controller::class, 'lead'])->name('leads.show');
});

Route::middleware('web')->group(function () {
    Route::get('/leads01/form/{slug}', [Leads01Controller::class, 'display'])->name('leads01.display');
    Route::post('/leads01/form/{slug}', [Leads01Controller::class, 'submit'])->name('leads01.submit');
});