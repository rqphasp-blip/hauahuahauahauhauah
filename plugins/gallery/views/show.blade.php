@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Foto da Galeria</div>

                <div class="card-body text-center">
                    <img src="{{ asset($photo->image_path) }}" class="img-fluid mb-3" alt="Foto da galeria">
                    <p class="lead">{{ $photo->caption ?? 'Sem legenda' }}</p>
                    <p class="text-muted">Enviada por {{ $user->name ?? 'Usuário' }} em {{ \Carbon\Carbon::parse($photo->created_at)->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('gallery.index') }}" class="btn btn-primary">Voltar para a galeria</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection