<?php
/**
 * Include para exibir o banner do perfil do usuário
 * 
 * Este arquivo deve ser incluído na view do perfil público do usuário
 * para exibir o banner no topo da página
 */

// Verificar se o usuário tem um banner de perfil
if (isset($user) && $user->profile_banner_path) {
    echo '<div class="profile-banner" style="width: 100%; margin-bottom: 20px;">
        <img src="' . Storage::url($user->profile_banner_path) . '" 
             alt="Banner do perfil" 
             style="width: 100%; max-height: 250px; object-fit: cover; border-radius: 8px;">
    </div>';
}
?>
