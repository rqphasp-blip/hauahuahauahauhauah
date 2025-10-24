<?php
// Rotas para o gerenciamento do banner de perfil
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/perfil/banner', 'App\Http\Controllers\ProfileBannerController@index')->name('profile.banner');
    Route::post('/perfil/banner', 'App\Http\Controllers\ProfileBannerController@store')->name('profile.banner.store');
    Route::delete('/perfil/banner', 'App\Http\Controllers\ProfileBannerController@destroy')->name('profile.banner.destroy');
});
