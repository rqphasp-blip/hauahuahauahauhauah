@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Foto da Galeria</span>
                    <a href="{{ route('gallery.index') }}" class="btn btn-primary">Voltar para a galeria</a>
                </div>

                <div class="card-body text-center">
                    <img src="{{ asset($photo->image_path) }}" alt="Foto da galeria" class="img-fluid mb-3">

                    <p><strong>Legenda:</strong> {{ $photo->caption ?? 'Sem legenda' }}</p>
                    <p><strong>Enviada por:</strong> {{ $user->name }}</p>
                    <p><strong>Enviada em:</strong> {{ \Carbon\Carbon::parse($photo->created_at)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection