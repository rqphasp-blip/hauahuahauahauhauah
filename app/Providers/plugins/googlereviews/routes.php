<?php

use Illuminate\Support\Facades\Route;

// Rotas do plugin Google Reviews
Route::middleware(['web', 'auth'])->prefix('googlereviews')->name('googlereviews.')->group(function () {
    Route::get('/', 'App\Providers\plugins\googlereviews\GooglereviewsController@index')->name('index');
    Route::get('/create', 'App\Providers\plugins\googlereviews\GooglereviewsController@create')->name('create');
    Route::post('/', 'App\Providers\plugins\googlereviews\GooglereviewsController@store')->name('store');
    Route::get('/{id}', 'App\Providers\plugins\googlereviews\GooglereviewsController@show')->name('show');
    Route::put('/{id}', 'App\Providers\plugins\googlereviews\GooglereviewsController@update')->name('update');
    Route::delete('/{id}', 'App\Providers\plugins\googlereviews\GooglereviewsController@destroy')->name('destroy');
    
    // Rota para o widget (sem autenticação)
    Route::get('/widget/{place_id}', 'App\Providers\plugins\googlereviews\GooglereviewsController@widget')->name('widget')->withoutMiddleware(['auth']);
    
    // Rotas para configuração da API
    Route::get('/config/settings', 'App\Providers\plugins\googlereviews\GooglereviewsController@config')->name('config');
    Route::post('/config/settings', 'App\Providers\plugins\googlereviews\GooglereviewsController@saveConfig')->name('saveconfig');
});
