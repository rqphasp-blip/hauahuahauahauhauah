<?php

use Illuminate\Support\Facades\Route;
use plugins\UserDashboardImage\Http\Controllers\DashboardImageController;

// Ensure the routes are only accessible to authenticated users
Route::middleware([config("linkstack.auth_middleware", "auth")])->group(function () {
    Route::post("/dashboard/image/upload", [DashboardImageController::class, "store"])->name("user.dashboard.image.upload");
});

