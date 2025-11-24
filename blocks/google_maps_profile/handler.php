<?php

function handleLinkType($request, $linkType) {
    $rules = [
        'feature_maps_status' => ['nullable', 'boolean'],
        'feature_maps_address' => [
            'nullable',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($request) {
                $address = is_string($value) ? trim($value) : '';
                $coords = is_string($request->feature_maps_coordinates ?? '') ? trim($request->feature_maps_coordinates) : '';

                if ($address === '' && $coords === '') {
                    $fail('Informe um endereço ou coordenadas para o mapa.');
                }
            },
        ],
        'feature_maps_coordinates' => [
            'nullable',
            'string',
            'max:255',
            'regex:/^-?\d{1,3}\.\d+\s*,\s*-?\d{1,3}\.\d+$/',
        ],
        'feature_maps_zoom' => ['nullable', 'integer', 'between:1,20'],
    ];

    $address = is_string($request->feature_maps_address ?? '') ? trim($request->feature_maps_address) : '';
    $coords = is_string($request->feature_maps_coordinates ?? '') ? trim($request->feature_maps_coordinates) : '';
    $zoom = $request->feature_maps_zoom ?? 15;
    $zoom = is_numeric($zoom) ? max(1, min(20, (int) $zoom)) : 15;
    $enabled = (bool) $request->feature_maps_status;

    $linkData = [
        'title' => $address !== '' ? strip_tags($address) : 'Google Maps',
        'link' => '#',
        'feature_maps_status' => $enabled,
        'feature_maps_address' => $address,
        'feature_maps_coordinates' => $coords,
        'feature_maps_zoom' => $zoom,
        'button_id' => "93",
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}