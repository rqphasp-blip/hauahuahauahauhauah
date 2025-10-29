<?php

use Illuminate\Support\Facades\Route;
use plugins\contatos\ContatosController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/contatos', [ContatosController::class, 'index'])->name('contatos.index');
    Route::get('/contatos/campanhas/criar', [ContatosController::class, 'createCampaign'])->name('contatos.campaigns.create');
    Route::post('/contatos/campanhas', [ContatosController::class, 'storeCampaign'])->name('contatos.campaigns.store');
    Route::get('/contatos/campanhas/{campaign}', [ContatosController::class, 'showCampaign'])->name('contatos.campaigns.show');
    Route::post('/contatos/campanhas/{campaign}/leads', [ContatosController::class, 'storeLead'])->name('contatos.leads.store');
});

Route::middleware(['web'])->group(function () {
    Route::get('/contatos/formulario/{slug}', [ContatosController::class, 'publicForm'])->name('contatos.form');
    Route::post('/contatos/formulario/{slug}', [ContatosController::class, 'submitPublicLead'])->name('contatos.form.submit');
});