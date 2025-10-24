{{-- Feature: Chamada de API Google Maps (Exibição de Mapa) --}}
{{-- Controlado por: $userinfo->feature_maps_status --}}
{{-- Campos disponíveis: $userinfo->feature_maps_address, $userinfo->feature_maps_coordinates, $userinfo->feature_maps_zoom --}}

@if(isset($userinfo) && $userinfo->feature_maps_status)
    <div class="feature-google-maps-container" style="margin-bottom: 20px;">
        @if(!empty($userinfo->feature_maps_coordinates))
            {{-- Usar coordenadas se disponíveis --}}
            <iframe
                width="100%"
                height="300"
                style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed/v1/view?key={{ env("GOOGLE_MAPS_API_KEY") }}&center={{ $userinfo->feature_maps_coordinates }}&zoom={{ $userinfo->feature_maps_zoom ?? 15 }}">
            </iframe>
        @elseif(!empty($userinfo->feature_maps_address))
            {{-- Usar endereço se coordenadas não estiverem disponíveis --}}
            <iframe
                width="100%"
                height="300"
                style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed/v1/place?key={{ env("GOOGLE_MAPS_API_KEY") }}&q={{ urlencode($userinfo->feature_maps_address) }}&zoom={{ $userinfo->feature_maps_zoom ?? 15 }}">
            </iframe>
        @else
            <p>Configuração do mapa incompleta (endereço ou coordenadas não fornecidos).</p>
        @endif
    </div>
    {{-- Nota: GOOGLE_MAPS_API_KEY deve estar configurada no seu arquivo .env --}}
@endif

