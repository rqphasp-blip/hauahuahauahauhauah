@extends('layouts.sidebar')

@section('content')
<div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded">
                <div class="card-body">
                    <h3 class="mb-3"><i class="bi bi-geo-alt-fill"></i> Google Maps no Perfil</h3>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('googlemapsprofile.update') }}" method="POST" class="col-lg-8">
                        @csrf
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="feature_maps_status" name="feature_maps_status" value="1" {{ old('feature_maps_status', $user->feature_maps_status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="feature_maps_status">Ativar mapa no perfil</label>
                        </div>

                        <div class="mb-3">
                            <label for="feature_maps_address" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="feature_maps_address" name="feature_maps_address" value="{{ old('feature_maps_address', $user->feature_maps_address) }}" placeholder="Rua, número, cidade" autocomplete="street-address">
                            <small class="text-muted">Este endereço será usado para montar o iframe do Google Maps.</small>
                        </div>

                        <div class="mb-3">
                            <label for="feature_maps_coordinates" class="form-label">Coordenadas (opcional)</label>
                            <input type="text" class="form-control" id="feature_maps_coordinates" name="feature_maps_coordinates" value="{{ old('feature_maps_coordinates', $user->feature_maps_coordinates) }}" placeholder="-23.5505,-46.6333">
                            <small class="text-muted">Se informado, tem prioridade sobre o endereço.</small>
                        </div>

                        <div class="mb-3">
                            <label for="feature_maps_zoom" class="form-label">Zoom</label>
                            <input type="number" class="form-control" id="feature_maps_zoom" name="feature_maps_zoom" value="{{ old('feature_maps_zoom', $user->feature_maps_zoom ?? 15) }}" min="1" max="20">
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar mapa</button>
                    </form>

                    @if($user->feature_maps_status && ( $user->feature_maps_coordinates || $user->feature_maps_address))
                        <hr class="my-4">
                        <h5 class="mb-3">Pré-visualização</h5>
                        <div class="feature-google-maps-preview" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,0.1);">
                            @include('features.google_maps_display', ['userinfo' => $user])
                        </div>
                        <p class="text-muted mt-2 mb-0">Lembre-se de definir a variável <code>GOOGLE_MAPS_API_KEY</code> no arquivo <code>.env</code>.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection