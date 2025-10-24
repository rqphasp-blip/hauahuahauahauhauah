        {{-- Feature: Imagem de Topo --}}
        @include('features.imagem_topo', ['userinfo' => $info])

        {{-- Feature: Posição do Avatar (Este include pode ser melhor posicionado onde o HTML do avatar é renderizado, ex: heading.blade.php) --}}
        @include('features.avatar_position', ['userinfo' => $info])

        {{-- Feature: Stories (Este include e a lógica de clique no avatar podem ser melhor posicionados onde o HTML do avatar é renderizado) --}}
        @include('features.stories_avatar', ['userinfo' => $info])

        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding-bottom: 30px;}</style>
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein">@if(env('ALLOW_USER_HTML') === true){!! $info->littlelink_description !!}{!! $info->littlelink_taggogle !!} @else{{ $info->littlelink_description }}@endif</p></div></center>

        {{-- Feature: Google Maps Display --}}
        @include('features.google_maps_display', ['userinfo' => $info])

        {{-- Feature: Google Maps Reviews --}}
        @include('features.google_maps_reviews', ['userinfo' => $info])

        {{-- Feature: Instagram Feed --}}
        @include('features.instagram_feed', ['userinfo' => $info])

