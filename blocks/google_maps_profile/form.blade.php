@php
    $user = Auth::user();
    $defaultZoom = 15;
@endphp

<div class="mb-3">
    <label for="feature_maps_status" class="form-label">Exibir mapa</label>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="feature_maps_status" name="feature_maps_status" value="1" {{ ($feature_maps_status ?? $user->feature_maps_status ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="feature_maps_status">Ativar o bloco de mapa</label>
    </div>
</div>

<div class="mb-3">
    <label for="feature_maps_address" class="form-label">Endereço</label>
    <input
        type="text"
        class="form-control"
        id="feature_maps_address"
        name="feature_maps_address"
        value="{{ $feature_maps_address ?? $user->feature_maps_address ?? '' }}"
        placeholder="Rua, número, cidade"
        autocomplete="street-address"
    >
    <small class="text-muted">Se preferir, informe as coordenadas para maior precisão.</small>
</div>

<div class="mb-3">
    <label for="feature_maps_coordinates" class="form-label">Coordenadas (latitude,longitude)</label>
    <input
        type="text"
        class="form-control"
        id="feature_maps_coordinates"
        name="feature_maps_coordinates"
        value="{{ $feature_maps_coordinates ?? $user->feature_maps_coordinates ?? '' }}"
        placeholder="-23.5505,-46.6333"
    >
    <small class="text-muted">Quando preenchidas, têm prioridade sobre o endereço.</small>
</div>

<div class="mb-3">
    <label for="feature_maps_zoom" class="form-label">Zoom</label>
    <input
        type="number"
        class="form-control"
        id="feature_maps_zoom"
        name="feature_maps_zoom"
        value="{{ $feature_maps_zoom ?? $user->feature_maps_zoom ?? $defaultZoom }}"
        min="1"
        max="20"
    >
</div>

<p class="small text-muted mb-0">Este bloco usa a <strong>GOOGLE_MAPS_API_KEY</strong> definida no seu <code>.env</code>.</p>