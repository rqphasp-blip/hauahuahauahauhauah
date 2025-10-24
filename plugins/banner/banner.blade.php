@extends('layouts.sidebar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Gerenciar Banner de Perfil') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.banner.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="banner_image">{{ __('Imagem do Banner (JPG, PNG, GIF, WEBP - Máx 2MB)') }}</label>
                            <input id="banner_image" type="file" class="form-control @error('banner_image') is-invalid @enderror" name="banner_image" required>
                            <small class="form-text text-muted">{{ __('A imagem será exibida no topo do seu perfil público com altura máxima de 250px.') }}</small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Enviar Banner') }}
                            </button>
                        </div>
                    </form>

                    @if ($user->profile_banner_path)
                        <hr class="my-4">
                        <h5>{{ __('Banner Atual') }}</h5>
                        <div class="mt-3">
                            <img src="{{ url($user->profile_banner_path) }}" 
                                 alt="{{ __('Banner do Perfil') }}" 
                                 style="max-height: 250px; width: auto; border-radius: 8px; max-width: 100%;">
                        </div>
                        <form method="POST" action="{{ route('profile.banner.destroy') }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                {{ __('Remover Banner') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
