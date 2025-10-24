<?php
/**
 * Rotas para o plugin de banner de perfil
 * 
 * Este arquivo deve ser incluído no seu arquivo de rotas web.php
 */

// Rota para exibir o formulário de upload (opcional, se você já incluiu o formulário em outra view)
Route::get('/perfil/banner', function () {
    return view('seu_diretorio.sua_view_com_formulario');
})->middleware('auth')->name('perfil.banner');

// Rota para processar o upload do banner
Route::post('/perfil/banner/upload', function () {
    include_once base_path('banner_plugin_simples/includes/banner_upload_handler.php');
})->middleware('auth')->name('perfil.banner.upload');

// Rota para remover o banner
Route::post('/perfil/banner/remover', function () {
    include_once base_path('banner_plugin_simples/includes/banner_remove_handler.php');
})->middleware('auth')->name('perfil.banner.remover');
