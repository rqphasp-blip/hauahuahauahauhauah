<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\leads\LeadsController;

// Rotas do plugin Leads
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadsController::class, 'create'])->name('leads.create');
    Route::post('/leads/store', [LeadsController::class, 'store'])->name('leads.store');
    Route::get('/leads/{id}/builder', [LeadsController::class, 'builder'])->name('leads.builder');
    Route::post('/leads/{id}/builder/save', [LeadsController::class, 'builderSave'])->name('leads.builder.save');
    Route::get('/leads/{id}/entries', [LeadsController::class, 'entries'])->name('leads.entries');
    Route::get('/leads/{id}/entries/{entryId}', [LeadsController::class, 'show'])->name('leads.show');
});



Route::get('/leads-debug', function () {
    return 'Rotas principais estão ok.';
});
