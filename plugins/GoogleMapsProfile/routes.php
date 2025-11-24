<?php

use Illuminate\Support\Facades\Route;
use plugins\GoogleMapsProfile\Http\Controllers\GoogleMapsProfileController;

Route::middleware([config('linkstack.auth_middleware', 'auth')])->group(function () {
    Route::get('/studio/maps', [GoogleMapsProfileController::class, 'edit'])->name('googlemapsprofile.edit');
    Route::post('/studio/maps', [GoogleMapsProfileController::class, 'update'])->name('googlemapsprofile.update');
});