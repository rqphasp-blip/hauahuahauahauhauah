{{-- Feature: Posição do Avatar --}}
{{-- Controlado por: $userinfo->feature_avatar_position_status --}}
{{-- Campos disponíveis: $userinfo->feature_avatar_align, $userinfo->feature_avatar_shape --}}

{{-- Lógica para exibir/modificar a posição e forma do avatar --}}
{{-- Exemplo: Pode ser CSS injetado ou classes condicionais no elemento do avatar --}}
@if(isset($userinfo) && $userinfo->feature_avatar_position_status)
    <script>
        // Exemplo de como poderia ser aplicado via JS, idealmente seria via classes CSS no elemento do avatar
        // document.addEventListener("DOMContentLoaded", function() {
        //     const avatarElement = document.querySelector(".profile-avatar-image"); // Seletor do avatar
        //     if (avatarElement) {
        //         avatarElement.style.textAlign = "{{ $userinfo->feature_avatar_align ?? 'center' }}";
        //         if ("{{ $userinfo->feature_avatar_shape }}" === "circle") {
        //             avatarElement.style.borderRadius = "50%";
        //         } else if ("{{ $userinfo->feature_avatar_shape }}" === "square") {
        //             avatarElement.style.borderRadius = "0";
        //         } else {
        //             avatarElement.style.borderRadius = "8px"; // default rounded
        //         }
        //     }
        // });
    </script>
    <!-- Ou, preferencialmente, adicionar classes CSS que são definidas em seu arquivo CSS principal -->
@endif

