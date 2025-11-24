@php
    $params = $link->type_params ?? [];
    if (is_string($params)) {
        $params = json_decode($params, true) ?? [];
    }

    $enabled = (bool) ($params['feature_maps_status'] ?? true);
    $coords = isset($params['feature_maps_coordinates']) ? trim((string) $params['feature_maps_coordinates']) : '';
    $address = isset($params['feature_maps_address']) ? trim((string) $params['feature_maps_address']) : '';
    $zoom = $params['feature_maps_zoom'] ?? 15;
    $zoom = is_numeric($zoom) ? max(1, min(20, (int) $zoom)) : 15;
    $apiKey = env('GOOGLE_MAPS_API_KEY');
@endphp

@if($enabled && ($coords !== '' || $address !== ''))
    @once('google-maps-profile-block-styles')
        <style>
            .ls-google-maps-block iframe {
                border: 0;
                border-radius: 16px;
                overflow: hidden;
                width: 100%;
                height: 300px;
                box-shadow: 0 10px 30px -18px rgba(0, 0, 0, 0.35);
            }

            .ls-google-maps-block {
                border-radius: 16px;
                overflow: hidden;
                margin-bottom: 1.25rem;
            }
        </style>
    @endonce

    <div class="ls-google-maps-block">
        @if(empty($apiKey))
            <p class="text-warning small mb-0">Defina <code>GOOGLE_MAPS_API_KEY</code> para exibir o mapa.</p>
        @else
            @if($coords !== '')
                <iframe
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps/embed/v1/view?key={{ $apiKey }}&center={{ $coords }}&zoom={{ $zoom }}">
                </iframe>
            @else
                <iframe
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps/embed/v1/place?key={{ $apiKey }}&q={{ urlencode($address) }}&zoom={{ $zoom }}">
                </iframe>
            @endif
        @endif
    </div>
@elseif($enabled)
    <p class="text-muted small mb-0">Configure um endereço ou coordenadas para mostrar o mapa.</p>
@endif