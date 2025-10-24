<?php
/**
 * Handler para remoção de banner de perfil
 * 
 * Este arquivo deve ser incluído em uma rota que processa a remoção do banner
 */

// Garantir que o usuário está autenticado
if (!auth()->check()) {
    return redirect()->route('login');
}

$user = auth()->user();

if ($user->profile_banner_path) {
    if (Storage::disk("public")->exists($user->profile_banner_path)) {
        Storage::disk("public")->delete($user->profile_banner_path);
    }
    $user->profile_banner_path = null;
    $user->save();
    return back()->with("success", "Banner do perfil removido com sucesso!");
}

return back()->with("error", "Nenhum banner para remover.");
?>
