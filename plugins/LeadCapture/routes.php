<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\LeadCapture\LeadCaptureController;

// Rotas para o gerenciamento de leads
Route::middleware(['web'])->group(function () {
    // Rotas públicas
    Route::get('/leads/form', [LeadCaptureController::class, 'showForm'])->name('leadcapture.form');
    Route::post('/leads/store', [LeadCaptureController::class, 'store'])->name('leadcapture.store');
    
    // Rotas administrativas
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/leads', [LeadCaptureController::class, 'index'])->name('leadcapture.index');
        Route::get('/admin/leads/{id}', [LeadCaptureController::class, 'show'])->name('leadcapture.show');
        Route::post('/admin/leads/{id}/status', [LeadCaptureController::class, 'updateStatus'])->name('leadcapture.update.status');
        Route::delete('/admin/leads/{id}', [LeadCaptureController::class, 'destroy'])->name('leadcapture.destroy');
        Route::get('/admin/leads/export', [LeadCaptureController::class, 'export'])->name('leadcapture.export');
    });
});
