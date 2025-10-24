<?php
/**
 * Handler para upload de banner de perfil
 * 
 * Este arquivo deve ser incluído em uma rota que processa o upload do banner
 */

// Garantir que o usuário está autenticado
if (!auth()->check()) {
    return redirect()->route('login');
}

// Validar o arquivo enviado
$request = request();
$validator = validator($request->all(), [
    "banner_image" => "required|image|mimes:jpeg,png,jpg,gif,webp|max:2048", // Max 2MB
]);

if ($validator->fails()) {
    return back()
        ->withErrors($validator)
        ->withInput();
}

$user = auth()->user();

// Exclui o banner antigo se existir
if ($user->profile_banner_path && Storage::disk("public")->exists($user->profile_banner_path)) {
    Storage::disk("public")->delete($user->profile_banner_path);
}

$image = $request->file("banner_image");
$filename = uniqid("banner_", true) . "." . $image->getClientOriginalExtension();

// Armazena em public/storage/profile_banners
// Certifique-se de ter executado `php artisan storage:link`
$path = $image->storeAs("profile_banners", $filename, "public");

$user->profile_banner_path = $path;
$user->save();

return back()->with("success", "Banner do perfil atualizado com sucesso!");
?>
