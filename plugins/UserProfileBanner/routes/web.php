<?php

use Illuminate\Support\Facades\Route;
use Plugins\UserProfileBanner\Http\Controllers\UserProfileBannerController;

Route::middleware(["web", "auth"]) // Assumindo que o usuário precisa estar logado
    ->prefix("profile/banner") // Prefixo para as rotas do banner
    ->name("profile.banner.") // Prefixo para os nomes das rotas
    ->group(function () {
        Route::get("/upload", [UserProfileBannerController::class, "create"])->name("upload.form");
        Route::post("/upload", [UserProfileBannerController::class, "store"])->name("upload.store");
        Route::post("/remove", [UserProfileBannerController::class, "destroy"])->name("remove"); // Rota para remover o banner
    });

