<?php

// Rotas para o gerenciamento do banner de perfil
// Forma correta usando ::class





use App\Providers\plugins\googlereviews\GooglereviewsController;

// Rotas para o gerenciamento de avaliações do Google
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/googlereviews', [GooglereviewsController::class, 'index'])->name('googlereviews.index');
    Route::get('/googlereviews/create', [GooglereviewsController::class, 'create'])->name('googlereviews.create');
    Route::post('/googlereviews', [GooglereviewsController::class, 'store'])->name('googlereviews.store');
    Route::get('/googlereviews/{id}', [GooglereviewsController::class, 'show'])->name('googlereviews.show');
    Route::put('/googlereviews/{id}', [GooglereviewsController::class, 'update'])->name('googlereviews.update');
    Route::delete('/googlereviews/{id}', [GooglereviewsController::class, 'destroy'])->name('googlereviews.destroy');
    Route::get('/googlereviews/widget/{place_id}', [GooglereviewsController::class, 'widget'])->name('googlereviews.widget');
});






use plugins\LeadCapture\Http\Controllers\LeadCaptureController;
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



use App\Providers\plugins\stories\StoriesController;
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/stories', [StoriesController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [StoriesController::class, 'create'])->name('stories.create');
    Route::post('/stories', [StoriesController::class, 'store'])->name('stories.store');
    Route::get('/stories/{id}', [StoriesController::class, 'show'])->name('stories.show');
    Route::delete('/stories/{id}', [StoriesController::class, 'destroy'])->name('stories.destroy');
    Route::get('/user/{username}/stories', [StoriesController::class, 'userStories'])->name('stories.user');
});


use App\Providers\plugins\banner\ProfileBannerController;

Route::middleware(["web", "auth"])->group(function () {
    Route::get("/banner", [ProfileBannerController::class, "index"])->name("banner.index");
    Route::post("/banner", [ProfileBannerController::class, "store"])->name("banner.store");
    Route::delete("/banner", [ProfileBannerController::class, "destroy"])->name("banner.destroy");
});