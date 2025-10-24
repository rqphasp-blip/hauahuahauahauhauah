        {{-- Imagem de Topo --}}
        @php
            $topoImagePathBio = public_path("assets/img/topo_images/" . $info->imagem_topo);
            $topoImageUrlBio = asset("assets/img/topo_images/" . $info->imagem_topo);
        @endphp
        @if($info->imagem_topo && $info->topo_status && file_exists($topoImagePathBio))
            <center>
                <img src="{{ $topoImageUrlBio }}" alt="Imagem de Topo" style="max-width: 500px; width: 100%; height: auto; max-height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
            </center>
        @endif
        {{-- Fim Imagem de Topo --}}

        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding-bottom: 30px;}</style>
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein">@if(env('ALLOW_USER_HTML') === true){!! $info->littlelink_description !!}{!! $info->littlelink_taggogle !!} @else{{ $info->littlelink_description }}@endif</p></div></center>