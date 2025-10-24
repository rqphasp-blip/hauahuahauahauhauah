<?php
/**
 * Código para exibir o banner no perfil público do usuário
 * 
 * Este arquivo deve ser incluído no topo da view do perfil público
 */

// Verifica se o usuário tem um banner de perfil
if (isset($user) && $user->profile_banner_path && file_exists(public_path($user->profile_banner_path))) {
    echo '<div class="profile-banner" style="width: 100%; margin-bottom: 20px;">
        <img src="' . url($user->profile_banner_path) . '" 
             alt="Banner do perfil" 
             style="width: 100%; max-height: 250px; object-fit: cover; border-radius: 8px;">
    </div>';
}
?>
