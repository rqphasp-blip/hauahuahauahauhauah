{{-- Feature: Chamada de API Google Maps (Exibição de Mapa) --}}
{{-- Controlado por: $userinfo->feature_maps_status --}}
{{-- Campos disponíveis: $userinfo->feature_maps_address, $userinfo->feature_maps_coordinates, $userinfo->feature_maps_zoom --}}

@if(isset($userinfo) && $userinfo->feature_maps_status)
    <style>
        .feature-google-maps-container iframe {
            border-radius: 16px;
            overflow: hidden;
        }

        .feature-google-maps-container {
            margin-bottom: 20px;
            border-radius: 16px;
            overflow: hidden;
        }
    </style>
    @php
        $mapsKey = env('GOOGLE_MAPS_API_KEY');
        $zoomLevel = $userinfo->feature_maps_zoom ?? 15;
    @endphp

    <div class="feature-google-maps-container">
        @if(!empty($userinfo->feature_maps_coordinates))
            {{-- Usar coordenadas se disponíveis --}}
            @php
                $coords = $userinfo->feature_maps_coordinates;
                $src = $mapsKey
                    ? "https://www.google.com/maps/embed/v1/view?key={$mapsKey}&center={$coords}&zoom={$zoomLevel}"
                    : "https://www.google.com/maps?q={$coords}&z={$zoomLevel}&output=embed";
            @endphp
            <iframe
                width="100%"
                height="300"
                style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="{{ $src }}">
            </iframe>
        @elseif(!empty($userinfo->feature_maps_address))
            {{-- Usar endereço se coordenadas não estiverem disponíveis --}}
            @php
                $address = $userinfo->feature_maps_address;
                $encodedAddress = urlencode($address);
                $src = $mapsKey
                    ? "https://www.google.com/maps/embed/v1/place?key={$mapsKey}&q={$encodedAddress}&zoom={$zoomLevel}"
                    : "https://www.google.com/maps?q={$encodedAddress}&z={$zoomLevel}&output=embed";
            @endphp
            <iframe
                width="100%"
                height="300"
                style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="{{ $src }}">
            </iframe>
        @else
            <p>Configuração do mapa incompleta (endereço ou coordenadas não fornecidos).</p>
        @endif
    </div>
    
@endif