{{-- Feature: Imagem de Topo --}}
{{-- Controlado por: $userinfo->feature_imagem_topo_status --}}
{{-- Campos disponíveis: $userinfo->feature_imagem_topo_url --}}

@if(isset($userinfo) && $userinfo->feature_imagem_topo_status && !empty($userinfo->feature_imagem_topo_url))
    <div class="feature-imagem-topo-container" style="text-align: center; margin-bottom: 20px;">
        <img src="{{ asset("assets/img/topo_images/" . $userinfo->feature_imagem_topo_url) }}" alt="Imagem de Topo" style="max-width: 100%; height: auto; max-height: 300px; border-radius: 8px;">
    </div>
@endif

